<?php

namespace App\Exports;

use App\Models\Pegawai;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class DataKontrakExport implements
// FromCollection,
WithEvents
{

    /**
     * @return \Illuminate\Support\Collection
     */

    // public function collection()
    // {
    //     $data = 'Test';

    //     // return $data;
    // }

    public function registerEvents():array
    {
        return[
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:S1');
            }
        ];
    }
}


