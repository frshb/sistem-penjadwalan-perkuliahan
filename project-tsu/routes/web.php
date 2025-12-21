<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\MahasiswaController;

use App\Http\Controllers\KpSkripsiController;


use App\Http\Controllers\DashboardController;

use App\Http\Controllers\AuthController;

Route::get('/management/kp-skripsi', [App\Http\Controllers\KpSkripsiController::class, 'index'])->name('management.kpskripsi.index');
Route::get('/debug-columns', function () {
    return redirect()->route('login');
});

Route::get('/', function () {
    return redirect()->route('login');
});

// Auth Routes
Route::middleware(['guest', 'revalidate'])->group(function () {
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/authenticate', [AuthController::class, 'authenticate'])->name('authenticate');
    
    // Public Register (as requested to be accessible from login)
    Route::get('/users/register', [AuthController::class, 'register'])->name('users.register');
    Route::post('/users/store', [AuthController::class, 'storeUser'])->name('users.store');

    // Forgot Password Routes
    Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.request');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/profile/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Admin Only (Moved register out, but maybe other admin things go here?)
    Route::middleware(['role:admin'])->group(function () {
       // Future Admin Routes
    });

    // Management Routes (Admin, Dekan, Kaprodi)
    // Kaprodi access logic handled inside controllers or via specific query scopes
    Route::middleware(['role:admin,dekan,kaprodi'])->group(function () {
        Route::get('/management/prodi/export-excel', [ProdiController::class, 'exportExcel'])->name('prodi.export.excel'); // Export Route
        Route::get('/management/prodi', [ProdiController::class, 'index'])->name('prodi.index');
    Route::get('/management/prodi/{id}', [ProdiController::class, 'show'])->name('prodi.show');
    Route::post('/management/prodi', [ProdiController::class, 'store'])->name('prodi.store');
        Route::delete('/management/prodi/{prodi}', [ProdiController::class, 'destroy'])->name('prodi.destroy');
        Route::put('/management/prodi/{id}', [ProdiController::class, 'update'])->name('prodi.update');
    });
    
    
    // Additional grouping for other controllers...
    // For now applying broadly, logic inside controllers to filter data.
    
    Route::get('/management/ruangan/export-excel', [RuanganController::class, 'exportExcel'])->name('ruangan.export.excel'); // Export Route prior to resource
    Route::get('/management/ruangan', [RuanganController::class, 'index'])->name('ruangan.index');
    Route::get('/management/dosen', [DosenController::class, 'index'])->name('dosen.index');
    Route::get('/management/dosen/export-excel', [DosenController::class, 'exportExcel'])->name('dosen.export.excel');
    Route::post('/management/dosen', [DosenController::class, 'store'])->name('dosen.store');
    
    Route::get('/management/matakuliah', [MataKuliahController::class, 'index'])->name('matakuliah.index');
    Route::post('/management/matakuliah', [MataKuliahController::class, 'store'])->name('matakuliah.store');
    Route::put('/management/matakuliah/{kode_matkul}', [MataKuliahController::class, 'update'])->name('matakuliah.update');
    Route::delete('/management/matakuliah/{matakuliah}', [MataKuliahController::class, 'destroy'])->name('matakuliah.destroy');
    Route::get('/management/matakuliah/export-excel', [MataKuliahController::class, 'exportExcel'])->name('matakuliah.export.excel');
    
    Route::get('/management/mahasiswa/export-excel', [MahasiswaController::class, 'exportExcel'])->name('mahasiswa.export.excel'); // Export Route
    Route::get('/management/mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
    
    Route::resource('/management/ruangan', RuanganController::class)
        ->except(['show', 'index']) 
        ->names('ruangan');
        
    Route::get('/modul-penjadwalan', [JadwalController::class, 'index'])->name('jadwal.index');
    Route::get('/penjadwalan/manual', [\App\Http\Controllers\ManualJadwalDummyController::class, 'index'])->name('jadwal.manual');
    Route::post('/modul-penjadwalan/generate-ga', [JadwalController::class, 'generateGA'])->name('jadwal.generate_ga');
    Route::post('/modul-penjadwalan/save-manual', [JadwalController::class, 'saveManual'])->name('jadwal.save_manual');

    // Automatic Scheduling Wizard Routes
    Route::get('/penjadwalan-otomatis/step-1', [\App\Http\Controllers\JadwalOtomatisController::class, 'step1'])->name('jadwal.otomatis.step1');
    Route::post('/penjadwalan-otomatis/step-1', [\App\Http\Controllers\JadwalOtomatisController::class, 'storeStep1'])->name('jadwal.otomatis.step1.store');
    Route::get('/penjadwalan-otomatis/step-2', [\App\Http\Controllers\JadwalOtomatisController::class, 'step2'])->name('jadwal.otomatis.step2');
    Route::post('/penjadwalan-otomatis/step-2', [\App\Http\Controllers\JadwalOtomatisController::class, 'storeStep2'])->name('jadwal.otomatis.step2.store');
Route::get('/penjadwalan-otomatis/step-3', [\App\Http\Controllers\JadwalOtomatisController::class, 'step3'])->name('jadwal.otomatis.step3');
Route::post('/penjadwalan-otomatis/step-3', [\App\Http\Controllers\JadwalOtomatisController::class, 'storeStep3'])->name('jadwal.otomatis.step3.store');
Route::get('/penjadwalan-otomatis/step-4', [\App\Http\Controllers\JadwalOtomatisController::class, 'step4'])->name('jadwal.otomatis.step4');

    // Role Management
    Route::get('/settings/roles', [\App\Http\Controllers\RoleManagementController::class, 'index'])->name('settings.roles.index');
    Route::post('/settings/roles', [\App\Http\Controllers\RoleManagementController::class, 'update'])->name('settings.roles.update');

    // Academic Calendar Settings
    Route::get('/settings/academic-calendar', [\App\Http\Controllers\AcademicCalendarController::class, 'index'])->name('settings.academic_calendar.index');
    Route::post('/settings/academic-calendar', [\App\Http\Controllers\AcademicCalendarController::class, 'store'])->name('settings.academic_calendar.store');
    Route::put('/settings/academic-calendar/{id}', [\App\Http\Controllers\AcademicCalendarController::class, 'update'])->name('settings.academic_calendar.update');
    Route::delete('/settings/academic-calendar/{id}', [\App\Http\Controllers\AcademicCalendarController::class, 'destroy'])->name('settings.academic_calendar.destroy');

});
