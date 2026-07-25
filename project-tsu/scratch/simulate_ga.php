<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TahunAkademik;
use App\Models\Kelas;
use App\Models\Slot_waktu;
use App\Services\GeneticAlgorithm\GeneticScheduler;

// Simulate JadwalOtomatisController::proses for Prodi 2 (or a specific prodi)
$tahunAkademikId = 2; // Assume 2 is the active one, let's find the active one
$activeTA = TahunAkademik::where('status_aktif', 1)->first();
$tahunAkademikId = $activeTA->id_tahunakademik;

$prodiId = 2; // Let's simulate for Prodi 2 (S1 Sistem Informasi)

$pengampuKelasQuery = \App\Models\PengampuKelas::with([
        'kelas.matakuliah.ruangans',
        'kelas.prodi',
        'dosen',
    ])
    ->where('id_tahunakademik', $tahunAkademikId);

$pengampuKelasQuery->whereHas('kelas', function ($q) use ($prodiId) {
    $q->where('id_prodi', $prodiId);
});

$pengampus = $pengampuKelasQuery->get();

$kelas = $pengampus->map(function ($pk) {
    $k = $pk->kelas;
    if ($k) {
        $k->setRelation('pengampus', collect([$pk]));
        $k->setRelation('pengampuKelas', collect([$pk]));
    }
    return $k;
})->filter()->values();

if ($kelas->isEmpty()) {
    die("No classes found for Prodi {$prodiId}\n");
}

$slots = Slot_waktu::orderBy('id_slot')->get()->map(fn($s) => [
    'id'           => $s->id_slot,
    'waktu_mulai'  => $s->waktu_mulai,
    'waktu_selesai'=> $s->waktu_selesai,
])->all();

$hariList = \App\Models\Hari::where('is_active', true)->pluck('id_hari')->toArray();

$scheduler = new GeneticScheduler(
    populationSize: 20,
    maxGenerations: 20,
    crossoverRate: 0.8,
    mutationRate: 0.1,
);
$scheduler->setTimeLimit(60);

$otherProdiQuery = \App\Models\Jadwal::with(['kelas.pengampuKelas', 'dosen'])
    ->where('id_tahunakademik', $tahunAkademikId)
    ->whereHas('kelas', fn($q) => $q->where('id_prodi', '!=', $prodiId));

$otherJadwals = $otherProdiQuery->get();
echo "Found " . count($otherJadwals) . " occupied jadwals from OTHER prodis.\n";

$scheduler->setOccupiedJadwals($otherJadwals);

echo "Running GA for Prodi {$prodiId}...\n";
$hasil = $scheduler->run($kelas, $slots, $hariList);

echo "Fitness: {$hasil['fitness_pct']}%\n";
echo "Dosen Conflicts: {$hasil['dosen_conflicts']}\n";
echo "Ruangan Conflicts: {$hasil['ruangan_conflicts']}\n";

// Let's manually check for conflicts between the output genes and the $otherJadwals!
$conflicts = [];
$roomSlotMap = [];
$dosenSlotMap = [];

foreach ($otherJadwals as $oj) {
    for ($i = 0; $i < $oj->durasi_sks; $i++) {
        $s = $oj->id_slot_mulai + $i;
        $roomSlotMap[$oj->id_ruang . '-' . $oj->id_hari . '-' . $s] = "DB-Jadwal-".$oj->id_jadwal;
        if ($oj->id_dosen) {
            $dosenSlotMap[$oj->id_dosen . '-' . $oj->id_hari . '-' . $s] = "DB-Jadwal-".$oj->id_jadwal;
        }
    }
}

foreach ($hasil['genes'] as $gene) {
    for ($i = 0; $i < $gene->durasi; $i++) {
        $s = $gene->slotMulai + $i;
        
        $keyRoom = $gene->ruangId . '-' . $gene->hariId . '-' . $s;
        if (isset($roomSlotMap[$keyRoom])) {
            $conflicts[] = "Room Conflict: Gen Kelas {$gene->namaKelas} uses Room {$gene->ruangId} at Hari {$gene->hariId} Slot {$s}, but it is OCCUPIED by {$roomSlotMap[$keyRoom]}";
        }
        
        if ($gene->dosenId) {
            $keyDosen = $gene->dosenId . '-' . $gene->hariId . '-' . $s;
            if (isset($dosenSlotMap[$keyDosen])) {
                $conflicts[] = "Dosen Conflict: Gen Kelas {$gene->namaKelas} uses Dosen {$gene->dosenId} at Hari {$gene->hariId} Slot {$s}, but it is OCCUPIED by {$dosenSlotMap[$keyDosen]}";
            }
        }
    }
}

if (count($conflicts) > 0) {
    echo "FAILED: The GA produced " . count($conflicts) . " conflicts with other prodis!\n";
    foreach ($conflicts as $c) echo $c . "\n";
} else {
    echo "SUCCESS: No conflicts with other prodis!\n";
}
