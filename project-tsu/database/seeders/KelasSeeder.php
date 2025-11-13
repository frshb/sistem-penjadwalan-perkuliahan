<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        // Peta Prodi (Sesuaikan dengan ProgramStudiSeeder Anda)
        $prodiMap = [
            'S1-Inf'   => 1,
            'S1-Sisfo' => 2,
            'S1-Rekom' => 3,
            'D3-MI'    => 6,
            'D3-TI'    => 7,
            'D3-SI'    => 8,
        ];

        // Peta Semester (Asumsi '1' di Excel = Gasal 25/26 = id_semester 1)
        // (Asumsi '7' di Excel = Gasal 25/26 = id_semester 1 juga, karena ini semester Gasal)
        // TODO: Sesuaikan 'id_semester' ini dengan 'SemesterSeeder' Anda
        $semester_gasal = 1;

        DB::table('kelas')->insert([
            [
                'id_kelas' => 1,
                'nama_kelas' => 'A1-1A',
                'id_prodi' => $prodiMap['S1-Sisfo'],
                'id_semester' => $semester_gasal, // Sem 1
                'kapasitas' => 40
            ],
            [
                'id_kelas' => 2,
                'nama_kelas' => 'A2-7S',
                'id_prodi' => $prodiMap['S1-Inf'],
                'id_semester' => $semester_gasal, // Sem 7
                'kapasitas' => 25
            ],
            [
                'id_kelas' => 3,
                'nama_kelas' => 'A3-1A',
                'id_prodi' => $prodiMap['S1-Rekom'],
                'id_semester' => $semester_gasal, // Sem 1
                'kapasitas' => 25
            ],
            [
                'id_kelas' => 4,
                'nama_kelas' => 'C2-5A',
                'id_prodi' => $prodiMap['D3-TI'],
                'id_semester' => $semester_gasal, // Sem 1
                'kapasitas' => 25
            ],
            [
                'id_kelas' => 5,
                'nama_kelas' => 'A2-1S',
                'id_prodi' => $prodiMap['S1-Inf'],
                'id_semester' => $semester_gasal, // Sem 1
                'kapasitas' => 25
            ],
            [
                'id_kelas' => 6,
                'nama_kelas' => 'A4-1S',
                'id_prodi' => $prodiMap['D3-SI'],
                'id_semester' => $semester_gasal, // Sem 1
                'kapasitas' => 25
            ],
            [
                'id_kelas' => 7,
                'nama_kelas' => 'A1-1S',
                'id_prodi' => $prodiMap['D3-MI'],
                'id_semester' => $semester_gasal, // Sem 1
                'kapasitas' => 30
            ],
            [
                'id_kelas' => 8,
                'nama_kelas' => 'A3-1S',
                'id_prodi' => $prodiMap['S1-Sisfo'],
                'id_semester' => $semester_gasal, // Sem 1
                'kapasitas' => 25
            ],
            [
                'id_kelas' => 9,
                'nama_kelas' => 'C1-1S',
                'id_prodi' => $prodiMap['D3-TI'],
                'id_semester' => $semester_gasal, // Sem 1
                'kapasitas' => 30
            ],
            [
                'id_kelas' => 10,
                'nama_kelas' => 'A1-7S',
                'id_prodi' => $prodiMap['S1-Inf'],
                'id_semester' => $semester_gasal, // Sem 7
                'kapasitas' => 25
            ],
        ]);
    }
}
