<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ApiKey extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.api-key';

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = 'API Key';

    protected static ?string $title = 'API Key';

    protected static string|\UnitEnum|null $navigationGroup = 'Kawalan';

    protected static ?int $navigationSort = 21;

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'turnstile_site_key' => Setting::get('turnstile_site_key', config('services.turnstile.site_key')),
            'turnstile_secret_key' => Setting::get('turnstile_secret_key', config('services.turnstile.secret_key')),
            'prestasi_v2_api_key' => Setting::get('prestasi_v2_api_key', $this->resolvePrestasiApiKeyFallback()),
            'prestasi_v2_base_url' => Setting::get('prestasi_v2_base_url', config('services.prestasi_v2.base_url', 'https://training.kdh.moh.gov.my/api/v1')),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Cloudflare Turnstile')
                    ->description('Kunci untuk pengesahan CAPTCHA di halaman log masuk. Biarkan kosong untuk melumpuhkan Turnstile (fail-open).')
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->schema([
                        TextInput::make('turnstile_site_key')
                            ->label('Site Key')
                            ->placeholder('0x4AAAAAAAxxxxxxxxxxxxxxxx')
                            ->helperText('Dapatkan di https://dash.cloudflare.com → Turnstile → Add site')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('turnstile_secret_key')
                            ->label('Secret Key')
                            ->placeholder('0x4AAAAAAAxxxxxxxxxxxxxxxx_secret')
                            ->helperText('Kunci rahsia — disimpan secara sulit')
                            ->password()
                            ->revealable()
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Prestasi V2 — API Key')
                    ->description('Kunci untuk integrasi dengan API Prestasi V2 (training.kdh.moh.gov.my). Digunakan oleh halaman API untuk carian No. KP.')
                    ->icon(Heroicon::OutlinedCodeBracket)
                    ->schema([
                        TextInput::make('prestasi_v2_api_key')
                            ->label('API Key')
                            ->placeholder('psv_xxxxxxxxxxxxxxxx...')
                            ->helperText('Kunci Bearer / X-API-Key. Jika kosong, sistem akan cuba membaca api/credentials.text sebagai fallback.')
                            ->password()
                            ->revealable()
                            ->maxLength(500)
                            ->columnSpanFull(),

                        TextInput::make('prestasi_v2_base_url')
                            ->label('Base URL')
                            ->placeholder('https://training.kdh.moh.gov.my/api/v1')
                            ->helperText('URL asas API Prestasi V2')
                            ->url()
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('turnstile_site_key', $data['turnstile_site_key'] ?? null);
        Setting::set('turnstile_secret_key', $data['turnstile_secret_key'] ?? null);
        Setting::set('prestasi_v2_api_key', $data['prestasi_v2_api_key'] ?? null);
        Setting::set('prestasi_v2_base_url', $data['prestasi_v2_base_url'] ?? null);

        // Refresh runtime config so Turnstile verification uses new values immediately.
        config([
            'services.turnstile.site_key' => $data['turnstile_site_key'] ?? null,
            'services.turnstile.secret_key' => $data['turnstile_secret_key'] ?? null,
            'services.prestasi_v2.api_key' => $data['prestasi_v2_api_key'] ?? null,
            'services.prestasi_v2.base_url' => $data['prestasi_v2_base_url'] ?? null,
        ]);

        Notification::make()
            ->title('Berjaya disimpan')
            ->body('Kunci API telah dikemaskini.')
            ->success()
            ->send();
    }

    protected function resolvePrestasiApiKeyFallback(): ?string
    {
        // 1. Env / config
        $envKey = config('services.prestasi_v2.api_key');

        if (filled($envKey)) {
            return $envKey;
        }

        // 2. Legacy file api/credentials.text
        $path = base_path('api/credentials.text');

        if (is_file($path)) {
            $fileKey = trim((string) file_get_contents($path));

            if ($fileKey !== '') {
                return $fileKey;
            }
        }

        return null;
    }
}
