<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kumpulan extends Model
{
    protected $fillable = [
        'nama_kumpulan'
    ];

    // public function kumpulan()
    // {
    //     return $this->belongsTo(Jawatan_Gred::class);
    // }
}
