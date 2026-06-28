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
        // Kolom id_kelas dan foreign key sudah ada di database.
        // Migration ini hanya sebagai penanda agar status migrate konsisten.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak melakukan apa-apa karena kolom sudah ada sebelumnya.
    }
};
