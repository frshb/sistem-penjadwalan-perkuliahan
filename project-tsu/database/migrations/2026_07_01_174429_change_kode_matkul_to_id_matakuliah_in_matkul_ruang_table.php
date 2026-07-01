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
        Schema::table('matkul_ruang', function (Blueprint $table) {
            $table->unsignedBigInteger('id_matakuliah')->nullable()->after('id_matkulruang');
        });

        // Update data using DB statement
        \Illuminate\Support\Facades\DB::statement('UPDATE matkul_ruang k JOIN mata_kuliah m ON k.kode_matkul = m.kode_matkul SET k.id_matakuliah = m.id_matakuliah');

        Schema::table('matkul_ruang', function (Blueprint $table) {
            $table->dropColumn('kode_matkul');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matkul_ruang', function (Blueprint $table) {
            $table->string('kode_matkul', 50)->nullable()->after('id_matkulruang');
        });

        \Illuminate\Support\Facades\DB::statement('UPDATE matkul_ruang k JOIN mata_kuliah m ON k.id_matakuliah = m.id_matakuliah SET k.kode_matkul = m.kode_matkul');

        Schema::table('matkul_ruang', function (Blueprint $table) {
            $table->dropColumn('id_matakuliah');
        });
    }
};
