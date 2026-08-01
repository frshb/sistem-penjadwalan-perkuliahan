<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'role_target',
        'judul',
        'pesan',
        'tipe',
        'url',
        'is_read',
        'deleted_by',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'deleted_by' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }
}
