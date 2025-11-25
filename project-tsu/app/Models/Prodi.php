<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; // <-- [BARU] Import DB facade

class Prodi extends Model
{
    use HasFactory;

    protected $table = 'program_studi';
    protected $primaryKey = 'id_prodi';
    public $incrementing = false;
    protected $keyType = 'integer';
    public $timestamps = false;

    protected $fillable = [
        'id_prodi',
        'nama_prodi',
        'kode_prodi',
    ];



    /**
     * [BARU]
     * Boot method untuk model.
     * Ini akan dijalankan secara otomatis oleh Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * 'creating' event ini berjalan SEBELUM data baru disimpan ke database.
         * Kita akan menggunakannya untuk membuat ID otomatis.
         */
        static::creating(function ($model) {
            // Periksa apakah id_prodi belum di-set secara manual
            if (empty($model->id_prodi)) {
                // 1. Ambil id_prodi tertinggi yang ada di tabel
                $maxId = DB::table('program_studi')->max('id_prodi');

                // 2. Tambahkan 1 ke ID tertinggi.
                //    (Jika tabel kosong, $maxId akan null, jadi kita pakai ?? 0)
                $model->id_prodi = ($maxId ?? 0) + 1;
            }
        });
    }
}
