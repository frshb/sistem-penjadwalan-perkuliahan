<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    use HasFactory;
    protected $table = 'mata_kuliah';
    protected $primaryKey = 'id_matkul';
    public $timestamps = false;
    public $incrementing = true;

    protected $fillable = [
        'id_matkul',
        'nama_matkul',
        'sks',
        'jenis',
        'semester',
        'kode_matkul',
    ];
}
