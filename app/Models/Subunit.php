<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subunit extends Model
{
    protected $fillable = [
        'unit_id',
        'dun_id',
        'nama_subunit',
        'parlimen_id',  
        
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
    public function dun()
    {
       return $this->belongsTo(Dun::class, 'dun_id');
    }

        public function parlimen()
    {
        return $this->belongsTo(Parlimen::class);
    }
        
}
