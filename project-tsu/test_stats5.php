<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ta = \App\Models\TahunAkademik::find(6);
$kelas = \App\Models\Kelas::with('matakuliah.ruangans')->where('id_tahunakademik', $ta->id_tahunakademik)->get();

echo "Courses strictly using [C 3.1, C 3.2, C 3.3]:\n";
foreach ($kelas as $k) {
    $mk = $k->matakuliah;
    if (!$mk) continue;
    $ruangans = $mk->ruangans->sortBy('id_ruang')->pluck('nama_ruang')->toArray();
    $key = implode(', ', $ruangans);
    if ($key === 'C 3.1, C 3.2, C 3.3') {
        echo "- Kelas: {$k->nama_kelas}, Matkul: {$mk->nama_matkul} ({$mk->sks} SKS)\n";
    }
}
