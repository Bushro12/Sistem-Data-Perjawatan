<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LetakJawatan extends Model
{
    protected $fillable = [
        'ptj_id',
        'jawatan_gred_id',
        'nama',
        'nokp',
        'tarikh_notis',
        'tarikh_kuatkuasa',
        'jenis_notis',
        'alasan',
        'tarikh_lantik',
        'lantikan',
        'ikatan_jpa',
        'ikatan_bpl',
        'pinjaman_lppsa'
    ];

    protected static function booted()
    {
        static::addGlobalScope('ptj_access', function (Builder $query) {
            $user = auth()->user();

            if (in_array($user->role, [1, 2])) {
                return;
            }

            $query->where('ptj_id', $user->ptj_id);
        });
    }
    public function ptj()
    {
        return $this->belongsTo(Ptj::class, 'ptj_id');
    }
    public function jawatan_gred()
    {
        return $this->belongsTo(Jawatan_Gred::class, 'jawatan_gred_id');
    }

    // protected static function booted()
    // {
    //     static::created(function ($letakJawatan) {

    //         $pegawai = Pegawai::where('nokp', $letakJawatan->nokp)->first();

    //         if ($pegawai) {
    //             $pegawai->delete();
    //         }
    //     });
    // }
}

