<?php

namespace App\Exports;

use App\Models\Gred;
use App\Models\Jawatan;
use App\Models\Jawatan_Gred;
use App\Models\Pegawai;
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
 * Data Perjawatan Kontrak.
 *
 * Structure (matches the approved Excel layout):
 *   Row 1 : DATA PERJAWATAN KONTRAK JKN KEDAH SEHINGGA <tarikh> (title, merged)
 *   Row 2 : BIL | PUSAT TANGGUNGJAWAB | JUMLAH JAWATAN | <satu lajur per jawatan/gred>
 *   Rows 3+ : per program: section header, unit (PTJ / bahagian JKN) rows,
 *             JUMLAH subtotal row; then a blank row and JUMLAH KESELURUHAN
 *
 * Hanya pegawai dengan is_kontrak = 1 dikira. JUMLAH JAWATAN = jumlah pegawai
 * kontrak di unit tersebut (merentasi semua jawatan, termasuk yang tiada dalam
 * senarai lajur). Lajur template adalah senarai tetap yang diluluskan; setiap
 * lajur dipadankan dengan rekod jawatan/gred dalam pangkalan data (gred
 * dipadankan melalui desc_gred atau kod_gred, cth. desc "U41" = kod "U9").
 * Lajur yang tidak dapat dipadankan kekal dipaparkan dengan kiraan 0.
 */
class DataKontrakExport implements FromCollection, WithEvents, WithStrictNullComparison
{
    /**
     * Senarai lajur tetap (label seperti dalam templat yang diluluskan).
     * 'jawatan' dipadankan dengan desc_jawatan (normalized exact, kemudian
     * contains). 'gred' dipadankan dengan desc_gred/kod_gred. Lajur terakhir
     * adalah "catch-all": semua pegawai kontrak bergred U41 yang jawatannya
     * belum dikira dalam mana-mana lajur bernama di atas.
     */
    protected array $template = [
        ['label' => 'PEGAWAI PERUBATAN UD43', 'jawatan' => 'PEGAWAI PERUBATAN', 'gred' => 'UD43'],
        ['label' => 'PEGAWAI PERUBATAN UD41', 'jawatan' => 'PEGAWAI PERUBATAN', 'gred' => 'UD41'],
        ['label' => 'PEGAWAI FARMASI UF48', 'jawatan' => 'PEGAWAI FARMASI', 'gred' => 'UF48'],
        ['label' => 'PEGAWAI SAINS MIKROBIOLOGI C41', 'jawatan' => 'PEGAWAI SAINS (MIKROBIOLOGI)', 'gred' => 'C41'],
        ['label' => 'PEGAWAI PSIKOLOGI S41', 'jawatan' => 'PEGAWAI PSIKOLOGI', 'gred' => 'S41'],
        ['label' => 'PKP U41', 'jawatan' => 'PEGAWAI KESIHATAN PERSEKITARAN', 'gred' => 'U41'],
        ['label' => 'PPP U41', 'jawatan' => 'PENOLONG PEGAWAI PERUBATAN', 'gred' => 'U41'],
        ['label' => 'JTMP U29', 'jawatan' => 'JURUTEKNOLOGI MAKMAL PERUBATAN', 'gred' => 'U29'],
        ['label' => 'FISIOTERAPI U41', 'jawatan' => 'PEGAWAI PEMULIHAN PERUBATAN (FISIOTERAPI)', 'gred' => 'U41'],
        ['label' => 'JURURAWAT U41', 'jawatan' => 'JURURAWAT', 'gred' => 'U41'],
        ['label' => 'PPPK U29', 'jawatan' => 'PENOLONG PEGAWAI KESIHATAN PERSEKITARAN', 'gred' => 'U29'],
        ['label' => 'JURU XRAY U41', 'jawatan' => 'JURU X-RAY', 'gred' => 'U41'],
        ['label' => 'PENOLONG JURUTERA JA29', 'jawatan' => 'PENOLONG JURUTERA', 'gred' => 'JA29'],
        ['label' => 'PEGAWAI PERGIGIAN UG41', 'jawatan' => 'PEGAWAI PERGIGIAN', 'gred' => 'UG41'],
        ['label' => 'JURUTEKNOLOGI PERGIGIAN U41', 'jawatan' => 'JURUTEKNOLOGI PERGIGIAN', 'gred' => 'U41'],
        ['label' => 'U41', 'jawatan' => null, 'gred' => 'U41', 'catchall' => true],
    ];

