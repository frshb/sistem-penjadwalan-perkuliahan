<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\DosenController;



Route::get('/', function () {
    return view('welcome');
});


Route::get('/management/prodi', [ProdiController::class, 'index'])->name('prodi.index');
Route::post('/management/prodi', [ProdiController::class, 'store'])->name('prodi.store');
Route::get('/management/ruangan', [RuanganController::class, 'index'])->name('ruangan.index');
Route::get('/management/dosen', [DosenController::class, 'index'])->name('dosen.index');
Route::post('/management/dosen', [DosenController::class, 'store'])->name('dosen.store');
