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
use App\Http\Controllers\RoleManagementController;

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

    // Settings Group (Admin & Dekan)
    Route::middleware(['role:admin,dekan'])->group(function () {
        Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
        Route::get('/settings/kurikulum', [\App\Http\Controllers\PortalKurikulumController::class, 'index'])->name('kurikulum.index');
    });

    // Admin Only Settings & Actions
    Route::middleware(['role:admin'])->group(function () {
        // Role Management
        Route::get('/settings/roles', [RoleManagementController::class, 'index'])->name('settings.roles.index');
        Route::post('/settings/roles', [RoleManagementController::class, 'update'])->name('settings.roles.update');

        // Academic Calendar
        Route::get('/settings/academic-calendar', [\App\Http\Controllers\AcademicCalendarController::class, 'index'])->name('settings.academic_calendar.index');
        Route::post('/settings/academic-calendar', [\App\Http\Controllers\AcademicCalendarController::class, 'store'])->name('settings.academic_calendar.store');
        Route::put('/settings/academic-calendar/{id}', [\App\Http\Controllers\AcademicCalendarController::class, 'update'])->name('settings.academic_calendar.update');
        Route::delete('/settings/academic-calendar/{id}', [\App\Http\Controllers\AcademicCalendarController::class, 'destroy'])->name('settings.academic_calendar.destroy');

        // Admin User Registration
        Route::get('/settings/users/register', [\App\Http\Controllers\UserRegistrationController::class, 'create'])->name('settings.users.register');
        Route::post('/settings/users', [\App\Http\Controllers\UserRegistrationController::class, 'store'])->name('settings.users.store');
        Route::get('/settings/users/{id}/edit', [\App\Http\Controllers\UserRegistrationController::class, 'edit'])->name('settings.users.edit');
        Route::put('/settings/users/{id}', [\App\Http\Controllers\UserRegistrationController::class, 'update'])->name('settings.users.update');
        Route::delete('/settings/users/{id}', [\App\Http\Controllers\UserRegistrationController::class, 'destroy'])->name('settings.users.destroy');

        // Kurikulum Write Actions
        Route::post('/settings/kurikulum/store', [\App\Http\Controllers\PortalKurikulumController::class, 'store'])->name('kurikulum.store');
        Route::post('/settings/kurikulum/store-kurikulum', [\App\Http\Controllers\PortalKurikulumController::class, 'storeKurikulum'])->name('kurikulum.storeKurikulum');
        Route::post('/settings/kurikulum/store-tahun-akademik', [\App\Http\Controllers\PortalKurikulumController::class, 'storeTahunAkademik'])->name('kurikulum.storeTahunAkademik');
        Route::post('/settings/kurikulum/toggle-tahun/{id}', [\App\Http\Controllers\PortalKurikulumController::class, 'toggleTahunAkademik'])->name('kurikulum.toggleTahun');
        Route::put('/settings/kurikulum/tahun/{id}', [\App\Http\Controllers\PortalKurikulumController::class, 'updateTahunAkademik'])->name('kurikulum.updateTahun');
        Route::delete('/settings/kurikulum/tahun/{id}', [\App\Http\Controllers\PortalKurikulumController::class, 'destroyTahunAkademik'])->name('kurikulum.destroyTahun');
        Route::put('/settings/kurikulum/master/{id}', [\App\Http\Controllers\PortalKurikulumController::class, 'updateKurikulum'])->name('kurikulum.updateKurikulum');
        Route::delete('/settings/kurikulum/master/{id}', [\App\Http\Controllers\PortalKurikulumController::class, 'destroyKurikulum'])->name('kurikulum.destroyKurikulum');

        // Manajemen Hari & Slot Waktu
        Route::get('/settings/waktu', [\App\Http\Controllers\WaktuController::class, 'index'])->name('settings.waktu.index');
        Route::post('/settings/waktu/hari/{id}/toggle', [\App\Http\Controllers\WaktuController::class, 'toggleHari'])->name('settings.hari.toggle');
        Route::post('/settings/waktu/slot/{id}/toggle', [\App\Http\Controllers\WaktuController::class, 'toggleSlot'])->name('settings.slot.toggle');
        Route::post('/settings/waktu/mapping', [\App\Http\Controllers\WaktuController::class, 'updateMapping'])->name('settings.waktu.mapping');
    });

    // KP & Skripsi
    Route::middleware(['permission:management_data,Management KP & Skripsi'])->group(function () {
        Route::get('/management/kp-skripsi', [App\Http\Controllers\KpSkripsiController::class, 'index'])->name('management.kpskripsi.index');
    });

    // Program Studi
    Route::middleware(['permission:management_data,Program Studi'])->group(function () {
        Route::get('/management/prodi', [ProdiController::class, 'index'])->name('prodi.index');
        Route::get('/management/prodi/export-excel', [ProdiController::class, 'exportExcel'])->name('prodi.export.excel');
        Route::get('/management/prodi/export-pdf', [ProdiController::class, 'exportPdf'])->name('prodi.export.pdf');
        Route::get('/management/prodi/{id}', [ProdiController::class, 'show'])->name('prodi.show');
        Route::post('/management/prodi', [ProdiController::class, 'store'])->name('prodi.store');
        Route::put('/management/prodi/{id}', [ProdiController::class, 'update'])->name('prodi.update');
        Route::delete('/management/prodi/{prodi}', [ProdiController::class, 'destroy'])->name('prodi.destroy');
    });

    // Ruangan
    Route::middleware(['permission:management_data,Ruangan'])->group(function () {
        Route::get('/management/ruangan', [RuanganController::class, 'index'])->name('ruangan.index');
        Route::get('/management/ruangan/export-excel', [RuanganController::class, 'exportExcel'])->name('ruangan.export.excel');
        Route::get('/management/ruangan/export-pdf', [RuanganController::class, 'exportPdf'])->name('ruangan.export.pdf');
        Route::resource('/management/ruangan', RuanganController::class)
            ->except(['show', 'index'])->names('ruangan');
    });

    // Mata Kuliah
    Route::middleware(['permission:management_data,Mata Kuliah'])->group(function () {
        Route::get('/management/matakuliah', [MataKuliahController::class, 'index'])->name('matakuliah.index');
        Route::post('/management/matakuliah', [MataKuliahController::class, 'store'])->name('matakuliah.store');
        Route::put('/management/matakuliah/{matakuliah}', [MataKuliahController::class, 'update'])->name('matakuliah.update');
        Route::delete('/management/matakuliah/{matakuliah}', [MataKuliahController::class, 'destroy'])->name('matakuliah.destroy');
        Route::get('/management/matakuliah/export-excel', [MataKuliahController::class, 'exportExcel'])->name('matakuliah.export.excel');
        Route::get('/management/matakuliah/export-pdf', [MataKuliahController::class, 'exportPDF'])->name('matakuliah.export.pdf');
    });

    // Dosen
    Route::middleware(['permission:management_data,Dosen'])->group(function () {
        Route::get('/management/dosen', [DosenController::class, 'index'])->name('dosen.index');
        Route::get('/management/dosen/export-excel', [DosenController::class, 'exportExcel'])->name('dosen.export.excel');
        Route::get('/management/dosen/export-pdf', [DosenController::class, 'exportPdf'])->name('dosen.export.pdf');
        Route::post('/management/dosen', [DosenController::class, 'store'])->name('dosen.store');
        Route::put('/management/dosen/{dosen:nuptk}', [DosenController::class, 'update'])->name('dosen.update');
        Route::delete('/management/dosen/{dosen:nuptk}', [DosenController::class, 'destroy'])->name('dosen.destroy');
    });

    // Pengampu Kelas
    Route::middleware(['permission:management_data,Pengampu Kelas'])->group(function () {
        Route::get('/dosen-pengampu/pilih-tahun', [PortalDosenPengampuController::class, 'pilihTahun'])->name('dosen-pengampu.pilih-tahun');
        Route::get('/dosen-pengampu', [PortalDosenPengampuController::class, 'index'])->name('dosen-pengampu.index');
        Route::post('/dosen-pengampu/simpan', [PortalDosenPengampuController::class, 'simpan'])->name('dosen-pengampu.simpan');
        Route::post('/dosen-pengampu/hapus', [PortalDosenPengampuController::class, 'hapus'])->name('dosen-pengampu.hapus');
    });


    // Kelas Paralel
    Route::middleware(['permission:management_data,Kelas Paralel'])->group(function () {
        Route::get('/management/kelas/pilih-tahun', [KelasController::class, 'pilihTahun'])->name('kelas.pilih-tahun');
        Route::get('/management/kelas', [KelasController::class, 'index'])->name('kelas.index');
        Route::get('/management/kelas/export/excel', [KelasController::class, 'exportExcel'])->name('kelas.export.excel');
        Route::get('/management/kelas/export/pdf', [KelasController::class, 'exportPdf'])->name('kelas.export.pdf');
        Route::resource('/management/kelas', KelasController::class)->except(['show', 'index']);
        Route::post('/management/kelas/generate', [KelasController::class, 'generate'])->name('kelas.generate');
        Route::post('/management/kelas/bulk-delete', [KelasController::class, 'bulkDelete'])->name('kelas.bulk-delete');
    });

    // Mahasiswa
    Route::middleware(['permission:management_data,Mahasiswa'])->group(function () {
        Route::get('/management/mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
        Route::get('/management/mahasiswa/export-excel', [MahasiswaController::class, 'exportExcel'])->name('mahasiswa.export.excel');
        Route::get('/management/mahasiswa/export-pdf', [MahasiswaController::class, 'exportPdf'])->name('mahasiswa.export.pdf');
    });

    // KP & Skripsi
    Route::middleware(['permission:management_data,KP & Skripsi'])->group(function () {
        Route::get('/management/kp-skripsi', [KpSkripsiController::class, 'index'])->name('management.kpskripsi.index');
    });

    // Generate Jadwal (Otomatis)
    Route::middleware(['permission:modul_penjadwalan,Generate Jadwal'])->group(function () {
        Route::get('/jadwal-otomatis', [JadwalOtomatisController::class, 'index'])->name('jadwal.otomatis.index');
        Route::post('/jadwal-otomatis/proses', [JadwalOtomatisController::class, 'proses'])->name('jadwal.otomatis.proses');
        Route::post('/jadwal-otomatis/simpan', [JadwalOtomatisController::class, 'simpan'])->name('jadwal.otomatis.simpan');
        Route::get('/jadwal-otomatis/stream', [JadwalOtomatisController::class, 'stream'])->name('jadwal.otomatis.stream');
        Route::get('/jadwal-otomatis/audit', [JadwalOtomatisController::class, 'audit'])->name('jadwal.otomatis.audit');

    });

    // Trials & Comparison
    Route::middleware(['permission:modul_penjadwalan,Perbandingan Hasil'])->group(function () {
        Route::post('/jadwal-otomatis/trial/simpan', [JadwalOtomatisController::class, 'simpanTrial'])->name('jadwal.otomatis.simpan_trial');
        Route::get('/jadwal-otomatis/trial/perbandingan', [JadwalOtomatisController::class, 'compareTrials'])->name('jadwal.otomatis.compare_trials');
        Route::post('/jadwal-otomatis/trial/{id}/apply', [JadwalOtomatisController::class, 'applyTrial'])->name('jadwal.otomatis.apply_trial');
        Route::delete('/jadwal-otomatis/trial/{id}', [JadwalOtomatisController::class, 'deleteTrial'])->name('jadwal.otomatis.delete_trial');
        Route::post('/jadwal-otomatis/trial/bulk-delete', [JadwalOtomatisController::class, 'deleteMultipleTrials'])->name('jadwal.otomatis.delete_multiple_trials');
    });

    // Penyesuaian Jadwal (Manual)
    Route::middleware(['permission:modul_penjadwalan,Penyesuaian Jadwal'])->group(function () {
        Route::get('/modul-penjadwalan', [JadwalController::class, 'index'])->name('jadwal.index');
        Route::get('/penjadwalan/pilih-tahun', [JadwalController::class, 'pilihTahun'])->name('jadwal.pilih-tahun');
        Route::get('/penjadwalan/manual', [JadwalController::class, 'manual'])->name('jadwal.manual');
        Route::post('/jadwal/simpan-slot', [JadwalController::class, 'simpanSlot'])->name('jadwal.simpan-slot');
        Route::delete('/jadwal/hapus-slot/{id}', [JadwalController::class, 'hapusSlot'])->name('jadwal.hapus-slot');
        Route::post('/jadwal/hapus-semua', [JadwalController::class, 'hapusSemua'])->name('jadwal.hapus-semua');
        Route::post('/jadwal/optimasi', [JadwalController::class, 'optimasi'])->name('jadwal.optimasi');
        Route::get('/jadwal/status-bentrok', [JadwalController::class, 'statusBentrok'])->name('jadwal.status-bentrok');
        Route::get('/jadwal/{tahunAkademikId}/export/excel', [JadwalExportController::class, 'exportExcel'])->name('jadwal.export.excel');
        Route::get('/jadwal/{tahunAkademikId}/export/pdf', [JadwalExportController::class, 'exportPdf'])->name('jadwal.export.pdf');
    });

});

