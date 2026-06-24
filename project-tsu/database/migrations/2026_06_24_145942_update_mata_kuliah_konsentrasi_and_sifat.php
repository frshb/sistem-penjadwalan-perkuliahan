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
        // Update Wajib courses (all courses ending with W or having W in their code)
        $wajibCodes = [
            '25UW03', '25NW03', '25AW02', '255A23W26', '255A22W27',
            '25AW03', '25NW01', '256A23W37', '256A23W39', '256A23W40'
        ];
        DB::table('mata_kuliah')
            ->whereIn('kode_matkul', $wajibCodes)
            ->update([
                'sifat' => 'W',
                'konsentrasi' => null
            ]);

        // Update Pilihan courses for Konsentrasi AI
        $aiCodes = [
            '255A23P01', '255A23P02', '255A23P03', // Semester 5
            '256A23P10', '256A23P11', '256A23P12'  // Semester 6
        ];
        DB::table('mata_kuliah')
            ->whereIn('kode_matkul', $aiCodes)
            ->update([
                'sifat' => 'P',
                'konsentrasi' => 'AI'
            ]);

        // Update Pilihan courses for Konsentrasi Programming and Software Development
        $psdCodes = [
            '255A23P04', '255A23P05', '255A23P06', // Semester 5
            '256A23P13', '256A23P14', '256A23P15'  // Semester 6
        ];
        DB::table('mata_kuliah')
            ->whereIn('kode_matkul', $psdCodes)
            ->update([
                'sifat' => 'P',
                'konsentrasi' => 'Programming and Software Development'
            ]);

        // Update Pilihan courses for Konsentrasi IT Mobility and Security
        $imsCodes = [
            '255A23P07', '255A23P08', '255A23P09', // Semester 5
            '256A23P16', '256A23P17', '256A23P18'  // Semester 6
        ];
        DB::table('mata_kuliah')
            ->whereIn('kode_matkul', $imsCodes)
            ->update([
                'sifat' => 'P',
                'konsentrasi' => 'IT Mobility and Security'
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $allCodes = array_merge(
            ['25UW03', '25NW03', '25AW02', '255A23W26', '255A22W27', '25AW03', '25NW01', '256A23W37', '256A23W39', '256A23W40'],
            ['255A23P01', '255A23P02', '255A23P03', '256A23P10', '256A23P11', '256A23P12'],
            ['255A23P04', '255A23P05', '255A23P06', '256A23P13', '256A23P14', '256A23P15'],
            ['255A23P07', '255A23P08', '255A23P09', '256A23P16', '256A23P17', '256A23P18']
        );
        DB::table('mata_kuliah')
            ->whereIn('kode_matkul', $allCodes)
            ->update([
                'sifat' => 'W',
                'konsentrasi' => null
            ]);
    }
};
