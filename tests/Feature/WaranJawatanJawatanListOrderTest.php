<?php

use App\Models\Jawatan;
use App\Models\WaranJawatan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('jawatans');

    Schema::create('jawatans', function (Blueprint $table) {
        $table->id();
        $table->string('desc_jawatan');
        $table->timestamps();
    });
});

test('jawatan list follows the selected order', function () {
    $pegawaiSains = Jawatan::query()->create(['desc_jawatan' => 'PEGAWAI SAINS']);
    $pegawaiSainsMakanan = Jawatan::query()->create(['desc_jawatan' => 'PEGAWAI SAINS (MAKANAN)']);

    $waranJawatan = new WaranJawatan([
        'jawatan_ids' => [$pegawaiSainsMakanan->id, $pegawaiSains->id],
    ]);

    expect($waranJawatan->jawatan_list)
        ->toBe('PEGAWAI SAINS (MAKANAN), PEGAWAI SAINS');
});
