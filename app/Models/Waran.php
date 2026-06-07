<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Waran extends Model
{
    protected $fillable = [
        'no_waran',
        'jenis',
        'jik',
        'catatan',
        'parent_id'
    ];

    // public function ptj()
    // {
    //     return $this->hasMany(Ptj::class);
    // }

    //     public function waranJawatan()
// {
//     return $this->hasMany(WaranJawatan::class, 'waran_id');
// }

    public function waranJawatan()
    {
        return $this->hasMany(WaranJawatan::class, 'waran_id')
            ->with(['ptj', 'pegawai', 'aktiviti', 'jawatan']);
    }

    public function getAktivitiListAttribute()
    {
        $query = WaranJawatan::withTrashed();

        $items = $this->jenis === 'tolak'
            ? $query->where('waran_tolak_id', $this->id)->get()
            : $query->where('waran_id', $this->id)->get();

        return $items
            ->pluck('aktiviti.nama_aktiviti')
            ->filter()
            ->unique()
            ->join('<br>');
    }

    public function getPenempatanListAttribute()
    {
        $query = WaranJawatan::withTrashed();

        $items = $this->jenis === 'tolak'
            ? $query->where('waran_tolak_id', $this->id)->get()
            : $query->where('waran_id', $this->id)->get();

        return $items
            ->groupBy(fn($wj) => $wj->ptj?->nama_ptj)
            ->map(function ($items, $ptjName) {
                return $ptjName . ' (' . $items->count() . ')';
            })
            ->filter()
            ->join('<br>');
    }

    // public function getButiranListAttribute()
// {
//     $query = \App\Models\WaranJawatan::query();

    //     if ($this->jenis === 'tolak') {
//         $items = $query->where('waran_tolak_id', $this->id)->get();
//     } else {
//         $items = $query->where('waran_id', $this->id)->get();
//     }

    //     return $items
//         ->groupBy('butiran')
//         ->map(fn ($items, $butiran) =>
//             $butiran . ' (' . $items->count() . ')'
//         )
//         ->values()
//         ->join('<br>');
// }

    public function getButiranListAttribute()
    {
        $query = \App\Models\WaranJawatan::withTrashed();

        $items = $this->jenis === 'tolak'
            ? $query->where('waran_tolak_id', $this->id)->get()
            : $query->where('waran_id', $this->id)->get();

        return $items
            ->groupBy('butiran')
            ->map(
                fn($items, $butiran) =>
                $butiran . ' (' . $items->count() . ')'
            )
            ->values()
            ->join('<br>');
    }
    protected static function booted()
    {
        static::deleting(function ($waran) {
            $waran->waranJawatan()->delete();
        });

        // static::saved(function ($waran) {

        //     if (! $waran->jik) return;

        //     $count = $waran->waranJawatan()->count();

        //     if ($waran->jik > $count) {

        //         for ($i = $count; $i < $waran->jik; $i++) {
        //             $waran->waranJawatan()->create([]);
        //         }
        //     }

        //     if ($waran->jik < $count) {

        //         $waran->waranJawatan()
        //             ->latest()
        //             ->take($count - $waran->jik)
        //             ->delete();
        //     }
        // });

        //  static::created(function ($waran) {

        //     // only generate rows for ANY waran (parent or child)
        //     $count = $waran->jik ?? 0;

        //     for ($i = 0; $i < $count; $i++) {
        //         $waran->waranJawatan()->create([
        //             'ptj_id' => null,
        //             'aktiviti_id' => null,
        //             'butiran' => null,
        //             'jawatan_id' => null,
        //             'gred_id' => null,
        //             'jawatan_gred_id' => null,
        //             'pegawai_id' => null,
        //             'catatan_jawatan' => null,
        //         ]);
        //     }

        // });
        static::addGlobalScope('ptj_access', function (Builder $query) {

        $user = auth()->user();

        // superadmin & admin can see all
        if (in_array($user->role, [1, 2])) {
            return;
        }

        // normal user → filter by PTJ
       $query->whereHas('waranJawatan', function ($q) use ($user) {
            $q->where('ptj_id', $user->ptj_id);
        });
        });
    }

    public function parent()
    {
        return $this->belongsTo(Waran::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Waran::class, 'parent_id');
    }

    // public function allWaranJawatan()
// {
//     return $this->waranJawatan->merge(
//         $this->children->flatMap->waranJawatan
//     );
// }

    public function allWaranIds(): array
    {
        return collect([$this->id])
            ->merge($this->children->pluck('id'))
            ->toArray();
    }

    public function getIsiCountAttribute()
    {
        if ($this->jenis === 'tolak') {
            return \App\Models\WaranJawatan::withTrashed()
                ->where('waran_tolak_id', $this->id)

                ->where('status', 'removed')
                ->count();
        }

        return $this->waranJawatan()
            ->whereNotNull('pegawai_id')
            ->where('status', 'active')
            ->count();
    }

    public function getKosongCountAttribute()
    {
        return (int) $this->jik - (int) $this->isi_count;
    }

    public function getStatusJikAttribute()
    {
        $k = (int) $this->kosong_count;

        if ($this->jenis === 'tolak') {
            return match (true) {
                $k > 0 => 'Kurang',
                $k < 0 => 'Lebih',
                default => 'Seimbang',
            };
        }

        return match (true) {
            $k > 0 => 'Kurang',
            $k < 0 => 'Lebih',
            default => 'Seimbang',
        };
    }

    public function getAktivitiNamaAttribute()
    {
        return $this->waranJawatan->first()?->aktiviti?->nama_aktiviti
            ?? 'Tiada Aktiviti';
    }
    protected $with = [
        'waranJawatan.aktiviti',
    ];
}
