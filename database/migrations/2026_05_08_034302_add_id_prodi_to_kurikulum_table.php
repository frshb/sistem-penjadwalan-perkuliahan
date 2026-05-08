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
        Schema::table('kurikulum', function (Blueprint $table) {
            $table->integer('id_prodi')->nullable()->after('id_kurikulum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kurikulum', function (Blueprint $table) {
            $table->dropColumn('id_prodi');
        });
    }
};
