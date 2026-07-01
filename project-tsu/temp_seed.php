<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
foreach ($hariList as $i => $h) {
    \App\Models\Hari::updateOrCreate(
        ['id_hari' => $i + 1],
        ['nama_hari' => $h, 'is_active' => true]
    );
}

// Map all slots to all days by default
$semuaHari = \App\Models\Hari::all();
$semuaSlot = \App\Models\Slot_waktu::all();
$slotIds = $semuaSlot->pluck('id_slot')->toArray();

foreach ($semuaHari as $h) {
    $h->slotWaktus()->sync($slotIds);
}

echo "Database seeded successfully.\n";
