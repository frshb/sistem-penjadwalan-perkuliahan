<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RuangSeeder extends Seeder
{
    public function run()
    {
        DB::table('ruang')->insert([
            ['id_ruang' => 1, 'nama_ruang' => 'C 3.1', 'kapasitas' => 25, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 1],
            ['id_ruang' => 2, 'nama_ruang' => 'B.1.5 (Lab Komputer)', 'kapasitas' => 30, 'fasilitas' => 'AC, Proyektor, 30 Unit PC', 'id_gedung' => 2],
            ['id_ruang' => 3, 'nama_ruang' => 'C 2.1', 'kapasitas' => 25, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 1],
            ['id_ruang' => 4, 'nama_ruang' => 'A 2.1', 'kapasitas' => 40, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 1],
            ['id_ruang' => 5, 'nama_ruang' => 'A 2.2', 'kapasitas' => 40, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 1],
            ['id_ruang' => 6, 'nama_ruang' => 'A 3.1', 'kapasitas' => 40, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 1],
            ['id_ruang' => 7, 'nama_ruang' => 'A 3.2', 'kapasitas' => 40, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 1],
            ['id_ruang' => 8, 'nama_ruang' => 'B 1.1 (Lab Jaringan)', 'kapasitas' => 25, 'fasilitas' => 'AC, Proyektor, Perangkat Jaringan', 'id_gedung' => 2],
            ['id_ruang' => 9, 'nama_ruang' => 'B 2.1 (Lab Basis Data)', 'kapasitas' => 30, 'fasilitas' => 'AC, Proyektor, 30 Unit PC', 'id_gedung' => 2],
            ['id_ruang' => 10, 'nama_ruang' => 'B 2.2 (Lab Multimedia)', 'kapasitas' => 25, 'fasilitas' => 'AC, Proyektor, 25 Unit PC Spek Tinggi', 'id_gedung' => 2],
            ['id_ruang' => 11, 'nama_ruang' => 'C 1.1', 'kapasitas' => 40, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 3],
            ['id_ruang' => 12, 'nama_ruang' => 'C 1.2', 'kapasitas' => 40, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 3],
            ['id_ruang' => 13, 'nama_ruang' => 'C 2.2', 'kapasitas' => 40, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 3],
            ['id_ruang' => 14, 'nama_ruang' => 'C 3.2', 'kapasitas' => 40, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 3],
            ['id_ruang' => 15, 'nama_ruang' => 'C 4.1 (Aula Mini)', 'kapasitas' => 80, 'fasilitas' => 'AC, Proyektor, Sound System', 'id_gedung' => 3],
            ['id_ruang' => 16, 'nama_ruang' => 'D 1.1 (Workshop Mesin)', 'kapasitas' => 50, 'fasilitas' => 'Mesin Bubut, Mesin CNC', 'id_gedung' => 4],
            ['id_ruang' => 17, 'nama_ruang' => 'D 1.2 (Workshop Elektro)', 'kapasitas' => 40, 'fasilitas' => 'Oscilloscope, Solder Station', 'id_gedung' => 4],
            ['id_ruang' => 18, 'nama_ruang' => 'A 4.1', 'kapasitas' => 30, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 1],
            ['id_ruang' => 19, 'nama_ruang' => 'A 4.2', 'kapasitas' => 30, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 1],
            ['id_ruang' => 20, 'nama_ruang' => 'B 3.1', 'kapasitas' => 30, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 2],
            ['id_ruang' => 21, 'nama_ruang' => 'B 3.2', 'kapasitas' => 30, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 2],
            ['id_ruang' => 22, 'nama_ruang' => 'C 1.3', 'kapasitas' => 25, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 3],
            ['id_ruang' => 23, 'nama_ruang' => 'C 2.3', 'kapasitas' => 25, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 3],
            ['id_ruang' => 24, 'nama_ruang' => 'D 2.1 (Lab PLC)', 'kapasitas' => 20, 'fasilitas' => 'PLC Trainer Kit', 'id_gedung' => 4],
            ['id_ruang' => 25, 'nama_ruang' => 'D 2.2 (Lab Robotika)', 'kapasitas' => 20, 'fasilitas' => 'Robot Arm, Sensor Kit', 'id_gedung' => 4],
            ['id_ruang' => 26, 'nama_ruang' => 'A 5.1 (Ruang Rapat)', 'kapasitas' => 20, 'fasilitas' => 'AC, Proyektor, Meja Rapat', 'id_gedung' => 1],
            ['id_ruang' => 27, 'nama_ruang' => 'A 1.1', 'kapasitas' => 40, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 1],
            ['id_ruang' => 28, 'nama_ruang' => 'A 1.2', 'kapasitas' => 40, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 1],
            ['id_ruang' => 29, 'nama_ruang' => 'B 1.2', 'kapasitas' => 35, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 2],
            ['id_ruang' => 30, 'nama_ruang' => 'C 4.2', 'kapasitas' => 35, 'fasilitas' => 'AC, Proyektor, Papan Tulis', 'id_gedung' => 3],
        ]);
    }
}
