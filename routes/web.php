<?php

use App\Exports\JikByJawatanExport;
use App\Http\Controllers\DataKontrakExportController;
use App\Http\Controllers\JikByJawatanExportController;
use App\Http\Controllers\LetakJawatanExportController;
use App\Http\Controllers\PenamatanPerkhidmatanExportController;
use App\Http\Controllers\DataKeseluruhanExportController;
use App\Http\Controllers\UserExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/export-users', [UserExportController::class, 'export'])
    ->middleware('auth')
    ->name('export.users');

// Route::get('/export-letakJawatan', [LetakJawatanExportController::class, 'export'])
//     ->name('export.letakJawatan');

Route::get('/export-letak-jawatan', [LetakJawatanExportController::class, 'export'])
    ->name('export.letakJawatan');

Route::get('/export-penamatan-perkhidmatan', [PenamatanPerkhidmatanExportController::class, 'export'])
    ->name('export.penamatanPerkhidmatan');

Route::get('/export-data-keseluruhan', [DataKeseluruhanExportController::class, 'export'])
    ->name('export.dataKeseluruhan');

Route::get('/export-data-kontrak', [DataKontrakExportController::class, 'export'])
    ->name('export.dataKontrak');

Route::get('/export-jik-by-jawatan', [JikByJawatanExportController::class, 'export'])
    ->name('export.jikByJawatan');
