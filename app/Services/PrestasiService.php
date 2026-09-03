<?php

namespace App\Services;

use App\Models\Bahagian;
use App\Models\Gred;
use App\Models\Jawatan;
use App\Models\Jawatan_Gred;
use App\Models\OpsyenPencen;
use App\Models\Pegawai;
use App\Models\Ptj;
use App\Models\Setting;
use App\Models\Unit;
use Illuminate\Support\Facades\Http;

class PrestasiService
{
    public static function resolveApiKey(): ?string
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

    public static function resolveBaseUrl(): string
    {
        $baseUrl = Setting::get('prestasi_v2_base_url', config('services.prestasi_v2.base_url', 'https://training.kdh.moh.gov.my/api/v1'));

        return rtrim((string) $baseUrl, '/');
    }

    public static function mapApiError(int $status): string
    {
        return match (true) {
            in_array($status, [401, 403], true) => 'Kunci API tidak sah atau tiada kebenaran (HTTP '.$status.').',
            $status === 429 => 'Had kadar permintaan API dicapai. Sila cuba sebentar lagi.',
            default => 'Gagal menghubungi API (HTTP '.$status.').',
        };
    }

    /**
     * Fetch pegawai data by nokp from external API, with local DB fallback.
     *
     * @return array{found: bool, data: array{nama: ?string, jantinaNama: ?string, ptjNama: ?string, bahagianNama: ?string, unitNama: ?string, jawatanNama: ?string, gredKod: ?string, khidmatKod: ?string, khidmatNama: ?string, tarikhLantikan: ?string, tarikhSahJawatan: ?string, pencenKod: ?string, pencenNama: ?string, opsyenPencenId: ?int}, error: ?string, source: ?string}
     */
    public static function fetchByNokp(string $nokp): array
    {
        $nokp = trim($nokp);

        if (! preg_match('/^\d{12}$/', $nokp)) {
            return ['found' => false, 'data' => self::emptyData(), 'error' => null, 'source' => null];
        }

        $apiKey = self::resolveApiKey();
        $baseUrl = self::resolveBaseUrl();
        $apiError = null;

        if (! blank($apiKey)) {
            try {
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
                        $apiError = self::mapApiError($response->status());

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
                        $kp = $items['no_kp'] ?? $items['kp_pegawai'] ?? $items['nokp'] ?? null;

                        if ($kp !== null && trim((string) $kp) === $nokp) {
                            return ['found' => true, 'data' => self::mapApiItem($items), 'error' => null, 'source' => 'api'];
                        }
                    }

                    foreach ($items as $item) {
                        if (! is_array($item)) {
                            continue;
                        }

                        $kp = $item['no_kp'] ?? $item['kp_pegawai'] ?? $item['nokp'] ?? $item['ic'] ?? null;

                        if ($kp !== null && trim((string) $kp) === $nokp) {
                            return ['found' => true, 'data' => self::mapApiItem($item), 'error' => null, 'source' => 'api'];
                        }
                    }

                    if ($totalPages !== null && $page >= $totalPages) {
                        break;
                    }

                    if ($totalPages === null && count($items) < 100) {
                        break;
                    }
                }
            } catch (\Throwable $e) {
                $apiError = 'Ralat API: '.$e->getMessage();
            }
        } else {
            $apiError = 'Kunci API tidak dikonfigur. Sila tetapkan di Kawalan → API Key.';
        }

        // Fallback to local DB
        $local = self::fetchLocalByNokp($nokp);

        if ($local !== null) {
            return ['found' => true, 'data' => $local, 'error' => null, 'source' => 'local'];
        }

        if ($apiError !== null) {
            return ['found' => false, 'data' => self::emptyData(), 'error' => $apiError, 'source' => null];
        }

        return ['found' => false, 'data' => self::emptyData(), 'error' => 'Tiada rekod pegawai dijumpai untuk No. KP tersebut.', 'source' => null];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{nama: ?string, jantinaNama: ?string, ptjNama: ?string, bahagianNama: ?string, unitNama: ?string, jawatanNama: ?string, gredKod: ?string, khidmatKod: ?string, khidmatNama: ?string, tarikhLantikan: ?string, tarikhSahJawatan: ?string, pencenKod: ?string, pencenNama: ?string, opsyenPencenId: ?int}
     */
    public static function mapApiItem(array $item): array
    {
        $nama = $item['nama_pegawai'] ?? $item['nama'] ?? null;

        $tarikh = $item['tarikh'] ?? null;
        $tarikhLantikan = is_array($tarikh) ? ($tarikh['lantikan'] ?? null) : null;
        $tarikhSahJawatan = null;

        if (is_array($tarikh)) {
            $tarikhSahJawatan = $tarikh['sah_jawatan'] ?? $tarikh['sah_lantikan'] ?? null;
        }

        $pencen = $item['pencen'] ?? null;
        $pencenKod = is_array($pencen) ? ($pencen['kod'] ?? $pencen['nama'] ?? null) : (is_string($pencen) ? $pencen : null);
        $pencenNama = is_array($pencen) ? ($pencen['nama'] ?? $pencen['kod'] ?? null) : null;
        $opsyenPencenId = null;

        if (filled($pencenKod)) {
            $opsyenPencenId = self::resolveOpsyenPencenId((string) $pencenKod);
        } elseif (filled($pencenNama)) {
            $opsyenPencenId = self::resolveOpsyenPencenId((string) $pencenNama);
        }

        return [
            'nama' => $nama !== null ? ltrim((string) $nama, "'") : null,
            'jantinaNama' => isset($item['jantina']) && is_array($item['jantina']) ? ($item['jantina']['nama'] ?? null) : (isset($item['jantina']) ? (string) $item['jantina'] : null),
            'ptjNama' => isset($item['ptj']) && is_array($item['ptj']) ? ($item['ptj']['nama'] ?? $item['ptj']['nama_ptj'] ?? null) : null,
            'bahagianNama' => isset($item['bahagian']) && is_array($item['bahagian']) ? ($item['bahagian']['nama'] ?? $item['bahagian']['nama_bahagian'] ?? null) : null,
            'unitNama' => isset($item['unit']) && is_array($item['unit']) ? ($item['unit']['nama'] ?? $item['unit']['nama_unit'] ?? null) : null,
            'jawatanNama' => isset($item['jawatan']) && is_array($item['jawatan']) ? ($item['jawatan']['nama'] ?? $item['jawatan']['desc_jawatan'] ?? null) : null,
            'gredKod' => isset($item['gred']) && is_array($item['gred']) ? ($item['gred']['kod'] ?? $item['gred']['kod_gred'] ?? null) : null,
            'khidmatKod' => isset($item['khidmat']) && is_array($item['khidmat']) ? ($item['khidmat']['kod'] ?? null) : (isset($item['khidmat']) && is_string($item['khidmat']) ? $item['khidmat'] : null),
            'khidmatNama' => isset($item['khidmat']) && is_array($item['khidmat']) ? ($item['khidmat']['nama'] ?? null) : null,
            'tarikhLantikan' => $tarikhLantikan ? trim((string) $tarikhLantikan) : null,
            'tarikhSahJawatan' => $tarikhSahJawatan ? trim((string) $tarikhSahJawatan) : null,
            'pencenKod' => $pencenKod ? trim((string) $pencenKod) : null,
            'pencenNama' => $pencenNama ? trim((string) $pencenNama) : null,
            'opsyenPencenId' => $opsyenPencenId,
        ];
    }

    /**
     * @return array{nama: ?string, jantinaNama: ?string, ptjNama: ?string, bahagianNama: ?string, unitNama: ?string, jawatanNama: ?string, gredKod: ?string, khidmatKod: ?string, khidmatNama: ?string, tarikhLantikan: ?string, tarikhSahJawatan: ?string, pencenKod: ?string, pencenNama: ?string, opsyenPencenId: ?int}
     */
    public static function emptyData(): array
    {
        return [
            'nama' => null,
            'jantinaNama' => null,
            'ptjNama' => null,
            'bahagianNama' => null,
            'unitNama' => null,
            'jawatanNama' => null,
            'gredKod' => null,
            'khidmatKod' => null,
            'khidmatNama' => null,
            'tarikhLantikan' => null,
            'tarikhSahJawatan' => null,
            'pencenKod' => null,
            'pencenNama' => null,
            'opsyenPencenId' => null,
        ];
    }

    /**
     * @return array{nama: ?string, jantinaNama: ?string, ptjNama: ?string, bahagianNama: ?string, unitNama: ?string, jawatanNama: ?string, gredKod: ?string, khidmatKod: ?string, khidmatNama: ?string, tarikhLantikan: ?string, tarikhSahJawatan: ?string, pencenKod: ?string, pencenNama: ?string, opsyenPencenId: ?int}|null
     */
    public static function fetchLocalByNokp(string $nokp): ?array
    {
        $pegawai = Pegawai::withoutGlobalScopes()
            ->with(['ptj', 'bahagian', 'unit', 'jawatan_gred.jawatan', 'jawatan_gred.gred', 'pegawaiKontrak', 'opsyenPencen'])
            ->where('nokp', $nokp)
            ->first();

        if (! $pegawai) {
            $pegawai = Pegawai::withTrashed()
                ->with(['ptj', 'bahagian', 'unit', 'jawatan_gred.jawatan', 'jawatan_gred.gred', 'pegawaiKontrak', 'opsyenPencen'])
                ->where('nokp', $nokp)
                ->first();
        }

        if (! $pegawai) {
            return null;
        }

        $khidmatKod = null;
        $khidmatNama = null;

        if ($pegawai->is_tetap) {
            $khidmatKod = 'TETAP';
            $khidmatNama = 'TETAP';
        } elseif ($pegawai->is_kontrak) {
            $khidmatKod = 'KONTRAK';
            $khidmatNama = 'KONTRAK';
        } elseif ($pegawai->is_kontrak_interim) {
            $khidmatKod = 'KONTRAK INTERIM';
            $khidmatNama = 'KONTRAK INTERIM';
        } elseif ($pegawai->is_kontrak_isi_tetap) {
            $khidmatKod = 'KONTRAK ISI TETAP';
            $khidmatNama = 'KONTRAK ISI TETAP';
        }

        return [
            'nama' => $pegawai->nama,
            'jantinaNama' => $pegawai->jantina ?: null,
            'ptjNama' => $pegawai->ptj?->nama_ptj,
            'bahagianNama' => $pegawai->bahagian?->nama_bahagian,
            'unitNama' => $pegawai->unit?->nama_unit,
            'jawatanNama' => $pegawai->jawatan_gred?->jawatan?->desc_jawatan,
            'gredKod' => $pegawai->jawatan_gred?->gred?->kod_gred,
            'khidmatKod' => $khidmatKod,
            'khidmatNama' => $khidmatNama,
            'tarikhLantikan' => $pegawai->tarikh_lantikan?->format('Y-m-d'),
            'tarikhSahJawatan' => $pegawai->tarikh_sah_jawatan?->format('Y-m-d'),
            'pencenKod' => $pegawai->opsyenPencen?->opsyen,
            'pencenNama' => $pegawai->opsyenPencen?->opsyen,
            'opsyenPencenId' => $pegawai->opsyen_pencen_id,
        ];
    }

    public static function resolvePtjId(?string $nama): ?int
    {
        if (blank($nama)) {
            return null;
        }

        $nama = trim($nama);

        $ptj = Ptj::where('nama_ptj', $nama)->first();

        if ($ptj) {
            return $ptj->id;
        }

        $ptj = Ptj::whereRaw('LOWER(nama_ptj) = LOWER(?)', [$nama])->first();

        if ($ptj) {
            return $ptj->id;
        }

        return null;
    }

    public static function resolveBahagianId(?string $nama, ?int $ptjId = null): ?int
    {
        if (blank($nama)) {
            return null;
        }

        $nama = trim($nama);

        $query = Bahagian::query()->where('nama_bahagian', $nama);

        if ($ptjId) {
            $found = (clone $query)->where('ptj_id', $ptjId)->first();

            if ($found) {
                return $found->id;
            }
        }

        $found = $query->first();

        if ($found) {
            return $found->id;
        }

        $query2 = Bahagian::whereRaw('LOWER(nama_bahagian) = LOWER(?)', [$nama]);

        if ($ptjId) {
            $found2 = (clone $query2)->where('ptj_id', $ptjId)->first();

            if ($found2) {
                return $found2->id;
            }
        }

        $found2 = $query2->first();

        return $found2?->id;
    }

    public static function resolveUnitId(?string $nama, ?int $bahagianId = null): ?int
    {
        if (blank($nama)) {
            return null;
        }

        $nama = trim($nama);

        $query = Unit::query()->where('nama_unit', $nama);

        if ($bahagianId) {
            $found = (clone $query)->where('bahagian_id', $bahagianId)->first();

            if ($found) {
                return $found->id;
            }
        }

        $found = $query->first();

        if ($found) {
            return $found->id;
        }

        $query2 = Unit::whereRaw('LOWER(nama_unit) = LOWER(?)', [$nama]);

        if ($bahagianId) {
            $found2 = (clone $query2)->where('bahagian_id', $bahagianId)->first();

            if ($found2) {
                return $found2->id;
            }
        }

        $found2 = $query2->first();

        return $found2?->id;
    }

    public static function resolveJawatanId(?string $nama): ?int
    {
        if (blank($nama)) {
            return null;
        }

        $nama = trim($nama);

        $jawatan = Jawatan::where('desc_jawatan', $nama)->first();

        if ($jawatan) {
            return $jawatan->id;
        }

        $jawatan = Jawatan::whereRaw('LOWER(desc_jawatan) = LOWER(?)', [$nama])->first();

        if ($jawatan) {
            return $jawatan->id;
        }

        $jawatan = Jawatan::where('desc_jawatan', 'like', '%'.$nama.'%')->first();

        return $jawatan?->id;
    }

    public static function resolveGredId(?string $kod): ?int
    {
        if (blank($kod)) {
            return null;
        }

        $kod = trim($kod);

        $gred = Gred::where('kod_gred', $kod)->first();

        if ($gred) {
            return $gred->id;
        }

        $gred = Gred::whereRaw('LOWER(kod_gred) = LOWER(?)', [$kod])->first();

        return $gred?->id;
    }

    public static function resolveJawatanGredId(?int $jawatanId, ?int $gredId): ?int
    {
        if (! $jawatanId || ! $gredId) {
            return null;
        }

        $jg = Jawatan_Gred::where('jawatan_id', $jawatanId)->where('gred_id', $gredId)->first();

        return $jg?->id;
    }

    public static function resolveOpsyenPencenId(?string $kodOrNama): ?int
    {
        if (blank($kodOrNama)) {
            return null;
        }

        $kodOrNama = trim((string) $kodOrNama);

        $op = OpsyenPencen::where('opsyen', $kodOrNama)->first();

        if ($op) {
            return $op->id;
        }

        $op = OpsyenPencen::whereRaw('LOWER(opsyen) = LOWER(?)', [$kodOrNama])->first();

        return $op?->id;
    }

    /**
     * Resolve form IDs for pegawai auto-fill.
     *
     * @param  array{nama: ?string, jantinaNama: ?string, ptjNama: ?string, bahagianNama: ?string, unitNama: ?string, jawatanNama: ?string, gredKod: ?string, khidmatKod: ?string, khidmatNama: ?string, tarikhLantikan: ?string, tarikhSahJawatan: ?string, pencenKod: ?string, pencenNama: ?string, opsyenPencenId: ?int}  $data
     * @return array{ptj_id: ?int, bahagian_id: ?int, unit_id: ?int, jawatan_id: ?int, gred_id: ?int, jawatan_gred_id: ?int}
     */
    public static function resolvePegawaiFormIds(array $data): array
    {
        $ptjId = self::resolvePtjId($data['ptjNama'] ?? null);
        $bahagianId = self::resolveBahagianId($data['bahagianNama'] ?? null, $ptjId);
        $unitId = self::resolveUnitId($data['unitNama'] ?? null, $bahagianId);
        $jawatanId = self::resolveJawatanId($data['jawatanNama'] ?? null);
        $gredId = self::resolveGredId($data['gredKod'] ?? null);
        $jawatanGredId = self::resolveJawatanGredId($jawatanId, $gredId);

        return [
            'ptj_id' => $ptjId,
            'bahagian_id' => $bahagianId,
            'unit_id' => $unitId,
            'jawatan_id' => $jawatanId,
            'gred_id' => $gredId,
            'jawatan_gred_id' => $jawatanGredId,
        ];
    }
}
