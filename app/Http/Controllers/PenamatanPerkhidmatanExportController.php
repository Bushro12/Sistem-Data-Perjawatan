<?php

namespace App\Http\Controllers;

use App\Exports\PenamatanPerkhidmatanExport;
use App\Models\Pencen;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PenamatanPerkhidmatanExportController extends Controller
{
    public function export(Request $request)
    {
        $query = Pencen::query();

        $jenis_pencen_id = $request->jenis_pencen_id;

        $count = $query->where('jenis_pencen_id', $jenis_pencen_id)->count();

        if ($count === 0) {
            Notification::make()
                ->title('Export Gagal')
                ->body('Tiada data untuk tempoh yang dipilih')
                ->danger()
                ->send();

            return back();
        }
        return Excel::download(
            new PenamatanPerkhidmatanExport(
                $request['jenis_pencen_id']
            ),
            'Laporan Penamatan Perkhidmatan.xlsx'
        );
    }
}
