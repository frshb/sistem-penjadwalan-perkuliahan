<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$trials = \App\Models\JadwalTrial::all();
foreach ($trials as $t) {
    echo "Trial {$t->id} - {$t->label}: Dosen Conflicts: {$t->dosen_conflicts}, Ruangan Conflicts: {$t->ruangan_conflicts}\n";
}
