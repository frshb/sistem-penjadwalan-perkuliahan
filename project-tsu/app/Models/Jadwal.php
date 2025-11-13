<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
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
}
