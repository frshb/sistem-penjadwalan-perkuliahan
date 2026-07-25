<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$jadwal = \App\Models\Jadwal::with('kelas.prodi')->find(6313);
echo "Jadwal 6313: Kelas " . $jadwal->kelas->nama_kelas . " Prodi " . $jadwal->kelas->id_prodi . " - " . $jadwal->kelas->prodi->nama_prodi . "\n";
