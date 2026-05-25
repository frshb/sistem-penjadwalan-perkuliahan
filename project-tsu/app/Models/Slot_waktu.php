<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slot_waktu extends Model
{
    protected $table = 'slot_waktu';

    protected $primaryKey = 'id_slot';

    public $timestamps = false;

    protected $fillable = [
        'jam_ke',
        'waktu_mulai',
        'waktu_selesai',
        'sesi'
    ];
}
