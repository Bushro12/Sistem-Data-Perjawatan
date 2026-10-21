<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aktiviti extends Model
{
    protected $fillable = [
        'program_id',
        'no_aktivit',
        'nama_aktiviti',
        'desc_aktiviti',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function ptjs(): BelongsToMany
    {
        return $this->belongsToMany(Ptj::class, 'aktiviti_ptj')->withTimestamps();
    }

    /**
     * PTJs assigned to this aktiviti in the Program form.
     *
     * @return array<int, string>
     */
    public function ptjSelectOptions(): array
    {
        return $this->ptjs()
            ->orderBy('nama_ptj')
            ->get()
            ->pluck('nama_ptj', 'id')
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function ptjSelectOptionsFor(mixed $aktivitiId, mixed $selectedPtjId = null): array
    {
        $options = blank($aktivitiId)
            ? []
            : (static::query()->find((int) $aktivitiId)?->ptjSelectOptions() ?? []);

        if (filled($selectedPtjId) && ! array_key_exists($selectedPtjId, $options)) {
            $ptj = Ptj::query()->find($selectedPtjId);

            if ($ptj) {
                $options[$ptj->id] = $ptj->nama_ptj;
            }
        }

        return $options;
    }

    public function butiran(): HasMany
    {
        return $this->hasMany(Butiran::class, 'aktiviti_id');
    }
}
