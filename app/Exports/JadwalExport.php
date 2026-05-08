<?php

namespace App\Exports;

use App\Models\Jadwal;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class JadwalExport implements FromView, ShouldAutoSize
{
    public function view(): View
    {
        $jadwal = Jadwal::with(['matkul.kurikulum', 'matkul.prodi', 'dosen.prodi', 'ruang.gedung', 'hari', 'slot'])->orderBy('id_hari')->orderBy('id_slot')->get();
        return view('penjadwalan.export_excel', compact('jadwal'));
    }
}
