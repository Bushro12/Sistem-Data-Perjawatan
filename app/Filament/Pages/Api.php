<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\PrestasiService;
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

    public ?string $jantinaNama = null;

    public ?string $ptjNama = null;

    public ?string $bahagianNama = null;

    public ?string $unitNama = null;

    public ?string $jawatanNama = null;

    public ?string $gredKod = null;

    /** @var array<int, array{id: mixed, kod: ?string, nama: ?string}>|null */
    public ?array $ptjs = null;

    public ?string $ptjError = null;

    public function updatedNokp(?string $value): void
    {
        $value = trim((string) $value);

        $this->namaPegawai = null;
        $this->jantinaNama = null;
        $this->ptjNama = null;
        $this->bahagianNama = null;
        $this->unitNama = null;
        $this->jawatanNama = null;
        $this->gredKod = null;
        $this->apiError = null;

        if ($value === '') {
            return;
        }

        if (! preg_match('/^\d{12}$/', $value)) {
            return;
        }

        $this->fetchNamaFromApi($value);
    }

    /**
     * Fetch all PTJ via the external API.
     *
     * The external API does not expose a dedicated `/ptj` endpoint — PTJ
     * data is embedded inside `/pegawai` records (`ptj: {id, kod, nama}`).
     * This method paginates through `/pegawai` and deduplicates by PTJ id.
     */
    public function fetchAllPtj(): void
    {
        $this->ptjs = null;
        $this->ptjError = null;

        try {
            $apiKey = $this->resolveApiKey();

            if (blank($apiKey)) {
                throw new \RuntimeException('Kunci API tidak dikonfigur. Sila tetapkan di Kawalan → API Key.');
            }

            $baseUrl = $this->resolveBaseUrl();

            /** @var array<int, array{id: mixed, kod: ?string, nama: ?string}> $ptjMap */
            $ptjMap = [];
            $totalPages = null;

            for ($page = 1; $page <= ($totalPages ?? 200); $page++) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$apiKey,
                    'X-API-Key' => $apiKey,
                    'Accept' => 'application/json',
                ])->timeout(15)->get($baseUrl.'/pegawai', [
                    'per_page' => 100,
                    'page' => $page,
                ]);

                if (! $response->successful()) {
                    $this->ptjError = $this->mapApiError($response->status());

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

                foreach ($items as $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $ptj = $item['ptj'] ?? null;

                    if (! is_array($ptj)) {
                        continue;
                    }

                    $id = $ptj['id'] ?? null;

                    if ($id === null) {
                        continue;
                    }

                    if (! isset($ptjMap[$id])) {
                        $ptjMap[$id] = [
                            'id' => $id,
                            'kod' => isset($ptj['kod']) ? (string) $ptj['kod'] : (isset($ptj['kod_ptj']) ? (string) $ptj['kod_ptj'] : null),
                            'nama' => $ptj['nama'] ?? $ptj['nama_ptj'] ?? null,
                        ];
                    }
                }

                if ($totalPages !== null && $page >= $totalPages) {
                    break;
                }

                if ($totalPages === null && count($items) < 100) {
                    break;
                }
            }

            if (! empty($ptjMap)) {
                $ptjs = array_values($ptjMap);
                usort($ptjs, fn (array $a, array $b): int => strcmp((string) ($a['nama'] ?? ''), (string) ($b['nama'] ?? '')));
                $this->ptjs = $ptjs;
                $this->ptjError = null;
            } elseif ($this->ptjError === null) {
                $this->ptjError = 'Tiada PTJ dijumpai melalui API.';
            }
        } catch (\Throwable $e) {
            $this->ptjError = 'Ralat PTJ: '.$e->getMessage();
        }
    }

    protected function resolveApiKey(): ?string
    {
        $apiKey = Setting::get('prestasi_v2_api_key', config('services.prestasi_v2.api_key'));

        if (blank($apiKey)) {
            $credentialsPath = base_path('api/credentials.text');

            if (is_file($credentialsPath)) {
                $apiKey = trim((string) file_get_contents($credentialsPath));
            }
        }

        return $apiKey ?: null;
    }

    protected function resolveBaseUrl(): string
    {
        $baseUrl = Setting::get('prestasi_v2_base_url', config('services.prestasi_v2.base_url', 'https://training.kdh.moh.gov.my/api/v1'));

        return rtrim((string) $baseUrl, '/');
    }

    protected function mapApiError(int $status): string
    {
        return match (true) {
            in_array($status, [401, 403], true) => 'Kunci API tidak sah atau tiada kebenaran (HTTP '.$status.').',
            $status === 429 => 'Had kadar permintaan API dicapai. Sila cuba sebentar lagi.',
            default => 'Gagal menghubungi API (HTTP '.$status.').',
        };
    }

    protected function fetchNamaFromApi(string $nokp): void
    {
        $result = PrestasiService::fetchByNokp($nokp);

        if ($result['found']) {
            $data = $result['data'];
            $this->namaPegawai = $data['nama'];
            $this->jantinaNama = $data['jantinaNama'];
            $this->ptjNama = $data['ptjNama'];
            $this->bahagianNama = $data['bahagianNama'];
            $this->unitNama = $data['unitNama'];
            $this->jawatanNama = $data['jawatanNama'];
            $this->gredKod = $data['gredKod'];
            $this->apiError = null;

            return;
        }

        // Not found — clear fields and surface error
        $this->namaPegawai = null;
        $this->jantinaNama = null;
        $this->ptjNama = null;
        $this->bahagianNama = null;
        $this->unitNama = null;
        $this->jawatanNama = null;
        $this->gredKod = null;
        $this->apiError = $result['error'];
    }
}
