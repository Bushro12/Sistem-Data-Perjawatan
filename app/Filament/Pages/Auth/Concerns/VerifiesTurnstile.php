<?php

namespace App\Filament\Pages\Auth\Concerns;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

trait VerifiesTurnstile
{
    protected function verifyTurnstile(): bool
    {
        $token = data_get($this->data, 'cfTurnstileResponse');

        if (blank($token)) {
            $this->sendTurnstileError();

            return false;
        }

        $response = Http::asForm()
            ->timeout(10)
            ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => config('services.turnstile.secret_key'),
                'response' => $token,
                'remoteip' => request()->ip(),
            ]);

        if (! $response->successful() || ! $response->json('success')) {
            $this->sendTurnstileError();

            return false;
        }

        return true;
    }

    protected function sendTurnstileError(): void
    {
        Notification::make()
            ->title('Pengesahan Turnstile gagal')
            ->body('Sila sahkan anda bukan robot sebelum meneruskan.')
            ->danger()
            ->send();
    }
}
