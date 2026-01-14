<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $table = 'jadwal';
    protected $primaryKey = 'id_jadwal';
    public $timestamps = false;

    protected $fillable = [
        'id_matkul',
        'id_dosen', 
        'id_ruang',
        'id_hari', 
        'id_slot', 
        'jenis_jadwal', 
        'status_validasi',
        'kelas',
        'is_manual',
    ];
    
    public function matkul() { return $this->belongTo(Matkul::class, 'id_matkul'); }
    public function dosen() { return $this->belongTo(Dosen::class, 'id_dosen'); }
    public function ruang() { return $this->belongTo(Ruang::class, 'id_ruang'); }
    public function hari() { return $this->belongTo(Hari::class, 'id_hari'); }
    public function slot() { return $this->belongTo(Slot::class, 'id_slot'); }

}