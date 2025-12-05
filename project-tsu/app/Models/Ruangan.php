<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB; // <-- [BARU] Import DB facade

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
        'ketersediaan',
    ];

    protected $casts = [
    'ketersediaan' => 'boolean'
    ];

    public $timestamps = false;
    public function gedung()
    {
        // Parameter: (Model Tujuan, Foreign Key di tabel ini, Primary Key di tabel tujuan)
        return $this->belongsTo(Gedung::class, 'id_gedung', 'id_gedung');
    }
        protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Jika ID belum di-set manual
            if (empty($model->id_ruang)) {

                // Cari ID terbesar di tabel ruang
                $maxId = DB::table('ruang')->max('id_ruang');

                // Set ID = max + 1 (jika null → mulai dari 1)
                $model->id_ruang = ($maxId ?? 0) + 1;
            }
        });
    }
}
