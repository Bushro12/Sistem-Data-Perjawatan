<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WaranJawatan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'waran_id',
        'ptj_id',
        'bahagian_id',
        'unit_id',
        'subunit_id',
        'aktiviti_id',
        'pegawai_id',
        'jawatan_gred_id',
        'jawatan_ids',
        'gred_ids',
        'butiran',
        'tarikh_kuatkuasa',
        'is_kup',
        'waran_tolak_id',
        'catatan_jawatan',
        'status',
    ];

    public function waran()
    {
        return $this->belongsTo(Waran::class, 'waran_id');
    }

    public function ptj()
    {
        return $this->belongsTo(Ptj::class, 'ptj_id');
    }

    public function bahagian()
    {
        return $this->belongsTo(Bahagian::class, 'bahagian_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function subunit()
    {
        return $this->belongsTo(Subunit::class, 'subunit_id');
    }

    public function aktiviti()
    {
        return $this->belongsTo(Aktiviti::class, 'aktiviti_id');
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id')
            ->withoutGlobalScopes();
    }

    public function jawatan()
    {
        return $this->belongsTo(Jawatan::class);
    }

    public function gred()
    {
        return $this->belongsTo(Gred::class);
    }

    public function jawatanGred()
    {
        return $this->belongsTo(Jawatan_Gred::class, 'jawatan_gred_id');
    }

    protected $casts = [
        'gred_ids' => 'array',
        'jawatan_ids' => 'array',
    ];

    public function getGredListAttribute()
    {
        return Gred::whereIn('id', $this->gred_ids ?? [])
            ->pluck('kod_gred')
            ->implode('/');
    }

    public function getJawatanListAttribute()
    {
        $jawatanIds = $this->jawatan_ids ?? [];

        if ($jawatanIds === []) {
            return '';
        }

        $jawatanById = Jawatan::whereIn('id', $jawatanIds)
            ->pluck('desc_jawatan', 'id');

        return collect($jawatanIds)
            ->map(fn ($id) => $jawatanById->get((int) $id))
            ->filter()
            ->implode(', ');
    }

    public function getJawatanGredDisplayAttribute(): string
    {
        $pegawaiJawatan = $this->pegawai?->jawatan_gred?->jawatan?->desc_jawatan;
        $pegawaiGred = $this->pegawai?->jawatan_gred?->gred?->kod_gred;
        $waranJawatanIds = $this->jawatan_ids ?? [];
        $waranJawatansById = $waranJawatanIds === []
            ? collect()
            : Jawatan::query()
                ->whereIn('id', $waranJawatanIds)
                ->pluck('desc_jawatan', 'id');
        $waranJawatans = collect($waranJawatanIds)
            ->map(fn ($id) => $waranJawatansById->get((int) $id))
            ->filter(fn ($jawatan): bool => filled($jawatan)
                && (! filled($pegawaiJawatan) || strcasecmp(trim($jawatan), trim($pegawaiJawatan)) !== 0))
            ->implode(', ');
        $waranGredIds = $this->gred_ids ?? [];
        $waranGredsById = $waranGredIds === []
            ? collect()
            : Gred::query()
                ->whereIn('id', $waranGredIds)
                ->pluck('kod_gred', 'id');
        $waranGreds = collect($waranGredIds)
            ->map(fn ($id) => $waranGredsById->get((int) $id))
            ->filter(fn ($gred): bool => filled($gred)
                && (! filled($pegawaiGred) || strcasecmp($gred, $pegawaiGred) !== 0))
            ->implode('/');

        $pegawaiPosition = collect([
            $pegawaiJawatan,
            $pegawaiGred,
        ])->filter()->implode(' ');

        $waranPosition = collect([
            $waranJawatans,
            $waranGreds,
        ])->filter()->implode(' ');

        $position = $pegawaiPosition;

        if ($this->is_kup || $this->pegawai?->is_kup) {
            $position .= ($position === '' ? '' : ' ').'KUP';
        }

        if ($waranPosition !== '') {
            $position .= ($position === '' ? '' : ', ').$waranPosition;
        }

        return $position ?: '-';
    }

    protected static function booted()
    {
        static::addGlobalScope('ptj_access', function (Builder $query) {

            $user = auth()->user();

            // No authenticated user (Artisan, Queue, tests, etc.)
            if (! $user) {
                return;
            }

            // superadmin & admin can see all
            if (in_array($user->role, [1, 2])) {
                return;
            }

            // normal user → filter by PTJ
            $query->where(function ($query) use ($user) {

                // Waran under user's PTJ
                $query->where('ptj_id', $user->ptj_id)

                    // OR waran assigned to pegawai in user's PTJ
                    ->orWhereHas('pegawai', function ($pegawaiQuery) use ($user) {
                        $pegawaiQuery->where('ptj_id', $user->ptj_id);
                    });
            });
        });

        static::deleting(function (WaranJawatan $waranJawatan): bool {
            return ! $waranJawatan->hasAssignedPegawai();
        });

        static::forceDeleting(function (WaranJawatan $waranJawatan): bool {
            return ! $waranJawatan->hasAssignedPegawai();
        });
    }

    public function tbk()
    {
        return $this->hasOne(Tbk::class);
    }

    public function hasAssignedPegawai(): bool
    {
        return filled($this->pegawai_id);
    }

    /**
     * Active rows for Nama Penyandang and the Waran Penempatan table.
     * A deleted waran jawatan stays out of both lists.
     */
    public function scopeListed(Builder $query): Builder
    {
        return $query
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->whereNull($query->qualifyColumn('deleted_at'));
    }
}
