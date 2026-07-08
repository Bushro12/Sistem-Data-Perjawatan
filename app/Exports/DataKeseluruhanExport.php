<?php

namespace App\Exports;

use App\Models\Pegawai;
use App\Models\Waran;
use App\Models\WaranJawatan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class DataKeseluruhanExport implements FromCollection, WithCustomStartCell, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public array $mergeRows = [];
    public function collection()
    {
        $programs = WaranJawatan::with([
            'aktiviti.program',
            'ptj',
            'pegawai'
        ])
            ->get()
            ->groupBy(fn($item) => $item->aktiviti?->program?->nama_program . ': ' .
                $item->aktiviti?->program?->desc_program);

        $rows = collect();

        $currentRow = 5; // because startCell = A5


        foreach ($programs as $program => $items) {

            // PROGRAM ROW
            $programRow = $currentRow;

            $rows->push([
                $program,
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ]);

            $currentRow++;


            // PTJ rows
            $ptjs = $items->groupBy('ptj_id');
            $ptjRows = [];
            $index = 1;
            foreach ($ptjs as $ptjItems) {

                $perjawatanTetap = $ptjItems->count() ?: '0';

                $pengisianTetap = $ptjItems->filter(function ($item) {
                    $pegawai = $item->pegawai;

                    return $pegawai && (
                        $pegawai->is_tetap ||
                        $pegawai->is_kontrak_interim
                    );
                })->count() ?: '0';

                $kosongTetap = $perjawatanTetap - $pengisianTetap ?: '0';

                $peratusPengisianTetap = $perjawatanTetap > 0 ? round(($pengisianTetap / $perjawatanTetap) * 100, 2) . '%' : '0%';

                $kontrak = Pegawai::whereIn('ptj_id', $ptjItems->pluck('ptj_id'))
                    ->where('is_kontrak', true)
                    ->count() ?: '0';

                $jumlahPengisian = ($pengisianTetap + $kontrak) ?: '0';

                $ptjRows[] = $currentRow;

                $ptj = $ptjItems->first()->ptj;
                $bahagian = $ptjItems->first()->bahagian;

                $nama = $ptj->nama_ptj === 'JABATAN KESIHATAN NEGERI KEDAH'
                    ? $bahagian?->nama_bahagian
                    : $ptj->nama_ptj;

                $rows->push([
                    $index, // BIL
                    // $ptj->nama_ptj,
                    $nama,
                    $perjawatanTetap,
                    $pengisianTetap,
                    $kosongTetap,
                    $peratusPengisianTetap,
                    // $ptjItems->pegawai?->where('is_kontrak', true)->count() ? : '0',
                    $kontrak,
                    $jumlahPengisian
                ]);

                $currentRow++;
                $index++;


            }

            // JUMLAH ROW
            $jumlahRow = $currentRow;

            $jumlahperjawatanTetap = $ptjs->sum(function ($ptjItems) {
                return $ptjItems->count() ?: '0';
            });
            $jumlahPengisianTetap = $ptjs->sum(function ($ptjItems) {
                return $ptjItems->filter(function ($item) {
                    $pegawai = $item->pegawai;

                    return $pegawai && (
                        $pegawai->is_tetap ||
                        $pegawai->is_kontrak_interim
                    );
                })->count() ?: '0';
            });

            $jumlahKosongTetap = ($jumlahperjawatanTetap - $jumlahPengisianTetap) ?: '0';
            $jumlahPeratusPengisianTetap = $jumlahperjawatanTetap > 0 ? round(($jumlahPengisianTetap / $jumlahperjawatanTetap) * 100, 2) . '%' : '0%';
            $jumlahKontrak = Pegawai::whereIn(
                'ptj_id',
                $items->pluck('ptj_id')->unique()
            )
                ->where('is_kontrak', true)
                ->count() ?: '0';

            $rows->push([
                'JUMLAH',
                '',
                $jumlahperjawatanTetap,
                $jumlahPengisianTetap,
                $jumlahKosongTetap,
                $jumlahPeratusPengisianTetap,
                $jumlahKontrak,
                $jumlahPengisianTetap + $jumlahKontrak
            ]);



            // $currentRow++;

            // blank row
            $rows->push([]);
            $currentRow++;


            // store merge info
            $this->mergeRows[] = [
                'program_row' => $programRow,
                'ptj_rows' => $ptjRows,
                'jumlah_row' => $jumlahRow,
                // 'last_row' => $lastRow
            ];
        }

        $jkPerjawatanTetap = $programs->sum(function ($items) {
            return $items->count();
        });

        $jkIsiTetap = $programs->sum(function ($items) {
            return $items->filter(function ($item) {
                $pegawai = $item->pegawai;

                return $pegawai && (
                    $pegawai->is_tetap ||
                    $pegawai->is_kontrak_interim
                );
            })->count();
        });

        $jkKosongTetap = ($jkPerjawatanTetap - $jkIsiTetap) ?: '0';

        $jkPeratusPengisianTetap = $jkPerjawatanTetap > 0 ? round(($jkIsiTetap / $jkPerjawatanTetap) * 100, 2) . '%' : '0%';

        // $jkIsiKontrak = Pegawai::addBinding($programs->pluck('ptj_id')->flatten()->unique())
        //     ->whereIn('ptj_id', $programs->pluck('ptj_id')->flatten()->unique())
        //     ->where('is_kontrak', true)
        //     ->count() ? : '0';

        $ptjIds = $programs->pluck('ptj_id')
            ->flatten()
            ->unique()
            ->values()
            ->toArray();

        $jkIsiKontrak = Pegawai::query()
            ->whereIn('ptj_id', $ptjIds)
            ->where('is_kontrak', true)
            ->count() ?: '0';

        $rows->push([
            'JUMLAH KESELURUHAN',
            '',
            $jkPerjawatanTetap,
            $jkIsiTetap,
            $jkKosongTetap,
            $jkPeratusPengisianTetap,
            $jkIsiKontrak,
            $jkIsiTetap + $jkIsiKontrak
        ]);



        return $rows;
    }


    public function startCell(): string
    {
        return 'A5';
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $lastRow = $sheet->getHighestRow();

                $sheet->mergeCells('A1:H1');
                $sheet->setCellValue('A1', 'DATA PERJAWATAN MANUAL JKN KEDAH SEHINGGA 28 FEBRUARI 2026');
                $sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => '000000'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ]
                    ]
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);

                $sheet->mergeCells('A2:H2');

                $sheet->setCellValue('A2', '(Jumlah perjawatan tetap termasuk jawatan tanpa waran-kod 11000)');

                $sheet->getStyle('A2:H2')->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'size' => 9,
                        'italic' => true
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ]
                    ]
                ]);
                $sheet->getRowDimension(2)->setRowHeight(30);

                $sheet->mergeCells('A3:A4');
                $sheet->setCellValue('A3', 'BIL');
                $sheet->getStyle('A3:A4')->applyFromArray([
                    'font' => [
                        'name' => "Calibri",
                        'bold' => true,
                        'size' => 12
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                $sheet->getColumnDimension('A')->setWidth(10);
                $sheet->getRowDimension('3')->setRowHeight(30);
                $sheet->getRowDimension('4')->setRowHeight(30);

                $sheet->mergeCells('B3:B4');
                $sheet->setCellValue('B3', 'PUSAT TANGGUNGJAWAB');
                $sheet->getStyle('B3:B4')->applyFromArray([
                    'font' => [
                        'name' => "Calibri",
                        'bold' => true,
                        'size' => 12
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                $sheet->getColumnDimension('B')->setWidth(40);

                $sheet->mergeCells('C3:F3');
                $sheet->setCellValue('C3', 'TETAP');
                $sheet->getStyle('C3:F3')->applyFromArray([
                    'font' => [
                        'name' => "Calibri",
                        'bold' => true,
                        'size' => 12
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $sheet->setCellValue('C4', 'Perjawatan');
                $sheet->getStyle('C4')->applyFromArray([
                    'font' => [
                        'name' => "Calibri",
                        'bold' => true,
                        'size' => 12
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $sheet->getColumnDimension('C')->setWidth(13);

                $sheet->setCellValue('D4', 'Pengisisan');
                $sheet->getStyle('D4')->applyFromArray([
                    'font' => [
                        'name' => "Calibri",
                        'bold' => true,
                        'size' => 12
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $sheet->getColumnDimension('D')->setWidth(13);

                $sheet->setCellValue('E4', 'Kosong');
                $sheet->getStyle('E4')->applyFromArray([
                    'font' => [
                        'name' => "Calibri",
                        'bold' => true,
                        'size' => 12
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $sheet->getColumnDimension('E')->setWidth(13);

                $sheet->setCellValue('F4', 'Peratus Pengisian');
                $sheet->getStyle('F4')->applyFromArray([
                    'font' => [
                        'name' => "Calibri",
                        'bold' => true,
                        'size' => 12
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $sheet->getColumnDimension('F')->setWidth(13);

                $sheet->setCellValue('G3', 'KONTRAK');
                $sheet->getStyle('G3')->applyFromArray([
                    'font' => [
                        'name' => "Calibri",
                        'bold' => true,
                        'size' => 12
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $sheet->getColumnDimension('G')->setWidth(15);

                $sheet->setCellValue('G4', 'Pengisian');
                $sheet->getStyle('G4')->applyFromArray([
                    'font' => [
                        'name' => "Calibri",
                        'bold' => true,
                        'size' => 12
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $sheet->setCellValue('H3', 'JUMLAH');
                $sheet->getStyle('H3')->applyFromArray([
                    'font' => [
                        'name' => "Calibri",
                        'bold' => true,
                        'size' => 12
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                $sheet->getColumnDimension('H')->setWidth(18);

                $sheet->setCellValue('H4', 'Pengisian (Tetap + Kontrak)');
                $sheet->getStyle('H4')->applyFromArray([
                    'font' => [
                        'name' => "Calibri",
                        'bold' => true,
                        'size' => 12
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $sheet->getStyle('A3:H4')->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => '989898'
                        ]
                    ]
                ]);

                $export = $event->getConcernable();

                $sheet = $event->sheet->getDelegate();

                foreach ($export->mergeRows as $row) {

                    // PROGRAM ROW (A to H merge)
                    $sheet->mergeCells("A{$row['program_row']}:H{$row['program_row']}");
                    $sheet->setCellValue("A{$row['program_row']}", $sheet->getCell("A{$row['program_row']}")->getValue());
                    $sheet->getStyle("A{$row['program_row']}:H{$row['program_row']}")->applyFromArray([
                        'font' => [
                            'name' => 'Calibri',
                            'bold' => true,
                            'size' => 11,
                            'color' => ['rgb' => '000000'],
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => '9bc0e2',
                            ],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ]
                    ]);
                    $sheet->getRowDimension($row['program_row'])->setRowHeight(30);

                    foreach ($row['ptj_rows'] as $ptjRow) {

                        $sheet->getStyle("A{$ptjRow}:H{$ptjRow}")->applyFromArray([
                            'alignment' => [
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                'wrapText' => true
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000'],
                                ]
                            ]
                        ]);

                        $sheet->getStyle("C{$ptjRow}:H{$ptjRow}")->applyFromArray([
                            'alignment' => [
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'wrapText' => true
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000'],
                                ]
                            ]
                        ]);

                        $sheet->getStyle("A{$ptjRow}")->applyFromArray([
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            ],
                        ]);

                        $sheet->getRowDimension($ptjRow)->setRowHeight(30);
                    }


                    // JUMLAH ROW (A to B merge)
                    $sheet->mergeCells("A{$row['jumlah_row']}:B{$row['jumlah_row']}");
                    $sheet->setCellValue("A{$row['jumlah_row']}", $sheet->getCell("A{$row['jumlah_row']}")->getValue());
                    $sheet->getStyle("A{$row['jumlah_row']}:H{$row['jumlah_row']}")->applyFromArray([
                        'font' => [
                            'name' => 'Calibri',
                            'bold' => true,
                            'size' => 11,
                            'color' => ['rgb' => '000000'],
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => 'ca9eb3'
                            ]
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ]
                        ]
                    ]);

                    $sheet->getRowDimension($row['jumlah_row'])->setRowHeight(30);

                    //Total

                    $sheet->mergeCells("A{$lastRow}:B{$lastRow}");
                    $sheet->getStyle("A{$lastRow}:H{$lastRow}")->applyFromArray([
                        'font' => [
                            'name' => 'Calibri',
                            'bold' => true,
                            'size' => 11,
                            'color' => ['rgb' => '000000'],
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ]
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => '00b0f0'
                            ]
                        ]
                    ]);
                    $sheet->getRowDimension($lastRow)->setRowHeight(30);
                }
            },
        ];
    }
}
