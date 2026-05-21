<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use Illuminate\Support\Facades\Auth;
use App\Models\Kurikulum;
use App\Models\Ruangan;
use App\Models\Kelas;
use App\Models\Dosen;
use App\Models\Slot_waktu;
use App\Models\Hari;
use App\Models\TahunAkademik;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facade\Excel;
use App\Export\JadwalExport;
use PDF;

class JadwalController extends Controller
{


    /**
     * Halaman pilih tahun akademik penjadwalan
     */
    public function pilihTahun()
    {
        $tahunAkademiks = \App\Models\TahunAkademik::orderBy(
            'tahun_ajaran',
            'desc'
        )->get();

        return view(
            'penjadwalan.pilih-tahun',
            compact('tahunAkademiks')
        );
    }

    /**
     * Menampilkan halaman Penjadwalan Manual.
     * (Preserved from local branch)
     */
    public function manual(Request $request)
    {
        $idTahun = $request->tahun;

        if (!$idTahun) {
            return redirect()->route('jadwal.pilih-tahun');
        }

        $tahunAkademik = TahunAkademik::findOrFail($idTahun);

        $kelas = Kelas::with([
            'matakuliah.ruangans',
            'prodi',
            'dosen',
            'matakuliah',
            'jadwals'
        ])
        ->where('id_tahunakademik', $idTahun)
        ->orderBy('semester')
        ->get();

        $hari = Hari::all();

        $slotWaktu = Slot_waktu::orderBy('jam_ke')->get();

        $ruangan = Ruangan::all();

        $jadwals = Jadwal::with([
            'kelas.prodi',
            'dosen',
            'matakuliah',
            'ruangan',
            'hari',
            'slot'
        ])->get();

        $jadwalTersimpan = Jadwal::with([
            'kelas.matakuliah',
            'kelas.dosen',
            'kelas.prodi',
            'slotMulai',
            'hari',
        ])
        ->whereHas('kelas', fn($q) => $q->where('id_tahunakademik', $idTahun))
        ->where('is_manual', 1)
        ->get();

        return view('penjadwalan.penjadwalan-manual', compact(
            'tahunAkademik',
            'kelas',
            'hari',
            'slotWaktu',
            'ruangan',
            'jadwals',
            'jadwalTersimpan'
        ));
    }

    public function index(){
        $step = session('penjadwalan.current_step', 1);

        return view('penjadwalan.penjadwalan', [
            'step' => $step,
            'kurikulums' => Kurikulum::all(),
            'ruangans' => Ruangan::all(),
            'dosens' => Dosen::all(),
            'waktus' => Waktu::all(),
            'haris' => Hari::all(),
            'matkuls' => MataKuliah::all(),
        ]);
    }

    public function simpanSlot(Request $request)
    {
        $request->validate([
            'kelas_id'   => 'required|integer',
            'slot_id'    => 'required|integer',
            'hari_id'    => 'required|integer',
            'ruang_id'   => 'nullable|integer',
            'durasi_sks' => 'required|integer',
            'dosen_id'   => 'nullable|integer',  // ← ubah jadi nullable
        ]);

        $kelas      = \App\Models\Kelas::with(['matakuliah', 'dosen'])->findOrFail($request->kelas_id);
        $kodeMatkul = $kelas->matakuliah->kode_matkul ?? null;

        // Fallback: ambil dosen dari relasi kelas jika frontend tidak kirim
        $dosenId = $request->dosen_id ?? $kelas->dosen->id_dosen ?? null;

        // Fallback ruangan: pakai yang dikirim, atau ambil ruang pertama dari DB
        $ruangId = $request->ruang_id
            ?: \App\Models\Ruangan::orderBy('id_ruang')->value('id_ruang');

        if (!$kodeMatkul) {
            return response()->json([
                'success' => false,
                'message' => 'Mata kuliah tidak ditemukan untuk kelas ini.',
            ], 422);
        }

        $jadwal = Jadwal::updateOrCreate(
            ['id_kelas' => $request->kelas_id],
            [
                'kode_matkul'     => $kodeMatkul,
                'id_slot_mulai'   => $request->slot_id,
                'durasi_sks'      => $request->durasi_sks,
                'id_hari'         => $request->hari_id,
                'id_ruang'        => $request->ruang_id ?? 1,
                'id_dosen'        => $dosenId,
                'is_manual'       => 1,
                'status_validasi' => 0,
                'jenis_jadwal'    => 'kuliah',
            ]
        );

        return response()->json([
            'success'   => true,
            'jadwal_id' => $jadwal->id_jadwal,
        ]);
    }

    public function hapusSlot($id)
    {
        Jadwal::where('id_jadwal', $id)->delete();
        return response()->json(['success' => true]);
    }



    //EXCEL
    //public function exportExcel()
    //{
    //    return Excel::donwload(new JadwalExport, 'jadwal.xlsx');
    //}

    //PDF
    //public function exportPDF()
    //{
    //    $jadwal = Jadwal::with(['matkul', 'dosen', 'kelas', 'ruang', 'hari', 'slot'])->get();
    //    $pdf = PDF::loadView('jadwal.export_pdf', compact('jadwal'));
    //    return $pdf->downloa('jadwal.pdf');
    //}
}
