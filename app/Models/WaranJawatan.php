<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



class WaranJawatan extends Model
{

    use SoftDeletes;
    protected $fillable = [
        'waran_id',
        'ptj_id',
        'bahagian_id',
        'unit_id',
        'subunit_id',
        'aktiviti_id',
        'pegawai_id',
        'jawatan_gred_id',
        'jawatan_ids',
        'gred_ids',
        'butiran',
        'is_kup',
        'waran_tolak_id',
        'catatan_jawatan',
        'status'
    ];

    public function waran()
    {
        return $this->belongsTo(Waran::class, 'waran_id');
    }

    public function ptj()
    {
        return $this->belongsTo(Ptj::class, 'ptj_id');
    }

    public function aktiviti()
    {
        return $this->belongsTo(Aktiviti::class, 'aktiviti_id');
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function jawatan()
    {
        return $this->belongsTo(Jawatan::class);
    }

    public function gred()
    {
        return $this->belongsTo(Gred::class);
    }

    public function jawatanGred()
    {
        return $this->belongsTo(Jawatan_Gred::class, 'jawatan_gred_id');
    }

    protected $casts = [
        'gred_ids' => 'array',
        'jawatan_ids' => 'array',
    ];

    public function getGredListAttribute()
    {
        return Gred::whereIn('id', $this->gred_ids ?? [])
            ->pluck('kod_gred')
            ->implode(', ');
    }

    public function getJawatanListAttribute()
    {
        return Jawatan::whereIn('id', $this->jawatan_ids ?? [])
            ->pluck('desc_jawatan')
            ->implode(', ');
    }

}
