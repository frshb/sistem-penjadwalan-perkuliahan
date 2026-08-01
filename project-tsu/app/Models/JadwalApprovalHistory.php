<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalApprovalHistory extends Model
{
    use HasFactory;

    protected $table = 'jadwal_approval_histories';

    protected $fillable = [
        'id_tahunakademik',
        'id_prodi',
        'user_id',
        'role_actor',
        'aksi',
        'status_sebelumnya',
        'status_sesudah',
        'catatan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'id_tahunakademik');
    }

    public function prodi()
    {
        return $this->belongsTo(ProgramStudi::class, 'id_prodi');
    }
}
