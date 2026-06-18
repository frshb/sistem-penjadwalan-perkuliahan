<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KpSkripsiController;
use App\Http\Controllers\JadwalExportController;
use App\Http\Controllers\JadwalOtomatisController;
use App\Http\Controllers\PortalDosenPengampuController;

use App\Http\Controllers\DashboardController;

use App\Http\Controllers\AuthController;


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

    // KP & Skripsi
    Route::middleware(['permission:management_data,Management KP & Skripsi'])->group(function () {
        Route::get('/management/kp-skripsi', [App\Http\Controllers\KpSkripsiController::class, 'index'])->name('management.kpskripsi.index');
    });

    // Management Prodi
    Route::middleware(['permission:management_data,Management Prodi'])->group(function () {
        Route::get('/management/prodi/export-excel', [ProdiController::class, 'exportExcel'])->name('prodi.export.excel');
        Route::get('/management/prodi/export-pdf', [ProdiController::class, 'exportPdf'])->name('prodi.export.pdf');
        Route::get('/management/prodi', [ProdiController::class, 'index'])->name('prodi.index');
        Route::get('/management/prodi/{id}', [ProdiController::class, 'show'])->name('prodi.show');
        Route::post('/management/prodi', [ProdiController::class, 'store'])->name('prodi.store');
        Route::delete('/management/prodi/{prodi}', [ProdiController::class, 'destroy'])->name('prodi.destroy');
        Route::put('/management/prodi/{id}', [ProdiController::class, 'update'])->name('prodi.update');
    });

    // Management Ruangan
    Route::middleware(['permission:management_data,Management Ruangan'])->group(function () {
        Route::get('/management/ruangan/export-excel', [RuanganController::class, 'exportExcel'])->name('ruangan.export.excel');
        Route::get('/management/ruangan/export-pdf', [RuanganController::class, 'exportPdf'])->name('ruangan.export.pdf');
        Route::get('/management/ruangan', [RuanganController::class, 'index'])->name('ruangan.index');
        Route::resource('/management/ruangan', RuanganController::class)
            ->except(['show', 'index'])
            ->names('ruangan');
    });

    // Management Dosen
    Route::middleware(['permission:management_data,Management Data Dosen'])->group(function () {
        Route::get('/management/dosen', [DosenController::class, 'index'])->name('dosen.index');
        Route::get('/management/dosen/export-excel', [DosenController::class, 'exportExcel'])->name('dosen.export.excel');
        Route::get('/management/dosen/export-pdf', [DosenController::class, 'exportPdf'])->name('dosen.export.pdf');
        Route::post('/management/dosen', [DosenController::class, 'store'])->name('dosen.store');
        Route::put('/management/dosen/{dosen:nuptk}', [DosenController::class, 'update'])->name('dosen.update');
        Route::delete('/management/dosen/{dosen:nuptk}', [DosenController::class, 'destroy'])->name('dosen.destroy');
    });

    // Management Mata Kuliah
    Route::middleware(['permission:management_data,Management Mata Kuliah'])->group(function () {
        Route::get('/management/matakuliah', [MataKuliahController::class, 'index'])->name('matakuliah.index');
        Route::post('/management/matakuliah', [MataKuliahController::class, 'store'])->name('matakuliah.store');
        Route::put('/management/matakuliah/{kode_matkul}', [MataKuliahController::class, 'update'])->name('matakuliah.update');
        Route::delete('/management/matakuliah/{matakuliah}', [MataKuliahController::class, 'destroy'])->name('matakuliah.destroy');
        Route::get('/management/matakuliah/export-excel', [MataKuliahController::class, 'exportExcel'])->name('matakuliah.export.excel');
        Route::get('/management/matakuliah/export-pdf', [MataKuliahController::class, 'exportPDF'])->name('matakuliah.export.pdf');
    });

    // Management Mahasiswa
    Route::middleware(['permission:management_data,Management Data Mahasiswa'])->group(function () {
        Route::get('/management/mahasiswa/export-excel', [MahasiswaController::class, 'exportExcel'])->name('mahasiswa.export.excel');
        Route::get('/management/mahasiswa/export-pdf', [MahasiswaController::class, 'exportPdf'])->name('mahasiswa.export.pdf');
        Route::get('/management/mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
    });

    // Management Kelas
    Route::middleware(['permission:management_data,Management Kelas'])->group(function () {
        Route::get('/management/kelas/pilih-tahun', [KelasController::class, 'pilihTahun'])->name('kelas.pilih-tahun');
        Route::get('/management/kelas', [KelasController::class, 'index'])->name('kelas.index');
        Route::get('/management/kelas/export/excel', [KelasController::class, 'exportExcel'])->name('kelas.export.excel');
        Route::get('/management/kelas/export/pdf', [KelasController::class, 'exportPdf'])->name('kelas.export.pdf');
        Route::resource('/management/kelas', KelasController::class)->except(['show', 'index']);
        Route::post('/management/kelas/generate', [KelasController::class, 'generate'])->name('kelas.generate');
        Route::post('/management/kelas/bulk-delete', [KelasController::class, 'bulkDelete'])->name('kelas.bulk-delete');
        Route::post('/kelas/tahun-akademik', [KelasController::class, 'storeTahunAkademik'])->name('kelas.tahun-akademik.store');
        Route::delete('/kelas/tahun-akademik/{id}', [KelasController::class, 'destroyTahunAkademik'])->name('kelas.tahun-akademik.destroy');
        Route::put('/kelas/tahun-akademik/{id}', [KelasController::class, 'updateTahunAkademik'])->name('kelas.tahun-akademik.update');
    });

    // Portal Dosen Pengampu
    Route::middleware(['permission:management_data,Management Kelas'])->group(function () {
        Route::get('/dosen-pengampu/pilih-tahun', [PortalDosenPengampuController::class, 'pilihTahun'])->name('dosen-pengampu.pilih-tahun');
        Route::get('/dosen-pengampu', [PortalDosenPengampuController::class, 'index'])->name('dosen-pengampu.index');
        Route::post('/dosen-pengampu/simpan', [PortalDosenPengampuController::class, 'simpan'])->name('dosen-pengampu.simpan');
        Route::post('/dosen-pengampu/hapus', [PortalDosenPengampuController::class, 'hapus'])->name('dosen-pengampu.hapus');
    });

    // Modul Penjadwalan - Manual
    Route::middleware(['permission:modul_penjadwalan,Penjadwalan Manual'])->group(function () {
        Route::get('/modul-penjadwalan',[JadwalController::class, 'index'])->name('jadwal.index');
        Route::get('/penjadwalan/pilih-tahun',[JadwalController::class, 'pilihTahun'])->name('jadwal.pilih-tahun');
        Route::get('/penjadwalan/manual',[JadwalController::class, 'manual'])->name('jadwal.manual');
        Route::post('/jadwal/simpan-slot', [JadwalController::class, 'simpanSlot'])->name('jadwal.simpan-slot');
        Route::delete('/jadwal/hapus-slot/{id}', [JadwalController::class, 'hapusSlot'])->name('jadwal.hapus-slot');
        Route::post('/modul-penjadwalan/generate-ga', [JadwalController::class, 'generateGA'])->name('jadwal.generate_ga');
        Route::get('/jadwal/{tahunAkademikId}/export/excel', [JadwalExportController::class, 'exportExcel'])->name('jadwal.export.excel');
        Route::get('/jadwal/{tahunAkademikId}/export/pdf',   [JadwalExportController::class, 'exportPdf'])->name('jadwal.export.pdf');
    });

    // Modul Penjadwalan - Otomatis
    Route::middleware(['permission:modul_penjadwalan,Penjadwalan Otomatis'])->group(function () {
        Route::get('/jadwal-otomatis',         [JadwalOtomatisController::class, 'index'])->name('jadwal.otomatis.index');
        Route::post('/jadwal-otomatis/proses', [JadwalOtomatisController::class, 'proses'])->name('jadwal.otomatis.proses');
        Route::post('/jadwal-otomatis/simpan', [JadwalOtomatisController::class, 'simpan'])->name('jadwal.otomatis.simpan');
        Route::get('/jadwal-otomatis/stream', [JadwalOtomatisController::class, 'stream'])->name('jadwal.otomatis.stream');
    });

});

