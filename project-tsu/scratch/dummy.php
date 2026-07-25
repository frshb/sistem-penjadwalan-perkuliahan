<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\GeneticAlgorithm\GeneticScheduler;

$scheduler = new GeneticScheduler();

// Wait, simulating the full GA again is easier. I will just modify GeneticScheduler locally to log failures of repairGene!
