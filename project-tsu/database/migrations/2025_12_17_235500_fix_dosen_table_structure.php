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
        Schema::table('dosen', function (Blueprint $table) {
            // Add new column
            if (!Schema::hasColumn('dosen', 'mata_kuliah')) {
                $table->string('mata_kuliah')->nullable()->after('nidn');
            }
        });

        // Modification for existing columns to be nullable and auto increment
        // Using raw SQL is often safer for changing constraints on existing columns without doctrine/dbal
        
        // Disable foreign key checks to allow modifying the primary key column
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Make nik nullable
        DB::statement("ALTER TABLE dosen MODIFY COLUMN nik VARCHAR(20) NULL");

        // 2. Make status_dosen nullable
        DB::statement("ALTER TABLE dosen MODIFY COLUMN status_dosen ENUM('Tetap', 'Tidak Tetap') NULL");

        // 3. Make id_dosen Auto Increment
        DB::statement("ALTER TABLE dosen MODIFY COLUMN id_dosen INT AUTO_INCREMENT");

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dosen', function (Blueprint $table) {
            if (Schema::hasColumn('dosen', 'mata_kuliah')) {
                $table->dropColumn('mata_kuliah');
            }
        });

        // Reverting text/enum columns to NOT NULL might fail if there are NULLs, so we generally leave them or handle with care.
        // Reverting Auto Increment:
        // DB::statement("ALTER TABLE dosen MODIFY COLUMN id_dosen INT NOT NULL"); 
    }
};
