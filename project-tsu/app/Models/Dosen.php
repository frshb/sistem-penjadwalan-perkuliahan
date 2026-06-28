<?php

namespace App\Models;
use App\Models\PengampuKelas;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    use HasFactory;
    protected $table = 'dosen';
    protected $primaryKey = 'id_dosen';
    public $timestamps = false;

    protected $fillable = [
        'nama_dosen',
        'nuptk',
        'nidn',
        'id_prodi',
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }

    public function kelas()
    {
        return $this->belongsToMany(
            Kelas::class,
            'pengampu_kelas',
            'id_dosen',
            'id_kelas',
            'id_dosen',
            'id_kelas'
        );
    }

    public function pengampus()
    {
        return $this->hasMany(
            PengampuKelas::class,
            'id_dosen'
        );
    }

    protected static function booted()
    {
        static::deleting(function ($dosen) {

            $dosen->kelas()->detach();

        });
    }
}
