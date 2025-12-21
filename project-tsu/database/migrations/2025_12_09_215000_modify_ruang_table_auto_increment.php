<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Using raw SQL to ensure it works even without doctrine/dbal
        // This command modifies the id_ruang column to be AUTO_INCREMENT
        // DB::statement('ALTER TABLE ruang MODIFY id_ruang INT NOT NULL AUTO_INCREMENT;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to just INT (removing auto_increment)
        // DB::statement('ALTER TABLE ruang MODIFY id_ruang INT NOT NULL;');
    }
};
