<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKelasTable extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->integer('id_kelas')->primary();
            $table->string('nama_kelas', 50);
            $table->integer('id_prodi')->nullable(); // FK
            $table->integer('id_semester')->nullable(); // FK
            $table->integer('kapasitas');

            $table->unique(['nama_kelas', 'id_prodi', 'id_semester']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('kelas');
    }
}
