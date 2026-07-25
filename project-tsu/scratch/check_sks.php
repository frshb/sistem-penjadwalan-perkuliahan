<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count5 = App\Models\Kelas::where('id_prodi', 2)->whereHas('matakuliah', function($q) { $q->where('sks', 5); })->count();
$count4 = App\Models\Kelas::where('id_prodi', 2)->whereHas('matakuliah', function($q) { $q->where('sks', 4); })->count();

echo "5 SKS classes: " . $count5 . "\n";
echo "4 SKS classes: " . $count4 . "\n";
