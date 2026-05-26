<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class UpdateRoleTableSeeder extends Seeder
{
    public function run()
    {
        // 1. Ubah tipe kolom nama_role menjadi string (menghapus enum)
        Schema::table('role', function (Blueprint $table) {
            $table->string('nama_role', 50)->change();
        });

        // 2. Update nama role
        DB::table('role')->where('id_role', 1)->update(['nama_role' => 'admin']);
        DB::table('role')->where('id_role', 2)->update(['nama_role' => 'kaprodi']);
        
        $this->command->info('Role table updated successfully.');
    }
}
