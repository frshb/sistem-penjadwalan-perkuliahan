<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSemesterTable extends Migration
{
public function up(): void
{
    Schema::create('semester', function (Blueprint $table) {
        $table->integer('id_semester')->primary();
        $table->string('nama_semester', 20);
        $table->string('tahun_ajaran', 20);
        $table->boolean('status_aktif')->default(true);
    });
}

    public function down()
    {
        Schema::dropIfExists('semester');
    }
}
