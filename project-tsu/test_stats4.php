<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ta = \App\Models\TahunAkademik::find(6);
$kelas = \App\Models\Kelas::with('matakuliah.ruangans')->where('id_tahunakademik', $ta->id_tahunakademik)->get();

$roomSets = [];

foreach ($kelas as $k) {
    $mk = $k->matakuliah;
    if (!$mk) continue;
    $sks = (int)$mk->sks;
    $ruangans = $mk->ruangans->sortBy('id_ruang')->pluck('nama_ruang')->toArray();
    
    if (empty($ruangans)) {
        $key = 'NO_SPECIFIC_ROOM';
    } else {
        $key = implode(', ', $ruangans);
    }
    
    $roomSets[$key]['sks'] = ($roomSets[$key]['sks'] ?? 0) + $sks;
    $roomSets[$key]['num_rooms'] = count($ruangans);
}

$capacityPerRoom = 59; // 12 slots/day * 5 days - 1

echo "Room Bottleneck Analysis:\n";
foreach($roomSets as $key => $data) {
    $capacity = $data['num_rooms'] * $capacityPerRoom;
    $status = $data['sks'] > $capacity ? "BOTTLECK/OVERLOAD" : "OK";
    echo "Rooms [{$key}]:\n";
    echo "  - Total SKS Required: {$data['sks']}\n";
    echo "  - Total Capacity (SKS): $capacity\n";
    echo "  - Status: $status\n\n";
}
