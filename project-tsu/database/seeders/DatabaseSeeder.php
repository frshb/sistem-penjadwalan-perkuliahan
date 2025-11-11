<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Urutan ini penting untuk foreign key constraints

        // Grup 1: Tidak ada dependensi
        $this->call([
            GedungSeeder::class,
            HariSeeder::class,
            SlotWaktuSeeder::class,
            SemesterSeeder::class,
            KurikulumSeeder::class,
            ProgramStudiSeeder::class,
            RoleSeeder::class,
            MigrationsSeeder::class, // Seeder untuk log migrasi
        ]);

        // Grup 2: Dependensi ke Grup 1
        $this->call([
            RuangSeeder::class,      // -> gedung
            DosenSeeder::class,      // -> program_studi
            KelasSeeder::class,      // -> program_studi, semester
            MataKuliahSeeder::class, // -> program_studi
        ]);

        // Grup 3: Dependensi ke Grup 1 & 2
        // (mahasiswa & user tidak ada data di dump)
        $this->call([
            JadwalSeeder::class,             // -> matkul, dosen, kelas, ruang, hari, slot
            KetersediaanDosenSeeder::class,  // -> dosen, hari, slot
        ]);
    }
}
