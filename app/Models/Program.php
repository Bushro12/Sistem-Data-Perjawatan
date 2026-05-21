<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = [
        'nama_program',
        'desc_program'
    ];

    public function aktiviti()
    {
        return $this->hasMany(Aktiviti::class);
    }

    protected static function booted()
    {
        static::deleting(function ($program) {
            $program->aktiviti()->delete();
        });
    }
}
