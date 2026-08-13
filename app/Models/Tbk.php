<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tbk extends Model
{
    protected $fillable =[
        'tbk',
        'waran_jawatan_id',
        'gred_id',
        'pegawai_id',

    ];

    public function waran_jawatan()
    {
        return $this->belongsTo(WaranJawatan::class, 'waran_jawatan_id');
    }
    
    public function gred()
    {
        return $this->belongsTo(Gred::class, 'gred_id');
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

}
