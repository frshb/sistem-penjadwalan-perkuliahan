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
        'nuptk',
        'nidn',
        'id_prodi',
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }

    public function mataKuliahs()
    {
        return $this->belongsToMany(
            MataKuliah::class,
            'pengampu_matkul',
            'id_dosen',
            'kode_matkul',
            'id_dosen',
            'kode_matkul'
        );
    }

        public function pengampus()
    {
        return $this->hasMany(
            PengampuMatkul::class,
            'id_dosen'
        );
    }
}
