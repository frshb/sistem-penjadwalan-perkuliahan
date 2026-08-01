<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tahun_akademik', function (Blueprint $table) {
            if (!Schema::hasColumn('tahun_akademik', 'status_validasi')) {
                $table->enum('status_validasi', ['draft', 'menunggu_persetujuan', 'disetujui', 'revisi'])
                      ->default('draft')
                      ->after('status_aktif');
            }
            if (!Schema::hasColumn('tahun_akademik', 'catatan_revisi')) {
                $table->text('catatan_revisi')->nullable()->after('status_validasi');
            }
            if (!Schema::hasColumn('tahun_akademik', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('catatan_revisi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tahun_akademik', function (Blueprint $table) {
            if (Schema::hasColumn('tahun_akademik', 'validated_at')) {
                $table->dropColumn('validated_at');
            }
            if (Schema::hasColumn('tahun_akademik', 'catatan_revisi')) {
                $table->dropColumn('catatan_revisi');
            }
            if (Schema::hasColumn('tahun_akademik', 'status_validasi')) {
                $table->dropColumn('status_validasi');
            }
        });
    }
};
