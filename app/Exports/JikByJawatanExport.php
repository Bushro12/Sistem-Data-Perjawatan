<?php

namespace App\Exports;

use App\Models\Gred;
use App\Models\Jawatan;
use App\Models\WaranJawatan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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
 *   Rows 4-5 : BIL | PTJ | <J/I/K per gred combination> | JUMLAH KESELURUHAN (J/I/K)
 *   Rows 6+  : per program: program header, PTJ rows, JUMLAH PROGRAM subtotal
 *   Last     : blank row, then JUMLAH KESELURUHAN grand total
 *
 * Columns come from distinct gred_ids sets on waran_jawatan rows that include
 * the selected jawatan. A single-gred waran (N4) becomes one column; a
 * multi-gred waran (N1/N2/N3) becomes one column labelled N1/N2/N3.
 *
 * J (perjawatan)  = waran_jawatans counted in the column matching their
 *                   exact gred_ids set.
 * I (isi)         = the same rows that are filled (pegawai is_tetap /
 *                   is_kontrak_interim), mirroring DataKeseluruhanExport.
 * K (kosong)      = J - I for every gred column (including multi-gred).
 * JUMLAH KESELURUHAN columns = sum across all gred columns of that row.
 */
class JikByJawatanExport implements FromCollection, WithEvents, WithStrictNullComparison
{
    protected int $jawatan_id;

    /** All rows (1-indexed positions start at A1). */
    public array $rows = [];

    /**
     * Ordered gred columns from waran_jawatan combinations.
     *
     * @var list<array{key: string, label: string, gred_ids: list<int>}>
     */
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

        $jawatan = Jawatan::findOrFail($this->jawatan_id);

        $warans = WaranJawatan::with(['ptj', 'aktiviti.program', 'pegawai'])
            ->whereJsonContains('jawatan_ids', $this->jawatan_id)
            ->orderBy('ptj_id')
            ->get();

        $this->greds = $this->buildGredColumns($warans);
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
            $header4[] = strtoupper($jawatan->desc_jawatan.' '.$gred['label']);
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
     * Distinct gred_ids combinations from waran_jawatan, ordered by highest
     * gred number descending (same visual order as the previous single-gred
     * columns). Multi-gred labels use ascending kod order (e.g. N1/N2/N3).
     *
     * @return list<array{key: string, label: string, gred_ids: list<int>}>
     */
    protected function buildGredColumns(Collection $warans): array
    {
        $combinations = [];

        foreach ($warans as $waran) {
            $ids = $this->normalizedGredIds($waran->gred_ids ?? []);
            if ($ids === []) {
                continue;
            }

            $key = implode(',', $ids);
            $combinations[$key] = $ids;
        }

        if ($combinations === []) {
            return [];
        }

        $gredById = Gred::query()
            ->whereIn('id', collect($combinations)->flatten()->unique()->all())
            ->get()
            ->keyBy('id');

        $columns = [];

        foreach ($combinations as $key => $ids) {
            $greds = collect($ids)
                ->map(fn (int $id) => $gredById->get($id))
                ->filter();

            if ($greds->isEmpty()) {
                continue;
            }

            $label = $greds
                ->sortBy(fn (Gred $gred) => (int) substr($gred->kod_gred, 1))
                ->pluck('kod_gred')
                ->implode('/');

            $columns[] = [
                // Cast: PHP stores numeric string array keys as ints (e.g. "1" → 1).
                'key' => (string) $key,
                'label' => $label,
                'gred_ids' => $ids,
                'sort' => $greds->max(fn (Gred $gred) => (int) substr($gred->kod_gred, 1)),
            ];
        }

        return collect($columns)
            ->sortByDesc('sort')
            ->map(fn (array $column) => [
                'key' => $column['key'],
                'label' => $column['label'],
                'gred_ids' => $column['gred_ids'],
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<int|string>|null  $gredIds
     * @return list<int>
     */
    protected function normalizedGredIds(?array $gredIds): array
    {
        $ids = array_values(array_unique(array_map('intval', $gredIds ?? [])));
        sort($ids);

        return array_values(array_filter($ids, fn (int $id) => $id > 0));
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
     * Each post counts exactly once, in the column matching its gred_ids set.
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

            $filled = $waran->pegawai
                && ($waran->pegawai->is_tetap || $waran->pegawai->is_kontrak_interim);

            $base = $index * 3;
            $isi = $filled ? 1 : 0;

            $counts[$base] += 1;       // J
            $counts[$base + 1] += $isi; // I
            $counts[$base + 2] += 1 - $isi; // K = J - I
            $counts[$totalBase] += 1;
            $counts[$totalBase + 1] += $isi;
            $counts[$totalBase + 2] += 1 - $isi;
        }

        return $counts;
    }

    /**
     * Index (into $this->greds) of the column whose gred_ids set matches the
     * waran's exactly. Returns null when the waran has no usable greds.
     */
    protected function columnIndexFor($waran): ?int
    {
        $key = implode(',', $this->normalizedGredIds($waran->gred_ids ?? []));

        if ($key === '') {
            return null;
        }

        foreach ($this->greds as $index => $gred) {
            if ((string) $gred['key'] === $key) {
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
