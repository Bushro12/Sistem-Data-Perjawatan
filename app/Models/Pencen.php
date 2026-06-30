<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Pencen extends Model
{
    protected $fillable = [
        'ptj_id',
        'jawatan_gred_id',
        'opsyen_pencen_id',
        'jenis_pencen_id',
        'nama',
        'nokp',
        'jenis_lantikan',
        'tarikh_lantikan',
        'tarikh_sah_jawatan',
        'umur_pencen',
        'tarikh_pencen',
        'tarikh_kuatkuasa',
        'tempoh_perkhidmatan',
        'catatan'
    ];

    protected static function booted()
{
    static::addGlobalScope('ptj_access', function (Builder $query) {
        $user = auth()->user();

        // Running from console or no authenticated user
        if (!$user) {
            return;
        }

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

    public function opsyenPencen()
    {
        return $this->belongsTo(OpsyenPencen::class, 'opsyen_pencen_id');
    }

    public function jenisPencen()
    {
        return $this->belongsTo(JenisPencen::class, 'jenis_pencen_id');
    }

}
