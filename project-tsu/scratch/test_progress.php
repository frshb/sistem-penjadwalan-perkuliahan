<?php
require 'c:\laragon\www\tsu\sistem-penjadwalan-perkuliahan\project-tsu\vendor\autoload.php';
$app = require_once 'c:\laragon\www\tsu\sistem-penjadwalan-perkuliahan\project-tsu\bootstrap\app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/penjadwalan/2/progress', 'GET')
);
echo $response->getContent();
$kernel->terminate($request, $response);
