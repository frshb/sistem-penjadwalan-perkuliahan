<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table = 'semesters';
    protected $primaryKey = 'id_semester';

    protected $fillable = [
        'nama_semester',
        'tahun_ajaran',
        'status_aktif'
    ];
}
