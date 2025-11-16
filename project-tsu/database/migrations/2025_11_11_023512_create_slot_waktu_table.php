<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlotWaktuTable extends Migration
{
    public function up(): void
    {
        Schema::create('slot_waktu', function (Blueprint $table) {
            $table->integer('id_slot')->primary();
            $table->integer('jam_ke')->unique();
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
        });
    }

    public function down()
    {
        Schema::dropIfExists('slot_waktu');
    }
}
