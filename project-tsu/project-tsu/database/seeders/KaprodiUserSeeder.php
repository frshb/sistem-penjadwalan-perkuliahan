<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class KaprodiUserSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        // Kaprodi Informatika (Prodi ID: 1) -> Dosen ID: 1 (Ahmad Muharya)
        $this->createUser('kaprodi_inf', 'password', 1, 2);

        // Kaprodi Sistem Informasi (Prodi ID: 2) -> Dosen ID: 7 (Rudi Hartono)
        $this->createUser('kaprodi_si', 'password', 7, 2);

        // Kaprodi Rekayasa Komputer (Prodi ID: 3) -> Dosen ID: 4 (Wawan Laksito YS)
        $this->createUser('kaprodi_rekom', 'password', 4, 2); 
    }

    private function createUser($username, $password, $dosenId, $roleId)
    {
        // Cek jika user sudah ada
        if (User::where('username', $username)->exists()) {
            return;
        }

        // Cari ID user baru (manual check karena tidak auto-increment di migration user table sebelumnya)
        $maxId = User::max('id_user');
        $newId = $maxId ? $maxId + 1 : 1;

        User::create([
            'id_user' => $newId,
            'username' => $username,
            'password_hash' => Hash::make($password),
            'id_role' => $roleId, // 2 = Kaprodi
            'id_dosen' => $dosenId,
        ]);
    }
}
