<?php
$lines = file('c:\laragon\www\tsu\sistem-penjadwalan-perkuliahan\project-tsu\resources\views\penjadwalan\penjadwalan-manual.blade.php');
foreach ($lines as $i => $line) {
    if (strpos($line, 'modal-progress-approval') !== false) {
        echo "Line " . ($i+1) . ": " . trim($line) . "\n";
    }
}
