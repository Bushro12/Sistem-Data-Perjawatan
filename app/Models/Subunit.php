<?php

namespace App\Models;
use App\Traits\BelongsToPtj; // 1. Import the namespace
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subunit extends Model
{
    use BelongsToPtj; // 2. Include the trait inside the class
    protected $fillable = [
        'unit_id',
        'dun_id',
        'nama_subunit',
        'parlimen_id',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function dun(): BelongsTo
    {
        return $this->belongsTo(Dun::class, 'dun_id');
    }

    public function parlimen(): BelongsTo
    {
        return $this->belongsTo(Parlimen::class);
    }

    public function aktivitis(): BelongsToMany
    {
        return $this->belongsToMany(Aktiviti::class, 'aktiviti_subunit')->withTimestamps();
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

    use BelongsToPtj;

    public function ptjRelation(): ?string
    {
        return 'unit';
    }

    // public function unit()
    // {
    //     return $this->belongsTo(Unit::class);
    // }

}
