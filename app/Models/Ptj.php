<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ptj extends Model
{
    protected $table = 'ptjs';
    protected $fillable = [
        'nama_ptj',
        'kod_ptj',
        'alamat',
        'pengarah',
        'is_jkn',
        'rujukan_surat',
        'parlimen_id',
        'dun_id',
    ];


    public function parlimen()
    {
        return $this->belongsTo(Parlimen::class);
    }

    public function dun()
    {
        return $this->belongsTo(Dun::class);
    }


}
