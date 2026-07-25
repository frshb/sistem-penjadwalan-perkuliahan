<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$latestTrial = \App\Models\JadwalTrial::orderBy('id', 'desc')->first();
if (!$latestTrial) {
    die("No trial found.\n");
}

$trialJadwals = json_decode($latestTrial->jadwal_json, true);

$dbJadwals = \App\Models\Jadwal::where('id_tahunakademik', $latestTrial->id_tahunakademik)->get();

$roomSlotMap = [];
$dosenSlotMap = [];

// Populate DB jadwals first
foreach ($dbJadwals as $j) {
    // skip if it's the same prodi? Wait, we want to see if the trial conflicts with existing DB.
    // The trial is for a specific prodi. If it overrides the DB jadwals of its own prodi, we shouldn't check against them.
    // Actually, let's just check conflicts within the trial itself first!
    for ($i = 0; $i < $j->durasi_sks; $i++) {
        $currentSlot = $j->id_slot_mulai + $i;
        
        $keyRoom = $j->id_ruang . '-' . $j->id_hari . '-' . $currentSlot;
        $roomSlotMap[$keyRoom] = 'DB-Jadwal-' . $j->id_jadwal;

        if ($j->id_dosen) {
            $keyDosen = $j->id_dosen . '-' . $j->id_hari . '-' . $currentSlot;
            $dosenSlotMap[$keyDosen] = 'DB-Jadwal-' . $j->id_jadwal;
        }
    }
}

$conflicts = [];
// Now check Trial Jadwals
foreach ($trialJadwals as $tj) {
    for ($i = 0; $i < $tj['sks']; $i++) {
        $currentSlot = $tj['slot_id'] + $i;
        
        if (!empty($tj['ruangan_id'])) {
            $keyRoom = $tj['ruangan_id'] . '-' . $tj['hari_id'] . '-' . $currentSlot;
            if (isset($roomSlotMap[$keyRoom])) {
                $conflicts[] = "Room Conflict: Trial Class {$tj['nama_kelas']} Room {$tj['ruangan_id']} Hari {$tj['hari_id']} Slot {$currentSlot} conflicts with {$roomSlotMap[$keyRoom]}";
            }
            $roomSlotMap[$keyRoom] = 'Trial-Class-' . $tj['nama_kelas'];
        }

        if (!empty($tj['dosen_id'])) {
            $keyDosen = $tj['dosen_id'] . '-' . $tj['hari_id'] . '-' . $currentSlot;
            if (isset($dosenSlotMap[$keyDosen])) {
                $conflicts[] = "Dosen Conflict: Trial Class {$tj['nama_kelas']} Dosen {$tj['dosen_id']} Hari {$tj['hari_id']} Slot {$currentSlot} conflicts with {$dosenSlotMap[$keyDosen]}";
            }
            $dosenSlotMap[$keyDosen] = 'Trial-Class-' . $tj['nama_kelas'];
        }
    }
}

if (count($conflicts) > 0) {
    echo "Found " . count($conflicts) . " conflicts in latest trial (ID {$latestTrial->id}):\n";
    foreach ($conflicts as $c) {
        echo $c . "\n";
    }
} else {
    echo "No conflicts found in latest trial.\n";
}
