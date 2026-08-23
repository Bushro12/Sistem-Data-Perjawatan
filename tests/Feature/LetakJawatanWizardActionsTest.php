<?php

use App\Filament\Resources\LetakJawatans\Pages\CreateLetakJawatan;
use App\Filament\Resources\LetakJawatans\Pages\EditLetakJawatan;
use App\Models\LetakJawatan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    config(['app.env' => 'local']);

    $this->user = User::create([
        'name' => 'ADMIN TEST',
        'email' => 'admin-test-lj@moh.gov.my',
        'password' => bcrypt('password'),
        'ptj_id' => 1,
        'nokp' => '990101015678',
        'phone_number' => '0123456789',
        'status' => true,
        'role' => 1,
    ]);

    $this->actingAs($this->user);

    $this->jawatanGredId = DB::table('jawatan__greds')->min('id');

    $this->pegawaiId = DB::table('pegawais')->insertGetId([
        'ptj_id' => 1,
        'bahagian_id' => 1,
        'unit_id' => 1,
        'subunit_id' => 1,
        'jawatan_gred_id' => $this->jawatanGredId,
        'nama' => 'PEGAWAI WIZARD',
        'nokp' => '234567890123',
        'jantina' => 'L',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->ljRecordIds = collect();
});

afterEach(function () {
    DB::table('letak_jawatans')
        ->where(function ($query) {
            $query->whereIn('id', $this->ljRecordIds)
                ->orWhere('nokp', '234567890123');
        })
        ->delete();

    DB::table('pegawais')->where('nokp', '234567890123')->delete();
    DB::table('users')->where('email', 'admin-test-lj@moh.gov.my')->delete();
});

function createLjRecord(): int
{
    $id = DB::table('letak_jawatans')->insertGetId([
        'ptj_id' => 1,
        'jawatan_gred_id' => test()->jawatanGredId,
        'nama' => 'PEGAWAI UJIAN',
        'nokp' => '345678901234',
        'tarikh_notis' => '2026-08-01',
        'tarikh_kuatkuasa' => '2026-08-31',
        'jenis_notis' => '30 Hari',
        'alasan' => 'Ujian asal',
        'tarikh_lantik' => '2020-01-01',
        'lantikan' => 'Tetap',
        'ikatan_jpa' => false,
        'ikatan_bpl' => false,
        'pinjaman_lppsa' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    test()->ljRecordIds->push($id);

    return $id;
}

function wizardFormData(): array
{
    return [
        'pegawai_id' => test()->pegawaiId,
        'nama' => 'PEGAWAI WIZARD',
        'nokp' => '234567890123',
        'jawatan_display' => 'PEGAWAI UJIAN (G17)',
        'ptj_display' => 'PTJ UJIAN',
        'lantikan' => 'Tetap',
        'tarikh_lantik' => '2020-01-01',
        'jenis_notis' => '30 Hari',
        'tarikh_notis' => '2026-08-01',
        'tarikh_kuatkuasa' => '2026-08-31',
        'alasan' => 'Ujian alasan',
        'ikatan_jpa' => true,
        'ikatan_bpl' => false,
    ];
}

it('renders the create page with the wizard Simpan button and Batal', function () {
    $response = $this->get('/app/letak-jawatans/create');

    $response->assertSuccessful();

    $html = $response->getContent();

    expect(str_contains($html, 'Simpan'))->toBeTrue()
        ->and(str_contains($html, 'Batal'))->toBeTrue()
        ->and(substr_count($html, 'wire:click="validateBeforeSubmit"'))->toBe(1)
        ->and(substr_count($html, 'type="submit"'))->toBe(0);
});

it('saves via the wizard Simpan confirmation flow on create', function () {
    Livewire::test(CreateLetakJawatan::class)
        ->fillForm(wizardFormData())
        ->call('validateBeforeSubmit')
        ->assertActionMounted('confirmCreate')
        ->unmountAction()
        ->callAction('confirmCreate')
        ->assertHasNoErrors();

    $record = LetakJawatan::query()->where('nokp', '234567890123')->first();

    expect($record)->not->toBeNull()
        ->and($record->nama)->toBe('PEGAWAI WIZARD')
        ->and((bool) $record->ikatan_jpa)->toBeTrue()
        ->and((bool) $record->ikatan_bpl)->toBeFalse()
        ->and((bool) $record->pinjaman_lppsa)->toBeFalse();
});

it('blocks the confirmation modal when validation fails on create', function () {
    $component = Livewire::test(CreateLetakJawatan::class)
        ->fillForm(wizardFormData())
        ->fillForm(['alasan' => ''])
        ->call('validateBeforeSubmit')
        ->assertHasErrors();

    expect($component->instance()->getMountedActions())->toBeEmpty();
});

it('renders the edit page without a Simpan button next to Batal', function () {
    $id = createLjRecord();

    $response = $this->get("/app/letak-jawatans/{$id}/edit");

    $response->assertSuccessful();

    $html = $response->getContent();

    expect(str_contains($html, 'Simpan'))->toBeTrue()
        ->and(str_contains($html, 'Batal'))->toBeTrue()
        ->and(substr_count($html, 'wire:click="validateBeforeSubmit"'))->toBe(1)
        ->and(substr_count($html, 'type="submit"'))->toBe(0);
});

it('saves via the wizard Simpan confirmation flow on edit', function () {
    $id = createLjRecord();

    Livewire::test(EditLetakJawatan::class, ['record' => $id])
        ->fillForm([
            'alasan' => 'Kemaskini ujian',
            'ikatan_jpa' => true,
            'pinjaman_lppsa' => true,
        ])
        ->call('validateBeforeSubmit')
        ->assertActionMounted('confirmSave')
        ->unmountAction()
        ->callAction('confirmSave')
        ->assertHasNoErrors();

    $record = LetakJawatan::query()->find($id);

    expect($record->alasan)->toBe('Kemaskini ujian')
        ->and((bool) $record->ikatan_jpa)->toBeTrue()
        ->and((bool) $record->pinjaman_lppsa)->toBeTrue();
});
