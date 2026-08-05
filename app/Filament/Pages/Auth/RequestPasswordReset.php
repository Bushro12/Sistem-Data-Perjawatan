<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;
use Filament\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\CanResetPassword;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Models\Contracts\FilamentUser;
use LogicException;
class RequestPasswordReset extends BaseRequestPasswordReset
{
    use WithRateLimiting;
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nokp')
                    ->label('No. Kad Pengenalan')
                    ->required()
                    ->maxLength(12),
            ]);
    }

    public function request(): void
{
    try {
        $this->rateLimit(1);
    } catch (TooManyRequestsException $exception) {
        Notification::make()
            ->title('Terlalu banyak permintaan')
            ->body("Sila tunggu {$exception->secondsUntilAvailable} saat sebelum cuba lagi.")
            ->danger()
            ->send();

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
