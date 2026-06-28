<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First drop foreign keys for kode_matkul to allow dropping the column
        Schema::table('pengampu_matkul', function (Blueprint $table) {
            $table->dropForeign('fk_pengampu_matkul');
            $table->dropForeign('pengampu_matkul_ibfk_2');
            
            $table->dropColumn(['kode_matkul', 'id_prodi']);
        });

        // Rename the table
        Schema::rename('pengampu_matkul', 'pengampu_kelas');

        // Truncate the table as requested
        DB::table('pengampu_kelas')->truncate();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('pengampu_kelas', 'pengampu_matkul');

        Schema::table('pengampu_matkul', function (Blueprint $table) {
            $table->string('kode_matkul', 10)->nullable();
            $table->integer('id_prodi')->nullable();
        });
    }
};
