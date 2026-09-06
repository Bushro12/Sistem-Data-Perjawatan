<?php

// use Illuminate\Foundation\Inspiring;
// use Illuminate\Support\Facades\Artisan;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');

// Schedule::command('pegawai:delete-letak-jawatan')
//     ->everyFiveSeconds();

// Schedule::command('pegawai:delete-tamat-perkhidmatan')
//     ->everyFiveSeconds();

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => Artisan::call('pegawai:delete-letak-jawatan'))
    ->everyFiveSeconds();

Schedule::call(fn () => Artisan::call('pegawai:delete-tamat-perkhidmatan'))
    ->everyFiveSeconds();
