<?php
$lines = file('c:\laragon\www\tsu\sistem-penjadwalan-perkuliahan\project-tsu\app\Http\Controllers\JadwalValidasiController.php');
foreach ($lines as $i => $line) {
    if (strpos($line, 'public function timeline') !== false) {
        echo "Line " . ($i+1) . ": " . trim($line) . "\n";
    }
}
