<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\MahasiswaController;

use App\Http\Controllers\KpSkripsiController;
use App\Http\Controllers\JadwalOtomatisController;
use App\Http\Controllers\JadwalManualController;


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

    // Admin Only
    Route::middleware(['role:admin'])->group(function () {
        // Main Settings Page
        Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');

        // Role Management
        Route::get('/settings/roles', [\App\Http\Controllers\RoleManagementController::class, 'index'])->name('settings.roles.index');
        Route::post('/settings/roles', [\App\Http\Controllers\RoleManagementController::class, 'update'])->name('settings.roles.update');

        // Academic Calendar Settings
        Route::get('/settings/academic-calendar', [\App\Http\Controllers\AcademicCalendarController::class, 'index'])->name('settings.academic_calendar.index');
        Route::post('/settings/academic-calendar', [\App\Http\Controllers\AcademicCalendarController::class, 'store'])->name('settings.academic_calendar.store');
        Route::put('/settings/academic-calendar/{id}', [\App\Http\Controllers\AcademicCalendarController::class, 'update'])->name('settings.academic_calendar.update');
        Route::delete('/settings/academic-calendar/{id}', [\App\Http\Controllers\AcademicCalendarController::class, 'destroy'])->name('settings.academic_calendar.destroy');

        // Admin User Registration
        Route::get('/settings/users/register', [\App\Http\Controllers\UserRegistrationController::class, 'create'])->name('settings.users.create');
        Route::post('/settings/users', [\App\Http\Controllers\UserRegistrationController::class, 'store'])->name('settings.users.store');
    });

    // Management Routes (Admin, Dekan, Kaprodi)
    // Kaprodi access logic handled inside controllers or via specific query scopes
    Route::middleware(['role:admin,dekan,kaprodi'])->group(function () {
        Route::get('/management/prodi/export-excel', [ProdiController::class, 'exportExcel'])->name('prodi.export.excel');
        Route::get('/management/prodi/export-pdf', [ProdiController::class, 'exportPdf'])->name('prodi.export.pdf'); // New PDF Export Route
        Route::get('/management/prodi', [ProdiController::class, 'index'])->name('prodi.index');
    Route::get('/management/prodi/{id}', [ProdiController::class, 'show'])->name('prodi.show');
    Route::post('/management/prodi', [ProdiController::class, 'store'])->name('prodi.store');
        Route::delete('/management/prodi/{prodi}', [ProdiController::class, 'destroy'])->name('prodi.destroy');
        Route::put('/management/prodi/{id}', [ProdiController::class, 'update'])->name('prodi.update');
    });


    // Additional grouping for other controllers...
    // For now applying broadly, logic inside controllers to filter data.

    Route::get('/management/ruangan/export-excel', [RuanganController::class, 'exportExcel'])->name('ruangan.export.excel');
    Route::get('/management/ruangan/export-pdf', [RuanganController::class, 'exportPdf'])->name('ruangan.export.pdf'); // New PDF Export Route
    Route::get('/management/ruangan', [RuanganController::class, 'index'])->name('ruangan.index');
    Route::get('/management/dosen', [DosenController::class, 'index'])->name('dosen.index');
    Route::get('/management/dosen/export-excel', [DosenController::class, 'exportExcel'])->name('dosen.export.excel');
    Route::get('/management/dosen/export-pdf', [DosenController::class, 'exportPdf'])->name('dosen.export.pdf'); // New PDF Export Route
    Route::post('/management/dosen', [DosenController::class, 'store'])->name('dosen.store');
    Route::put('/management/dosen/{dosen:nuptk}', [DosenController::class, 'update'])->name('dosen.update');
    Route::delete('/management/dosen/{dosen:nuptk}', [DosenController::class, 'destroy'])->name('dosen.destroy');

    Route::get('/management/matakuliah', [MataKuliahController::class, 'index'])->name('matakuliah.index');
    Route::post('/management/matakuliah', [MataKuliahController::class, 'store'])->name('matakuliah.store');
    Route::put('/management/matakuliah/{kode_matkul}', [MataKuliahController::class, 'update'])->name('matakuliah.update');
    Route::delete('/management/matakuliah/{matakuliah}', [MataKuliahController::class, 'destroy'])->name('matakuliah.destroy');
    Route::get('/management/matakuliah/export-excel', [MataKuliahController::class, 'exportExcel'])->name('matakuliah.export.excel');
    Route::get('/management/matakuliah/export-pdf', [MataKuliahController::class, 'exportPDF'])->name('matakuliah.export.pdf'); // New PDF Export Route

    Route::get('/management/mahasiswa/export-excel', [MahasiswaController::class, 'exportExcel'])->name('mahasiswa.export.excel');
    Route::get('/management/mahasiswa/export-pdf', [MahasiswaController::class, 'exportPdf'])->name('mahasiswa.export.pdf'); // New PDF Export Route
    Route::get('/management/mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa.index');

    Route::resource('/management/ruangan', RuanganController::class)
        ->except(['show', 'index'])
        ->names('ruangan');

    Route::get('/modul-penjadwalan', [JadwalController::class, 'index'])->name('jadwal.index');
    Route::get('/penjadwalan/manual', [\App\Http\Controllers\ManualJadwalDummyController::class, 'index'])->name('jadwal.manual');
    Route::post('/modul-penjadwalan/generate-ga', [JadwalController::class, 'generateGA'])->name('jadwal.generate_ga');

    // Automatic Scheduling Wizard Routes
    Route::prefix('penjadwalan-otomatis')
    ->middleware(['auth'])
    ->name('jadwal.otomatis.')
    ->group(function () {

        // STEP 1 – Pilih Kurikulum & Semester
        Route::get('/step-1', [JadwalOtomatisController::class, 'step1'])
            ->name('step1');

        Route::post('/step-1', [JadwalOtomatisController::class, 'storeStep1'])
            ->name('step1.store');

        // STEP 2 – Pilih Ruangan (by Gedung)
        Route::get('/step-2', [JadwalOtomatisController::class, 'step2'])
            ->name('step2');

        Route::post('/step-2', [JadwalOtomatisController::class, 'storeStep2'])
            ->name('step2.store');

        // STEP 3 – Input Mata Kuliah & Kelas
        Route::get('/step-3', [JadwalOtomatisController::class, 'step3'])
            ->name('step3');

        Route::post('/step-3', [JadwalOtomatisController::class, 'storeStep3'])
            ->name('step3.store');

        // STEP 4 – Hasil Jadwal
        Route::get('/step-4', [JadwalOtomatisController::class, 'step4'])
            ->name('step4');
    });


    Route::get('/penjadwalan', [JadwalController::class, 'index'])->name('penjadwalan.index');
    Route::post('/penjadwalan/proses', [JadwalController::class, 'proses'])->name('penjadwalan.proses'); 


    Route::prefix('penjadwalan-manual')
    ->middleware(['auth'])
    ->name('jadwal.manual.')
    ->group(function () {

        // Halaman utama penjadwalan manual
        Route::get('/', [JadwalManualController::class, 'index'])
            ->name('index');

        // Simpan jadwal manual
        Route::post('/store', [JadwalManualController::class, 'store'])
            ->name('store');

        // Edit jadwal
        Route::get('/{jadwal}/edit', [JadwalManualController::class, 'edit'])
            ->name('edit');

        // Update jadwal
        Route::put('/{jadwal}', [JadwalManualController::class, 'update'])
            ->name('update');

        // Hapus jadwal
        Route::delete('/{jadwal}', [JadwalManualController::class, 'destroy'])
            ->name('destroy');
    });

});
