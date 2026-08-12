<?php

namespace App\Filament\Pages\Auth;

use App\Filament\Pages\Auth\Concerns\VerifiesTurnstile;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    use VerifiesTurnstile;
    use WithRateLimiting;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nokp')
                    ->label('No. Kad Pengenalan')
                    ->required()
                    ->maxLength(12),
                Hidden::make('cfTurnstileResponse')
                    ->default(null),
                View::make('filament.components.turnstile'),
            ]);
    }

    public function request(): void
    {
        try {
            $this->rateLimit(3);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title('Terlalu banyak permintaan')
                ->body("Sila tunggu {$exception->secondsUntilAvailable} saat sebelum cuba lagi.")
                ->danger()
                ->send();

            return;
        }

        // Cloudflare Turnstile — fail-open when not configured.
        if (config('services.turnstile.secret_key') && ! $this->verifyTurnstile()) {
            return;
        }

        $data = $this->form->getState();

        $user = User::where('nokp', $data['nokp'])->first();

        if ($user) {
            $token = app('auth.password.broker')
                ->createToken($user);

            $notification = app(
                ResetPasswordNotification::class,
                ['token' => $token]
            );

            $notification->url = Filament::getResetPasswordUrl(
                $token,
                $user
            );

            $user->notify($notification);
        }

        Notification::make()
            ->title('Permintaan diterima')
            ->body('Jika maklumat wujud, pautan reset kata laluan telah dihantar ke emel berdaftar.')
            ->success()
            ->send();

        $this->form->fill();
    }
}
