<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHariTable extends Migration
{
public function up(): void
{
    Schema::create('hari', function (Blueprint $table) {
        $table->integer('id_hari')->primary();
        $table->string('nama_hari', 20)->unique();
    });
}
    public function down()
    {
        Schema::dropIfExists('hari');
    }
}
