<?php

namespace App\Filament\Pages;

use App\Models\Pegawai;
use App\Models\Setting;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Http;

class Api extends Page
{
    protected string $view = 'filament.pages.api';

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCodeBracket;

    protected static ?string $navigationLabel = 'API';

    protected static ?string $title = 'API';

    protected static string|\UnitEnum|null $navigationGroup = 'Kawalan';

    protected static ?int $navigationSort = 20;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public ?string $nokp = null;

    public ?string $namaPegawai = null;

    public ?string $apiError = null;

    public function updatedNokp(?string $value): void
    {
        $value = trim((string) $value);

        $this->namaPegawai = null;
        $this->apiError = null;

        if ($value === '') {
            return;
        }

        if (! preg_match('/^\d{12}$/', $value)) {
            return;
        }

        $this->fetchNamaFromApi($value);
    }

    protected function fetchNamaFromApi(string $nokp): void
    {
        $apiErrorBeforeFallback = null;

        try {
            $apiKey = Setting::get('prestasi_v2_api_key', config('services.prestasi_v2.api_key'));

            if (blank($apiKey)) {
                $credentialsPath = base_path('api/credentials.text');

                if (is_file($credentialsPath)) {
                    $apiKey = trim((string) file_get_contents($credentialsPath));
                }
            }

            if (blank($apiKey)) {
                throw new \RuntimeException('Kunci API tidak dikonfigur. Sila tetapkan di Kawalan → API Key.');
            }

            $baseUrl = Setting::get('prestasi_v2_base_url', config('services.prestasi_v2.base_url', 'https://training.kdh.moh.gov.my/api/v1'));
            $baseUrl = rtrim((string) $baseUrl, '/');

            // Read from ALL pages (not only first page) — scan entire dataset for exact no_kp match
            $totalPages = null;

            for ($page = 1; $page <= ($totalPages ?? 195); $page++) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$apiKey,
                    'X-API-Key' => $apiKey,
                    'Accept' => 'application/json',
                ])->timeout(15)->get($baseUrl.'/pegawai', [
                    'no_kp' => $nokp,
                    'per_page' => 100,
                    'page' => $page,
                ]);

                if (! $response->successful()) {
                    $status = $response->status();

                    if (in_array($status, [401, 403], true)) {
                        $this->apiError = 'Kunci API tidak sah atau tiada kebenaran (HTTP '.$status.').';
                    } elseif ($status === 429) {
                        $this->apiError = 'Had kadar permintaan API dicapai. Sila cuba sebentar lagi.';
                    } else {
                        $this->apiError = 'Gagal menghubungi API (HTTP '.$status.').';
                    }

                    $apiErrorBeforeFallback = $this->apiError;

                    break;
                }

                $json = $response->json();

                if ($totalPages === null && isset($json['meta']['last_page'])) {
                    $totalPages = (int) $json['meta']['last_page'];
                }

                $items = $json['data'] ?? $json;

                if (isset($items['data']) && is_array($items['data'])) {
                    $items = $items['data'];
                }

                if (! is_array($items) || empty($items)) {
                    break;
                }

                // Single object case
                if (isset($items['nama_pegawai']) || isset($items['nama'])) {
                    $nama = $items['nama_pegawai'] ?? $items['nama'] ?? null;
                    $kp = $items['no_kp'] ?? $items['kp_pegawai'] ?? $items['nokp'] ?? null;

                    if ($kp !== null && trim((string) $kp) === $nokp && $nama !== null) {
                        $this->namaPegawai = ltrim((string) $nama, "'");

                        return;
                    }
                }

                foreach ($items as $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $kp = $item['no_kp'] ?? $item['kp_pegawai'] ?? $item['nokp'] ?? $item['ic'] ?? null;

                    if ($kp !== null && trim((string) $kp) === $nokp) {
                        $nama = $item['nama_pegawai'] ?? $item['nama'] ?? null;

                        if ($nama !== null) {
                            $this->namaPegawai = ltrim((string) $nama, "'");

                            return;
                        }
                    }
                }

                if ($totalPages !== null && $page >= $totalPages) {
                    break;
                }

                if ($totalPages === null && count($items) < 100) {
                    break;
                }
            }

            $apiErrorBeforeFallback = $this->apiError;
        } catch (\Throwable $e) {
            $this->apiError = 'Ralat API: '.$e->getMessage();
            $apiErrorBeforeFallback = $this->apiError;
        }

        // Fallback to local DB (bypass PTJ global scope) — handles cases like 790330025227 / 941015025636 that exist locally but not in remote API first pages
        $localNama = Pegawai::withoutGlobalScopes()->where('nokp', $nokp)->value('nama');

        if ($localNama) {
            $this->namaPegawai = $localNama;
            $this->apiError = null;

            return;
        }

        // Also check withTrashed and alternative formatting
        $localNama = Pegawai::withTrashed()->where('nokp', $nokp)->value('nama');

        if ($localNama) {
            $this->namaPegawai = $localNama;
            $this->apiError = null;

            return;
        }

        if ($apiErrorBeforeFallback !== null) {
            $this->apiError = $apiErrorBeforeFallback;
        } elseif ($this->apiError === null) {
            $this->apiError = 'Tiada rekod pegawai dijumpai untuk No. KP tersebut.';
        }
    }
}
