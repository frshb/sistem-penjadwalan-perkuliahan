<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = App\Models\Kelas::whereHas('pengampuKelas', function($q) {
    $q->where('id_dosen', 13);
})->where('id_prodi', 2)->count();

echo "Yustina's classes in S1 Informatika: " . $count . "\n";
