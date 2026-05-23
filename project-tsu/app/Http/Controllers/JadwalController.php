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
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\JadwalExport;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    /**
     * Halaman pilih tahun akademik penjadwalan
     */
    public function pilihTahun()
    {
        $tahunAkademiks = TahunAkademik::orderBy('tahun_ajaran', 'desc')->get();

        return view('penjadwalan.pilih-tahun', compact('tahunAkademiks'));
    }

    /**
     * Halaman Penjadwalan Manual
     */
    public function manual(Request $request)
    {
        $idTahun = $request->tahun;

        if (!$idTahun) {
            return redirect()->route('jadwal.pilih-tahun');
        }

        $tahunAkademik = TahunAkademik::findOrFail($idTahun);

        // Kelas yang terdaftar di tahun akademik ini
        $kelas = Kelas::with([
            'matakuliah.ruangans',
            'prodi',
            'dosen',
            'matakuliah',
            'jadwals' => fn($q) => $q->where('id_tahunakademik', $idTahun),
        ])
        ->where('id_tahunakademik', $idTahun)
        ->orderBy('semester')
        ->get();

        $hari      = Hari::all();
        $slotWaktu = Slot_waktu::orderBy('jam_ke')->get();
        $ruangan   = Ruangan::all();

        // Jadwal yang sudah tersimpan untuk tahun akademik ini
        $jadwalTersimpan = Jadwal::with([
            'kelas.matakuliah',
            'kelas.dosen',
            'kelas.prodi',
            'slotMulai',
            'hari',
            'ruangan',
        ])
        ->where('id_tahunakademik', $idTahun)
        ->where('is_manual', 1)
        ->get();

        // ✅ Tambahkan ini — encode ke array bersih untuk JS
        $jadwalJson = $jadwalTersimpan->map(fn($j) => [
            'jadwal_id'  => $j->id_jadwal,
            'kelas_id'   => $j->id_kelas,
            'nama_kelas' => $j->kelas->nama_kelas        ?? '-',
            'nama'       => $j->kelas->matakuliah->nama_matkul  ?? '-',
            'kode_mk'    => $j->kelas->matakuliah->kode_matkul  ?? '-',
            'dosen'      => $j->kelas->dosen->nama_dosen ?? '-',
            'ruangan'    => $j->ruangan->nama_ruang       ?? '',
            'ruangan_id' => $j->id_ruang,
            'slot_id'    => $j->id_slot_mulai,
            'sks'        => $j->durasi_sks,
            'hari'       => strtolower($j->hari->nama_hari ?? 'senin'),
        ])->toJson();

        return view('penjadwalan.penjadwalan-manual', compact(
            'tahunAkademik',
            'kelas',
            'hari',
            'slotWaktu',
            'ruangan',
            'jadwalTersimpan',
            'jadwalJson',
        ));
    }

    /**
     * Simpan / update satu slot jadwal dari workspace
     */
    public function simpanSlot(Request $request)
    {
        $request->validate([
            'kelas_id'          => 'required|integer',
            'slot_id'           => 'required|integer',
            'hari_id'           => 'required|integer',
            'ruang_id'          => 'nullable|integer',
            'durasi_sks'        => 'required|integer',
            'dosen_id'          => 'nullable|integer',
            'tahun_akademik_id' => 'required|integer',
        ]);

        $kelas      = Kelas::with(['matakuliah', 'dosen'])->findOrFail($request->kelas_id);
        $kodeMatkul = $kelas->matakuliah->kode_matkul ?? null;

        if (!$kodeMatkul) {
            return response()->json([
                'success' => false,
                'message' => 'Mata kuliah tidak ditemukan untuk kelas ini.',
            ], 422);
        }

        // Fallback dosen dari relasi kelas jika frontend tidak kirim
        $dosenId = $request->dosen_id ?? $kelas->dosen?->id_dosen ?? null;

        // Fallback ruangan: ambil ruang pertama jika tidak dikirim
        $ruangId = $request->ruang_id
            ?: Ruangan::orderBy('id_ruang')->value('id_ruang');

        $jadwal = Jadwal::updateOrCreate(
            [
                // Identifikasi unik: satu kelas hanya punya satu jadwal per tahun akademik
                'id_kelas'          => $request->kelas_id,
                'id_tahunakademik'  => $request->tahun_akademik_id,
            ],
            [
                'kode_matkul'       => $kodeMatkul,
                'id_slot_mulai'     => $request->slot_id,
                'durasi_sks'        => $request->durasi_sks,
                'id_hari'           => $request->hari_id,
                'id_ruang'          => $ruangId,
                'id_dosen'          => $dosenId,
                'is_manual'         => 1,
            ]
        );

        return response()->json([
            'success'   => true,
            'jadwal_id' => $jadwal->id_jadwal,
        ]);
    }

    /**
     * Hapus satu slot jadwal
     */
    public function hapusSlot($id)
    {
        Jadwal::where('id_jadwal', $id)->delete();

        return response()->json(['success' => true]);
    }
}
