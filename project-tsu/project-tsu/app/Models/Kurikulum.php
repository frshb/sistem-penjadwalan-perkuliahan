<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kurikulum extends Model
{
    use HasFactory;

    /**
     * Tentukan nama tabel.
     */
    protected $table = 'kurikulum';

    /**
     * Tentukan primary key.
     */
    protected $primaryKey = 'id_kurikulum';

    /**
     * Nonaktifkan timestamps (created_at, updated_at).
     */
    public $timestamps = false;

    /**
     * Tentukan kolom yang bisa diisi (mass assignable).
     */
    protected $fillable = [
        'nama_kurikulum',
        'status',
    ];
}
