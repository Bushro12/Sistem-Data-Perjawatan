<?php

use App\Models\Parlimen;
use App\Models\Dun;
use App\Models\User;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'ADMIN TEST',
        'email' => 'admin-test-parlimen@moh.gov.my',
        'password' => bcrypt('password'),
        'ptj_id' => 1,
        'nokp' => '990101011234',
        'phone_number' => '0123456789',
        'status' => true,
        'role' => 1,
    ]);

    $this->actingAs($this->user);

    // Track created records for cleanup
    $this->createdParlimenIds = collect();
    $this->createdUserIds = collect([$this->user->id]);
});

afterEach(function () {
    // Clean up DUNs
    Dun::whereIn('parlimen_id', $this->createdParlimenIds)->delete();
    // Clean up Parlimens
    Parlimen::whereIn('id', $this->createdParlimenIds)->delete();
    // Clean up test user
    User::whereIn('id', $this->createdUserIds)->delete();
});

// --- List Page ---

it('can render the parlimen list page', function () {
    $this->get('/app/parlimens')->assertSuccessful();
});

it('displays parlimen data in the table', function () {
    $parlimen = Parlimen::create(['nama_parlimen' => 'PARLIMEN TEST LIST']);
    $this->createdParlimenIds->push($parlimen->id);

    $this->get('/app/parlimens')
        ->assertSuccessful()
        ->assertSee('PARLIMEN TEST LIST');
});

it('displays DUN names in the parlimen table', function () {
    $parlimen = Parlimen::create(['nama_parlimen' => 'PARLIMEN TEST DUN']);
    $this->createdParlimenIds->push($parlimen->id);

    Dun::create(['parlimen_id' => $parlimen->id, 'nama_dun' => 'DUN SATU LIST']);
    Dun::create(['parlimen_id' => $parlimen->id, 'nama_dun' => 'DUN DUA LIST']);

    $this->get('/app/parlimens')
        ->assertSuccessful()
        ->assertSee('DUN SATU LIST')
        ->assertSee('DUN DUA LIST');
});

it('can search parlimen by name', function () {
    $parlimenA = Parlimen::create(['nama_parlimen' => 'PARLIMEN LANGKAWI SEARCH']);
    $parlimenB = Parlimen::create(['nama_parlimen' => 'PARLIMEN KULIM SEARCH']);
    $this->createdParlimenIds->push($parlimenA->id, $parlimenB->id);

    $this->get('/app/parlimens?tableSearch=LANGKAWI SEARCH')
        ->assertSuccessful()
        ->assertSee('PARLIMEN LANGKAWI SEARCH')
        ->assertDontSee('PARLIMEN KULIM SEARCH');
});

it('can search parlimen by DUN name', function () {
    $parlimenA = Parlimen::create(['nama_parlimen' => 'PARLIMEN A SEARCH']);
    $parlimenB = Parlimen::create(['nama_parlimen' => 'PARLIMEN B SEARCH']);
    $this->createdParlimenIds->push($parlimenA->id, $parlimenB->id);

    Dun::create(['parlimen_id' => $parlimenA->id, 'nama_dun' => 'DUN KOTA SETAR SEARCH']);
    Dun::create(['parlimen_id' => $parlimenB->id, 'nama_dun' => 'DUN PADANG SERAI SEARCH']);

    $this->get('/app/parlimens?tableSearch=KOTA SETAR SEARCH')
        ->assertSuccessful()
        ->assertSee('PARLIMEN A SEARCH')
        ->assertDontSee('PARLIMEN B SEARCH');
});

// --- Create Page ---

it('can render the create parlimen page', function () {
    $this->get('/app/parlimens/create')->assertSuccessful();
});

it('can create a parlimen', function () {
    $this->post('/app/parlimens', [
        'data' => [
            'nama_parlimen' => 'PARLIMEN NEW CREATE',
            'duns' => [],
        ],
    ])->assertRedirect();

    $parlimen = Parlimen::where('nama_parlimen', 'PARLIMEN NEW CREATE')->first();
    expect($parlimen)->not->toBeNull();
    $this->createdParlimenIds->push($parlimen->id);
});

it('can create a parlimen with DUNs', function () {
    $this->post('/app/parlimens', [
        'data' => [
            'nama_parlimen' => 'PARLIMEN WITH DUN CREATE',
            'duns' => [
                ['nama_dun' => 'DUN SATU CREATE'],
                ['nama_dun' => 'DUN DUA CREATE'],
            ],
        ],
    ])->assertRedirect();

    $parlimen = Parlimen::where('nama_parlimen', 'PARLIMEN WITH DUN CREATE')->first();
    expect($parlimen)->not->toBeNull();
    $this->createdParlimenIds->push($parlimen->id);

    $duns = Dun::where('parlimen_id', $parlimen->id)->get();
    expect($duns)->toHaveCount(2);
    expect($duns->pluck('nama_dun')->toArray())->toEqual(['DUN SATU CREATE', 'DUN DUA CREATE']);
});

it('can create parlimen with uppercase DUN names', function () {
    $this->post('/app/parlimens', [
        'data' => [
            'nama_parlimen' => 'PARLIMEN UPPER CREATE',
            'duns' => [
                ['nama_dun' => 'dun kecil create'],
            ],
        ],
    ])->assertRedirect();

    $parlimen = Parlimen::where('nama_parlimen', 'PARLIMEN UPPER CREATE')->first();
    expect($parlimen)->not->toBeNull();
    $this->createdParlimenIds->push($parlimen->id);

    $dun = Dun::where('parlimen_id', $parlimen->id)->first();
    expect($dun->nama_dun)->toBe('DUN KECIL CREATE');
});

