<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
{
public function up(): void
{
    Schema::create('user', function (Blueprint $table) {
        $table->integer('id_user')->primary();
        $table->string('username', 50)->unique();
        $table->string('password_hash', 255);
        $table->integer('id_role'); // FK
        $table->integer('id_dosen')->nullable(); // FK
        $table->integer('id_mhs')->nullable(); // FK
    });
}

    public function down()
    {
        Schema::dropIfExists('user');
    }
}
