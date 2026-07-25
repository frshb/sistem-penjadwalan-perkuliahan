<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tas = \App\Models\TahunAkademik::where('nama_tahunakademik', 'like', '%2026/2027%')->get();
foreach($tas as $ta) {
    $kelasCount = \App\Models\Kelas::where('id_tahunakademik', $ta->id_tahunakademik)->count();
    echo "TA ID: {$ta->id_tahunakademik}, Name: {$ta->nama_tahunakademik}, Semester: {$ta->semester}, Kelas: $kelasCount\n";
}
