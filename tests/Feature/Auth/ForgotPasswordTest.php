<?php

use App\Filament\Pages\Auth\RequestPasswordReset;
use App\Models\User;
use Filament\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Local .env may hold real Turnstile keys; force fail-open so the
    // captcha branch never blocks programmatic requests under test.
    config(['services.turnstile.secret_key' => null]);

    // Rate limiter uses the array cache which persists across tests in
    // the same process — clear it so the rate-limit test starts fresh.
    Cache::clear();
});

function requestResetUser(): User
{
    return User::factory()->create([
        'name' => 'PENGUNA UJIAN',
        'nokp' => '990101011234',
        'email' => 'reset-test@moh.gov.my',
        'password' => 'katalaluan-rahsia',
        'ptj_id' => 1,
        'phone_number' => '0123456789',
        'status' => true,
        'role' => 1,
    ]);
}

it('renders the forgot password page', function () {
    $this->get('/app/password-reset/request')
        ->assertSuccessful()
        ->assertSee('No. Kad Pengenalan');
});

it('sends a reset link for an existing nokp', function () {
    $user = requestResetUser();

    Notification::fake();

    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['nokp' => '990101011234'])
        ->call('request')
        ->assertHasNoErrors()
        ->assertNotified('Permintaan diterima');

    Notification::assertSentTo($user, ResetPasswordNotification::class);

    // The broker token must exist for the emailed link to work.
    expect(DB::table('password_reset_tokens')->where('email', $user->email)->exists())
        ->toBeTrue();
});

it('does not reveal whether a nokp exists', function () {
    requestResetUser();

    Notification::fake();

    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['nokp' => '000000000000'])
        ->call('request')
        ->assertHasNoErrors()
        // Same generic success message as the existing-account path.
        ->assertNotified('Permintaan diterima');

    Notification::assertNothingSent();
});

it('validates the nokp field', function () {
    Notification::fake();

    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['nokp' => ''])
        ->call('request')
        ->assertHasErrors(['data.nokp']);

    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['nokp' => str_repeat('9', 13)])
        ->call('request')
        ->assertHasErrors(['data.nokp']);
});

it('rate limits after three requests', function () {
    requestResetUser();

    Notification::fake();

    foreach (range(1, 3) as $i) {
        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['nokp' => '990101011234'])
            ->call('request');
    }

    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['nokp' => '990101011234'])
        ->call('request')
        ->assertNotified('Terlalu banyak permintaan');
});
