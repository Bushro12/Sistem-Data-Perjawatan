<?php

namespace App\Filament\Pages\Auth;

use App\Filament\Pages\Auth\Concerns\VerifiesTurnstile;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    use VerifiesTurnstile;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                Hidden::make('cfTurnstileResponse')
                    ->default(null),
                View::make('filament.components.turnstile'),
            ]);
    }

    public function authenticate(): ?LoginResponse
    {
        // Fail-open when Turnstile is not configured (local dev without keys).
        if (config('services.turnstile.secret_key') && ! $this->verifyTurnstile()) {
            return null;
        }

        return parent::authenticate();
    }

    protected function getEmailFormComponent(): TextInput
    {
        return TextInput::make('nokp')
            ->label('No. Kad Pengenalan')
            ->required()
            ->autofocus();
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'nokp' => $data['nokp'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        // Token is single-use - reset widget so user can retry without page refresh
        if (filled(config('services.turnstile.site_key'))) {
            $this->data['cfTurnstileResponse'] = null;
            $this->dispatch('cf-turnstile-reset');
            try {
                $this->js('window.dispatchEvent(new CustomEvent("cf-turnstile-reset"))');
            } catch (\Throwable $e) {
            }
        }

        throw ValidationException::withMessages([
            'data.nokp' => 'No. Kad Pengenalan atau kata laluan tidak sah.',
        ]);
    }

    public function getView(): string
    {
        return 'filament.pages.auth.login';
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return null;
    }

    public function hasLogo(): bool
    {
        return false;
    }

    protected function redirectTo(): string
    {
        return '/app/dashboard';
    }
}
