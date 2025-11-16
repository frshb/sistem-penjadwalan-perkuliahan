<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DosenSeeder extends Seeder
{
    public function run(): void
    {
        // Peta Prodi (Sesuaikan dengan ProgramStudiSeeder Anda)
        // 1=INF, 2=SI, 3=ReKom, 6=MI, 7=TI, 8=D3SI

        DB::table('dosen')->insert([
            [
                'id_dosen' => 1,
                'nama_dosen' => 'Ahmad Muharya, S.Kom, M.Kom',
                'status_dosen' => 'Tetap',
                'nik' => '11111111', // TODO: Ganti NIK
                'nuptk' => null,
                'nidn' => null,
                'id_prodi' => 1, // TODO: Ganti ID Prodi
            ],
            [
                'id_dosen' => 2,
                'nama_dosen' => 'Yenny, S.Kom, M.Eng',
                'status_dosen' => 'Tetap',
                'nik' => '22222222', // TODO: Ganti NIK
                'nuptk' => null,
                'nidn' => null,
                'id_prodi' => 1, // TODO: Ganti ID Prodi
            ],
            [
                'id_dosen' => 3,
                'nama_dosen' => 'Retno Tri Vulandari, S.Si, M.Si',
                'status_dosen' => 'Tetap',
                'nik' => '33333333', // TODO: Ganti NIK
                'nuptk' => null,
                'nidn' => null,
                'id_prodi' => 1, // TODO: Ganti ID Prodi
            ],
            [
                'id_dosen' => 4,
                'nama_dosen' => 'Wawan Laksito YS, S.Si, M.Kom',
                'status_dosen' => 'Tidak Tetap',
                'nik' => '44444444', // TODO: Ganti NIK
                'nuptk' => null,
                'nidn' => null,
                'id_prodi' => 3, // TODO: Ganti ID Prodi
            ],
            [
                'id_dosen' => 5,
                'nama_dosen' => 'Dr. Ir. Muhammad Hasbi, M.Kom',
                'status_dosen' => 'Tetap',
                'nik' => '55555555', // TODO: Ganti NIK
                'nuptk' => null,
                'nidn' => null,
                'id_prodi' => 1, // TODO: Ganti ID Prodi
            ],
            [
                'id_dosen' => 6,
                'nama_dosen' => 'Intan Rofi\'ah, S.Pd., M.Pd',
                'status_dosen' => 'Tidak Tetap',
                'nik' => '66666666', // TODO: Ganti NIK
                'nuptk' => null,
                'nidn' => null,
                'id_prodi' => 1, // TODO: Ganti ID Prodi (Matkul Umum)
            ],
            [
                'id_dosen' => 7,
                'nama_dosen' => 'Rudi Hartono, S.Kom, M.Kom',
                'status_dosen' => 'Tetap',
                'nik' => '77777777', // TODO: Ganti NIK
                'nuptk' => null,
                'nidn' => null,
                'id_prodi' => 2, // TODO: Ganti ID Prodi
            ],
            [
                'id_dosen' => 8,
                'nama_dosen' => 'Agus Purbo, S.Kom, M.Kom',
                'status_dosen' => 'Tetap',
                'nik' => '88888888', // TODO: Ganti NIK
                'nuptk' => null,
                'nidn' => null,
                'id_prodi' => 7, // TODO: Ganti ID Prodi
            ],
            [
                'id_dosen' => 9,
                'nama_dosen' => 'Joko S, S.Kom, M.Kom',
                'status_dosen' => 'Tetap',
                'nik' => '99999999', // TODO: Ganti NIK
                'nuptk' => null,
                'nidn' => null,
                'id_prodi' => 8, // TODO: Ganti ID Prodi
            ],
            [
                'id_dosen' => 10,
                'nama_dosen' => 'Budi Santoso, S.Kom, M.Kom',
                'status_dosen' => 'Tetap',
                'nik' => '10101010', // TODO: Ganti NIK
                'nuptk' => null,
                'nidn' => null,
                'id_prodi' => 6, // TODO: Ganti ID Prodi
            ],
            [
                'id_dosen' => 11,
                'nama_dosen' => 'Hery P, S.Kom, M.Kom',
                'status_dosen' => 'Tetap',
                'nik' => '12121212', // TODO: Ganti NIK
                'nuptk' => null,
                'nidn' => null,
                'id_prodi' => 2, // TODO: Ganti ID Prodi
            ],
        ]);
    }
}
