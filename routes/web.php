<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\MataKuliahController;



Route::get('/', function () {
    return view('welcome');
});


Route::get('/management/prodi', [ProdiController::class, 'index'])->name('prodi.index');
Route::post('/management/prodi', [ProdiController::class, 'store'])->name('prodi.store');
Route::get('/management/ruangan', [RuanganController::class, 'index'])->name('ruangan.index');

Route::get('/management/dosen', [DosenController::class, 'index'])->name('dosen.index');
Route::post('/management/dosen', [DosenController::class, 'store'])->name('dosen.store');

Route::get('/management/matakuliah', [MataKuliahController::class, 'index'])->name('matakuliah.index');
Route::post('/management/matakuliah', [MataKuliahController::class, 'store'])->name('matakuliah.store');
Route::get('/management/matakuliah/export-excel', [MataKuliahController::class, 'exportExcel'])->name('matakuliah.export.excel');

Route::resource('/management/ruangan', RuanganController::class)
    ->except(['show']) // <-- 'show' biasanya tidak perlu untuk tabel manajemen
    ->names('ruangan');
