<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$jadwals = \App\Models\JadwalTrial::all();
$conflicts = [];
$roomSlotMap = [];
$dosenSlotMap = [];
$kelasSlotMap = [];

foreach ($jadwals as $j) {
    $trialUuid = $j->uuid;

    for ($i = 0; $i < $j->durasi_sks; $i++) {
        $currentSlot = $j->id_slot_mulai + $i;
        
        $keyRoom = $trialUuid . '-' . $j->id_ruang . '-' . $j->id_hari . '-' . $currentSlot . '-' . $j->id_tahunakademik;
        if (isset($roomSlotMap[$keyRoom])) {
            $conflicts[] = "Room Conflict Trial {$trialUuid}: Ruang {$j->id_ruang} Hari {$j->id_hari} Slot {$currentSlot} (Jadwal 1: {$roomSlotMap[$keyRoom]}, Jadwal 2: {$j->id})";
        }
        $roomSlotMap[$keyRoom] = $j->id;

        if ($j->id_dosen) {
            $keyDosen = $trialUuid . '-' . $j->id_dosen . '-' . $j->id_hari . '-' . $currentSlot . '-' . $j->id_tahunakademik;
            if (isset($dosenSlotMap[$keyDosen])) {
                $conflicts[] = "Dosen Conflict Trial {$trialUuid}: Dosen {$j->id_dosen} Hari {$j->id_hari} Slot {$currentSlot} (Jadwal 1: {$dosenSlotMap[$keyDosen]}, Jadwal 2: {$j->id})";
            }
            $dosenSlotMap[$keyDosen] = $j->id;
        }

        if ($j->kelas_id) { // using kelas_id for trial? Wait, check JadwalTrial schema
            $keyKelas = $trialUuid . '-' . $j->kelas_id . '-' . $j->id_hari . '-' . $currentSlot . '-' . $j->id_tahunakademik;
            if (isset($kelasSlotMap[$keyKelas])) {
                $conflicts[] = "Kelas Conflict Trial {$trialUuid}: Kelas {$j->kelas_id} Hari {$j->id_hari} Slot {$currentSlot} (Jadwal 1: {$kelasSlotMap[$keyKelas]}, Jadwal 2: {$j->id})";
            }
            $kelasSlotMap[$keyKelas] = $j->id;
        }
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
