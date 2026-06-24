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
        if (!Schema::hasColumn('pengampu_matkul', 'id_tahunakademik')) {
            Schema::table('pengampu_matkul', function (Blueprint $table) {
                $table->integer('id_tahunakademik')->nullable()->after('id');
            });

            // Assign existing rows to the current active academic year or the first academic year
            $activeTahun = DB::table('tahun_akademik')->where('status_aktif', 1)->first()
                ?? DB::table('tahun_akademik')->first();

            if ($activeTahun) {
                DB::table('pengampu_matkul')->update([
                    'id_tahunakademik' => $activeTahun->id_tahunakademik
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pengampu_matkul', 'id_tahunakademik')) {
            Schema::table('pengampu_matkul', function (Blueprint $table) {
                $table->dropColumn('id_tahunakademik');
            });
        }
    }
};
