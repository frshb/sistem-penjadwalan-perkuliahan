<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJadwalTable extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal', function (Blueprint $table) {
            $table->integer('id_jadwal')->primary();
            $table->string('id_matkul', 10); // FK
            $table->integer('id_dosen'); // FK
            $table->integer('id_kelas'); // FK
            $table->integer('id_ruang'); // FK
            $table->integer('id_hari'); // FK
            $table->integer('id_slot'); // FK
            $table->enum('jenis_jadwal', ['kuliah', 'kp', 'ta'])->default('kuliah');
            $table->boolean('status_validasi')->default(false);

            $table->unique(['id_ruang', 'id_hari', 'id_slot'], 'unique_ruang_waktu');
            $table->unique(['id_kelas', 'id_hari', 'id_slot'], 'unique_kelas_waktu');
            $table->unique(['id_dosen', 'id_hari', 'id_slot'], 'unique_dosen_waktu');
        });
    }

    public function down()
    {
        Schema::dropIfExists('jadwal');
    }
}
