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
        Schema::table('hari', function (Blueprint $table) {
            if (!Schema::hasColumn('hari', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        Schema::table('slot_waktu', function (Blueprint $table) {
            if (!Schema::hasColumn('slot_waktu', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        if (!Schema::hasTable('hari_slot_waktu')) {
            Schema::create('hari_slot_waktu', function (Blueprint $table) {
                $table->id();
                $table->integer('id_hari');
                $table->integer('id_slot');
                
                $table->foreign('id_hari')->references('id_hari')->on('hari')->onDelete('cascade');
                $table->foreign('id_slot')->references('id_slot')->on('slot_waktu')->onDelete('cascade');
                
                $table->unique(['id_hari', 'id_slot']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hari_slot_waktu');

        Schema::table('slot_waktu', function (Blueprint $table) {
            if (Schema::hasColumn('slot_waktu', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        Schema::table('hari', function (Blueprint $table) {
            if (Schema::hasColumn('hari', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
