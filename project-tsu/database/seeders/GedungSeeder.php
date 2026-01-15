<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GedungSeeder extends Seeder
{
    public function run()
    {
        DB::table('gedung')->insert([
            ['id_gedung' => 1, 'nama_gedung' => 'Gedung A (FT)', 'lantai' => 5, 'lokasi' => 'Area Fakultas Teknik'],
            ['id_gedung' => 2, 'nama_gedung' => 'Gedung B (FT)', 'lantai' => 3, 'lokasi' => 'Area Fakultas Teknik'],
            ['id_gedung' => 3, 'nama_gedung' => 'Gedung C (FT)', 'lantai' => 4, 'lokasi' => 'Area Fakultas Teknik'],
            ['id_gedung' => 4, 'nama_gedung' => 'Gedung D (Workshop)', 'lantai' => 2, 'lokasi' => 'Area Belakang FT'],
        ]);
    }
}
