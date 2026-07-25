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
use App\Services\GeneticAlgorithm\Chromosome;

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

// We need to use Reflection to call private methods to simulate repairGene
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

// Create a mock gene for A2-4A
$targetKelas = $kelas->where('nama_kelas', 'A2-4A')->first();
if (!$targetKelas) die("Class A2-4A not found!\n");

$info = $reflection->getProperty('kelasData')->getValue($scheduler)[$targetKelas->id_kelas];

$gene = new Gene(
    $targetKelas->id_kelas,
    'A2-4A',
    $info['kode_matkul'],
    $info['sks'],
    1, // Hari 1
    7, // Slot 7
    17, // Room 17
    0, // kap
    Gene::TIPE_REGULER,
    'Room 17',
    $info['id_dosen'],
    $info['jenis'],
    $info['kategori'],
    false // isKelasS
);

// Call repairGene
$repairGene = $reflection->getMethod('repairGene');
$repairGene->setAccessible(true);
$others = [$gene]; // only one gene
$selfIdx = 0;

echo "Before Repair: Hari {$gene->hariId}, Slot {$gene->slotMulai}, Ruang {$gene->ruangId}\n";

$result = $repairGene->invoke($scheduler, $gene, $others, $selfIdx, false);

echo "Repair Success: " . ($result ? 'Yes' : 'No') . "\n";
echo "After Repair: Hari {$gene->hariId}, Slot {$gene->slotMulai}, Ruang {$gene->ruangId}\n";
