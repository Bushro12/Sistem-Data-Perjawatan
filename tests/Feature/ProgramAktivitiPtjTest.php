<?php

use App\Models\Aktiviti;
use App\Models\Program;
use App\Models\Ptj;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('aktiviti_ptj');
    Schema::dropIfExists('program_ptj');
    Schema::dropIfExists('aktivitis');
    Schema::dropIfExists('programs');
    Schema::dropIfExists('ptjs');

    Schema::create('programs', function (Blueprint $table): void {
        $table->id();
        $table->string('nama_program');
        $table->string('desc_program')->nullable();
        $table->timestamps();
    });

    Schema::create('aktivitis', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('program_id');
        $table->string('no_aktivit', 20);
        $table->string('nama_aktiviti')->nullable();
        $table->timestamps();
    });

    Schema::create('ptjs', function (Blueprint $table): void {
        $table->id();
        $table->string('nama_ptj');
        $table->integer('kod_ptj')->default(0);
        $table->timestamps();
    });

    Schema::create('program_ptj', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('program_id');
        $table->unsignedBigInteger('ptj_id');
        $table->timestamps();
    });

    Schema::create('aktiviti_ptj', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('aktiviti_id');
        $table->unsignedBigInteger('ptj_id');
        $table->timestamps();
        $table->unique(['aktiviti_id', 'ptj_id']);
    });
});

it('stores aktiviti ptjs on the program and drops ptjs that are not on an aktiviti', function () {
    $program = Program::create(['nama_program' => 'PROGRAM 1']);
    $aktiviti = Aktiviti::create([
        'program_id' => $program->id,
        'no_aktivit' => '1.1.1',
        'nama_aktiviti' => 'PENGURUSAN',
    ]);

    $assigned = Ptj::create(['nama_ptj' => 'JABATAN KESIHATAN', 'kod_ptj' => 1]);
    $removed = Ptj::create(['nama_ptj' => 'PUSAT PENTADBIRAN', 'kod_ptj' => 2]);

    $program->ptjs()->attach($removed->id);
    $aktiviti->ptjs()->attach($assigned->id);

    $program->syncPtjsFromAktiviti();

    expect($aktiviti->ptjs()->pluck('ptjs.id')->all())->toBe([$assigned->id])
        ->and($program->ptjs()->pluck('ptjs.id')->all())->toBe([$assigned->id]);
});

it('lists only aktiviti assigned to the chosen ptj', function () {
    $program = Program::create(['nama_program' => 'PROGRAM 1']);
    $assigned = Aktiviti::create([
        'program_id' => $program->id,
        'no_aktivit' => '1.1.1',
        'nama_aktiviti' => 'PENGURUSAN',
    ]);
    Aktiviti::create([
        'program_id' => $program->id,
        'no_aktivit' => '1.1.2',
        'nama_aktiviti' => 'PENTADBIRAN',
    ]);

    $ptj = Ptj::create(['nama_ptj' => 'JABATAN KESIHATAN', 'kod_ptj' => 1]);
    $assigned->ptjs()->attach($ptj->id);

    expect($ptj->aktivitiSelectOptions())->toBe([
        $assigned->id => '1.1.1 - PENGURUSAN',
    ]);
});

it('lists only ptjs assigned to an aktiviti', function () {
    $program = Program::create(['nama_program' => 'PROGRAM 1']);
    $aktiviti = Aktiviti::create([
        'program_id' => $program->id,
        'no_aktivit' => '1.1.1',
        'nama_aktiviti' => 'PENGURUSAN',
    ]);

    $assigned = Ptj::create(['nama_ptj' => 'PTJ ASSIGNED', 'kod_ptj' => 1]);
    $other = Ptj::create(['nama_ptj' => 'PTJ OTHER', 'kod_ptj' => 2]);

    $aktiviti->ptjs()->attach($assigned->id);

    expect(Aktiviti::ptjSelectOptionsFor($aktiviti->id))
        ->toBe([$assigned->id => 'PTJ ASSIGNED'])
        ->and(Aktiviti::ptjSelectOptionsFor($aktiviti->id, $other->id))
        ->toBe([
            $assigned->id => 'PTJ ASSIGNED',
            $other->id => 'PTJ OTHER',
        ])
        ->and(Aktiviti::ptjSelectOptionsFor(null))->toBe([])
        ->and(array_key_exists($other->id, Aktiviti::ptjSelectOptionsFor($aktiviti->id)))->toBeFalse();
});
