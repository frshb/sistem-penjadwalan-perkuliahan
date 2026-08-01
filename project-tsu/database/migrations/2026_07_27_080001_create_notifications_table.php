<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id')->nullable(); // Target user or null for broadcast
                $table->string('role_target')->nullable(); // Target role e.g. 'dosen', 'kaprodi', 'admin', 'dekan'
                $table->string('judul');
                $table->text('pesan');
                $table->enum('tipe', ['info', 'success', 'warning', 'danger'])->default('info');
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
