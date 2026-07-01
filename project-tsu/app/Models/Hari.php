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
        'nama_hari',
        'is_active',
    ];

    public function slotWaktus()
    {
        return $this->belongsToMany(Slot_waktu::class, 'hari_slot_waktu', 'id_hari', 'id_slot');
    }
}
