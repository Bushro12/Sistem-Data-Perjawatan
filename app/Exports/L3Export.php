<?php

namespace App\Exports;

use App\Models\Bahagian;
use App\Models\Jawatan_Gred;
use App\Models\Kumpulan;
use App\Models\Program;
use App\Models\WaranJawatan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class L3Export implements FromCollection, WithCustomStartCell, WithEvents
{
    /** @var Collection<int, Kumpulan> */
    public Collection $kumpulans;

    public int $lastColumnIndex = 0;

    public string $lastColLetter = 'L';

    /** @var list<int> */
    public array $programRows = [];

    /** @var list<int> */
    public array $dataRows = [];

    /** @var list<int> */
    public array $jumlahRows = [];

    public int $totalRow = 0;

    public const TITLE = 'DATA PERJAWATAN KUMPULAN DI JKN KEDAH SEHINGGA 28 FEBRUARI 2026';

    public function collection(): Collection
    {
        $this->kumpulans = Kumpulan::orderBy('id')->get();
        $kCount = max(1, $this->kumpulans->count());
        $this->lastColumnIndex = 2 + ($kCount * 2) + 2;
        $this->lastColLetter = Coordinate::stringFromColumnIndex($this->lastColumnIndex);

        $kumpulanIds = $this->kumpulans->pluck('id')->all();
        $kumpulanIndex = [];
        foreach ($kumpulanIds as $i => $kid) {
            $kumpulanIndex[(int) $kid] = $i;
        }

        // Map "jawatan_id:gred_id" => kumpulan_id + id => kumpulan_id for jawatan_gred_id fast path.
        $jgByPair = [];
        $jgById = [];
        foreach (Jawatan_Gred::query()->select(['id', 'jawatan_id', 'gred_id', 'kumpulan_id'])->get() as $jg) {
            $jgById[(int) $jg->id] = $jg->kumpulan_id !== null ? (int) $jg->kumpulan_id : null;
            if ($jg->jawatan_id !== null && $jg->gred_id !== null) {
                $jgByPair[$jg->jawatan_id.':'.$jg->gred_id] = $jg->kumpulan_id !== null ? (int) $jg->kumpulan_id : null;
            }
        }

        $user = auth()->user();
        $isScoped = $user && (int) ($user->role ?? 0) === 3 && $user->ptj_id;
        $scopedPtjId = $isScoped ? (int) $user->ptj_id : null;

        // Active posts only: exclude soft-deleted (default) and removed placements.
        $warans = WaranJawatan::with([
            'aktiviti.program',
            'pegawai.jawatan_gred',
        ])->get()->filter(fn ($w) => ($w->status ?? 'active') !== 'removed');

        // Pre-compute per-waran single kumpulan: 1 active waran_jawatan row = 1 J.
        $computed = [];
        foreach ($warans as $w) {
            $programId = $w->aktiviti?->program_id;
            if (! $programId) {
                continue;
            }

            // Pegawai's own kumpulan (used for I, and as tiebreaker for J).
            $pegawaiKid = null;
            if (filled($w->pegawai_id) && $w->pegawai) {
                $pegawaiKid = $w->pegawai->jawatan_gred?->kumpulan_id;
                $pegawaiKid = $pegawaiKid !== null ? (int) $pegawaiKid : null;
            }

            // Resolve this row's single kumpulan for J.
            $jKumpulanIdx = null;
            if (filled($w->jawatan_gred_id) && isset($jgById[(int) $w->jawatan_gred_id])) {
                $kid = $jgById[(int) $w->jawatan_gred_id];
                if ($kid !== null && isset($kumpulanIndex[$kid])) {
                    $jKumpulanIdx = $kumpulanIndex[$kid];
                }
            } else {
                $jawatanIds = is_array($w->jawatan_ids) ? $w->jawatan_ids : (filled($w->jawatan_ids) ? [$w->jawatan_ids] : []);
                $gredIds = is_array($w->gred_ids) ? $w->gred_ids : (filled($w->gred_ids) ? [$w->gred_ids] : []);

                $distinct = [];
                foreach ($jawatanIds as $jid) {
                    foreach ($gredIds as $gid) {
                        $kid = $jgByPair[$jid.':'.$gid] ?? null;
                        if ($kid !== null && isset($kumpulanIndex[$kid])) {
                            $distinct[$kumpulanIndex[$kid]] = true;
                        }
                    }
                }

                // Single-sided lists (only jawatan or only gred): distinct kumpulan matches.
                if ($distinct === [] && ($jawatanIds !== [] || $gredIds !== [])) {
                    foreach (Jawatan_Gred::query()->select(['jawatan_id', 'gred_id', 'kumpulan_id'])
                        ->when($jawatanIds !== [], fn ($q) => $q->whereIn('jawatan_id', $jawatanIds))
                        ->when($gredIds !== [], fn ($q) => $q->whereIn('gred_id', $gredIds))
                        ->get() as $jg) {
                        if ($jg->kumpulan_id !== null && isset($kumpulanIndex[(int) $jg->kumpulan_id])) {
                            $distinct[$kumpulanIndex[(int) $jg->kumpulan_id]] = true;
                        }
                    }
                }

                if (count($distinct) === 1) {
                    $jKumpulanIdx = (int) array_key_first($distinct);
                } elseif (count($distinct) > 1) {
                    // Row spans several kumpulan: keep J with the pegawai's
                    // kumpulan when it is one of them, else first (deterministic).
                    if ($pegawaiKid !== null && isset($kumpulanIndex[$pegawaiKid]) && isset($distinct[$kumpulanIndex[$pegawaiKid]])) {
                        $jKumpulanIdx = $kumpulanIndex[$pegawaiKid];
                    } else {
                        $jKumpulanIdx = min(array_keys($distinct));
                    }
                } elseif ($pegawaiKid !== null && isset($kumpulanIndex[$pegawaiKid])) {
                    // Waran has no classifiable jawatan/gred: fall back to pegawai's.
                    $jKumpulanIdx = $kumpulanIndex[$pegawaiKid];
                }
            }

            // I: 1 per assigned pegawai, in the pegawai's kumpulan when known,
            // else in the row's J kumpulan so I stays with its J.
            $iKumpulanIdx = null;
            if (filled($w->pegawai_id) && $w->pegawai) {
                if ($pegawaiKid !== null && isset($kumpulanIndex[$pegawaiKid])) {
                    $iKumpulanIdx = $kumpulanIndex[$pegawaiKid];
                } else {
                    $iKumpulanIdx = $jKumpulanIdx;
                }
            }

            $computed[] = [
                'program_id' => (int) $programId,
                'ptj_id' => $w->ptj_id !== null ? (int) $w->ptj_id : null,
                'bahagian_id' => $w->bahagian_id !== null ? (int) $w->bahagian_id : ($w->pegawai?->bahagian_id !== null ? (int) $w->pegawai->bahagian_id : null),
                'j_idx' => $jKumpulanIdx,
                'i_idx' => $iKumpulanIdx,
            ];
        }

        $groupedByProgram = collect($computed)->groupBy('program_id');

        $programs = Program::with('ptjs')->orderBy('nama_program')->get();

        $rows = collect();
        $currentRow = 4; // startCell A4

        $grandJ = array_fill(0, $kCount, 0);
        $grandI = array_fill(0, $kCount, 0);

        foreach ($programs as $program) {
            $isIbuPejabat = $program->nama_program === 'PROGRAM 1';

            if ($isIbuPejabat) {
                $ptjIds = $program->ptjs->pluck('id')->all();
                $units = Bahagian::query()
                    ->whereIn('ptj_id', $ptjIds !== [] ? $ptjIds : [0])
                    ->orderBy('nama_bahagian')
                    ->get()
                    ->map(fn ($b) => [
                        'key' => 'b'.$b->id,
                        'label' => $b->nama_bahagian,
                        'bahagian_id' => (int) $b->id,
                        'ptj_id' => (int) $b->ptj_id,
                    ])->values();
                if ($scopedPtjId) {
                    $units = $units->filter(fn ($u) => $u['ptj_id'] === $scopedPtjId)->values();
                }
            } else {
                $units = $program->ptjs->sortBy('nama_ptj')->values()
                    ->map(fn ($ptj) => [
                        'key' => 'p'.$ptj->id,
                        'label' => $ptj->nama_ptj,
                        'ptj_id' => (int) $ptj->id,
                        'bahagian_id' => null,
                    ]);
                if ($scopedPtjId) {
                    $units = $units->filter(fn ($u) => $u['ptj_id'] === $scopedPtjId)->values();
                }
            }

            $programWarans = $groupedByProgram->get($program->id, collect());

            // Scoped users only see their own PTJ's numbers; other units already filtered.
            if ($isScoped) {
                $programWarans = $programWarans->filter(fn ($c) => $c['ptj_id'] === $scopedPtjId);
                if ($units->isEmpty() && $programWarans->isEmpty()) {
                    continue;
                }
            }

            // Skip truly empty programs (no units and no waran) to keep the sheet tidy.
            if ($units->isEmpty() && $programWarans->isEmpty()) {
                continue;
            }

            $programLabel = $isIbuPejabat
                ? 'IBU PEJABAT JKN'
                : trim($program->nama_program.($program->desc_program ? ' : '.$program->desc_program : ''));

            $rows->push($this->blankRow($programLabel));
            $this->programRows[] = $currentRow;
            $currentRow++;

            // Aggregate per unit.
            $bil = 0;
            foreach ($units as $unit) {
                $bil++;
                $unitJ = array_fill(0, $kCount, 0);
                $unitI = array_fill(0, $kCount, 0);

                foreach ($programWarans as $c) {
                    $match = $isIbuPejabat
                        ? ($c['bahagian_id'] !== null && $c['bahagian_id'] === $unit['bahagian_id'])
                        : ($c['ptj_id'] !== null && $c['ptj_id'] === $unit['ptj_id']);
                    if (! $match) {
                        continue;
                    }
                    if ($c['j_idx'] !== null) {
                        $unitJ[$c['j_idx']]++;
                    }
                    if ($c['i_idx'] !== null) {
                        $unitI[$c['i_idx']]++;
                    }
                }

                $rows->push($this->dataRow($bil, $unit['label'], $unitJ, $unitI));
                $this->dataRows[] = $currentRow;
                $currentRow++;
            }

            // Program totals: all warans in this program (includes unassigned rows).
            $progJ = array_fill(0, $kCount, 0);
            $progI = array_fill(0, $kCount, 0);
            foreach ($programWarans as $c) {
                if ($c['j_idx'] !== null) {
                    $progJ[$c['j_idx']]++;
                    $grandJ[$c['j_idx']]++;
                }
                if ($c['i_idx'] !== null) {
                    $progI[$c['i_idx']]++;
                    $grandI[$c['i_idx']]++;
                }
            }

            $rows->push($this->jumlahRow($progJ, $progI));
            $this->jumlahRows[] = $currentRow;
            $currentRow++;
        }

        $rows->push($this->jumlahRow($grandJ, $grandI, 'JUMLAH KESELURUHAN'));
        $this->totalRow = $currentRow;

        return $rows;
    }

    public function startCell(): string
    {
        return 'A4';
    }

    /**
     * @param  list<int>  $j
     * @param  list<int>  $i
     */
    protected function dataRow(int $bil, ?string $label, array $j, array $i): array
    {
        $row = [$bil, $label ?? ''];
        $sumJ = 0;
        $sumI = 0;
        foreach ($j as $idx => $jVal) {
            $iVal = $i[$idx] ?? 0;
            $sumJ += $jVal;
            $sumI += $iVal;
            // Blank when no posts in this kumpulan; show 0 for I when posts exist but none filled.
            $row[] = $jVal > 0 ? $jVal : '';
            $row[] = $jVal > 0 ? $iVal : '';
        }
        $row[] = $sumJ > 0 ? $sumJ : '';
        $row[] = $sumJ > 0 ? $sumI : '';

        return $this->padRow($row);
    }

    /**
     * @param  list<int>  $j
     * @param  list<int>  $i
     */
    protected function jumlahRow(array $j, array $i, string $label = 'JUMLAH'): array
    {
        $row = [$label, ''];
        $sumJ = 0;
        $sumI = 0;
        foreach ($j as $idx => $jVal) {
            $iVal = $i[$idx] ?? 0;
            $sumJ += $jVal;
            $sumI += $iVal;
            $row[] = $jVal;
            $row[] = $iVal;
        }
        $row[] = $sumJ;
        $row[] = $sumI;

        return $this->padRow($row);
    }

    protected function blankRow(string $first = ''): array
    {
        return $this->padRow([$first]);
    }

    protected function padRow(array $row): array
    {
        $row = array_values($row);
        while (count($row) < $this->lastColumnIndex) {
            $row[] = '';
        }

        return array_slice($row, 0, $this->lastColumnIndex);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = $this->lastColLetter;
                $lastRow = max($sheet->getHighestRow(), $this->totalRow);

                $thin = [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ];

                // Title A1:lastCol1
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', self::TITLE);
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font' => ['name' => 'Calibri', 'bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => $thin],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Header rows 2-3
                $sheet->mergeCells('A2:A3');
                $sheet->setCellValue('A2', 'BIL');
                $sheet->mergeCells('B2:B3');
                $sheet->setCellValue('B2', 'PUSAT TANGGUNGJAWAB');

                $colIdx = 3;
                foreach ($this->kumpulans as $kumpulan) {
                    $start = Coordinate::stringFromColumnIndex($colIdx);
                    $end = Coordinate::stringFromColumnIndex($colIdx + 1);
                    $sheet->mergeCells("{$start}2:{$end}2");
                    $sheet->setCellValue("{$start}2", mb_strtoupper($kumpulan->nama_kumpulan ?? ''));
                    $sheet->setCellValue("{$start}3", 'J');
                    $sheet->setCellValue("{$end}3", 'I');
                    $colIdx += 2;
                }
                $start = Coordinate::stringFromColumnIndex($colIdx);
                $end = Coordinate::stringFromColumnIndex($colIdx + 1);
                $sheet->mergeCells("{$start}2:{$end}2");
                $sheet->setCellValue("{$start}2", 'JUMLAH');
                $sheet->setCellValue("{$start}3", 'J');
                $sheet->setCellValue("{$end}3", 'I');

                $sheet->getStyle("A2:{$lastCol}3")->applyFromArray([
                    'font' => ['name' => 'Calibri', 'bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BFBFBF']],
                    'borders' => ['allBorders' => $thin],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(30);
                $sheet->getRowDimension(3)->setRowHeight(24);

                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(48);
                for ($c = 3; $c <= $this->lastColumnIndex; $c++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(11);
                }

                // Program section rows
                foreach ($this->programRows as $row) {
                    $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'font' => ['name' => 'Calibri', 'bold' => true, 'size' => 11],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9BC0E2']],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['allBorders' => $thin],
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(26);
                }

                // Data rows
                foreach ($this->dataRows as $row) {
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'font' => ['name' => 'Calibri', 'size' => 11],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                        'borders' => ['allBorders' => $thin],
                    ]);
                    $sheet->getStyle("A{$row}")->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getStyle("C{$row}:{$lastCol}{$row}")->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(22);
                }

                // JUMLAH per program
                foreach ($this->jumlahRows as $row) {
                    $sheet->mergeCells("A{$row}:B{$row}");
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'font' => ['name' => 'Calibri', 'bold' => true, 'size' => 11],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CA9EB3']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['allBorders' => $thin],
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(24);
                }

                // Grand total
                if ($this->totalRow > 0) {
                    $row = $this->totalRow;
                    $sheet->mergeCells("A{$row}:B{$row}");
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'font' => ['name' => 'Calibri', 'bold' => true, 'size' => 11],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '00B0F0']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['allBorders' => $thin],
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(26);
                }

                $sheet->freezePane('C4');
                $sheet->setAutoFilter("A3:{$lastCol}3");
            },
        ];
    }
}