    /** Resolved columns: label + jawatan_gred_id (or null) + catchall flag. */
    public array $columns = [];

    /** All rows (1-indexed positions start at A1). */
    public array $rows = [];

    /** Row numbers of program section header rows. */
    public array $sectionRows = [];

    /** Row numbers of unit (PTJ/bahagian) data rows. */
    public array $dataRows = [];

    /** Row numbers of JUMLAH subtotal rows. */
    public array $jumlahRows = [];

    /** Row number of the JUMLAH KESELURUHAN row. */
    public int $totalRow = 0;

    /** Last column index (1-based). */
    public int $lastColumnIndex = 0;

    public function collection()
    {
        Carbon::setLocale('ms');
        $today = strtoupper(Carbon::now()->translatedFormat('d F Y'));

        $this->lastColumnIndex = 3 + count($this->template);

        $this->resolveTemplate();

        $units = $this->buildUnits();
        $counts = $this->countKontrak($units);

        $rows = collect();

        // Row 1 – title
        $row = array_fill(0, $this->lastColumnIndex, '');
        $row[0] = "DATA PERJAWATAN KONTRAK JKN KEDAH SEHINGGA {$today}";
        $rows->push($row);

        // Row 2 – header
        $header = ['BIL', 'PUSAT TANGGUNGJAWAB', 'JUMLAH JAWATAN'];
        foreach ($this->columns as $column) {
            $header[] = $column['label'];
        }
        $rows->push($header);

        // Group units by program (sorted by program id, TANPA PROGRAM last)
        $groups = $units->groupBy(fn ($unit) => $unit['programId'] ?? PHP_INT_MAX)
            ->sortKeys()
            ->map(function ($items) {
                return $items->sortBy('label')->values();
            });

        $grand = $this->emptyCounts();

        foreach ($groups as $programId => $items) {
            $first = $items->first();

            // Section header row (merged across all columns)
            $row = array_fill(0, $this->lastColumnIndex, '');
            $row[0] = $first['programLabel'];
            $rows->push($row);
            $this->sectionRows[] = $rows->count();

            $programCounts = $this->emptyCounts();
            $bil = 0;

            foreach ($items as $unit) {
                $bil++;
                $unitCounts = $counts[$unit['key']] ?? $this->emptyCounts();

                $row = array_fill(0, $this->lastColumnIndex, '');
                $row[0] = $bil;
                $row[1] = $unit['label'];
                $row[2] = $unitCounts['total'];
                for ($i = 0; $i < count($this->columns); $i++) {
                    $row[3 + $i] = $unitCounts['cols'][$i];
                }

                $rows->push($row);
                $this->dataRows[] = $rows->count();

                $this->addCounts($programCounts, $unitCounts);
                $this->addCounts($grand, $unitCounts);
            }

            // JUMLAH subtotal row (A:B merged)
            $row = array_fill(0, $this->lastColumnIndex, '');
            $row[0] = 'JUMLAH';
            $row[2] = $programCounts['total'];
            for ($i = 0; $i < count($this->columns); $i++) {
                $row[3 + $i] = $programCounts['cols'][$i];
            }
            $rows->push($row);
            $this->jumlahRows[] = $rows->count();
        }

        // Blank row, then grand total row
        $rows->push(array_fill(0, $this->lastColumnIndex, ''));

        $row = array_fill(0, $this->lastColumnIndex, '');
        $row[0] = 'JUMLAH KESELURUHAN';
        $row[2] = $grand['total'];
        for ($i = 0; $i < count($this->columns); $i++) {
            $row[3 + $i] = $grand['cols'][$i];
        }
        $rows->push($row);
        $this->totalRow = $rows->count();

        $this->rows = $rows->toArray();

        return $rows;
    }

