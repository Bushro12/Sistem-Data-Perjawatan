<?php

namespace App\Filament\Pages\Auth;

// use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    public function getView(): string
    {
        return 'filament.pages.auth.login';
    }

    public function getHeading(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function getSubheading(): string | \Illuminate\Contracts\Support\Htmlable | null
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