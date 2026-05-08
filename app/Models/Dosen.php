<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    use HasFactory;
    protected $table = 'dosen';
    protected $primaryKey = 'id_dosen';
    public $timestamps = false;

    protected $fillable = [
        'nama_dosen',
        'nidn',
        'mata_kuliah',
        'id_prodi',
        'ketersediaan_waktu',
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class, 'id_dosen', 'id_dosen');
    }
}
