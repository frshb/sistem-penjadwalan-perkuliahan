<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ta = \App\Models\TahunAkademik::find(6);
if (!$ta) { echo "TA not found\n"; exit; }

$kelas = \App\Models\Kelas::with('matakuliah', 'pengampuKelas')->where('id_tahunakademik', $ta->id_tahunakademik)->get();
echo "Total Kelas: " . $kelas->count() . "\n";

$dosenSks = [];
$labSks = 0; $soreSks = 0; $regSks = 0;
$totalSks = 0;

foreach ($kelas as $k) {
    $mk = $k->matakuliah; $sks = $mk ? (int)$mk->sks : 0;
    $totalSks += $sks;
    $dosenId = $k->pengampuKelas->first()?->id_dosen ?? 0;
    $dosenSks[$dosenId] = ($dosenSks[$dosenId] ?? 0) + $sks;
    if (strtolower(trim($mk->jenis ?? '')) === 'praktikum') { $labSks += $sks; }
    
    $namaKelas = trim($k->nama_kelas);
    $isKelasS = preg_match('/(-S|-S1|-S2|-SI)$/i', $namaKelas) || stripos($namaKelas, 'sore') !== false || stripos($namaKelas, 'malam') !== false;
    if ($isKelasS) { $soreSks += $sks; } else { $regSks += $sks; }
}

echo "Total SKS Keseluruhan: $totalSks\n";
echo "Total SKS Lab: $labSks\n";
echo "Total SKS Sore: $soreSks\n";
echo "Total SKS Reguler: $regSks\n";

arsort($dosenSks);
echo "Top Dosen Beban SKS:\n";
$i=0;
foreach($dosenSks as $did => $sks) {
    $dosen = \App\Models\Dosen::find($did);
    $dname = $dosen ? $dosen->nama_dosen : "ID $did";
    echo "- $dname: $sks SKS\n";
    if(++$i > 5) break;
}

$slotsPerDay = 14; $days = 5; $slotsPerWeek = $slotsPerDay * $days - 1;
$rooms = \App\Models\Ruangan::all();
$labRooms = $rooms->filter(fn($r) => strtolower(trim($r->tipe_ruangan)) === 'lab')->count();
$regRooms = $rooms->filter(fn($r) => strtolower(trim($r->tipe_ruangan)) !== 'lab')->count();
$totalRooms = $rooms->count();

echo "Total Rooms: $totalRooms\n";
echo "Total Lab Rooms: $labRooms (Capacity SKS: " . ($labRooms * $slotsPerWeek) . ")\n";
echo "Total Reg Rooms: $regRooms (Capacity SKS: " . ($regRooms * $slotsPerWeek) . ")\n";

// eveningStartSlot = 13 (meaning slot 13 and 14 are evening) => 2 slots per day
$soreCapacityPerRoom = 2 * 5; // 10 slots per week
$totalSoreCapacity = $soreCapacityPerRoom * $totalRooms;
echo "Total Sore Slots Capacity (All Rooms): $totalSoreCapacity\n";
