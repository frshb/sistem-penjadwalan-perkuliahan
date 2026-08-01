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
        if (!Schema::hasTable('jadwal_approval_histories')) {
            Schema::create('jadwal_approval_histories', function (Blueprint $table) {
                $table->id();
                $table->integer('id_tahunakademik');
                $table->integer('id_prodi')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('role_actor', 50);
                $table->string('aksi', 50); // submit, setujui, revisi, resubmit, publish, batalkan
                $table->string('status_sebelumnya', 50)->nullable();
                $table->string('status_sesudah', 50)->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->index('id_tahunakademik');
                $table->index('id_prodi');
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_approval_histories');
    }
};
