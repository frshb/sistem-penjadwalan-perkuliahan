<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$jadwals = \App\Models\JadwalTrial::all();
$conflicts = [];
$roomSlotMap = [];
$dosenSlotMap = [];

foreach ($jadwals as $j) {
    for ($i = 0; $i < $j->durasi_sks; $i++) {
        $currentSlot = $j->id_slot_mulai + $i;
        
        // Include trial UUID to isolate checks per trial!
        $trialUuid = $j->uuid ?? 'default';

        $keyRoom = $trialUuid . '-' . $j->id_ruang . '-' . $j->id_hari . '-' . $currentSlot . '-' . $j->id_tahunakademik;
        if (isset($roomSlotMap[$keyRoom])) {
            $conflicts[] = "Room Conflict in Trial {$trialUuid}: Ruang {$j->id_ruang} Hari {$j->id_hari} Slot {$currentSlot} (Jadwal 1: {$roomSlotMap[$keyRoom]}, Jadwal 2: {$j->id_jadwal})";
        }
        $roomSlotMap[$keyRoom] = $j->id_jadwal;

        $keyDosen = $trialUuid . '-' . $j->id_dosen . '-' . $j->id_hari . '-' . $currentSlot . '-' . $j->id_tahunakademik;
        if (isset($dosenSlotMap[$keyDosen])) {
            $conflicts[] = "Dosen Conflict in Trial {$trialUuid}: Dosen {$j->id_dosen} Hari {$j->id_hari} Slot {$currentSlot} (Jadwal 1: {$dosenSlotMap[$keyDosen]}, Jadwal 2: {$j->id_jadwal})";
        }
        $dosenSlotMap[$keyDosen] = $j->id_jadwal;
    }
}

if (count($conflicts) > 0) {
    echo "Found " . count($conflicts) . " conflicts in JadwalTrial:\n";
    foreach ($conflicts as $c) {
        echo $c . "\n";
    }
} else {
    echo "No conflicts found in JadwalTrial.\n";
}
