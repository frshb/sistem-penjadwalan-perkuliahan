<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgramStudiSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        DB::table('program_studi')->insert([
            // Prodi S1 dari Excel
            [
                'id_prodi' => 1,
                'nama_prodi' => 'S1 Informatika',
                'kode_prodi' => 'INF'
            ],
            [
                'id_prodi' => 2,
                'nama_prodi' => 'S1 Sistem Informasi',
                'kode_prodi' => 'SI'
            ],
            [
                'id_prodi' => 3,
                'nama_prodi' => 'S1 Rekayasa Komputer',
                'kode_prodi' => 'ReKom'
            ],

            // Prodi D3 dari Excel (WAJIB ADA untuk MataKuliahSeeder)
            [
                'id_prodi' => 6,
                'nama_prodi' => 'D3 Manajemen Informatika',
                'kode_prodi' => 'D3-MI'
            ],
            [
                'id_prodi' => 7,
                'nama_prodi' => 'D3 Teknik Informatika',
                'kode_prodi' => 'D3-TI'
            ],
            [
                'id_prodi' => 8,
                'nama_prodi' => 'D3 Sistem Informasi',
                'kode_prodi' => 'D3-SI'
            ],
        ]);
    }
}
