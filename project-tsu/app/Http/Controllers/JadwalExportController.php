<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jadwal;
use App\Models\TahunAkademik;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\JadwalExport;

class JadwalExportController extends Controller
{
    /**
     * Export jadwal ke Excel.
     */
    public function exportExcel(Request $request, $tahunAkademikId)
    {
        $tahunAkademik = TahunAkademik::findOrFail($tahunAkademikId);

        return Excel::download(
            new JadwalExport($tahunAkademikId),
            'jadwal_' . $this->sanitizeFilename($tahunAkademik->nama_tahunakademik) . '.xlsx'
        );
    }

    public function exportPdf(Request $request, $tahunAkademikId)
    {
        $tahunAkademik = TahunAkademik::findOrFail($tahunAkademikId);

        $jadwals = $this->getJadwalData($tahunAkademikId);

        $pdf = Pdf::loadView('exports.jadwal-pdf', [
            'jadwals'       => $jadwals,
            'tahunAkademik' => $tahunAkademik,
        ])->setPaper('a4', 'landscape');

        return $pdf->download(
            'jadwal_' . $this->sanitizeFilename($tahunAkademik->nama_tahunakademik) . '.pdf'
        );
    }

    private function sanitizeFilename(string $name): string
    {
        // Replace slashes with a dash, spaces with underscore, strip anything else invalid
        return preg_replace('/[^\w\-.]/', '_', str_replace('/', '-', $name));
    }

    /**
     * Ambil data jadwal yang sudah di-join relasi.
     */
        private function getJadwalData($tahunAkademikId): \Illuminate\Support\Collection
    {
        return Jadwal::with([
                'kelas.matakuliah',
                'kelas.prodi',
                'kelas.dosen',
                'slotMulai',
                'hari',
                'ruangan',
            ])
            ->where('id_tahunakademik', $tahunAkademikId) // ← fix
            ->get()
            ->map(function ($j) {
                $slotId      = $j->id_slot_mulai; // ← fix nama kolom
                $sks         = $j->durasi_sks;    // ← fix nama kolom
                $slotSelesai = \App\Models\Slot_waktu::find($slotId + $sks - 1);

                return [
                    'hari'        => ucfirst($j->hari->nama_hari ?? '-'),
                    'prodi'       => $j->kelas->prodi->nama_prodi        ?? '-',
                    'semester'    => $j->kelas->semester                  ?? '-',
                    'sesi'        => $this->getSesi($slotId),
                    'kelas'       => $j->kelas->nama_kelas                ?? '-',
                    'kode_mk'     => $j->kelas->matakuliah->kode_matkul   ?? '-',
                    'nama_mk'     => $j->kelas->matakuliah->nama_matkul   ?? '-',
                    'jenis' => $j->kelas->matakuliah->jenis ?? 'Teori',  // ← baca dari DB
                    'sks'         => $sks,
                    'dosen'       => $j->kelas->dosen->nama_dosen         ?? '-',
                    'ruangan'     => $j->ruangan->nama_ruang              ?? '-',
                    'jam_mulai'   => $j->slotMulai->waktu_mulai           ?? '-',
                    'jam_selesai' => $slotSelesai->waktu_selesai          ?? '-',
                ];
            })
            ->sortBy([
                fn ($a, $b) => $this->hariOrder($a['hari']) <=> $this->hariOrder($b['hari']),
                fn ($a, $b) => $a['jam_mulai'] <=> $b['jam_mulai'],
            ])
            ->values();
    }

    private function getSesi(int $slotId): string
    {
        if ($slotId <= 11) return 'Pagi';
        return 'Malam';
    }

    private function hariOrder(string $hari): int
    {
        return match (strtolower($hari)) {
            'senin'  => 1,
            'selasa' => 2,
            'rabu'   => 3,
            'kamis'  => 4,
            'jumat'  => 5,
            default  => 6,
        };
    }
}