    /**
     * Resolve each template column to a jawatan__greds pivot id.
     */
    protected function resolveTemplate(): void
    {
        $jawatans = Jawatan::all();
        $greds = Gred::all();

        $gredIdByKey = [];
        foreach ($greds as $gred) {
            $gredIdByKey[$this->norm($gred->kod_gred)] = $gred->id;
            $gredIdByKey[$this->norm($gred->desc_gred)] = $gred->id;
        }

        $namedJgIds = [];
        foreach ($this->template as $entry) {
            $jawatanIds = [];
            $gredId = $entry['gred'] ? ($gredIdByKey[$this->norm($entry['gred'])] ?? null) : null;

            if ($entry['jawatan']) {
                $needle = $this->norm($entry['jawatan']);
                foreach ($jawatans as $jawatan) {
                    $haystack = $this->norm($jawatan->desc_jawatan);
                    if ($haystack === $needle || str_contains($haystack, $needle)) {
                        $jawatanIds[] = $jawatan->id;
                    }
                }
            }

            $jgId = null;
            if ($jawatanIds && $gredId) {
                $jg = Jawatan_Gred::whereIn('jawatan_id', $jawatanIds)
                    ->where('gred_id', $gredId)
                    ->first();
                $jgId = $jg?->id;
            }

            $this->columns[] = [
                'label' => $entry['label'],
                'jawatanGredId' => $jgId,
                'catchall' => ! empty($entry['catchall']),
            ];

            if ($jgId) {
                $namedJgIds[] = $jgId;
            }
        }

        // Remember the U41 gred + named jawatan_gred ids for the catch-all column
        $this->u41GredId = $gredIdByKey['U41'] ?? null;
        $this->namedJgIds = $namedJgIds;
    }

    protected ?int $u41GredId = null;

    protected array $namedJgIds = [];

    protected function norm(string $value): string
    {
        return str_replace('-', ' ', mb_strtoupper(trim(preg_replace('/\s+/', ' ', $value) ?? '')));
    }

    /**
     * Org units from waran_jawatans (plus any unit that has kontrak pegawai).
     * JKN KEDAH units are per bahagian; other PTJs are per PTJ.
     */
    protected function buildUnits()
    {
        $units = collect();

        $warans = WaranJawatan::with(['ptj', 'bahagian', 'aktiviti.program'])->get();
        foreach ($warans as $waran) {
            $unit = $this->unitFor($waran->ptj, $waran->bahagian);
            if ($unit && ! $units->has($unit['key'])) {
                $unit['programId'] = $waran->aktiviti?->program?->id;
                $unit['programLabel'] = $this->programLabel($waran->aktiviti?->program);
                $units->put($unit['key'], $unit);
            }
        }

        return $units;
    }

    protected function isJkn($ptj): bool
    {
        return $ptj && ($ptj->is_jkn || $ptj->nama_ptj === 'JABATAN KESIHATAN NEGERI KEDAH');
    }

    protected function unitFor($ptj, $bahagian): ?array
    {
        if (! $ptj) {
            return null;
        }

        $isJkn = $this->isJkn($ptj);
        $key = $isJkn ? $ptj->id.':'.($bahagian?->id ?? '') : 'p'.$ptj->id;
        $label = $isJkn
            ? ($bahagian?->nama_bahagian ?? $ptj->nama_ptj)
            : $ptj->nama_ptj;

        return [
            'key' => $key,
            'label' => $label,
            'programId' => null,
            'programLabel' => 'TANPA PROGRAM',
        ];
    }

    protected function programLabel($program): string
    {
        if (! $program) {
            return 'TANPA PROGRAM';
        }

        // Same convention as DataKeseluruhanExport: PROGRAM 1 = ibu pejabat JKN
        if ($program->nama_program === 'PROGRAM 1') {
            return 'IBU PEJABAT JKN';
        }

        return "{$program->nama_program} : {$program->desc_program}";
    }

