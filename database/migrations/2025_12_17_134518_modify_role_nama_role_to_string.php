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
        Schema::table('role', function (Blueprint $table) {
            $table->string('nama_role', 50)->change();
        });

        // Update role names after schema change
        DB::table('role')->where('id_role', 1)->update(['nama_role' => 'admin']);
        DB::table('role')->where('id_role', 2)->update(['nama_role' => 'kaprodi']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role', function (Blueprint $table) {
            // Revert is hard because we don't know previous enum state exactly easily, but we can leave it as string or try to revert to enum
           // $table->enum('nama_role', ['super_admin', 'admin_fakultas', 'dekan', 'dosen', 'mahasiswa'])->change();
        });
    }
};
