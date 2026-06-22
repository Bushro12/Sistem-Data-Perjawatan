<?php
namespace App\Exports;

use App\Models\LetakJawatan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class LetakJawatanExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
{

    protected $fromMonth;
    protected $fromYear;
    protected $toMonth;
    protected $toYear;

    public function __construct($fromMonth, $fromYear, $toMonth, $toYear)
    {
        $this->fromMonth = $fromMonth;
        $this->fromYear = $fromYear;
        $this->toMonth = $toMonth;
        $this->toYear = $toYear;
    }

    public function collection()
    {
        $query = LetakJawatan::with([
            'ptj',
            'jawatan_gred.jawatan',
            'jawatan_gred.gred',
        ]);

        // if ($this->year !== 'all') {
        //     $query->whereYear('tarikh_kuatkuasa', $this->year);
        // }

        // if ($this->month !== 'all') {
        //     $query->whereMonth('tarikh_kuatkuasa', $this->month);
        // }

        $from = \Carbon\Carbon::create($this->fromYear, $this->fromMonth, 1)->startOfDay();
        $to = \Carbon\Carbon::create($this->toYear, $this->toMonth, 1)->endOfMonth();

        $query->whereBetween('tarikh_kuatkuasa', [$from, $to]);


        return $query->get()->map(function ($item, $index) {
            $kedah = 'Kedah';
            return [
                $index + 1, // Bil
                $item->nama, // Nama
                $item->jawatan_gred?->jawatan?->desc_jawatan, // Jawatan
                $item->jawatan_gred?->gred?->kod_gred, // Gred
                $item->lantikan, // Lantikan
                $item->nokp, // No KP
                $item->tarikh_lantik
                ? \Carbon\Carbon::parse($item->tarikh_lantik)->format('d.m.Y')
                : '',
                $item->ptj?->nama_ptj, // Tempat Bertugas
                $kedah, // Negeri / Institusi
                $item->alasan, // Alasan
                $item->jenis_notis, // Notis
                $item->tarikh_notis
                ? \Carbon\Carbon::parse($item->tarikh_notis)->format('d.m.Y')
                : '', // Tarikh Mula
                $item->tarikh_kuatkuasa
                ? \Carbon\Carbon::parse($item->tarikh_kuatkuasa)->format('d.m.Y')
                : '', // Tarikh Kuatkuasa
                $item->ikatan_jpa ? 'Ada' : 'Tiada',
                $item->ikatan_bpl ? 'Ada' : 'Tiada',
                $item->pinjaman_lppsa ? 'Ada' : 'Tiada',
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
            'Bil',
            'Nama',
            'Jawatan',
            'Gred',
            'Lantikan (Kontrak / Tetap)',
            'No KP',
            'Tarikh Lantik',
            'Tempat Bertugas',
            'Negeri / Institusi',
            'Alasan',
            'Notis (30 Hari @ 24 Jam)',
            'Tarikh Mula Notis',
            'Tarikh Kuatkuasa',
            'Ikatan JPA (Ada/Tiada)',
            'Ikatan BPL (Ada/Tiada)',
            'Pinjaman LPPSA (Perumahan) (Ada/TIada)',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $lastRow = $sheet->getHighestRow();


                // merge title row A1 to D1
                $sheet->mergeCells('A1:P1');

                $sheet->setCellValue('A1', 'Tempat Bertugas: JABATAN KESIHATAN NEGERI KEDAH');

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '000000'],

                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getRowDimension(1)->setRowHeight(30);

                $sheet->mergeCells('A2:P2');

                $sheet->setCellValue('A2', 'Negeri / Institusi : KEDAH');

                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '000000'],

                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->mergeCells('A3:P3');

                $monthNames = [
                    1 => 'JANUARI',
                    2 => 'FEBRUARI',
                    3 => 'MAC',
                    4 => 'APRIL',
                    5 => 'MEI',
                    6 => 'JUN',
                    7 => 'JULAI',
                    8 => 'OGOS',
                    9 => 'SEPTEMBER',
                    10 => 'OKTOBER',
                    11 => 'NOVEMBER',
                    12 => 'DISEMBER',
                ];

                $fromMonth = $this->fromMonth;
                $fromYear = $this->fromYear;
                $toMonth = $this->toMonth;
                $toYear = $this->toYear;

                // ALL
                if (!$fromMonth || !$fromYear || !$toMonth || !$toYear) {
                    $title = 'Bulan / Tahun : SEMUA';
                }

                // SAME RANGE (single month/year)
                elseif ($fromMonth === $toMonth && $fromYear === $toYear) {
                    $title = 'Bulan / Tahun : '
                        . $monthNames[(int) $fromMonth] . ' ' . $fromYear;
                }

                // RANGE
                else {
                    $title = 'Bulan / Tahun : Dari '
                        . $monthNames[(int) $fromMonth] . ' ' . $fromYear
                        . ' hingga '
                        . $monthNames[(int) $toMonth] . ' ' . $toYear;
                }
                $sheet->setCellValue('A3', $title);

                $sheet->getStyle('A3')->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '000000'],


                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A5:P5')->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => '000000'],
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => [
                            'rgb' => '9CC2E5', // no #
                        ],
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

                $sheet->getStyle('A6:P' . $lastRow)->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'bold' => false,
                        'size' => 11,
                        'color' => ['rgb' => '000000'],
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => [
                            'rgb' => 'deeaf6', // no #
                        ],
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

                $sheet->getColumnDimension('A')->setWidth(25);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(25);
                $sheet->getColumnDimension('F')->setWidth(25);
                $sheet->getColumnDimension('G')->setWidth(25);
                $sheet->getColumnDimension('H')->setWidth(25);
                $sheet->getColumnDimension('I')->setWidth(25);
                $sheet->getColumnDimension('J')->setWidth(25);
                $sheet->getColumnDimension('K')->setWidth(25);
                $sheet->getColumnDimension('L')->setWidth(25);
                $sheet->getColumnDimension('M')->setWidth(25);
                $sheet->getColumnDimension('N')->setWidth(25);
                $sheet->getColumnDimension('O')->setWidth(25);
                $sheet->getColumnDimension('P')->setWidth(25);


                $sheet->getRowDimension(5)->setRowHeight(45);

                $highestRow = $sheet->getHighestRow();

                for ($row = 6; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(66);
                }
            },
        ];
    }
}
