<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengampuKelas extends Model
{
    protected $table = 'pengampu_kelas';

    protected $fillable = [
        'id_dosen',
        'id_kelas',
        'id_tahunakademik',
    ];

    public $timestamps = false;

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen', 'id_dosen');
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'id_tahunakademik', 'id_tahunakademik');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }
}
