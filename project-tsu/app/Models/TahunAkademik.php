<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAkademik extends Model
{
    protected $table = 'tahun_akademik';

    protected $primaryKey = 'id_tahunakademik';

    public $timestamps = false;

    protected $fillable = [
        'nama_tahunakademik',
        'tahun_ajaran',
        'status_aktif',
    ];

    public function kelas()
    {
        return $this->hasMany(
            Kelas::class,
            'id_tahunakademik',
            'id_tahunakademik'
        );
    }
}
