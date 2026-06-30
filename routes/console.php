<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('pegawai:delete-letak-jawatan')
    ->daily();

Schedule::command('pegawai:delete-tamat-perkhidmatan')
    ->everyFifteenSeconds();
