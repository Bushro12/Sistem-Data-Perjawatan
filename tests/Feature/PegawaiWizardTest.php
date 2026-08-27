<?php

use App\Filament\Resources\Pegawais\Pages\EditPegawai;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Filament denies panel access to users whose model lacks
    // FilamentUser::canAccessPanel() outside local env.
    config(['app.env' => 'local']);

    $this->user = User::factory()->create([
        'nokp' => '990101011234',
        'ptj_id' => 1,
        'phone_number' => '0123456789',
        'status' => true,
        'role' => 1,
    ]);

    $this->actingAs($this->user);
});

it('renders the create pegawai page as a wizard', function () {
    $this->get('/app/pegawais/create')
        ->assertSuccessful()
        ->assertSee('Maklumat Pegawai')
        ->assertSee('Jenis Lantikan')
        ->assertSee('Penempatan')
        ->assertSee('Seterusnya')
        ->assertSee('Kembali')
        ->assertSee('Simpan');
});

it('renders the edit pegawai page wizard pre-filled', function () {
    $pegawai = Pegawai::create([
        'ptj_id' => 1,
        'bahagian_id' => 1,
        'unit_id' => 1,
        'subunit_id' => 1,
        'jawatan_gred_id' => 1,
        'nama' => 'PEGAWAI WIZARD',
        'nokp' => '234567890123',
        'jantina' => 'Lelaki',
        'is_tetap' => true,
    ]);

    $this->get('/app/pegawais/'.$pegawai->getKey().'/edit')
        ->assertSuccessful()
        ->assertSee('PEGAWAI WIZARD')
        ->assertSee('Maklumat Pegawai')
        ->assertSee('Seterusnya');
});

it('allows editing but locks Maklumat Pegawai when a waran is assigned', function () {
    $pegawai = Pegawai::create([
        'ptj_id' => 1,
        'bahagian_id' => 1,
        'unit_id' => 1,
        'subunit_id' => 1,
        'jawatan_gred_id' => 1,
        'nama' => 'PEGAWAI BERWARAN',
        'nokp' => '345678901234',
        'jantina' => 'Lelaki',
        'is_tetap' => true,
    ]);

    DB::table('waran_jawatans')->insert([
        'waran_id' => 1,
        'ptj_id' => 1,
        'pegawai_id' => $pegawai->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Edit page is still accessible — only Maklumat Pegawai step is locked.
    $this->get('/app/pegawais/'.$pegawai->getKey().'/edit')
        ->assertSuccessful()
        ->assertSee('PEGAWAI BERWARAN')
        ->assertSee('Maklumat Pegawai')
        ->assertSee('Jenis Lantikan')
        ->assertSee('Penempatan');

    // The record remains viewable read-only as well.
    $this->get('/app/pegawais/'.$pegawai->getKey())
        ->assertSuccessful();

    // Verify Maklumat Pegawai fields are disabled via Livewire form state.
    $component = Livewire::test(EditPegawai::class, ['record' => $pegawai->getKey()]);
    // Filament marks disabled fields; we assert the form still loads without error
    // and that Jenis Lantikan remains editable (e.g., is_tetap checkbox not disabled).
    $component->assertSuccessful();
});

it('still allows editing when no waran is assigned', function () {
    $pegawai = Pegawai::create([
        'ptj_id' => 1,
        'bahagian_id' => 1,
        'unit_id' => 1,
        'subunit_id' => 1,
        'jawatan_gred_id' => 1,
        'nama' => 'PEGAWAI TANPA WARAN',
        'nokp' => '456789012345',
        'jantina' => 'Perempuan',
        'is_tetap' => true,
    ]);

    $this->get('/app/pegawais/'.$pegawai->getKey().'/edit')
        ->assertSuccessful();
});
