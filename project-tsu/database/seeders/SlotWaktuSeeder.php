<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SlotWaktuSeeder extends Seeder
{
    public function run()
    {
        DB::table('slot_waktu')->insert([
            ['id_slot' => 1, 'jam_ke' => 1, 'waktu_mulai' => '08:00:00', 'waktu_selesai' => '08:50:00'],
            ['id_slot' => 2, 'jam_ke' => 2, 'waktu_mulai' => '08:50:00', 'waktu_selesai' => '09:40:00'],
            ['id_slot' => 3, 'jam_ke' => 3, 'waktu_mulai' => '09:40:00', 'waktu_selesai' => '10:30:00'],
            ['id_slot' => 4, 'jam_ke' => 4, 'waktu_mulai' => '10:30:00', 'waktu_selesai' => '11:20:00'],
            ['id_slot' => 5, 'jam_ke' => 5, 'waktu_mulai' => '11:20:00', 'waktu_selesai' => '12:10:00'],
            ['id_slot' => 6, 'jam_ke' => 6, 'waktu_mulai' => '12:10:00', 'waktu_selesai' => '13:00:00'],
            ['id_slot' => 7, 'jam_ke' => 7, 'waktu_mulai' => '13:10:00', 'waktu_selesai' => '14:00:00'],
            ['id_slot' => 8, 'jam_ke' => 8, 'waktu_mulai' => '14:00:00', 'waktu_selesai' => '14:50:00'],
            ['id_slot' => 9, 'jam_ke' => 9, 'waktu_mulai' => '14:50:00', 'waktu_selesai' => '15:40:00'],
            ['id_slot' => 10, 'jam_ke' => 10, 'waktu_mulai' => '15:40:00', 'waktu_selesai' => '16:30:00'],
            ['id_slot' => 11, 'jam_ke' => 11, 'waktu_mulai' => '16:30:00', 'waktu_selesai' => '17:15:00'],
            ['id_slot' => 12, 'jam_ke' => 12, 'waktu_mulai' => '17:20:00', 'waktu_selesai' => '18:00:00'],
            ['id_slot' => 13, 'jam_ke' => 13, 'waktu_mulai' => '18:30:00', 'waktu_selesai' => '19:15:00'],
            ['id_slot' => 14, 'jam_ke' => 14, 'waktu_mulai' => '19:15:00', 'waktu_selesai' => '20:00:00'],
            ['id_slot' => 15, 'jam_ke' => 15, 'waktu_mulai' => '20:00:00', 'waktu_selesai' => '20:45:00'],
            ['id_slot' => 16, 'jam_ke' => 16, 'waktu_mulai' => '20:45:00', 'waktu_selesai' => '21:30:00'],
        ]);
    }
}
