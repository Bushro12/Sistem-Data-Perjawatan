<?php

namespace App\Http\Controllers;

use App\Exports\DataKeseluruhanExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;

class DataKeseluruhanExportController extends Controller
{
    public function export(Request $request)
    {
        // $query = LetakJawatan::query();

        // $from = \Carbon\Carbon::create($request->from_year, $request->from_month, 1)->startOfDay();
        // $to = \Carbon\Carbon::create($request->to_year, $request->to_month, 1)->endOfMonth();

        // $count = $query->whereBetween('tarikh_kuatkuasa', [$from, $to])->count();

        // if ($count === 0) {
        //     Notification::make()
        //         ->title('Export gagal')
        //         ->body('Tiada data untuk tempoh yang dipilih.')
        //         ->danger()
        //         ->send();

        //     return back(); // stop export
        // }

        return Excel::download(
            new DataKeseluruhanExport(),
            'data_keseluruhan.xlsx'
        );
    }
}
