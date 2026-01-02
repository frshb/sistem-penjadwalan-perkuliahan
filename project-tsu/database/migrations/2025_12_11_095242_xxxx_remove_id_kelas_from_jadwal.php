<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('jadwal', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal', 'id_kelas')) {
                $table->dropColumn('id_kelas');
            }

            // tambah field kelas manual
            if (!Schema::hasColumn('jadwal', 'kelas')) {
                $table->string('kelas')->nullable()->after('id_matkul');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('jadwal', function (Blueprint $table) {
            $table->integer('id_kelas')->nullable()->after('id_dosen');
            $table->dropColumn('kelas');
        });
    }
};
