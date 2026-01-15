<?php
namespace Database\Seeders;

// Pastikan ini benar (bukan Illuminate_Database_Seeder)
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MataKuliahSeeder extends Seeder
{
    /**
     * Jalankan seeder mata kuliah berdasarkan file XLSX.
     * * CATATAN: 'kode_matkul' dan 'jenis' diperkirakan/diasumsikan
     * karena tidak ada di file data sumber.
     */
    public function run(): void
    {
        // Peta untuk menerjemahkan nama Prodi di Excel ke ID Prodi
        // (Sesuaikan ID ini jika ProgramStudiSeeder Anda berbeda)
        $prodiMap = [
            'S1-Inf'   => 1,
            'S1-Sisfo' => 2,
            'S1-Rekom' => 3,
            'D3-MI'    => 6,
            'D3-TI'    => 7,
            'D3-SI'    => 8,
        ];

        DB::table('mata_kuliah')->insert([
            [
                'kode_matkul' => 'SI-101', // Diperkirakan
                'nama_matkul' => 'Dasar Pemrograman',
                'sks' => 4,
                'jenis' => 'praktikum', // Diperkirakan
                'id_prodi' => $prodiMap['S1-Sisfo'],
                'semester' => 1,
                'id_kurikulum' => null,
            ],
            [
                'kode_matkul' => 'INF-701', // Diperkirakan
                'nama_matkul' => 'Skripsi/Tugas Akhir',
                'sks' => 6,
                'jenis' => 'praktikum', // Diperkirakan
                'id_prodi' => $prodiMap['S1-Inf'],
                'semester' => 7,
                'id_kurikulum' => null,
            ],
            [
                'kode_matkul' => 'RK-101', // Diperkirakan
                'nama_matkul' => 'Dasar Pemrograman',
                'sks' => 4,
                'jenis' => 'praktikum', // Diperkirakan
                'id_prodi' => $prodiMap['S1-Rekom'],
                'semester' => 1,
                'id_kurikulum' => null,
            ],
            [
                'kode_matkul' => 'TI-101', // Diperkirakan
                'nama_matkul' => 'Pendidikan Agama',
                'sks' => 2,
                'jenis' => 'teori', // Diperkirakan
                'id_prodi' => $prodiMap['D3-TI'],
                'semester' => 1,
                'id_kurikulum' => null,
            ],
            [
                'kode_matkul' => 'TI-102', // Diperkirakan
                'nama_matkul' => 'Bhs. Indonesia',
                'sks' => 2,
                'jenis' => 'teori', // Diperkirakan
                'id_prodi' => $prodiMap['D3-TI'],
                'semester' => 1,
                'id_kurikulum' => null,
            ],
            [
                'kode_matkul' => 'INF-101', // Diperkirakan
                'nama_matkul' => 'Pendidikan Agama',
                'sks' => 2,
                'jenis' => 'teori', // Diperkirakan
                'id_prodi' => $prodiMap['S1-Inf'],
                'semester' => 1,
                'id_kurikulum' => null,
            ],
            [
                'kode_matkul' => 'INF-702', // Diperkirakan
                'nama_matkul' => 'Internet Of Things',
                'sks' => 2,
                'jenis' => 'praktikum', // Diperkirakan
                'id_prodi' => $prodiMap['S1-Inf'],
                'semester' => 7,
                'id_kurikulum' => null,
            ],
            [
                'kode_matkul' => 'RK-102', // Diperkirakan
                'nama_matkul' => 'Transformasi Digital',
                'sks' => 3,
                'jenis' => 'teori', // Diperkirakan
                'id_prodi' => $prodiMap['S1-Rekom'],
                'semester' => 1,
                'id_kurikulum' => null,
            ],
            [
                'kode_matkul' => 'INF-102', // Diperkirakan
                'nama_matkul' => 'Transformasi Digital',
                'sks' => 3,
                'jenis' => 'teori', // Diperkirakan
                'id_prodi' => $prodiMap['S1-Inf'],
                'semester' => 1,
                'id_kurikulum' => null,
            ],
            [
                'kode_matkul' => 'INF-103', // Diperkirakan
                'nama_matkul' => 'Algoritma & Pemrograman',
                'sks' => 4,
                'jenis' => 'praktikum', // Diperkirakan
                'id_prodi' => $prodiMap['S1-Inf'],
                'semester' => 1,
                'id_kurikulum' => null,
            ],
            [
                'kode_matkul' => 'TI-103', // Diperkirakan
                'nama_matkul' => 'Pemrograman Dasar',
                'sks' => 4,
                'jenis' => 'praktikum', // Diperkirakan
                'id_prodi' => $prodiMap['D3-TI'],
                'semester' => 1,
                'id_kurikulum' => null,
            ],
            [
                'kode_matkul' => 'RK-103', // Diperkirakan
                'nama_matkul' => 'Kalkulus',
                'sks' => 3,
                'jenis' => 'teori', // Diperkirakan
                'id_prodi' => $prodiMap['S1-Rekom'],
                'semester' => 1,
                'id_kurikulum' => null,
            ],
            [
                'kode_matkul' => 'DSI-101', // Diperkirakan
                'nama_matkul' => 'Logika & Algoritma',
                'sks' => 3,
                'jenis' => 'teori', // Diperkirakan
                'id_prodi' => $prodiMap['D3-SI'],
                'semester' => 1,
                'id_kurikulum' => null,
            ],
            [
                'kode_matkul' => 'MI-101', // Diperkirakan
                'nama_matkul' => 'Konsep SI',
                'sks' => 3,
                'jenis' => 'teori', // Diperkirakan
                'id_prodi' => $prodiMap['D3-MI'],
                'semester' => 1,
                'id_kurikulum' => null,
            ],
            [
                'kode_matkul' => 'MI-102', // Diperkirakan
                'nama_matkul' => 'Bhs. Inggris',
                'sks' => 2,
                'jenis' => 'teori', // Diperkirakan
                'id_prodi' => $prodiMap['D3-MI'],
                'semester' => 1,
                'id_kurikulum' => null,
            ],
            [
                'kode_matkul' => 'SI-102', // Diperkirakan
                'nama_matkul' => 'P. Kewarganegaraan',
                'sks' => 2,
                'jenis' => 'teori', // Diperkirakan
                'id_prodi' => $prodiMap['S1-Sisfo'],
                'semester' => 1,
                'id_kurikulum' => null,
            ],
        ]);
    }
}
