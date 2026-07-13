<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table      = 'user';
    protected $primaryKey = 'id_user';
    public    $timestamps = false;

    protected $fillable = [
        'username',
        'password_hash',
        'id_role',
        'id_dosen',
        'id_mhs',
        'id_prodi',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    // ✅ Sesuaikan casts hanya dengan kolom yang benar-benar ada di tabel
    protected function casts(): array
    {
        return [];
    }

    // ✅ Wajib ada agar Laravel Auth bisa verifikasi password di kolom password_hash
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // =========================================================
    // RELASI
    // =========================================================

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen', 'id_dosen');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }

    // public function mahasiswa()
    // {
    //     return $this->belongsTo(Mahasiswa::class, 'id_mhs', 'id_mhs');
    // }

    // =========================================================
    // ROLE CONSTANTS — berbasis nama, bukan ID
    // Lebih aman: tidak bergantung pada urutan seeder/insert
    // =========================================================

    const ROLE_ADMIN     = 'admin';
    const ROLE_KAPRODI   = 'kaprodi';
    const ROLE_SEKPRODI  = 'sekretaris prodi';
    const ROLE_DEKAN     = 'dekan';
    const ROLE_DOSEN     = 'dosen';
    const ROLE_MAHASISWA = 'mahasiswa';

    // =========================================================
    // HELPER ROLE
    // =========================================================

    /**
     * Cek apakah user memiliki nama role tertentu.
     * Aman jika relasi role null.
     */
    public function hasRole(string $roleName): bool
    {
        // ✅ Tambah null-check agar tidak error jika id_role tidak valid
        return $this->role?->nama_role === $roleName;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isDekan(): bool
    {
        // ✅ Fix typo: isDeKan → isDekan
        return $this->hasRole(self::ROLE_DEKAN);
    }

    public function isKaprodi(): bool
    {
        return $this->hasRole(self::ROLE_KAPRODI);
    }

    public function isSekretarisProdi(): bool
    {
        return $this->hasRole(self::ROLE_SEKPRODI);
    }

    public function isDosen(): bool
    {
        return $this->hasRole(self::ROLE_DOSEN);
    }

    public function isMahasiswa(): bool
    {
        return $this->hasRole(self::ROLE_MAHASISWA);
    }

    // =========================================================
    // HELPER LAIN
    // =========================================================

    /**
     * Ambil id_prodi dari user langsung, atau fallback ke prodi dosen.
     */
    public function getProdiId(): ?int
    {
        if ($this->id_prodi) {
            return $this->id_prodi;
        }

        // Fallback ke prodi dosen jika ada
        return $this->dosen?->id_prodi ?? null;
    }

    /**
     * Cek permission berdasarkan module dan item.
     * Admin selalu mendapat akses penuh.
     */
    public function hasPermission(string $module, ?string $item = null): bool
    {
        // ✅ Admin bypass semua permission check
        if ($this->isAdmin()) {
            return true;
        }

        // ✅ Aman jika role null
        return $this->role?->hasPermission($module, $item) ?? false;
    }

    /**
     * Cek permission access level (read/edit).
     * Admin selalu mendapat akses penuh (edit).
     */
    public function hasPermissionAccess(string $module, string $item, string $requiredAccess = 'read'): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->role?->hasPermissionAccess($module, $item, $requiredAccess) ?? false;
    }
}
