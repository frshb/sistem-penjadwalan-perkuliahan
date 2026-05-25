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
        Schema::table('kurikulum', function (Blueprint $table) {
            $table->string('kelas')->nullable();
            $table->string('matkul')->nullable();
            $table->string('dosen_pengampu')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kurikulum', function (Blueprint $table) {
            $table->dropColumn(['kelas', 'matkul', 'dosen_pengampu']);
        });
    }
};
