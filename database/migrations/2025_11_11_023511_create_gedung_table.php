<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGedungTable extends Migration
{
    public function up(): void
    {
        Schema::create('gedung', function (Blueprint $table) {
            $table->integer('id_gedung')->primary();
            $table->string('nama_gedung', 50);
            $table->integer('lantai')->nullable();
            $table->string('lokasi', 100)->nullable();
        });
    }
    public function down()
    {
        Schema::dropIfExists('gedung');
    }
}
