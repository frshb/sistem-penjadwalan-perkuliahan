<?php
$file = 'c:\laragon\www\tsu\sistem-penjadwalan-perkuliahan\project-tsu\storage\logs\laravel.log';
$lines = file($file);
$lastLines = array_slice($lines, -50);
echo implode("", $lastLines);
