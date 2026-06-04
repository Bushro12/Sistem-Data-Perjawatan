<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
     use SoftDeletes;
    protected $fillable = [
        'bahagian_id',
        'nama_unit',
        'parlimen_id',
        'dun_id',
    ];

    public function bahagian()
    {
        return $this->belongsTo(Bahagian::class, 'bahagian_id');

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
