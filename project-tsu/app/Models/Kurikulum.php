<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kurikulum extends Model
{
    use HasFactory;
    protected $table = 'kurikulum';
    protected $primaryKey = 'id_kurikulum';

    public $timestamps = false;
    protected $fillable = [
        'kode_kurikulum',
        'nama_kurikulum',
        'tahun_berlaku',
        'status',
    ];
}
