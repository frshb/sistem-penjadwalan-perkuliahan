<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    //
    use HasFactory;

    protected $table = 'mata_kuliah';
    protected $primaryKey = 'kode_matkul';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    /**
     * Kolom yang bisa diisi.
     */
    protected $fillable = [
        'kode_matkul',
        'nama_matkul',
        'sks',
        'jenis',
        'id_prodi',
        'semester',
        'id_kurikulum',
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
}
