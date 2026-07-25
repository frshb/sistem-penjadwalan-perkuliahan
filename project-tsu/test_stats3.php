<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ta = \App\Models\TahunAkademik::find(6);
$kelas = \App\Models\Kelas::with('matakuliah.ruangans')->where('id_tahunakademik', $ta->id_tahunakademik)->get();

$roomDemand = [];
$courseRoomCount = [];

foreach ($kelas as $k) {
    $mk = $k->matakuliah;
    if (!$mk) continue;
    $sks = (int)$mk->sks;
    $ruangans = $mk->ruangans;
    
    $courseName = $mk->nama_matkul;
    $courseRoomCount[$courseName] = $ruangans->count();
    
    if ($ruangans->count() > 0) {
        foreach($ruangans as $r) {
            $roomDemand[$r->nama_ruang] = ($roomDemand[$r->nama_ruang] ?? 0) + $sks;
        }
    } else {
        $roomDemand['NO_SPECIFIC_ROOM'] = ($roomDemand['NO_SPECIFIC_ROOM'] ?? 0) + $sks;
    }
}

echo "Courses with highly restricted rooms (<= 1 room):\n";
foreach($courseRoomCount as $course => $count) {
    if ($count <= 1) {
        echo "- $course: $count rooms allowed\n";
    }
}

arsort($roomDemand);
echo "\nRoom Demand (SKS):\n";
foreach($roomDemand as $rName => $demand) {
    echo "- $rName: $demand SKS\n";
}

