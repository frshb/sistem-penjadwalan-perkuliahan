<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlotWaktu extends Model
{
    protected $table = 'slot_waktu';
    protected $primaryKey = 'id_slot_waktu';
    public $timestamps = false;

    protected $fillable = [
        'id_slot',
        'jam_ke',
        'waktu_mulai',
        'waktu_selesai',
    ];
}
