<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoleUserTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->bigIncrements('id');

            // user_id mengikuti tipe users.id = BIGINT UNSIGNED
            $table->unsignedBigInteger('user_id');

            // role_id mengikuti tipe role.id_role = INTEGER (signed)
            $table->integer('role_id');

            $table->timestamps();

            // Foreign key ke users
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Foreign key ke role
            $table->foreign('role_id')
                ->references('id_role')
                ->on('role')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
}
