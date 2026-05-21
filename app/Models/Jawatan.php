<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jawatan extends Model
{
    protected $table = 'jawatans';
    protected $fillable = [
        'kod_jawatan',
        'desc_jawatan',
    ];

    public function greds()
{
    return $this->belongsToMany(
        Gred::class,
        'jawatan__greds'
    )->withPivot('kumpulan_id');
}





































}
