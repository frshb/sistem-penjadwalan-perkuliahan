<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hari extends Model
{
    protected $table = 'hari';
    protected $primaryKey = 'id_hari';
    public $timestamps = false;

    protected $fillable = [
        'nama_hari'
    ];
}
