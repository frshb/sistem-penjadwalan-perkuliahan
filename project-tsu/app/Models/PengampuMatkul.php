<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengampuMatkul extends Model
{
    protected $table = 'pengampu_matkul';

    protected $fillable = [
        'id_dosen',
        'kode_matkul',
        'id_kelas',
    ];

    public $timestamps = false;

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen', 'id_dosen');
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'kode_matkul', 'kode_matkul');
    }
}
