<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$kelas = \App\Models\Kelas::where('nama_kelas', 'A2-4A')->first();
if ($kelas) {
    echo "Kelas A2-4A (ID {$kelas->id_kelas})\n";
    $mk = $kelas->matakuliah;
    if ($mk) {
        echo "Matakuliah: {$mk->nama_matkul} ({$mk->sks} SKS)\n";
        echo "Ruangan Options:\n";
        foreach ($mk->ruangans as $r) {
            echo "- Ruang {$r->id_ruang}: {$r->nama_ruang} (Kapasitas: {$r->kapasitas})\n";
        }
    }
}
