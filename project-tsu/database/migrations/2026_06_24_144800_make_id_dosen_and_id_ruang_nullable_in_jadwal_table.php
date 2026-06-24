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
        Schema::disableForeignKeyConstraints();
        Schema::table('jadwal', function (Blueprint $table) {
            $table->integer('id_dosen')->nullable()->change();
            $table->unsignedInteger('id_ruang')->nullable()->change();
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('jadwal', function (Blueprint $table) {
            $table->integer('id_dosen')->nullable(false)->change();
            $table->unsignedInteger('id_ruang')->nullable(false)->change();
        });
        Schema::enableForeignKeyConstraints();
    }
};
