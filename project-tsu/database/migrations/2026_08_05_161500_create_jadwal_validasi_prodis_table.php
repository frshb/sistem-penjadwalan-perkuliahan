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
        Schema::dropIfExists('jadwal_validasi_prodis');
        Schema::create('jadwal_validasi_prodis', function (Blueprint $table) {
            $table->id();
            $table->integer('id_tahunakademik');
            $table->integer('id_prodi');
            $table->enum('status_sekprodi', ['menunggu', 'revisi', 'disetujui'])->default('menunggu');
            $table->enum('status_kaprodi', ['menunggu', 'revisi', 'disetujui'])->default('menunggu');
            $table->text('catatan_revisi_sekprodi')->nullable();
            $table->text('catatan_revisi_kaprodi')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_tahunakademik')->references('id_tahunakademik')->on('tahun_akademik')->onDelete('cascade');
            $table->foreign('id_prodi')->references('id_prodi')->on('program_studi')->onDelete('cascade');
            
            // Unique constraint to prevent duplicate rows for the same prodi in the same academic year
            $table->unique(['id_tahunakademik', 'id_prodi'], 'unique_tahun_prodi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_validasi_prodis');
    }
};
