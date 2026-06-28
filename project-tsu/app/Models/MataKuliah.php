<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    use HasFactory;

    protected $table = 'mata_kuliah';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'kode_matkul',
        'nama_matkul',
        'sks',
        'jenis',
        'id_prodi',
        'semester',
        'id_kurikulum',
        'konsentrasi',
        'sifat',
    ];

    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'id_kurikulum', 'id_kurikulum');
    }

        public function program_studi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }

        public function ruangans()
    {
        return $this->belongsToMany(
            Ruangan::class,
            'matkul_ruang',
            'kode_matkul',
            'id_ruang',
            'kode_matkul',
            'id_ruang'
        );
    }

        public function dosens()
    {
        return $this->belongsToMany(
            Dosen::class,
            'dosen_matakuliah',
            'kode_matkul',
            'nuptk',
            'kode_matkul',
            'nuptk'
        );
    }

    public function pengampus()
    {
        return $this->hasManyThrough(
            PengampuKelas::class,
            Kelas::class,
            'kode_matkul', // Foreign key on kelas table
            'id_kelas',    // Foreign key on pengampu_kelas table
            'kode_matkul', // Local key on mata_kuliah table
            'id_kelas'     // Local key on kelas table
        );
    }

    protected static function booted()
    {
        static::deleting(function ($matkul) {
            $matkul->ruangans()->detach();
        });
    }
}
