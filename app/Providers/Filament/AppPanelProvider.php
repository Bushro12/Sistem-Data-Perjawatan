<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Resources\WaranJawatans\Widgets\NamaPenyandang;
use Filament\Actions\Action;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;



class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('app')
            ->homeUrl('/app')
            ->brandLogo(view('filament.brand-logo'))
            ->brandLogoHeight('auto')
            ->brandName('MySTAFF')
            ->favicon(asset('images/mystaff-logo-clean.png'))
            ->viteTheme('resources/css/filament/app/theme.css')
            ->font('Public Sans')
            ->darkMode(true)
            ->spa()
            // ->login()
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->passwordReset(\App\Filament\Pages\Auth\RequestPasswordReset::class,
)
            ->profile(EditProfile::class)
            ->userMenuItems([
                'logout' => Action::make('logout')
                    ->label('Log Keluar')
                    ->requiresConfirmation()
                    ->modalHeading('Pengesahan Log Keluar')
                    ->modalDescription('Adakah anda pasti mahu log keluar?')
                    ->modalSubmitActionLabel('Ya, Log Keluar')
                    ->modalCancelActionLabel('Batal')
                    ->action(function () {
                        Filament::auth()->logout();

                        request()->session()->invalidate();
                        request()->session()->regenerateToken();

                        redirect()->to('/app/login');
                    }),
            ])
            ->colors([
                'primary' => Color::Teal,
                'secondary' => Color::Violet,
                'tertiary' => Color::Lime,
                'quartenary' => Color::Slate,
                'neutral' => Color::Neutral,
                'export' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // NamaPenyandang::class
                // AccountWidget::class,
                // FilamentInfoWidget::class,
                // StatsOverviewWidget::class

            ])

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s');
        // ->renderHook(
        //     PanelsRenderHook::TOPBAR_END,
        //     fn (): \Illuminate\Contracts\View\View => view('filament.topbar.dark-toggle'),
        // );

    }
}
