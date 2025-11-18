<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDosenTable extends Migration
{
public function up(): void
{
    Schema::create('dosen', function (Blueprint $table) {
        $table->integer('id_dosen')->primary();
        $table->string('nama_dosen', 100);

        // Informasi baru Anda:
        $table->enum('status_dosen', ['Tetap', 'Tidak Tetap']);
        $table->string('nik', 20)->unique(); // Wajib diisi untuk semua
        $table->string('nuptk', 20)->nullable()->unique(); // Boleh kosong
        $table->string('nidn', 20)->nullable()->unique(); // Boleh kosong

        $table->integer('id_prodi')->nullable(); // FK
        $table->text('ketersediaan_waktu')->nullable();
    });
}

    public function down()
    {
        Schema::dropIfExists('dosen');
    }
}
