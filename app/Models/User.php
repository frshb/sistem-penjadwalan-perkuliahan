<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'id_user';
    public $timestamps = false; // Migration didn't show timestamps, verifying...

    protected $fillable = [
        'id_user', // Manually assigning ID? Migration wasn't auto-increment.
        'username',
        'password_hash',
        'id_role',
        'id_dosen',
        'id_mhs',
        // 'id_prodi'
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen', 'id_dosen');
    }

    // public function prodi()
    // {
    //     return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    // }

    // public function mahasiswa()
    // {
    //     return $this->belongsTo(Mahasiswa::class, 'id_mhs', 'id_mhs');
    // }

    // Constants for Role IDs (Matching Seeder)
    const ROLE_ADMIN = 1;
    const ROLE_KAPRODI = 2; // Formerly admin_fakultas
    const ROLE_DEKAN = 3;
    const ROLE_DOSEN = 4;
    const ROLE_MAHASISWA = 5;

    public function hasRole($roleName)
    {
        return $this->role->nama_role === $roleName;
    }

    public function isAdmin()
    {
        return $this->id_role === self::ROLE_ADMIN;
    }

    public function isDeKan()
    {
        return $this->id_role === self::ROLE_DEKAN;
    }

    public function isKaprodi()
    {
        return $this->id_role === self::ROLE_KAPRODI;
    }

    public function isDosen()
    {
        return $this->id_role === self::ROLE_DOSEN;
    }
}
