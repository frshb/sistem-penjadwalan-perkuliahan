<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    use HasFactory;
    protected $table = 'ruang';
    protected $primaryKey = 'id_ruang';

    protected $fillable = [
        'nama_ruang',
        'kapasitas',
        'fasilitas',
        'id_gedung',
    ];
    public $timestamps = false;
    public function gedung()
    {
        return $this->belongsTo(Gedung::class, 'id_gedung', 'id_gedung');
    }
        public function mata_kuliahs()
    {
        return $this->belongsToMany(
            MataKuliah::class,
            'matkul_ruang',
            'id_ruang',
            'kode_matkul',
            'id_ruang',
            'kode_matkul'
        );
    }
}