it('validates parlimen name is required', function () {
    $this->post('/app/parlimens', [
        'data' => [
            'nama_parlimen' => '',
            'duns' => [],
        ],
    ])->assertSessionHasErrors(['data.nama_parlimen']);
});

it('validates DUN name is required when adding DUN', function () {
    $this->post('/app/parlimens', [
        'data' => [
            'nama_parlimen' => 'PARLIMEN VALIDATE DUN',
            'duns' => [
                ['nama_dun' => ''],
            ],
        ],
    ])->assertSessionHasErrors(['data.duns.0.nama_dun']);
});

// --- Edit Page ---

it('can render the edit parlimen page', function () {
    $parlimen = Parlimen::create(['nama_parlimen' => 'PARLIMEN EDIT PAGE']);
    $this->createdParlimenIds->push($parlimen->id);

    $this->get("/app/parlimens/{$parlimen->id}/edit")->assertSuccessful();
});

it('can update a parlimen', function () {
    $parlimen = Parlimen::create(['nama_parlimen' => 'PARLIMEN OLD UPDATE']);
    $this->createdParlimenIds->push($parlimen->id);

    $this->patch("/app/parlimens/{$parlimen->id}", [
        'data' => [
            'nama_parlimen' => 'PARLIMEN UPDATED',
            'duns' => [],
        ],
    ])->assertRedirect();

    $this->assertDatabaseHas('parlimens', [
        'id' => $parlimen->id,
        'nama_parlimen' => 'PARLIMEN UPDATED',
    ]);
});

it('can add DUNs to an existing parlimen', function () {
    $parlimen = Parlimen::create(['nama_parlimen' => 'PARLIMEN ADD DUN']);
    $this->createdParlimenIds->push($parlimen->id);

    $this->patch("/app/parlimens/{$parlimen->id}", [
        'data' => [
            'nama_parlimen' => 'PARLIMEN ADD DUN',
            'duns' => [
                ['nama_dun' => 'DUN BARU EDIT'],
            ],
        ],
    ])->assertRedirect();

    $this->assertDatabaseHas('duns', [
        'parlimen_id' => $parlimen->id,
        'nama_dun' => 'DUN BARU EDIT',
    ]);
});

it('can remove DUNs from a parlimen', function () {
    $parlimen = Parlimen::create(['nama_parlimen' => 'PARLIMEN REMOVE DUN']);
    $dun = Dun::create(['parlimen_id' => $parlimen->id, 'nama_dun' => 'DUN TO REMOVE']);
    $this->createdParlimenIds->push($parlimen->id);

    $this->patch("/app/parlimens/{$parlimen->id}", [
        'data' => [
            'nama_parlimen' => 'PARLIMEN REMOVE DUN',
            'duns' => [],
        ],
    ])->assertRedirect();

    $this->assertDatabaseMissing('duns', ['id' => $dun->id]);
});

// --- Delete ---

it('can delete a parlimen', function () {
    $parlimen = Parlimen::create(['nama_parlimen' => 'PARLIMEN DELETE']);

    $this->delete("/app/parlimens/{$parlimen->id}")->assertRedirect();

    $this->assertDatabaseMissing('parlimens', ['id' => $parlimen->id]);
});

it('can delete a parlimen and its DUNs', function () {
    $parlimen = Parlimen::create(['nama_parlimen' => 'PARLIMEN DEL ALL']);
    Dun::create(['parlimen_id' => $parlimen->id, 'nama_dun' => 'DUN 1 DEL']);
    Dun::create(['parlimen_id' => $parlimen->id, 'nama_dun' => 'DUN 2 DEL']);

    $this->delete("/app/parlimens/{$parlimen->id}")->assertRedirect();

    $this->assertDatabaseMissing('parlimens', ['id' => $parlimen->id]);
    $this->assertDatabaseMissing('duns', ['parlimen_id' => $parlimen->id]);
});

// --- Uniqueness ---

it('validates parlimen name is unique', function () {
    $parlimen = Parlimen::create(['nama_parlimen' => 'PARLIMEN UNIQUE TEST']);
    $this->createdParlimenIds->push($parlimen->id);

    $this->post('/app/parlimens', [
        'data' => [
            'nama_parlimen' => 'PARLIMEN UNIQUE TEST',
            'duns' => [],
        ],
    ])->assertSessionHasErrors(['data.nama_parlimen']);
});

// --- Multiple DUNs ---

it('can create parlimen with multiple DUNs', function () {
    $this->post('/app/parlimens', [
        'data' => [
            'nama_parlimen' => 'PARLIMEN MULTI DUN',
            'duns' => [
                ['nama_dun' => 'DUN PERTAMA MULTI'],
                ['nama_dun' => 'DUN KEDUA MULTI'],
                ['nama_dun' => 'DUN KETIGA MULTI'],
            ],
        ],
    ])->assertRedirect();

    $parlimen = Parlimen::where('nama_parlimen', 'PARLIMEN MULTI DUN')->first();
    expect($parlimen)->not->toBeNull();
    $this->createdParlimenIds->push($parlimen->id);

    expect($parlimen->duns)->toHaveCount(3);
});
