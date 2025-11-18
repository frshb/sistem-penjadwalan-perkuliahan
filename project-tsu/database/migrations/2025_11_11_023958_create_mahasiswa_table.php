<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMahasiswaTable extends Migration
{
public function up(): void
{
    Schema::create('mahasiswa', function (Blueprint $table) {
        $table->integer('id_mhs')->primary();
        $table->string('nim', 20)->unique();
        $table->string('nama_mhs', 100);
        $table->integer('id_prodi')->nullable(); // FK
        $table->integer('id_kelas')->nullable(); // FK
        $table->integer('semester')->nullable();
    });
}

    public function down()
    {
        Schema::dropIfExists('mahasiswa');
    }
}
