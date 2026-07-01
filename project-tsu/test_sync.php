<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$mks = App\Models\MataKuliah::where('nama_matkul', 'LIKE', '%Agama%')->get();
foreach($mks as $mk) {
    echo "ID: " . $mk->id_matakuliah . " | Kode: " . $mk->kode_matkul . " | Nama: " . $mk->nama_matkul . "\n";
}
