<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gedung extends Model
{
    use HasFactory;
    protected $table = 'gedung';
    protected $primaryKey = 'id_gedung';
    public $timestamps = false;

    /**
     * Relasi one-to-many ke Ruangan.
     * Satu gedung bisa memiliki banyak ruangan.
     */
    public function ruangans()
    {
        return $this->hasMany(Ruangan::class, 'id_gedung', 'id_gedung');
    }
}
