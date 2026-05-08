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
    ];
    
    public function matkul() { return $this->belongsTo(MataKuliah::class, 'id_matkul', 'kode_matkul'); }
    public function dosen() { return $this->belongsTo(Dosen::class, 'id_dosen'); }
    public function ruang() { return $this->belongsTo(Ruangan::class, 'id_ruang'); }
    public function hari() { return $this->belongsTo(Hari::class, 'id_hari'); }
    public function slot() { return $this->belongsTo(Waktu::class, 'id_slot'); }

}