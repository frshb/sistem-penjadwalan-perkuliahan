<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = \Illuminate\Http\Request::create('/jadwal-otomatis/audit', 'GET', [
    'tahun_akademik_id' => 6 // Gasal 2026/2027
]);

$controller = $app->make(\App\Http\Controllers\JadwalOtomatisController::class);
$response = $controller->audit($request);

echo $response->getContent();
