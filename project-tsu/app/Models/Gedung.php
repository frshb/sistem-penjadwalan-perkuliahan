<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gedung extends Model
{
    use HasFactory;

    protected $table = 'gedung';
    protected $primaryKey = 'id_gedung';
    public $timestamps = false;

    protected $fillable = [
        'nama_gedung',
        'lantai',
        'lokasi',
    ];

    public function ruangs()
    {
        return $this->hasMany(Ruangan::class, 'id_gedung', 'id_gedung');
    }
}
