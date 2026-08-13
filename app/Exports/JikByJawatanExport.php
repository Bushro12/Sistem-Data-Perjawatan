<?php

namespace App\Exports;

use App\Models\Jawatan;
use App\Models\Pegawai;
use App\Models\WaranJawatan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class JikByJawatanExport implements
    // FromCollection,
    WithEvents
{

    /**
     * @return \Illuminate\Support\Collection
     */

    public array $mergeRows = [];
    protected $jawatan_id;

    public function __construct($jawatan_id)
    {
        $this->jawatan_id = $jawatan_id;

    }



    public function collection()
    {
        Carbon::setLocale('ms');

        $today = Carbon::now()->translatedFormat('d F Y');


        // dd($today); // 22 Julai 2026
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                Carbon::setLocale('ms');

                $today = Carbon::now()->translatedFormat('d F Y');

                $jawatan = Jawatan::findOrFail($this->jawatan_id);

                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:B1');
                $sheet->setCellValue('A1', 'MAKLUMAT PERJAWATAN');
                $sheet->getStyle('A1:B1')->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'bold' => true,
                        'size' => 13,
                        'color' => ['rgb' => '000000'],
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    // 'borders' => [
                    //     'allBorders' => [
                    //         'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    //         'color' => ['rgb' => '000000'],
                    //     ]
                    // ]
                ]);

                $sheet->getRowDimension(1)->setRowHeight(35);
                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(32);

                $sheet->setCellValue('C1', ': ' . $jawatan->desc_jawatan);
                $sheet->getStyle('C1')->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'bold' => true,
                        'size' => 13,
                        'color' => ['rgb' => '000000']
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ]
                ]);

                $sheet->mergeCells('A2:B2');
                $sheet->setCellValue('A2', 'DATA SEHINGGA');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'bold' => true,
                        'size' => 13,
                        'color' => ['rgb' => '000000']
                    ],
                ]);

                $sheet->setCellValue('C2', ': ' . $today);
                $sheet->getStyle('C2')->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'bold' => true,
                        'size' => 13,
                        'color' => ['rgb' => '000000'],
                    ]
                ]);

                $sheet->mergeCells('A4:A6');
                $sheet->setCellValue('A4', 'BIL');
                $sheet->getStyle('A4:A6')->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'bold' => true,
                        'size' => 13,
                        'color' => ['rgb' => '000000']
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => '7f7f7f'
                        ]
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                        ]
                    ]
                ]);

                $sheet->mergeCells('B4:B6');
                $sheet->setCellValue('B4', 'PTJ');
                $sheet->getStyle('B4:B6')->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'bold' => true,
                        'size' => 13,
                        'color' => ['rgb' => '000000']
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => '7f7f7f'
                        ]
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                        ]
                    ]
                ]);

                // $sheet->mergeCells('C4:E4');
                // $sheet->setCellValue('C4', 'JURURAWAT U12');
                // $sheet->getStyle('C4:E4')->applyFromArray([
                //     'font' => [
                //         'name' => 'Calibri',
                //         'bold' => true,
                //         'size' => 13,
                //         'color' => ['rgb' => '000000']
                //     ],
                //     'alignment' => [
                //         'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                //         'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                //     ],
                //     'fill' => [
                //         'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                //         'startColor' => [
                //             'rgb' => '7f7f7f'
                //         ]
                //     ],
                //     'borders' => [
                //         'allBorders' => [
                //             'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                //         ]
                //     ]
                // ]);
                // $sheet->getRowDimension(4)->setRowHeight(35);

                // $sheet->mergeCells('C5:C6');
                // $sheet->setCellValue('C5', 'J');
                // $sheet->getStyle('C5:C6')->applyFromArray([
                //     'font' => [
                //         'name' => 'Calibri',
                //         'bold' => true,
                //         'size' => 13,
                //         'color' => ['rgb' => '000000']
                //     ],
                //     'alignment' => [
                //         'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                //         'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                //     ],
                //     'fill' => [
                //         'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                //         'startColor' => [
                //             'rgb' => '7f7f7f'
                //         ]
                //     ],
                //     'borders' => [
                //         'allBorders' => [
                //             'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                //         ]
                //     ]
                // ]);

                // $sheet->mergeCells('D5:D6');
                // $sheet->setCellValue('D5', 'I');
                // $sheet->getStyle('D5:D6')->applyFromArray([
                //     'font' => [
                //         'name' => 'Calibri',
                //         'bold' => true,
                //         'size' => 13,
                //         'color' => ['rgb' => '000000']
                //     ],
                //     'alignment' => [
                //         'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                //         'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                //     ],
                //     'fill' => [
                //         'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                //         'startColor' => [
                //             'rgb' => '7f7f7f'
                //         ]
                //     ],
                //     'borders' => [
                //         'allBorders' => [
                //             'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                //         ]
                //     ]
                // ]);

                // $sheet->mergeCells('E5:E6');
                // $sheet->setCellValue('E5', 'K');
                // $sheet->getStyle('E5:E6')->applyFromArray([
                //     'font' => [
                //         'name' => 'Calibri',
                //         'bold' => true,
                //         'size' => 13,
                //         'color' => ['rgb' => '000000']
                //     ],
                //     'alignment' => [
                //         'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                //         'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                //     ],
                //     'fill' => [
                //         'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                //         'startColor' => [
                //             'rgb' => '7f7f7f'
                //         ]
                //     ],
                //     'borders' => [
                //         'allBorders' => [
                //             'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                //         ]
                //     ]
                // ]);

                $columnIndex = 3; // Column C
