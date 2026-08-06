<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalValidasiProdi extends Model
{
    use HasFactory;

    protected $table = 'jadwal_validasi_prodis';

    protected $fillable = [
        'id_tahunakademik',
        'id_prodi',
        'status_sekprodi',
        'status_kaprodi',
        'catatan_revisi_sekprodi',
        'catatan_revisi_kaprodi',
    ];

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'id_tahunakademik', 'id_tahunakademik');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }
}
