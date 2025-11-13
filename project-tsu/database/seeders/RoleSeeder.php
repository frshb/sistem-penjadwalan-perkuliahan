<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run()
    {
        DB::table('role')->insert([
            ['id_role' => 1, 'nama_role' => 'super_admin'],
            ['id_role' => 2, 'nama_role' => 'admin_fakultas'],
            ['id_role' => 3, 'nama_role' => 'dekan'],
            ['id_role' => 4, 'nama_role' => 'dosen'],
            ['id_role' => 5, 'nama_role' => 'mahasiswa'],
        ]);
    }
}
