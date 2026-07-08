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

    public function waranTolakJawatan()
    {
        return $this->hasMany(WaranJawatan::class, 'waran_tolak_id');
    }

    public function getAktivitiListAttribute()
    {
        $user = auth()->user();
        $query = WaranJawatan::query()->withTrashed();

        if (!$user->isSuperadmin() && !$user->isAdmin()) {
            $query->where('ptj_id', $user->ptj_id);
        }

        $items = $this->jenis === 'Tolak'
            ? $query->where('waran_tolak_id', $this->id)->get()
            : $query->where('waran_id', $this->id)->get();

        return $items
            ->groupBy(
                fn($wj) =>
                $wj->aktiviti?->no_aktivit . ' - ' . $wj->aktiviti?->nama_aktiviti
            )
            ->map(function ($items, $aktivitiName) {

                $count = $items->count();

                // ONLY grey if ALL are inactive
                $isInactive = $this->jenis === 'Tambah' && $items->every(fn($item) => $item->status === 'removed');

                $class = $isInactive ? 'text-gray-400' : 'text-black';

                return "<span class='{$class}'>"
                    . $aktivitiName . " ({$count})"
                    . "</span>";
            })
            ->filter()
            ->join('<br>');
    }

    public function getPenempatanListAttribute()
    {
        $user = auth()->user();

        $query = WaranJawatan::withTrashed();

        // User only sees their own PTJ
        if (!$user->isSuperadmin() && !$user->isAdmin()) {
            $query->where('ptj_id', $user->ptj_id);
        }

        $items = $this->jenis === 'Tolak'
            ? $query->where('waran_tolak_id', $this->id)->get()
            : $query->where('waran_id', $this->id)->get();

        return $items
            ->groupBy(fn($wj) => $wj->ptj?->nama_ptj)
            ->map(function ($items, $ptjName) {

                $count = $items->count();

                // ONLY grey if ALL are inactive
                $isInactive = $this->jenis === 'Tambah' && $items->every(fn($item) => $item->status === 'removed');

                $class = $isInactive ? 'text-gray-400' : 'text-black';

                return "<span class='{$class}'>"
                    . $ptjName . " ({$count})"
                    . "</span>";
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
        $query = WaranJawatan::query()->withTrashed();

        $items = $this->jenis === 'Tolak'
            ? $query->where('waran_tolak_id', $this->id)->get()
            : $query->where('waran_id', $this->id)->get();

        return $items
            ->groupBy('butiran')
            ->map(function ($items, $butiran) {

                $count = $items->count();

                // ONLY grey if ALL are inactive
                $isInactive = $this->jenis === 'Tambah' && $items->every(fn($item) => $item->status === 'removed');

                $class = $isInactive ? 'text-gray-400' : 'text-black';

                return "<span class='{$class}'>"
                    . $butiran . " ({$count})"
                    . "</span>";
            })
            ->filter()
            ->join('<br>');

    }
    protected static function booted()
    {
        static::deleting(function ($waran) {
            $waran->waranJawatan()->forcedelete();
        });


        static::addGlobalScope('ptj_access', function (Builder $query) {

            $user = auth()->user();

            if (in_array($user->role, [1, 2])) {
                return;
            }

            $query->where(function ($q) use ($user) {

                $q->whereHas('waranJawatan', function ($sub) use ($user) {
                    $sub->where('ptj_id', $user->ptj_id);
                })
                    ->orWhereHas('waranTolakJawatan', function ($sub) use ($user) {
                        $sub->where('ptj_id', $user->ptj_id);
                    });

            });

        });
    }

    // public function parent()
    // {
    //     return $this->belongsTo(Waran::class, 'parent_id');
    // }

    // public function children()
    // {
    //     return $this->hasMany(Waran::class, 'parent_id');
    // }

    private function waranJawatanQuery()
    {
        $user = auth()->user();

        return \App\Models\WaranJawatan::query()
            ->when(!$user->isSuperadmin() && !$user->isAdmin(), function ($q) use ($user) {
                $q->where('ptj_id', $user->ptj_id);
            });
    }

    // public function allWaranIds(): array
    // {
    //     return collect([$this->id])
    //         ->merge($this->children->pluck('id'))
    //         ->toArray();
    // }

    public function getJikCountAttribute()
    {
        $user = auth()->user();
        $query = $this->waranJawatanQuery();

        if ($user->isUser()) {
            return $query
                ->where('ptj_id', $user->ptj_id)
                ->count();
        } elseif ($user->isSuperadmin() || $user->isAdmin()) {
            return $this->jik;
        }
    }
    public function getIsiCountAttribute()
    {
        $user = auth()->user();
        $query = $this->waranJawatanQuery();
        $query2 = $this->waranJawatanQuery()->withTrashed();

        if ($user->isUser()) {
            $query->where('ptj_id', $user->ptj_id);
        }

        if ($this->jenis === 'Tolak') {
            return $query2
                ->where('waran_tolak_id', $this->id)
                // ->whereNotNull('pegawai_id')
                ->where('status', 'removed')
                ->count();
        }

        return $query
            ->where('waran_id', $this->id)
            ->whereNotNull('pegawai_id')
            ->where('status', 'active')
            ->count();
    }

    public function getKosongCountAttribute()
    {
        return (int) $this->jik_count - (int) $this->isi_count;
    }

    public function getStatusJikAttribute()
    {
        $k = (int) $this->kosong_count;

        if ($this->jenis === 'Tolak') {
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
