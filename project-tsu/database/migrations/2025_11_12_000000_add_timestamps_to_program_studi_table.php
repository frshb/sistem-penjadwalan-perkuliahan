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
        Schema::table('program_studi', function (Blueprint $table) {
            // Perintah ini akan menambahkan 2 kolom:
            // created_at (tipe TIMESTAMP, nullable)
            // updated_at (tipe TIMESTAMP, nullable)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_studi', function (Blueprint $table) {
            // Ini untuk membatalkan (rollback) migrasi
            $table->dropTimestamps();
        });
    }
};
