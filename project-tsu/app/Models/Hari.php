<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hari extends Model
{
    protected $table = 'hari';

    protected $primaryKey = 'id_hari';
    
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_hari',
        'nama_hari'
    ];
}
