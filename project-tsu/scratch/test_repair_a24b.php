<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TahunAkademik;
use App\Models\Kelas;
use App\Models\Slot_waktu;
use App\Services\GeneticAlgorithm\GeneticScheduler;
use App\Services\GeneticAlgorithm\Gene;

$activeTA = TahunAkademik::where('status_aktif', 1)->first();
$tahunAkademikId = $activeTA->id_tahunakademik;
$prodiId = 2; // S1 Sistem Informasi

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

$slots = Slot_waktu::orderBy('id_slot')->get()->map(fn($s) => [
    'id'           => $s->id_slot,
    'waktu_mulai'  => $s->waktu_mulai,
    'waktu_selesai'=> $s->waktu_selesai,
])->all();
$hariList = \App\Models\Hari::where('is_active', true)->pluck('id_hari')->toArray();

$scheduler = new GeneticScheduler();

$reflection = new ReflectionClass(GeneticScheduler::class);
$validateInputData = $reflection->getMethod('validateInputData');
$validateInputData->setAccessible(true);
$validateInputData->invoke($scheduler, $kelas, $slots, $hariList);

$prepareData = $reflection->getMethod('prepareData');
$prepareData->setAccessible(true);
$prepareData->invoke($scheduler, $kelas, $slots, $hariList);

$otherProdiQuery = \App\Models\Jadwal::with(['kelas.pengampuKelas', 'dosen'])
    ->where('id_tahunakademik', $tahunAkademikId)
    ->whereHas('kelas', fn($q) => $q->where('id_prodi', '!=', $prodiId));
$otherJadwals = $otherProdiQuery->get();
$scheduler->setOccupiedJadwals($otherJadwals);

$targetKelas = $kelas->where('nama_kelas', 'A2-4B')->first();
if (!$targetKelas) die("Class A2-4B not found!\n");
$info = $reflection->getProperty('kelasData')->getValue($scheduler)[$targetKelas->id_kelas];

$gene = new Gene(
    $targetKelas->id_kelas, // kelasId
    2, // HariId
    10, // SlotMulai
    $info['sks'], // durasi
    16, // ruangId
    $info['id_dosen'], // dosenId
    $info['kapasitas'], // kapasitasKelas
    $info['jenis'], // jenisMatkul
    $info['kapasitas'], // kapasitasRuang (mock)
    $targetKelas->nama_kelas, // namaKelas
    14, // maxSlot
    Gene::TIPE_REGULER, // tipeRuangan
    $info['kategori'], // kategoriMatkul
    $info['kode_matkul'], // namaMatkul (mock)
    'Room 16' // namaRuang
);

$repairGene = $reflection->getMethod('repairGene');
$repairGene->setAccessible(true);
$others = [$gene];
$selfIdx = 0;

echo "Before Repair: Hari {$gene->hariId}, Slot {$gene->slotMulai}, Ruang {$gene->ruangId}\n";

$dosenIdxProp = $reflection->getProperty('lockedDosenIndex');
$dosenIdxProp->setAccessible(true);
$lockedDosen = $dosenIdxProp->getValue($scheduler);
$ruangIdxProp = $reflection->getProperty('lockedRuangIndex');
$ruangIdxProp->setAccessible(true);
$lockedRuang = $ruangIdxProp->getValue($scheduler);

echo "Is Room 16 occupied at Day 2 Slot 10? " . (isset($lockedRuang[16][2][10]) ? "YES" : "NO") . "\n";
echo "Is Dosen " . $info['id_dosen'] . " occupied at Day 2 Slot 10? " . (isset($lockedDosen[$info['id_dosen']][2][10]) ? "YES" : "NO") . "\n";

$result = $repairGene->invoke($scheduler, $gene, $others, $selfIdx, false);
echo "Repair Success: " . ($result ? 'Yes' : 'No') . "\n";
echo "After Repair: Hari {$gene->hariId}, Slot {$gene->slotMulai}, Ruang {$gene->ruangId}\n";
