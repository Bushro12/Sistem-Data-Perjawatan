<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToPtj;

class Unit extends Model
{
    use BelongsToPtj; // 2. Include the trait inside the class
    use SoftDeletes;

    protected $fillable = [
        'ptj_id',
        'bahagian_id',
        'nama_unit',
        'parlimen_id',
        'dun_id',
    ];

    public function ptj(): BelongsTo
    {
        return $this->belongsTo(Ptj::class, 'ptj_id');
    }

    public function bahagian(): BelongsTo
    {
        return $this->belongsTo(Bahagian::class, 'bahagian_id');
    }

    public function aktivitis(): BelongsToMany
    {
        return $this->belongsToMany(Aktiviti::class, 'aktiviti_unit')->withTimestamps();
    }

    /**
     * @param  array<int, mixed>  $ids
     */
    public function syncAktivitis(array $ids): void
    {
        $this->aktivitis()->sync(static::aktivitiIds($ids));
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    public static function aktivitiIds(array $ids): array
    {
        return collect($ids)
            ->filter(fn ($id): bool => filled($id))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function subunits(): HasMany
    {
        return $this->hasMany(Subunit::class);
    }

    public function parlimen(): BelongsTo
    {
        return $this->belongsTo(Parlimen::class);
    }

    public function dun(): BelongsTo
    {
        return $this->belongsTo(Dun::class);
    }
}
