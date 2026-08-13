<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hebahan extends Model
{
    protected $fillable = [
        'tajuk',
        'kandungan',
        'tarikh_hebahan',
        'lampiran',
        'status',
        'dipaparkan_sehingga',
    ];

    protected $casts = [
        'tarikh_hebahan' => 'date',
        'dipaparkan_sehingga' => 'date',
    ];
}
