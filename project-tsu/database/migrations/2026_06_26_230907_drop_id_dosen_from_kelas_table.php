<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop foreign key jika masih ada (nama FK bisa bervariasi antar DB)
        $fkName = 'fk_kelas_dosen';
        $checkFk = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                                WHERE TABLE_SCHEMA = DATABASE()
                                AND TABLE_NAME = 'kelas'
                                AND COLUMN_NAME = 'id_dosen'
                                AND REFERENCED_TABLE_NAME IS NOT NULL
                                LIMIT 1");
        if (!empty($checkFk)) {
            $fkName = $checkFk[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE kelas DROP FOREIGN KEY `{$fkName}`");
        }

        // Hapus index jika ada
        $checkIdx = DB::select("SHOW INDEX FROM kelas WHERE Column_name = 'id_dosen'");
        if (!empty($checkIdx)) {
            DB::statement("ALTER TABLE kelas DROP INDEX `{$checkIdx[0]->Key_name}`");
        }

        // Hapus kolom
        if (Schema::hasColumn('kelas', 'id_dosen')) {
            Schema::table('kelas', function (Blueprint $table) {
                $table->dropColumn('id_dosen');
            });
        }
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_dosen')->nullable()->after('kode_matkul');
            $table->foreign('id_dosen', 'fk_kelas_dosen_restored')
                  ->references('id_dosen')
                  ->on('dosen')
                  ->onDelete('set null');
        });
    }
};
