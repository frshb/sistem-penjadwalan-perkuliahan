<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToAllTables extends Migration
{
    public function up(): void
    {
        Schema::table('ruang', function (Blueprint $table) {
            $table->foreign('id_gedung')->references('id_gedung')->on('gedung');
        });

        Schema::table('dosen', function (Blueprint $table) {
            $table->foreign('id_prodi')->references('id_prodi')->on('program_studi')->onDelete('set null');
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->foreign('id_prodi')->references('id_prodi')->on('program_studi')->onDelete('set null');
            $table->foreign('id_semester')->references('id_semester')->on('semester');
        });

        Schema::table('mata_kuliah', function (Blueprint $table) {
            $table->foreign('id_prodi')->references('id_prodi')->on('program_studi')->onDelete('set null');
            $table->foreign('id_kurikulum')->references('id_kurikulum')->on('kurikulum')->onDelete('set null');
        });

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->foreign('id_prodi')->references('id_prodi')->on('program_studi')->onDelete('set null');
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas');
        });

        Schema::table('user', function (Blueprint $table) {
            $table->foreign('id_role')->references('id_role')->on('role');
            $table->foreign('id_dosen')->references('id_dosen')->on('dosen');
            $table->foreign('id_mhs')->references('id_mhs')->on('mahasiswa');
        });

        Schema::table('jadwal', function (Blueprint $table) {
            $table->foreign('id_matkul')->references('kode_matkul')->on('mata_kuliah');
            $table->foreign('id_dosen')->references('id_dosen')->on('dosen');
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas');
            $table->foreign('id_ruang')->references('id_ruang')->on('ruang');
            $table->foreign('id_hari')->references('id_hari')->on('hari');
            $table->foreign('id_slot')->references('id_slot')->on('slot_waktu');
        });

        Schema::table('ketersediaan_dosen', function (Blueprint $table) {
            $table->foreign('id_dosen')->references('id_dosen')->on('dosen');
            $table->foreign('id_hari')->references('id_hari')->on('hari');
            $table->foreign('id_slot')->references('id_slot')->on('slot_waktu');
        });
}}
