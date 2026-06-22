<?php

use App\Http\Controllers\LetakJawatanExportController;
use App\Http\Controllers\UserExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/export-users', [UserExportController::class, 'export'])
    ->name('export.users');

// Route::get('/export-letakJawatan', [LetakJawatanExportController::class, 'export'])
//     ->name('export.letakJawatan');

Route::get('/export-letak-jawatan', [LetakJawatanExportController::class, 'export'])
    ->name('export.letakJawatan');
