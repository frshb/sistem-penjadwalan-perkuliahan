<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $primaryKey = 'id_kelas';

    public $timestamps = false;

    protected $fillable = [
        'nama_kelas',
        'id_prodi',
        'id_tahunakademik',
        'kapasitas',
        'semester',
        'kode_matkul',
        'jumlah_mahasiswa',
    ];

    /**
     * Relasi ke Program Studi
     */
    public function prodi()
    {
        return $this->belongsTo(
            Prodi::class,
            'id_prodi',
            'id_prodi'
        );
    }

    /**
     * Relasi ke Tahun Akademik
     */
    public function tahunAkademik()
    {
        return $this->belongsTo(
            TahunAkademik::class,
            'id_tahunakademik',
            'id_tahunakademik'
        );
    }

    /**
     * Relasi ke Mata Kuliah
     */
    public function matakuliah()
    {
        return $this->belongsTo(
            MataKuliah::class,
            'kode_matkul',
            'kode_matkul'
        );
    }

    /**
     * Relasi ke Dosen melalui pengampu_kelas
     */
    public function dosen()
    {
        return $this->hasOneThrough(
            Dosen::class,
            PengampuKelas::class,
            'id_kelas',
            'id_dosen',
            'id_kelas',
            'id_dosen'
        );
    }

    public function pengampus()
    {
        return $this->hasMany(
            PengampuKelas::class,
            'id_kelas',
            'id_kelas'
        );
    }

    public function pengampuMatkul()
    {
        return $this->hasMany(
            PengampuKelas::class,
            'id_kelas',
            'id_kelas'
        );
    }

    public function jadwals()
    {
        return $this->hasMany(
            Jadwal::class,
            'id_kelas',
            'id_kelas'
        );
    }
}