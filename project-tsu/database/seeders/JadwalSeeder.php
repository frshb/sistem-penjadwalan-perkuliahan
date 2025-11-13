<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JadwalSeeder extends Seeder
{
    public function run(): void
    {
        // Peta ini adalah 'terjemahan' dari data Excel ke ID database
        // PETA HARI (id_hari => nama_hari)
        $hariMap = [ 'Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5 ];

        // PETA MATKUL (nama_matkul + id_prodi => kode_matkul)
        $matkulMap = [
            'Dasar Pemrograman_2' => 'SI-101', // S1-Sisfo
            'Skripsi/Tugas Akhir_1' => 'INF-701', // S1-Inf
            'Dasar Pemrograman_3' => 'RK-101', // S1-Rekom
            'Transformasi Digital_3' => 'RK-102', // S1-Rekom
            'Internet Of Things_1' => 'INF-702', // S1-Inf
            //... (Tambahkan semua matkul dari MataKuliahSeeder)
        ];

        // PETA DOSEN (nama_dosen => id_dosen)
        $dosenMap = [
            'Ahmad Muharya, S.Kom, M.Kom' => 1,
            'Yenny, S.Kom, M.Eng' => 2,
            'Retno Tri Vulandari, S.Si, M.Si' => 3,
            'Wawan Laksito YS, S.Si, M.Kom' => 4,
            //... (Tambahkan semua dosen dari DosenSeeder)
        ];

        // PETA KELAS (nama_kelas => id_kelas)
        $kelasMap = [
            'A1-1A' => 1,
            'A2-7S' => 2,
            'A3-1A' => 3,
            //... (Tambahkan semua kelas dari KelasSeeder)
        ];

        // PETA RUANG (nama_ruang_excel => id_ruang)
        $ruangMap = [
            'C21' => 6, // C 2.1
            'L6' => 6, // TODO: Cek RuangSeeder Anda. L6 tidak ada, saya asumsikan C21
            'C31' => 1, // C 3.1
            //... (Tambahkan semua ruang dari RuangSeeder)
        ];

        // PETA SLOT (nomor_kolom_excel => id_slot)
        $slotMap = [
            1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8,
            9 => 9, 10 => 10, 11 => 11, 12 => 12, 13 => 13, 14 => 14, 15 => 15, 16 => 16,
        ];

        // =================================================================
        // DATA EXCEL BARIS 1: Senin, S1-Sisfo, A1-1A, Dasar Pemrograman, 4 SKS, Ahmad Muharya, C21 (Jam 7,8,9,10)
        // =================================================================
        $id_jadwal = 1;
        $id_hari = $hariMap['Senin']; // 1
        $id_matkul = $matkulMap['Dasar Pemrograman_2']; // SI-101
        $id_dosen = $dosenMap['Ahmad Muharya, S.Kom, M.Kom']; // 1
        $id_kelas = $kelasMap['A1-1A']; // 1
        $id_ruang = $ruangMap['C21']; // 6

        // Loop untuk 4 slot (karena 4 SKS)
        foreach ([7, 8, 9, 10] as $slot_excel) {
            DB::table('jadwal')->insert([
                'id_jadwal' => $id_jadwal++,
                'id_matkul' => $id_matkul,
                'id_dosen' => $id_dosen,
                'id_kelas' => $id_kelas,
                'id_ruang' => $id_ruang,
                'id_hari' => $id_hari,
                'id_slot' => $slotMap[$slot_excel], // Terjemahkan slot excel ke id_slot
                'jenis_jadwal' => 'kuliah',
                'status_validasi' => true
            ]);
        }

        // =================================================================
        // DATA EXCEL BARIS 2: Senin, S1-Inf, A2-7S, Skripsi/TA, 6 SKS, Yenny, C21 (Jam 11,12)
        // =================================================================
        // Catatan: Excel Anda aneh, 6 SKS tapi hanya 2 slot. Saya ikuti Excel.
        $id_hari = $hariMap['Senin']; // 1
        $id_matkul = $matkulMap['Skripsi/Tugas Akhir_1']; // INF-701
        $id_dosen = $dosenMap['Yenny, S.Kom, M.Eng']; // 2
        $id_kelas = $kelasMap['A2-7S']; // 2
        $id_ruang = $ruangMap['C21']; // 6

        foreach ([11, 12] as $slot_excel) {
            DB::table('jadwal')->insert([
                'id_jadwal' => $id_jadwal++,
                'id_matkul' => $id_matkul,
                'id_dosen' => $id_dosen,
                'id_kelas' => $id_kelas,
                'id_ruang' => $id_ruang,
                'id_hari' => $id_hari,
                'id_slot' => $slotMap[$slot_excel],
                'jenis_jadwal' => 'kuliah',
                'status_validasi' => true
            ]);
        }

        // ... LANJUTKAN UNTUK BARIS EXCEL BERIKUTNYA ...
        // Polanya sama: cari ID, lalu loop per jam/slot
    }
}
