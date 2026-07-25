<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$idTahunAkademik = 2;
$prodiInformatika = \App\Models\Prodi::where('nama_prodi', 'LIKE', '%Informatika%')->first();
$prodiId = $prodiInformatika->id_prodi;

echo "Running GA for Informatika (Prodi ID: $prodiId, TA: $idTahunAkademik)...\n";

$otherProdiQuery = \App\Models\Jadwal::with(['kelas.pengampuKelas', 'dosen'])
    ->where('id_tahunakademik', $idTahunAkademik)
    ->whereHas('kelas', fn($q) => $q->where('id_prodi', '!=', $prodiId));
$otherJadwals = $otherProdiQuery->get();
echo "Other prodi jadwals locked count: " . $otherJadwals->count() . "\n";

$pengampuKelasQuery = \App\Models\PengampuKelas::with([
    'kelas.matakuliah.ruangans',
    'kelas.prodi',
    'dosen',
])->whereHas('kelas', function($q) use ($prodiId, $idTahunAkademik) {
    $q->where('id_prodi', $prodiId)
      ->where('id_tahunakademik', $idTahunAkademik);
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

echo "Kelas Informatika count to schedule: " . $kelas->count() . "\n";

$slots = \App\Models\Slot_waktu::orderBy('id_slot')
    ->get()
    ->map(fn($s) => [
        'id'           => $s->id_slot,
        'waktu_mulai'  => $s->waktu_mulai,
        'waktu_selesai'=> $s->waktu_selesai,
    ])
    ->all();

$hariList = \App\Models\Hari::where('is_active', true)->pluck('id_hari')->toArray();

$scheduler = new \App\Services\GeneticAlgorithm\GeneticScheduler(populationSize: 50, maxGenerations: 100);
$scheduler->setOccupiedJadwals($otherJadwals);

$hasil = $scheduler->run($kelas, $slots, $hariList);

echo "GA Completed!\n";
echo "Fitness Pct: " . ($hasil['fitness_pct'] ?? 0) . "%\n";
echo "Constraint Violations: " . ($hasil['constraint_violations'] ?? 0) . "\n";
echo "Ruangan Conflicts: " . ($hasil['ruangan_conflicts'] ?? 0) . "\n";
echo "Dosen Conflicts: " . ($hasil['dosen_conflicts'] ?? 0) . "\n";
echo "Kelas Conflicts: " . ($hasil['kelas_conflicts'] ?? 0) . "\n";

$genes = $hasil['genes'] ?? [];
echo "Genes generated count: " . count($genes) . "\n";

// Insert into DB
$inserted = 0;
foreach ($genes as $gene) {
    $kelasId = $gene->kelasId;
    $k = $kelas->firstWhere('id_kelas', $kelasId);
    if (!$k) continue;

    \App\Models\Jadwal::create([
        'id_kelas'         => $kelasId,
        'kode_matkul'      => $k->matakuliah->kode_matkul ?? '',
        'id_dosen'         => $gene->dosenId ?: null,
        'id_slot_mulai'    => $gene->slotMulai,
        'durasi_sks'       => $gene->durasi,
        'id_hari'          => $gene->hariId,
        'id_ruang'         => $gene->ruangId,
        'id_tahunakademik' => $idTahunAkademik,
        'is_manual'        => 0,
    ]);
    $inserted++;
}

echo "Successfully saved $inserted new Informatika schedules to DB.\n";

// Audit DB conflicts
$allJadwals = \App\Models\Jadwal::with(['kelas.matakuliah', 'kelas.pengampuKelas.dosen', 'kelas.prodi', 'dosen', 'ruangan', 'hari', 'slotMulai'])
    ->where('id_tahunakademik', $idTahunAkademik)
    ->get();

$bySlotRoom = [];
$bySlotDosen = [];
$finalBentrok = 0;

foreach ($allJadwals as $j) {
    $h = (int) $j->id_hari;
    $sm = (int) $j->id_slot_mulai;
    $dur = (int) ($j->durasi_sks ?? 1);
    $r = (int) ($j->id_ruang ?? 0);
    $d = (int) ($j->id_dosen ?: ($j->kelas?->pengampuKelas?->first()?->id_dosen ?? 0));
    $namaK = $j->kelas?->nama_kelas ?? '';
    $prodi = $j->kelas?->prodi?->nama_prodi ?? '';

    for ($s = $sm; $s < $sm + $dur; $s++) {
        $keyR = $h . '_' . $s . '_' . $r;
        if ($r > 0) {
            if (isset($bySlotRoom[$keyR])) {
                $finalBentrok++;
                echo " [FINAL AUDIT BENTROK RUANGAN] [$prodi] $namaK VS [{$bySlotRoom[$keyR]['prodi']}] {$bySlotRoom[$keyR]['nama']} at Hari $h Slot $s Ruang $r\n";
            } else {
                $bySlotRoom[$keyR] = ['nama' => $namaK, 'prodi' => $prodi];
            }
        }
        $keyD = $h . '_' . $s . '_' . $d;
        if ($d > 0) {
            if (isset($bySlotDosen[$keyD])) {
                $finalBentrok++;
                echo " [FINAL AUDIT BENTROK DOSEN] Dosen ID $d: [$prodi] $namaK VS [{$bySlotDosen[$keyD]['prodi']}] {$bySlotDosen[$keyD]['nama']} at Hari $h Slot $s\n";
            } else {
                $bySlotDosen[$keyD] = ['nama' => $namaK, 'prodi' => $prodi];
            }
        }
    }
}

echo "=========================================\n";
echo "FINAL AUDIT TOTAL BENTROK IN DB FOR TA $idTahunAkademik: $finalBentrok\n";
echo "=========================================\n";
