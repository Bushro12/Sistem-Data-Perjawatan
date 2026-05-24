<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pegawai extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'ptj_id',
        'bahagian_id',
        'unit_id',
        'subunit_id',
        'jawatan_gred_id',
        'opsyen_pencen_id',
        'nama',
        'nokp',
        'jantina',
        'tarikh_lantikan',
        'tarikh_sah_jawatan',
        'tarikh_pencen',
        'is_tetap',
        'is_kontrak_interim',
        'is_kontrak',
        'is_kup',
        'is_kupj',
        'is_jtw',
        'emel'
    ];

    public function ptj()
    {
        return $this->belongsTo(Ptj::class, 'ptj_id');
    }

    public function bahagian()
    {
        return $this->belongsTo(Bahagian::class, 'bahagian_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function subunit()
    {
        return $this->belongsTo(Subunit::class, 'subunit_id');
    }

    public function jawatan_gred()
    {
        return $this->belongsTo(Jawatan_Gred::class, 'jawatan_gred_id');
    }

    public function opsyenPencen()
    {
        return $this->belongsTo(OpsyenPencen::class, 'bahagian_id');
    }

    public function pegawaiKontrak()
    {
        return $this->hasOne(PegawaiKontrak::class);
    }

}
