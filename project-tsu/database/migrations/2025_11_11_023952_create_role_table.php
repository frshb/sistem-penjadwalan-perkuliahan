<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoleTable extends Migration
{
public function up(): void
{
    Schema::create('role', function (Blueprint $table) {
        $table->integer('id_role')->primary();
        $table->enum('nama_role', [
            'super_admin',
            'admin_fakultas',
            'dekan',
            'dosen',
            'mahasiswa'
        ])->unique();
    });
}

    public function down()
    {
        Schema::dropIfExists('role');
    }
}
