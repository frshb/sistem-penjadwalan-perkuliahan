<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KetersediaanDosenSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     * Ini akan membuat "matriks" ketersediaan penuh untuk semua dosen.
     */
    public function run(): void
    {
        // 1. Ambil semua ID master
        $dosenIds = DB::table('dosen')->pluck('id_dosen');
        $hariIds = DB::table('hari')->pluck('id_hari');
        $slotIds = DB::table('slot_waktu')->pluck('id_slot');

        $ketersediaan = [];

        // 2. Loop melalui setiap kemungkinan kombinasi
        foreach ($dosenIds as $dosen) {
            foreach ($hariIds as $hari) {
                foreach ($slotIds as $slot) {

                    // 3. Buat status acak
                    // rand(1, 10) > 3 berarti 7 dari 10 (70%) akan bernilai TRUE
                    $status_acak = (rand(1, 10) > 3);

                    $ketersediaan[] = [
                        'id_dosen' => $dosen,
                        'id_hari' => $hari,
                        'id_slot' => $slot,
                        'status_tersedia' => $status_acak
                    ];
                }
            }
        }

        // 4. Masukkan semua data ke database sekaligus
        // Kita bagi per 500 data agar tidak terlalu berat jika datanya besar
        foreach (array_chunk($ketersediaan, 500) as $chunk) {
            DB::table('ketersediaan_dosen')->insert($chunk);
        }
    }
}
