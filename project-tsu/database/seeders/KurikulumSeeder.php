<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class kurikulumSeeder extends Seeder
{
    public function run()
    {
        DB::table('kurikulum')->insert([
            ['id_kurikulum' => 1, 'nama_kurikulum' => '2018', 'status' => 'Aktif'],
            ['id_kurikulum' => 2, 'nama_kurikulum' => '2022', 'status' => 'Aktif'],
            ['id_kurikulum' => 3, 'nama_kurikulum' => '2025', 'status' => 'Aktif'],
        ]);
    }
}
