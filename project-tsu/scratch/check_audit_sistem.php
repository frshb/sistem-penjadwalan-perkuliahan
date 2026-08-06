<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Jadwal;
use App\Models\MataKuliah;
use App\Models\Kelas;
use App\Models\Ruangan;

$mk = MataKuliah::where('nama_matkul', 'like', '%Audit Sistem Informasi%')->first();
if (!$mk) {
    echo "MK Audit Sistem Informasi not found.\n";
    exit;
}
echo "MK: " . $mk->nama_matkul . " (ID: " . $mk->id_matakuliah . ")\n";

$ruangans = $mk->ruangans;
echo "Pivot Ruangans:\n";
foreach ($ruangans as $r) {
    echo "- " . $r->nama_ruang . " (ID: " . $r->id_ruang . ", Kapasitas: " . $r->kapasitas . ")\n";
}

$kelas = Kelas::where('id_matakuliah', $mk->id_matakuliah)->get();
echo "\nKelas:\n";
foreach ($kelas as $k) {
    echo "- " . $k->nama_kelas . " (ID: " . $k->id_kelas . ", Kapasitas: " . $k->kapasitas . ")\n";
}

$jadwals = Jadwal::whereIn('id_kelas', $kelas->pluck('id_kelas'))->get();
echo "\nJadwals:\n";
foreach ($jadwals as $j) {
    $r = Ruangan::find($j->id_ruang);
    echo "- Jadwal ID: " . $j->id_jadwal . ", Hari: " . $j->id_hari . ", Slot: " . $j->id_slot_mulai . ", Ruangan: " . ($r ? $r->nama_ruang : 'NONE') . "\n";
}
