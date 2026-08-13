<?php

namespace App\Http\Controllers;

use App\Exports\DataKontrakExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DataKontrakExportController
{

    public function export(Request $request)
    {
        return Excel::download(
            new DataKontrakExport(),
            'data_kontrak.xlsx'
        );
    }
}
