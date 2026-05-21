<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jawatan_Gred extends Model
{
    protected $table = 'jawatan__greds';

    protected $fillable = [
        'jawatan_id',
        'gred_id',
        'kumpulan_id'
    ];

    public function jawatan()
    {
        return $this->belongsTo(Jawatan::class, 'jawatan_id');
    }

    public function gred()
    {
        return $this->belongsTo(Gred::class, 'gred_id');
    }

    public function kumpulan()
    {
        return $this->belongsTo(Kumpulan::class, 'kumpulan_id');
    }

    public function butiran()
    {
        return $this->belongsToMany(
            Butiran::class,
            'butiran__jawatans',
            'jawatan_gred_id',
            'butiran_id'
        );
    }

    protected $appends = ['display_name'];

public function getDisplayNameAttribute()
{
    return $this->jawatan->desc_jawatan . ' (' . $this->gred->kod_gred . ')';
}
}
