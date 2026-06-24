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
        $exists = DB::table('program_studi')->where('nama_prodi', 'Dosen Eksternal Fakultas')->exists();
        if (!$exists) {
            $id = 99;
            if (DB::table('program_studi')->where('id_prodi', $id)->exists()) {
                $id = DB::table('program_studi')->max('id_prodi') + 1;
            }
            DB::table('program_studi')->insert([
                'id_prodi' => $id,
                'nama_prodi' => 'Dosen Eksternal Fakultas',
                'kode_prodi' => 'EXT',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('program_studi')->where('nama_prodi', 'Dosen Eksternal Fakultas')->delete();
    }
};
