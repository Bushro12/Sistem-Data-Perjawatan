<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::query()->where('key', $key)->value('value');

        return $value !== null ? $value : $default;
    }

    /**
     * Set setting value (upsert).
     */
    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrInsert(
            ['key' => $key],
            ['value' => $value !== null ? (string) $value : null, 'updated_at' => now(), 'created_at' => now()],
        );
    }
}
