<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

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
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id_prodi)) {
                $maxId = DB::table('program_studi')->max('id_prodi');
                $model->id_prodi = ($maxId ?? 0) + 1;
            }
        });
    }
}
