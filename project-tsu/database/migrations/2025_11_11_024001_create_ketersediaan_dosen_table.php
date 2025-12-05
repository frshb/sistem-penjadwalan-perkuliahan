<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKetersediaanDosenTable extends Migration
{
    public function up(): void
    {
        Schema::create('ketersediaan_dosen', function (Blueprint $table) {
            $table->integer('id_dosen'); // FK
            $table->integer('id_hari'); // FK
            $table->integer('id_slot'); // FK
            $table->boolean('status_tersedia')->default(true);

            $table->primary(['id_dosen', 'id_hari', 'id_slot']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ketersediaan_dosen');
    }
}
