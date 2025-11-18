<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramStudiTable extends Migration
{
public function up(): void
{
    Schema::create('program_studi', function (Blueprint $table) {
        $table->integer('id_prodi')->primary();
        $table->string('nama_prodi', 100)->unique();
        $table->string('kode_prodi', 20)->nullable()->unique();
    });
}
    public function down()
    {
        Schema::dropIfExists('program_studi');
    }
}
