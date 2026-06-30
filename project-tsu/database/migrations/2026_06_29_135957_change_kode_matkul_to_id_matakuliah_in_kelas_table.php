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
        Schema::table('kelas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_matakuliah')->nullable()->after('semester');
        });

        // Update data using DB statement
        \Illuminate\Support\Facades\DB::statement('UPDATE kelas k JOIN mata_kuliah m ON k.kode_matkul = m.kode_matkul SET k.id_matakuliah = m.id_matakuliah');

        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn('kode_matkul');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->string('kode_matkul', 50)->nullable()->after('semester');
        });

        \Illuminate\Support\Facades\DB::statement('UPDATE kelas k JOIN mata_kuliah m ON k.id_matakuliah = m.id_matakuliah SET k.kode_matkul = m.kode_matkul');

        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn('id_matakuliah');
        });
    }
};
