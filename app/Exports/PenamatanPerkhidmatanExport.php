<?php

namespace App\Exports;

use App\Models\Pencen;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
// use PhpOffice\PhpSpreadsheet\Style\Border;
// use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PenamatanPerkhidmatanExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $jenis_pencen_id;

    public function __construct($jenis_pencen_id)
    {
        $this->jenis_pencen_id = $jenis_pencen_id;
    }

    public function collection()
    {
        $query = Pencen::with([
            'ptj',
            'jawatan_gred.jawatan',
            'jawatan_gred.gred',
            'opsyenPencen',
            'jenisPencen'
        ]);

        if (!empty($this->jenis_pencen_id)) {
            $query->whereIn('jenis_pencen_id', $this->jenis_pencen_id);
        }

        return $query->get()->map(function ($item, $index) {
            return [
                $index + 1,
                $item->ptj?->nama_ptj,
                $item->nama,
                $item->nokp,
                $item->jawatan_gred?->jawatan?->desc_jawatan,
                $item->jawatan_gred?->gred?->kod_gred,
                $item->tarikh_lantikan
                ? Carbon::parse($item->tarikh_lantikan)->format('d.m.Y') : '',
                $item->tempoh_perkhidmatan,
                $item->opsyenPencen?->opsyen ? : 'Tiada',
                $item->tarikh_pencen
                ? Carbon::parse($item->tarikh_pencen)->format('d.m.Y')
                : ($item->tarikh_kuatkuasa
                    ? Carbon::parse($item->tarikh_kuatkuasa)->format('d.m.Y')
                    : ''),
                $item->jenisPencen?->jenis

            ];
        });
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function headings(): array
    {
        return [
            'BIL',
            'PUSAT TANGGUNGJAWAB',
            'NAMA PENYANDANG',
            'NO. KAD PENGENALAN',
            'JAWATAN',
            'GRED',
            'TARIKH LANTIKAN PERTAMA',
            'TEMPOH PERKHIDMATAN',
            'OPSYEN (UMUR BERSARA) (55/56/58/60)',
            'TARIKH BERSARA WAJIB / TARIKH KUATKUASA',
            'JENIS PERSARAAN (WAJIB/PILIHAN/ATAS SEBAB KESIHATAN/KEMATIAN'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $sheet->mergeCells('A1:K1');
                $sheet->setCellValue('A1', 'LAPORAN PENAMATAN PERKHIDMATAN');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '000000'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ]
                ]);

                $sheet->getRowDimension(1)->setRowHeight(45);

                $sheet->mergeCells('A2:K2');
                // $sheet -> setCellValue('A2', 'LAPORAN PENAMATAN PERKHIDMATAN');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '000000'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ]
                ]);

                $sheet->getRowDimension(2)->setRowHeight(45);

                $sheet->mergeCells('A3:K3');
                // $sheet -> setCellValue('A3', 'LAPORAN PENAMATAN PERKHIDMATAN');
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '000000'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ]
                ]);

                $sheet->getRowDimension(3)->setRowHeight(45);

                $sheet->getStyle('A5:K5')->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => '000000'],
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => [
                            'rgb' => 'd8d8d8'
                        ]
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ]
                ]);

                $sheet->getRowDimension(5)->setRowHeight(100);

                $sheet->getStyle('A6:K' . $lastRow)->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'bold' => false,
                        'size' => 11,
                        'color' => ['rgb' => '000000']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                $sheet->getColumnDimension('A')->setWidth(40);
                $sheet->getColumnDimension('B')->setWidth(40);
                $sheet->getColumnDimension('C')->setWidth(40);
                $sheet->getColumnDimension('D')->setWidth(40);
                $sheet->getColumnDimension('E')->setWidth(40);
                $sheet->getColumnDimension('F')->setWidth(40);
                $sheet->getColumnDimension('G')->setWidth(40);
                $sheet->getColumnDimension('H')->setWidth(40);
                $sheet->getColumnDimension('I')->setWidth(40);
                $sheet->getColumnDimension('J')->setWidth(40);
                $sheet->getColumnDimension('K')->setWidth(40);

                $highestRow = $sheet->getHighestRow();

                for ($row = 6; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(45);
                }

            },
        ];
    }
}
