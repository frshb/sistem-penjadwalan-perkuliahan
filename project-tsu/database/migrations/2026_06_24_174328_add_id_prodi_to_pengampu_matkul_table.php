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
        if (!Schema::hasColumn('pengampu_matkul', 'id_prodi')) {
            Schema::table('pengampu_matkul', function (Blueprint $table) {
                $table->integer('id_prodi')->nullable()->after('kode_matkul');
            });

            // Populate existing rows
            $pengampus = DB::table('pengampu_matkul')->get();
            foreach ($pengampus as $p) {
                // Find a matching mata_kuliah's id_prodi
                $mk = DB::table('mata_kuliah')->where('kode_matkul', $p->kode_matkul)->first();
                if ($mk) {
                    DB::table('pengampu_matkul')->where('id', $p->id)->update([
                        'id_prodi' => $mk->id_prodi
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pengampu_matkul', 'id_prodi')) {
            Schema::table('pengampu_matkul', function (Blueprint $table) {
                $table->dropColumn('id_prodi');
            });
        }
    }
};
