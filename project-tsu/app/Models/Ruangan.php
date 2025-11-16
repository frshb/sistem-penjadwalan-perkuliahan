<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    use HasFactory;
    protected $table = 'ruang';
    protected $primaryKey = 'id_ruang';

    protected $fillable = [
        'nama_ruang',
        'kapasitas',
        'fasilitas',
        'id_gedung',
    ];
    public $timestamps = false;
    public function gedung()
    {
        // Parameter: (Model Tujuan, Foreign Key di tabel ini, Primary Key di tabel tujuan)
        return $this->belongsTo(Gedung::class, 'id_gedung', 'id_gedung');
    }
}
