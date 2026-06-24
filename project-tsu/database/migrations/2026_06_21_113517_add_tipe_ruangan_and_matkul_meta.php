<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Menambahkan field yang dibutuhkan untuk constraint baru GA:
 *
 * 1. ruang.tipe_ruangan   — HC13: tipe ruangan (reguler/lab/studio/hybrid)
 * 2. mata_kuliah.kategori — HC13,SC13,SC15: kategori matkul (ringan/sedang/berat/praktikum)
 * 3. mata_kuliah.max_sks_hari — HC14: batas SKS dosen per hari (default dari matkul)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Tipe Ruangan ─────────────────────────────────────────────────────
        if (!Schema::hasColumn('ruang', 'tipe_ruangan')) {
            Schema::table('ruang', function (Blueprint $table) {
                $table->enum('tipe_ruangan', ['reguler', 'lab', 'studio', 'hybrid'])
                      ->default('reguler')
                      ->after('fasilitas')
                      ->comment('reguler=kelas teori, lab=praktikum komputer/kimia, studio=seni/desain, hybrid=bisa keduanya');
            });

            // Auto-detect: ruangan dengan nama mengandung kata kunci → lab/studio
            DB::statement("
                UPDATE ruang SET tipe_ruangan = 'lab'
                WHERE LOWER(nama_ruang) REGEXP 'lab|laboratorium|komputer|kimia|fisika|elektronika'
            ");
            DB::statement("
                UPDATE ruang SET tipe_ruangan = 'studio'
                WHERE LOWER(nama_ruang) REGEXP 'studio|bengkel|workshop|gambar'
            ");
        }

        // ── Kategori Mata Kuliah ──────────────────────────────────────────────
        if (!Schema::hasColumn('mata_kuliah', 'kategori')) {
            Schema::table('mata_kuliah', function (Blueprint $table) {
                $table->enum('kategori', ['ringan', 'sedang', 'berat', 'praktikum'])
                      ->default('sedang')
                      ->after('jenis')
                      ->comment('dipakai SC13/SC15: berat=matematika/algoritma/pemrograman, praktikum=lab');
            });

            // Auto-detect dari nama matkul
            DB::statement("
                UPDATE mata_kuliah SET kategori = 'berat'
                WHERE LOWER(nama_matkul) REGEXP
                    'matematika|algoritma|pemrograman|kalkulus|statistika|fisika|
                     struktur data|jaringan|keamanan|kriptografi|basis data|
                     rekayasa perangkat'
            ");
            DB::statement("
                UPDATE mata_kuliah SET kategori = 'praktikum'
                WHERE jenis = 'praktikum'
            ");
            DB::statement("
                UPDATE mata_kuliah SET kategori = 'ringan'
                WHERE LOWER(nama_matkul) REGEXP
                    'agama|pancasila|kewarganegaraan|bahasa indonesia|bahasa inggris|
                     komunikasi|etika|kewirausahaan|manajemen|pengantar'
            ");
        }
    }

    public function down(): void
    {
        Schema::table('ruang', function (Blueprint $table) {
            if (Schema::hasColumn('ruang', 'tipe_ruangan')) {
                $table->dropColumn('tipe_ruangan');
            }
        });
        Schema::table('mata_kuliah', function (Blueprint $table) {
            if (Schema::hasColumn('mata_kuliah', 'kategori')) {
                $table->dropColumn('kategori');
            }
        });
    }
};
