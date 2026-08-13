<?php

namespace App\Http\Controllers;

use App\Exports\JikByJawatanExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;

class JikByJawatanExportController extends Controller
{
    public function export(Request $request)
    {

return Excel::download(
    new JikByJawatanExport($request->jawatan_id),
    'JIK.xlsx'
);
    }
}
