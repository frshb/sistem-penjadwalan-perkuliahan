<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prodi extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'program_studi';
    protected $primaryKey = 'id_prodi';
    protected $fillable = [
        'nama_prodi',
        'kode_prodi',
    ];
}
