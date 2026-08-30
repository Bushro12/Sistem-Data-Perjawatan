<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }

            $turnstileSiteKey = Setting::get('turnstile_site_key');
            $turnstileSecretKey = Setting::get('turnstile_secret_key');
            $prestasiApiKey = Setting::get('prestasi_v2_api_key');

            if (filled($turnstileSiteKey)) {
                config(['services.turnstile.site_key' => $turnstileSiteKey]);
            }

            if (filled($turnstileSecretKey)) {
                config(['services.turnstile.secret_key' => $turnstileSecretKey]);
            }

            if (filled($prestasiApiKey)) {
                config(['services.prestasi_v2.api_key' => $prestasiApiKey]);
            }

            $prestasiBaseUrl = Setting::get('prestasi_v2_base_url');

            if (filled($prestasiBaseUrl)) {
                config(['services.prestasi_v2.base_url' => $prestasiBaseUrl]);
            }
        } catch (\Throwable $e) {
            // Fail silently during install/migrate when table not ready.
        }
    }
}
