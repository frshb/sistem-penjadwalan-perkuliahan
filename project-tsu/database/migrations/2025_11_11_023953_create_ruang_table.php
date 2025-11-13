<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRuangTable extends Migration
{
    public function up(): void
    {
        Schema::create('ruang', function (Blueprint $table) {
            $table->integer('id_ruang')->primary();
            $table->string('nama_ruang', 50)->unique();
            $table->integer('kapasitas');
            $table->text('fasilitas')->nullable();
            $table->integer('id_gedung')->nullable(); // FK
        });
    }

    public function down()
    {
        Schema::dropIfExists('ruang');
    }
}
