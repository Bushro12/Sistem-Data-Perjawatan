<?php

namespace App\Exports;

use App\Models\Waran;
use Maatwebsite\Excel\Concerns\FromCollection;

class DataKeseluruhanExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
       return Waran::select(
        'no_waran',
        // 'tahun',
        // 'status'
    )->get();
    }
}
