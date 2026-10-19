<?php

use App\Models\Gred;
use App\Models\Jawatan;
use App\Models\Jawatan_Gred;
use App\Models\Pegawai;
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

    Schema::dropIfExists('greds');

    Schema::create('greds', function (Blueprint $table) {
        $table->id();
        $table->string('kod_gred');
        $table->string('desc_gred')->nullable();
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

test('jawatan gred display places KUP between assigned and remaining posts', function () {
    $penolongAkauntan = Jawatan::query()->create(['desc_jawatan' => 'PENOLONG AKAUNTAN']);
    $pembantuTadbir = Jawatan::query()->create(['desc_jawatan' => 'PEMBANTU TADBIR (KEWANGAN)']);
    $gredW5 = Gred::query()->create(['kod_gred' => 'W5']);
    $gredW1 = Gred::query()->create(['kod_gred' => 'W1']);

    $jawatanGred = new Jawatan_Gred;
    $jawatanGred->setRelation('jawatan', $penolongAkauntan);
    $jawatanGred->setRelation('gred', $gredW5);

    $pegawai = new Pegawai(['is_kup' => true]);
    $pegawai->setRelation('jawatan_gred', $jawatanGred);

    $waranJawatan = new WaranJawatan([
        'jawatan_ids' => [$penolongAkauntan->id, $pembantuTadbir->id],
        'gred_ids' => [$gredW5->id, $gredW1->id],
        'is_kup' => true,
    ]);
    $waranJawatan->setRelation('pegawai', $pegawai);

    expect($waranJawatan->jawatan_gred_display)
        ->toBe('PENOLONG AKAUNTAN W5 KUP, PEMBANTU TADBIR (KEWANGAN) W1');
});
