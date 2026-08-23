<?php

namespace App\Http\Controllers;

use App\Exports\LetakJawatanExport;
use App\Models\LetakJawatan;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LetakJawatanExportController extends Controller
{
    public function export(Request $request)
    {
        $query = LetakJawatan::query();

        $from = Carbon::create($request->from_year, $request->from_month, 1)->startOfDay();
        $to = Carbon::create($request->to_year, $request->to_month, 1)->endOfMonth();

        $count = $query->whereBetween('tarikh_kuatkuasa', [$from, $to])->count();

        if ($count === 0) {
            Notification::make()
                ->title('Export gagal')
                ->body('Tiada data pada bulan dan tahun yang dipilih')
                ->danger()
                ->send();

            return back(); // stop export
        }

        return Excel::download(
            new LetakJawatanExport(
                $request->from_month,
                $request->from_year,
                $request->to_month,
                $request->to_year
            ),
            'letak_jawatan.xlsx'
        );
    }
}
