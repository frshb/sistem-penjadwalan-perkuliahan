<?php
$file = file('c:\laragon\www\tsu\sistem-penjadwalan-perkuliahan\project-tsu\storage\logs\laravel.log');
$lines = array_slice($file, -50);
foreach($lines as $line) echo $line;
