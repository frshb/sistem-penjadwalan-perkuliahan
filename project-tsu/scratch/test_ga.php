<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing GA...\n";
$kelas = App\Models\Kelas::where('id_prodi', 2)->where('id_tahun_akademik', 2)->with(['matakuliah.ruangans', 'pengampuKelas.dosen'])->get();
$slots = App\Models\Slot_waktu::orderBy('jam_ke')->get()->toArray();
$hariList = [1, 2, 3, 4, 5];

// Generate fake occupied schedules to mimic JadwalOtomatisController
$occupied = []; // Or skip it for basic test

$scheduler = new App\Services\GeneticAlgorithm\GeneticScheduler(100, 50);
$result = $scheduler->run($kelas, $slots, $hariList);
echo "Total kelas: " . $result['total_kelas'] . "\n";
echo "Conflicts: " . $result['pelanggaran'] . "\n";
echo "Fitness: " . $result['fitness_pct'] . "\n";
