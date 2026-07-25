<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$idTahunAkademik = 2;
$prodiInformatika = \App\Models\Prodi::where('nama_prodi', 'LIKE', '%Informatika%')->first();
$prodiId = $prodiInformatika->id_prodi;

echo "Prodi ID: $prodiId\n";

$kelas = \App\Models\Kelas::where('id_prodi', $prodiId)
    ->where('id_tahunakademik', $idTahunAkademik)
    ->get();

echo "Kelas count in TA $idTahunAkademik: " . $kelas->count() . "\n";

$allKelas = \App\Models\Kelas::where('id_prodi', $prodiId)->get();
echo "Kelas count total for prodi: " . $allKelas->count() . "\n";

$jadwalsInf = \App\Models\Jadwal::where('id_tahunakademik', $idTahunAkademik)
    ->whereHas('kelas', fn($q) => $q->where('id_prodi', $prodiId))
    ->get();
echo "Existing Jadwal count for Informatika in TA $idTahunAkademik: " . $jadwalsInf->count() . "\n";
