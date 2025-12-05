<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    /**
     * Tentukan nama tabel.
     */
    protected $table = 'jadwal';

    /**
     * Tentukan primary key.
     */
    protected $primaryKey = 'id_jadwal'; // Asumsi, sesuaikan jika perlu

    /**
     * Nonaktifkan timestamps jika tabel Anda tidak memilikinya.
     */
    public $timestamps = false;

    /**
     * Kolom yang bisa diisi (mass assignable).
     * Sesuaikan ini dengan kolom di tabel 'jadwal' Anda.
     */
    protected $fillable = [
        'id_matkul', // Ini mungkin 'kode_matkul' jika itu PK Anda
        'id_dosen',
        'id_ruangan',
        'id_prodi',
        'hari',
        'jam',
        'semester',
        'tipe', // 'Teori' atau 'Praktikum'
    ];

    // --- Relasi (Sangat Direkomendasikan) ---

    public function mataKuliah()
    {
        // Sesuaikan 'kode_matkul' jika itu adalah foreign key
        return $this->belongsTo(MataKuliah::class, 'id_matkul', 'kode_matkul');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen');
    }

    public function ruangan()
    {
        // Sesuaikan 'id_ruang' jika itu PK di tabel 'ruang'
        return $this->belongsTo(Ruangan::class, 'id_ruangan', 'id_ruang');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi');
    }
}
