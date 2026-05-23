<?php

namespace App\Models;

use App\Models\Slot_waktu;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $table = 'jadwal';

    protected $primaryKey = 'id_jadwal';

    public $timestamps = false;

    protected $fillable = [
        'kode_matkul',
        'id_dosen',
        'id_kelas',
        'id_ruang',
        'id_hari',
        'id_slot_mulai',
        'durasi_sks',
        'id_tahunakademik',   // ← tambahan baru
        'is_manual',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELASI
    |--------------------------------------------------------------------------
    */

    // Mata Kuliah
    public function matakuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'kode_matkul', 'kode_matkul');
    }

    // Dosen
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen', 'id_dosen');
    }

    // Kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    // Ruangan
    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'id_ruang', 'id_ruang');
    }

    // Hari
    public function hari()
    {
        return $this->belongsTo(Hari::class, 'id_hari', 'id_hari');
    }

    // Slot Waktu
    public function slotMulai()
    {
        return $this->belongsTo(Slot_waktu::class, 'id_slot_mulai', 'id_slot');
    }

    // Tahun Akademik
    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'id_tahunakademik', 'id_tahunakademik');
    }
}
