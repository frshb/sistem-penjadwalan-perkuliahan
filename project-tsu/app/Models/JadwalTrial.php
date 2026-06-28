<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalTrial extends Model
{
    protected $table = 'jadwal_trials';

    protected $fillable = [
        'id_tahunakademik',
        'label',
        'fitness',
        'generasi',
        'total_kelas',
        'dosen_conflicts',
        'ruangan_conflicts',
        'soft_violations',
        'jadwal_json',
    ];

    protected $casts = [
        'fitness' => 'double',
        'generasi' => 'integer',
        'total_kelas' => 'integer',
        'dosen_conflicts' => 'integer',
        'ruangan_conflicts' => 'integer',
        'soft_violations' => 'integer',
    ];

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'id_tahunakademik', 'id_tahunakademik');
    }
}
