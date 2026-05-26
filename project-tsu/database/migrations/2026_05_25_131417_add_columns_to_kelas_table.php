<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            if (!Schema::hasColumn('kelas', 'semester')) {
                $table->integer('semester')->nullable()->after('kapasitas');
            }
            if (!Schema::hasColumn('kelas', 'kode_matkul')) {
                $table->string('kode_matkul', 10)->nullable()->after('semester');
            }
            if (!Schema::hasColumn('kelas', 'id_dosen')) {
                $table->integer('id_dosen')->nullable()->after('kode_matkul');
            }
            if (!Schema::hasColumn('kelas', 'jumlah_mahasiswa')) {
                $table->integer('jumlah_mahasiswa')->nullable()->after('id_dosen');
            }
        });
        try {
            $classes = DB::table('kelas')->get();
            foreach ($classes as $class) {
                if ($class->nama_kelas && str_contains($class->nama_kelas, '-')) {
                    $parts = explode('-', $class->nama_kelas);
                    if (isset($parts[1]) && preg_match('/^\d+/', $parts[1], $matches)) {
                        DB::table('kelas')
                            ->where('id_kelas', $class->id_kelas)
                            ->update(['semester' => intval($matches[0])]);
                    }
                }
            }
        } catch (\Exception $e) {
        }
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('kelas', 'semester')) {
                $columns[] = 'semester';
            }
            if (Schema::hasColumn('kelas', 'kode_matkul')) {
                $columns[] = 'kode_matkul';
            }
            if (Schema::hasColumn('kelas', 'id_dosen')) {
                $columns[] = 'id_dosen';
            }
            if (Schema::hasColumn('kelas', 'jumlah_mahasiswa')) {
                $columns[] = 'jumlah_mahasiswa';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
