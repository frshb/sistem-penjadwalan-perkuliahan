<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jadwal_trials', function (Blueprint $table) {
            $table->id();
            $table->integer('id_tahunakademik');
            $table->string('label');
            $table->double('fitness');
            $table->integer('generasi');
            $table->integer('total_kelas');
            $table->integer('dosen_conflicts');
            $table->integer('ruangan_conflicts');
            $table->integer('soft_violations');
            $table->longText('jadwal_json');
            $table->timestamps();

            $table->index('id_tahunakademik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_trials');
    }
};
