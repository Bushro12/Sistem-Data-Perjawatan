<?php

namespace App\Exports;

use App\Models\Jawatan;
use App\Models\WaranJawatan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Laporan JIK (Jawatan / Isi / Kosong) mengikut Jawatan.
 *
 * Structure (matches the approved Excel layout):
 *   Row 1 : MAKLUMAT PERJAWATAN : <jawatan>
 *   Row 2 : DATA SEHINGGA        : <tarikh>
 *   Row 3 : blank
 *   Rows 4-5 : BIL | PTJ | <J/I/K per gred jawatan> | JUMLAH KESELURUHAN (J/I/K)
 *   Rows 6+  : per program: program header, PTJ rows, JUMLAH PROGRAM subtotal
 *   Last     : blank row, then JUMLAH KESELURUHAN grand total
 *
 * J (perjawatan)  = waran_jawatans where jawatan_ids contains the selected
 *                   jawatan. Each post is counted exactly once, in one gred
 *                   column: a single-gred waran in its own gred; a waran
 *                   spanning multiple greds in the gred of its assigned
 *                   pegawai (when that gred is inside the waran's range),
 *                   otherwise in the waran's lowest gred.
 * I (isi)         = the same rows that are filled (pegawai is_tetap /
 *                   is_kontrak_interim), mirroring DataKeseluruhanExport.
 * K (kosong)      = J - I for single-gred warans. A multi-gred post is never
 *                   kosong (K stays 0) - the post is filled at whatever gred
 *                   the pegawai holds, or awaits a pegawai at any gred.
 * JUMLAH KESELURUHAN columns = sum across all gred columns of that row.
 */
class JikByJawatanExport implements FromCollection, WithEvents, WithStrictNullComparison
{
    protected int $jawatan_id;

    /** All rows (1-indexed positions start at A1). */
    public array $rows = [];

    /** Ordered greds of the selected jawatan (one J/I/K column group each). */
    public array $greds = [];

    /** Row numbers of program header rows. */
    public array $programRows = [];

    /** Row numbers of PTJ data rows. */
    public array $ptjRows = [];

    /** Row numbers of JUMLAH PROGRAM subtotal rows. */
    public array $jumlahRows = [];

    /** Row number of the JUMLAH KESELURUHAN grand total row. */
    public int $totalRow = 0;

    /** Last column index (1-based) of the widest row. */
    public int $lastColumnIndex = 0;

    public function __construct($jawatan_id)
    {
        $this->jawatan_id = (int) $jawatan_id;
    }

    public function collection()
    {
        Carbon::setLocale('ms');
        $today = strtoupper(Carbon::now()->translatedFormat('d F Y'));

        $jawatan = Jawatan::with(['greds' => function ($query) {
            $query->orderByRaw('CAST(SUBSTRING(kod_gred, 2) AS UNSIGNED) DESC');
        }])->findOrFail($this->jawatan_id);

        $this->greds = $jawatan->greds->values()->all();

        $warans = WaranJawatan::with(['ptj', 'aktiviti.program', 'pegawai.jawatan_gred.gred'])
            ->whereJsonContains('jawatan_ids', $this->jawatan_id)
            ->orderBy('ptj_id')
            ->get();

        $this->lastColumnIndex = 2 + (count($this->greds) * 3) + 3;

        $rows = collect();

        // Row 1 – title
        $rows->push(['MAKLUMAT PERJAWATAN', '', ':', $jawatan->desc_jawatan, '']);

        // Row 2 – date
        $rows->push(['DATA SEHINGGA', '', ':', $today, '']);

        // Row 3 – blank
        $rows->push(array_fill(0, $this->lastColumnIndex, ''));

        // Row 4 – column group headers
        $header4 = ['BIL', 'PTJ'];
        foreach ($this->greds as $gred) {
            $header4[] = strtoupper($jawatan->desc_jawatan.' '.$gred->kod_gred);
            $header4[] = '';
            $header4[] = '';
        }
        $header4[] = 'JUMLAH KESELURUHAN';
        $header4[] = '';
        $header4[] = '';
        $rows->push($header4);

        // Row 5 – J / I / K sub-headers
        $header5 = ['', ''];
        foreach ($this->greds as $gred) {
            $header5[] = 'J';
            $header5[] = 'I';
            $header5[] = 'K';
        }
        $header5[] = 'J';
        $header5[] = 'I';
        $header5[] = 'K';
        $rows->push($header5);

        $programs = $warans
            ->groupBy(fn ($w) => $w->aktiviti?->program?->nama_program ?: 'TANPA PROGRAM')
            ->sortKeys(SORT_NATURAL);

        $grand = $this->emptyCounts();

        foreach ($programs as $programName => $items) {
            $program = $items->first()->aktiviti?->program;

            $namaProgram = $program && $programName !== 'TANPA PROGRAM'
                ? "{$program->nama_program} : {$program->desc_program}"
                : 'TANPA PROGRAM';

            // Program header row (merged across all columns)
            $row = array_fill(0, $this->lastColumnIndex, '');
            $row[0] = $namaProgram;
            $rows->push($row);
            $this->programRows[] = $rows->count();

            // PTJ rows
            $ptjs = $items->groupBy('ptj_id');
            $bil = 0;
            $programCounts = $this->emptyCounts();

            foreach ($ptjs as $ptjItems) {
                $bil++;
                $ptj = $ptjItems->first()->ptj;
                $counts = $this->countsFor($ptjItems);

                $row = array_fill(0, $this->lastColumnIndex, '');
                $row[0] = $bil;
                $row[1] = $ptj?->nama_ptj ?? '-';
                $this->fillCounts($row, $counts);

                $rows->push($row);
                $this->ptjRows[] = $rows->count();

                $this->addCounts($programCounts, $counts);
                $this->addCounts($grand, $counts);
            }

            // JUMLAH PROGRAM subtotal row (A:B merged)
            $row = array_fill(0, $this->lastColumnIndex, '');
            $row[0] = 'JUMLAH '.$programName;
            $this->fillCounts($row, $programCounts);
            $rows->push($row);
            $this->jumlahRows[] = $rows->count();
        }

        // Blank row, then grand total row
        $rows->push(array_fill(0, $this->lastColumnIndex, ''));

        $row = array_fill(0, $this->lastColumnIndex, '');
        $row[0] = 'JUMLAH KESELURUHAN';
        $this->fillCounts($row, $grand);
        $rows->push($row);
        $this->totalRow = $rows->count();

        $this->rows = $rows->toArray();

        return $rows;
    }

    /**
     * Flat count array: 3 slots per gred column (J/I/K) + 3 for the total.
     */
    protected function emptyCounts(): array
    {
        return array_fill(0, (count($this->greds) * 3) + 3, 0);
    }

    /**
     * Tally J/I/K for a group of waran_jawatans against each gred column.
     * Each post counts exactly once, in the column chosen by columnIndexFor().
     */
    protected function countsFor($items): array
    {
        $counts = $this->emptyCounts();
        $totalBase = count($this->greds) * 3;

        foreach ($items as $waran) {
            $index = $this->columnIndexFor($waran);
            if ($index === null) {
                continue;
            }

            $multiGred = count($waran->gred_ids ?? []) > 1;
            $filled = $waran->pegawai
                && ($waran->pegawai->is_tetap || $waran->pegawai->is_kontrak_interim);

            $base = $index * 3;

            $counts[$base] += 1;                            // J
            $counts[$base + 1] += $filled ? 1 : 0;          // I
            $counts[$base + 2] += ($multiGred || $filled) ? 0 : 1; // K
            $counts[$totalBase] += 1;
            $counts[$totalBase + 1] += $filled ? 1 : 0;
            $counts[$totalBase + 2] += ($multiGred || $filled) ? 0 : 1;
        }

        return $counts;
    }

    /**
     * Index (into $this->greds) of the single column where a waran's post is
     * counted. A single-gred waran counts in its only gred. A multi-gred waran
     * counts in the pegawai's own gred when assigned and inside the range;
     * otherwise (no pegawai, or a pegawai whose gred is outside the range) it
     * counts in the range's lowest gred. Returns null when no report column
     * matches.
     */
    protected function columnIndexFor($waran): ?int
    {
        $gredIds = array_map('intval', $waran->gred_ids ?? []);

        if (count($gredIds) === 1) {
            foreach ($this->greds as $index => $gred) {
                if ((int) $gred->id === $gredIds[0]) {
                    return $index;
                }
            }

            return null;
        }

        // Pegawai's own gred decides the column when it is inside the range.
        $pegawaiGred = (int) ($waran->pegawai?->jawatan_gred?->gred_id ?? 0);
        if ($pegawaiGred && in_array($pegawaiGred, $gredIds, true)) {
            foreach ($this->greds as $index => $gred) {
                if ((int) $gred->id === $pegawaiGred) {
                    return $index;
                }
            }
        }

        // Lowest gred of the range: $this->greds is ordered DESC by gred number
        // (U12, U10, ..., U5), so the last matching column is the lowest.
        for ($index = count($this->greds) - 1; $index >= 0; $index--) {
            if (in_array((int) $this->greds[$index]->id, $gredIds, true)) {
                return $index;
            }
        }

        return null;
    }

    protected function fillCounts(array &$row, array $counts): void
    {
        foreach ($counts as $index => $value) {
            $row[2 + $index] = $value;
        }
    }

    protected function addCounts(array &$target, array $source): void
    {
        foreach ($source as $index => $value) {
            $target[$index] += $value;
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $lastCol = Coordinate::stringFromColumnIndex($this->lastColumnIndex);
                $totalGroupStart = Coordinate::stringFromColumnIndex(2 + (count($this->greds) * 3) + 1);

                $this->styleTitle($sheet);
                $this->styleHeader($sheet, $lastCol);

                foreach ($this->programRows as $row) {
                    $this->styleProgramRow($sheet, $row, $lastCol);
                }

                foreach ($this->ptjRows as $row) {
                    $this->stylePtjRow($sheet, $row, $lastCol);
                }

                foreach ($this->jumlahRows as $row) {
                    $this->styleSubtotalRow($sheet, $row, $lastCol);
                }

                $this->styleTotalRow($sheet, $this->totalRow, $lastCol);
            },
        ];
    }

    protected function styleTitle($sheet): void
    {
        $sheet->mergeCells('A1:B1');
        $sheet->mergeCells('D1:E1');
        $sheet->mergeCells('A2:B2');
        $sheet->mergeCells('D2:E2');

        $sheet->getStyle('A1:E2')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'size' => 13,
                'color' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(24);

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(32);

        for ($column = 3; $column <= $this->lastColumnIndex; $column++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setWidth(13);
        }
    }

    protected function styleHeader($sheet, string $lastCol): void
    {
        // BIL / PTJ span rows 4-5
        $sheet->mergeCells('A4:A5');
        $sheet->mergeCells('B4:B5');

        // Each gred group header spans its 3 columns on row 4
        $column = 3;
        foreach ($this->greds as $gred) {
            $start = Coordinate::stringFromColumnIndex($column);
            $end = Coordinate::stringFromColumnIndex($column + 2);
            $sheet->mergeCells("{$start}4:{$end}4");
            $column += 3;
        }

        // JUMLAH KESELURUHAN group header
        $totalStart = Coordinate::stringFromColumnIndex($column);
        $sheet->mergeCells("{$totalStart}4:{$lastCol}4");

        $sheet->getStyle("A4:{$lastCol}5")->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '7F7F7F'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // JUMLAH KESELURUHAN header is medium blue (distinct from gred groups)
        $sheet->getStyle("{$totalStart}4:{$lastCol}5")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '9BC0E2'],
            ],
        ]);

        $sheet->getRowDimension(4)->setRowHeight(28);
        $sheet->getRowDimension(5)->setRowHeight(28);
    }

    protected function styleProgramRow($sheet, int $row, string $lastCol): void
    {
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");

        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '7F7F7F'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $sheet->getRowDimension($row)->setRowHeight(26);
    }

    protected function stylePtjRow($sheet, int $row, string $lastCol): void
    {
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Count cells share a light grey background
        $sheet->getStyle("C{$row}:{$lastCol}{$row}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F2F2F2'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle("A{$row}")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension($row)->setRowHeight(22);
    }

    protected function styleSubtotalRow($sheet, int $row, string $lastCol): void
    {
        $sheet->mergeCells("A{$row}:B{$row}");

        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'BFBFBF'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $sheet->getRowDimension($row)->setRowHeight(26);
    }

    protected function styleTotalRow($sheet, int $row, string $lastCol): void
    {
        $sheet->mergeCells("A{$row}:B{$row}");

        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '9BC0E2'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $sheet->getRowDimension($row)->setRowHeight(26);
    }
}