    /**
     * Count kontrak pegawai (is_kontrak = 1) per unit, per resolved column.
     */
    protected function countKontrak($units)
    {
        $counts = [];

        $pegawai = Pegawai::with(['ptj', 'bahagian'])->where('is_kontrak', 1)->get();

        // Map jawatan_gred_id -> gred_id for the catch-all column
        $jgGredId = [];
        if ($this->u41GredId) {
            foreach (Jawatan_Gred::pluck('gred_id', 'id') as $jgId => $gredId) {
                $jgGredId[$jgId] = $gredId;
            }
        }

        foreach ($pegawai as $p) {
            $unit = $this->unitFor($p->ptj, $p->bahagian);
            if (! $unit) {
                continue;
            }

            if (! $units->has($unit['key'])) {
                $unit['programId'] = null;
                $unit['programLabel'] = 'TANPA PROGRAM';
                $units->put($unit['key'], $unit);
            }

            $counts[$unit['key']] ??= $this->emptyCounts();
            $counts[$unit['key']]['total']++;

            $pJgId = $p->jawatan_gred_id;
            foreach ($this->columns as $i => $column) {
                if ($column['catchall']) {
                    $isU41 = $this->u41GredId
                        && ($jgGredId[$pJgId] ?? null) === $this->u41GredId
                        && ! in_array($pJgId, $this->namedJgIds);
                    if ($isU41) {
                        $counts[$unit['key']]['cols'][$i]++;
                    }

                    continue;
                }

                if ($column['jawatanGredId'] && $pJgId === $column['jawatanGredId']) {
                    $counts[$unit['key']]['cols'][$i]++;
                }
            }
        }

        return $counts;
    }

    protected function emptyCounts(): array
    {
        return [
            'total' => 0,
            'cols' => array_fill(0, count($this->columns), 0),
        ];
    }

    protected function addCounts(array &$target, array $source): void
    {
        $target['total'] += $source['total'];
        foreach ($source['cols'] as $i => $value) {
            $target['cols'][$i] += $value;
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $lastCol = Coordinate::stringFromColumnIndex($this->lastColumnIndex);

                $this->styleTitle($sheet, $lastCol);
                $this->styleHeader($sheet, $lastCol);

                foreach ($this->sectionRows as $row) {
                    $this->styleSectionRow($sheet, $row, $lastCol);
                }

                foreach ($this->dataRows as $row) {
                    $this->styleDataRow($sheet, $row, $lastCol);
                }

                foreach ($this->jumlahRows as $row) {
                    $this->styleJumlahRow($sheet, $row, $lastCol);
                }

                $this->styleTotalRow($sheet, $this->totalRow, $lastCol);
            },
        ];
    }

    protected function styleTitle($sheet, string $lastCol): void
    {
        $sheet->mergeCells("A1:{$lastCol}1");

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(34);

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(42);
        $sheet->getColumnDimension('C')->setWidth(16);

        for ($column = 4; $column <= $this->lastColumnIndex; $column++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setWidth(14);
        }
    }

    protected function styleHeader($sheet, string $lastCol): void
    {
        $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '92CDDC'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $sheet->getRowDimension(2)->setRowHeight(34);
    }

    protected function styleSectionRow($sheet, int $row, string $lastCol): void
    {
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");

        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => '000000'],
            ],
            'alignment' => [
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

        $sheet->getRowDimension($row)->setRowHeight(24);
    }

    protected function styleDataRow($sheet, int $row, string $lastCol): void
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

        $sheet->getStyle("C{$row}:{$lastCol}{$row}")->applyFromArray([
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

        $sheet->getRowDimension($row)->setRowHeight(20);
    }

    protected function styleJumlahRow($sheet, int $row, string $lastCol): void
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
                'startColor' => ['rgb' => 'CA9EB3'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $sheet->getRowDimension($row)->setRowHeight(24);
    }

    protected function styleTotalRow($sheet, int $row, string $lastCol): void
    {
        $sheet->mergeCells("A{$row}:B{$row}");

        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '00B0F0'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $sheet->getRowDimension($row)->setRowHeight(24);
    }
}