$warans = WaranJawatan::whereJsonContains(
        'jawatan_ids',
        $this->jawatan_id
    )
    ->get();
                $jawatans = Jawatan::with([
                    'greds' => function ($query) {

                        $query->orderByRaw("
            CAST(SUBSTRING(kod_gred, 2) AS UNSIGNED) DESC
        ");

                    }
                ])
                    ->where('id', $this->jawatan_id)
                    ->get();
                foreach ($jawatans as $jawatan) {

                    foreach ($jawatan->greds as $gred) {

                        $start = Coordinate::stringFromColumnIndex($columnIndex);
                        $middle = Coordinate::stringFromColumnIndex($columnIndex + 1);
                        $end = Coordinate::stringFromColumnIndex($columnIndex + 2);

                        // Header
                        $sheet->mergeCells("{$start}4:{$end}4");
                        $sheet->setCellValue(
                            "{$start}4",
                            strtoupper($jawatan->desc_jawatan . ' ' . $gred->kod_gred)
                        );

                        // Style
                        $sheet->getStyle("{$start}4:{$end}4")->applyFromArray([
                            'font' => [
                                'name' => 'Calibri',
                                'bold' => true,
                                'size' => 13,
                            ],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => [
                                    'rgb' => '7f7f7f',
                                ],
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                ],
                            ],
                        ]);

                        // J
                        $sheet->mergeCells("{$start}5:{$start}6");
                        $sheet->setCellValue("{$start}5", 'J');

                        // I
                        $sheet->mergeCells("{$middle}5:{$middle}6");
                        $sheet->setCellValue("{$middle}5", 'I');

                        // K
                        $sheet->mergeCells("{$end}5:{$end}6");
                        $sheet->setCellValue("{$end}5", 'K');

                        // Style JIK
                        $sheet->getStyle("{$start}5:{$end}6")->applyFromArray([
                            'font' => [
                                'name' => 'Calibri',
                                'bold' => true,
                                'size' => 13,
                            ],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => [
                                    'rgb' => '7f7f7f',
                                ],
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                ],
                            ],
                        ]);

                        $columnIndex += 3;
                    }
                }
            }
        ];
    }
}
