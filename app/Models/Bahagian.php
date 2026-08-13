<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bahagian extends Model
{
    use SoftDeletes;
    protected $table = 'bahagians';
    protected $fillable = [
        'ptj_id',
        'nama_bahagian',
        'parlimen_id',
        'dun_id',

    ];

    public function ptj()
    {
        return $this->belongsTo(PTJ::class, 'ptj_id');
    }

        public function units()
    {
        return $this->hasMany(Unit::class);
    }

        public function parlimen()
    {
        return $this->belongsTo(Parlimen::class);
    }

    public function dun()
    {
        return $this->belongsTo(Dun::class);
    }
}
