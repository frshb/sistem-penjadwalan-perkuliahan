<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKurikulumTable extends Migration
{
public function up(): void
{
    Schema::create('kurikulum', function (Blueprint $table) {
        $table->increments('id_kurikulum');
        $table->string('nama_kurikulum', 50);
        $table->enum('status', ['Aktif', 'Tidak Aktif']);
    });
}

    public function down()
    {
        Schema::dropIfExists('kurikulum');
    }
}
