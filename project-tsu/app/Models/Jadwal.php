<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $table = 'jadwal';
    protected $primaryKey = 'id_jadwal';
    public $timestamps = false;

    protected $fillable = [
        'id_matkul','id_dosen', 'id_kelas', 'id_ruang',
        'id_hari', 'id_slot', 'jenis_jadwal', 'status_validasi'
    ];
    
    public function matkul() { return $this->belongTo(Matkul::class, 'id_matkul'); }
    public function dosen() { return $this->belongTo(Dosen::class, 'id_dosen'); }
    public function kelas() { return $this->belongTo(Kelas::class, 'id_kelas'); }
    public function ruang() { return $this->belongTo(Ruang::class, 'id_ruang'); }
    public function hari() { return $this->belongTo(Hari::class, 'id_hari'); }
    public function slot() { return $this->belongTo(Slot::class, 'id_slot'); }

    //use HasFactory;

    /**
     * Tentukan nama tabel.
     */
    //protected $table = 'jadwal';

    /**
     * Tentukan primary key.
     */
    //protected $primaryKey = 'id_jadwal'; // Asumsi, sesuaikan jika perlu

    /**
     * Nonaktifkan timestamps jika tabel Anda tidak memilikinya.
     */
    //public $timestamps = false;

    /**
     * Kolom yang bisa diisi (mass assignable).
     * Sesuaikan ini dengan kolom di tabel 'jadwal' Anda.
     */
    //protected $fillable = [
        //'id_matkul', // Ini mungkin 'kode_matkul' jika itu PK Anda
        //'id_dosen',
        //'id_ruangan',
        //'id_prodi',
        //'hari',
        //'jam',
        //'semester',
        //'tipe', // 'Teori' atau 'Praktikum'
    //];

    // --- Relasi (Sangat Direkomendasikan) ---

    //public function mataKuliah()
    //{
        // Sesuaikan 'kode_matkul' jika itu adalah foreign key
        //return $this->belongsTo(MataKuliah::class, 'id_matkul', 'kode_matkul');
    //}

    //public function dosen()
    //{
        //return $this->belongsTo(Dosen::class, 'id_dosen');
    //}

    //public function ruangan()
    //{
        // Sesuaikan 'id_ruang' jika itu PK di tabel 'ruang'
        //return $this->belongsTo(Ruangan::class, 'id_ruangan', 'id_ruang');
    //}

    //public function prodi()
    //{
        //return $this->belongsTo(Prodi::class, 'id_prodi');
    //}

}
