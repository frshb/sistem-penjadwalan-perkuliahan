<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMataKuliahTable extends Migration
{
public function up(): void
{
    Schema::create('mata_kuliah', function (Blueprint $table) {
        $table->string('kode_matkul', 10)->primary();
        $table->string('nama_matkul', 100);
        $table->integer('sks');
        $table->enum('jenis', ['teori', 'praktikum']);
        $table->integer('id_prodi')->nullable(); // FK
        $table->integer('semester');
        $table->integer('id_kurikulum')->unsigned()->nullable(); // FK
    });
}

    public function down()
    {
        Schema::dropIfExists('mata_kuliah');
    }
}
