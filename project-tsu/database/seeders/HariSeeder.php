<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HariSeeder extends Seeder
{
    public function run()
    {
        DB::table('hari')->insert([
            ['id_hari' => 1, 'nama_hari' => 'Senin'],
            ['id_hari' => 2, 'nama_hari' => 'Selasa'],
            ['id_hari' => 3, 'nama_hari' => 'Rabu'],
            ['id_hari' => 4, 'nama_hari' => 'Kamis'],
            ['id_hari' => 5, 'nama_hari' => 'Jumat'],
        ]);
    }
}
