<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SemesterSeeder extends Seeder
{
    public function run()
    {
        DB::table('semester')->insert([
            ['id_semester' => 1, 'nama_semester' => 'Gasal 2025/2026', 'tahun_ajaran' => '2025/2026', 'status_aktif' => 1],
            ['id_semester' => 2, 'nama_semester' => 'Genap 2025/2026', 'tahun_ajaran' => '2025/2026', 'status_aktif' => 0],
            ['id_semester' => 3, 'nama_semester' => 'Gasal 2024/2025', 'tahun_ajaran' => '2024/2025', 'status_aktif' => 0],
            ['id_semester' => 4, 'nama_semester' => 'Genap 2024/2025', 'tahun_ajaran' => '2024/2025', 'status_aktif' => 0],
            ['id_semester' => 5, 'nama_semester' => 'Gasal 2023/2024', 'tahun_ajaran' => '2023/2024', 'status_aktif' => 0],
        ]);
    }
}
