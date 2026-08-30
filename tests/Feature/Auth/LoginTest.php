<?php

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Local .env may hold real Turnstile keys; force fail-open so the
    // captcha branch never blocks programmatic logins under test.
    config(['services.turnstile.secret_key' => null]);
});

function loginUser(): User
{
    return User::factory()->create([
        'name' => 'PENGUNA UJIAN',
        'nokp' => '990101011234',
        'password' => 'katalaluan-rahsia',
        'ptj_id' => 1,
        'phone_number' => '0123456789',
        'status' => true,
        'role' => 1,
    ]);
}

it('renders the login page', function () {
    $this->get('/app/login')
        ->assertSuccessful()
        ->assertSee('No. Kad Pengenalan');
});

it('logs in with a valid nokp and password', function () {
    $user = loginUser();

    Livewire::test(Login::class)
        ->fillForm([
            'nokp' => '990101011234',
            'password' => 'katalaluan-rahsia',
        ])
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect('/app');

    $this->assertAuthenticatedAs($user);
});

it('rejects an invalid password', function () {
    loginUser();

    Livewire::test(Login::class)
        ->fillForm([
            'nokp' => '990101011234',
            'password' => 'salah-betul-betul',
        ])
        ->call('authenticate')
        ->assertHasErrors(['data.nokp']);

    $this->assertGuest();
});

it('rejects an unknown nokp', function () {
    loginUser();

    Livewire::test(Login::class)
        ->fillForm([
            'nokp' => '000000000000',
            'password' => 'katalaluan-rahsia',
        ])
        ->call('authenticate')
        ->assertHasErrors(['data.nokp']);

    $this->assertGuest();
});

it('requires nokp and password', function () {
    loginUser();

    Livewire::test(Login::class)
        ->fillForm([
            'nokp' => '',
            'password' => '',
        ])
        ->call('authenticate')
        ->assertHasErrors(['data.nokp', 'data.password']);

    $this->assertGuest();
});
