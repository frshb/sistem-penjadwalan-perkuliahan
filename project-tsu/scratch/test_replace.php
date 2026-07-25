<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$idTahunAkademik = 2;
$prodiInformatika = \App\Models\Prodi::where('nama_prodi', 'LIKE', '%Informatika%')->first();
$prodiId = $prodiInformatika->id_prodi;

$otherJadwals = \App\Models\Jadwal::with(['kelas.pengampuKelas', 'dosen', 'kelas.prodi'])
    ->where('id_tahunakademik', $idTahunAkademik)
    ->whereHas('kelas', fn($q) => $q->where('id_prodi', '!=', $prodiId))
    ->get();

echo "Existing Other Prodi Jadwals count: " . $otherJadwals->count() . "\n";

// Audit conflicts among OTHER PRODI jadwals alone first:
$bySlotRoom = [];
$bySlotDosen = [];
$otherBentrok = 0;

foreach ($otherJadwals as $j) {
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
                $otherBentrok++;
                echo " [OTHER PRODI INTERNAL BENTROK RUANGAN] [$prodi] $namaK VS [{$bySlotRoom[$keyR]['prodi']}] {$bySlotRoom[$keyR]['nama']} at Hari $h Slot $s Ruang $r\n";
            } else {
                $bySlotRoom[$keyR] = ['nama' => $namaK, 'prodi' => $prodi];
            }
        }
        $keyD = $h . '_' . $s . '_' . $d;
        if ($d > 0) {
            if (isset($bySlotDosen[$keyD])) {
                $otherBentrok++;
                echo " [OTHER PRODI INTERNAL BENTROK DOSEN] Dosen ID $d: [$prodi] $namaK VS [{$bySlotDosen[$keyD]['prodi']}] {$bySlotDosen[$keyD]['nama']} at Hari $h Slot $s\n";
            } else {
                $bySlotDosen[$keyD] = ['nama' => $namaK, 'prodi' => $prodi];
            }
        }
    }
}

echo "Total Conflicts existing ONLY within other prodis: $otherBentrok\n";
