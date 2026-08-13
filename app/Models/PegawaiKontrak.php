<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PegawaiKontrak extends Model
{
    protected $fillable = [
        'pegawai_id',
        'program_id',
        'aktiviti_id',
        'tarikh_lantikan1',
        'tarikh_tamat1',
        'tarikh_lantikan2',
        'tarikh_tamat2',
        'tarikh_lantikan3',
        'tarikh_tamat3',
        'tarikh_lantikan4',
        'tarikh_tamat4',
        'tarikh_lantikan5',
        'tarikh_tamat5',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'ptj_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function aktiviti()
    {
        return $this->belongsTo(Aktiviti::class, 'aktiviti_id');
    }


}
