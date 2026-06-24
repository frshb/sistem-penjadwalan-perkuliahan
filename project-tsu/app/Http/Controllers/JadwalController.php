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
use App\Services\GeneticAlgorithm\OptimizeJadwal;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\JadwalExport;
use Illuminate\Http\Request;
use App\Helpers\ProdiFilter;

class JadwalController extends Controller
{
    /**
     * Halaman pilih tahun akademik penjadwalan
     */
    public function pilihTahun()
    {
        $tahunAkademiks = TahunAkademik::orderBy('tahun_ajaran', 'desc')
            ->withCount(['kelas', 'jadwals'])
            ->get();

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
        $prodiId       = ProdiFilter::getProdiId(); // ✅ Satu baris ganti logika panjang

        $kelasQuery = Kelas::with([
            'matakuliah.ruangans', 'prodi', 'dosen', 'matakuliah',
            'jadwals' => fn($q) => $q->where('id_tahunakademik', $idTahun),
        ])->where('id_tahunakademik', $idTahun);

        if ($prodiId) {
            $kelasQuery->where('id_prodi', $prodiId);
        }

        $kelas     = $kelasQuery->orderBy('semester')->get();
        $hari      = Hari::all();
        $slotWaktu = Slot_waktu::orderBy('jam_ke')->get();
        $ruangan   = Ruangan::all();

        $jadwalQuery = Jadwal::with([
            'kelas.matakuliah', 'kelas.dosen', 'kelas.prodi',
            'slotMulai', 'hari', 'ruangan',
        ])->where('id_tahunakademik', $idTahun);

        if ($prodiId) {
            $jadwalQuery->whereHas('kelas', fn($q) => $q->where('id_prodi', $prodiId));
        }

        $jadwalTersimpan = $jadwalQuery->get();

        $jadwalJson = $jadwalTersimpan->map(fn($j) => [
            'jadwal_id'  => $j->id_jadwal,
            'kelas_id'   => $j->id_kelas,
            'nama_kelas' => $j->kelas->nama_kelas               ?? '-',
            'nama'       => $j->kelas->matakuliah->nama_matkul  ?? '-',
            'kode_mk'    => $j->kelas->matakuliah->kode_matkul  ?? '-',
            'dosen'      => $j->kelas->dosen->nama_dosen        ?? '-',
            'ruangan'    => $j->ruangan->nama_ruang             ?? '',
            'ruangan_id' => $j->id_ruang,
            'slot_id'    => $j->id_slot_mulai,
            'sks'        => $j->durasi_sks,
            'hari'       => strtolower($j->hari->nama_hari ?? 'senin'),
        ])->toJson();

        $adaJadwalOtomatis = Jadwal::where('id_tahunakademik', $idTahun)
            ->where('is_manual', 0)->exists();

        return view('penjadwalan.penjadwalan-manual', compact(
            'tahunAkademik', 'kelas', 'hari', 'slotWaktu',
            'ruangan', 'jadwalTersimpan', 'jadwalJson', 'adaJadwalOtomatis'
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

    /**
     * Jalankan optimasi jadwal otomatis (perbaiki bentrok)
     */
    public function optimasi(Request $request, OptimizeJadwal $service)
    {
        $request->validate([
            'tahun_akademik_id' => 'required|integer|exists:tahun_akademik,id_tahunakademik',
        ]);

        $idTahun = (int) $request->tahun_akademik_id;

        // Cek ada tidaknya jadwal otomatis
        $adaOtomatis = Jadwal::where('id_tahunakademik', $idTahun)
            ->where('is_manual', 0)
            ->exists();

        if (!$adaOtomatis) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada jadwal dari penjadwalan otomatis. Jalankan Algoritma Genetika terlebih dahulu.',
            ], 422);
        }

        $hasil = $service->optimasi($idTahun);

        // Kembalikan jadwal terbaru setelah optimasi (untuk refresh workspace)
        $jadwalTerbaru = Jadwal::with(['kelas.matakuliah', 'kelas.dosen', 'slotMulai', 'hari', 'ruangan'])
            ->where('id_tahunakademik', $idTahun)
            ->get()
            ->map(fn($j) => [
                'jadwal_id'  => $j->id_jadwal,
                'kelas_id'   => $j->id_kelas,
                'nama_kelas' => $j->kelas->nama_kelas              ?? '-',
                'nama'       => $j->kelas->matakuliah->nama_matkul ?? '-',
                'kode_mk'    => $j->kelas->matakuliah->kode_matkul ?? '-',
                'dosen'      => $j->kelas->dosen->nama_dosen        ?? '-',
                'ruangan'    => $j->ruangan->nama_ruang             ?? '',
                'ruangan_id' => $j->id_ruang,
                'slot_id'    => $j->id_slot_mulai,
                'sks'        => $j->durasi_sks,
                'hari'       => strtolower($j->hari->nama_hari ?? 'senin'),
                'is_manual'  => $j->is_manual,
            ]);

        return response()->json([
            'success'        => true,
            'hasil'          => $hasil,
            'jadwal_terbaru' => $jadwalTerbaru,
        ]);
    }

    /**
     * Endpoint ringan — hanya hitung jumlah bentrok/pelanggaran constraint
     * saat ini tanpa menjalankan optimasi. Digunakan untuk refresh badge.
     *
     * Eager-load 'kelas.matakuliah.ruangans' DITAMBAHKAN agar konsisten
     * dengan deteksiBentrok() di OptimizeJadwal yang sekarang juga
     * memeriksa HC10 (ruangan harus valid/terdaftar untuk mata kuliah).
     * Tanpa ini, Eloquent tetap berjalan via lazy-load otomatis, hanya
     * lebih banyak query (N+1) untuk endpoint yang dipanggil cukup sering.
     */
    public function statusBentrok(Request $request, OptimizeJadwal $service)
    {
        $request->validate([
            'tahun_akademik_id' => 'required|integer|exists:tahun_akademik,id_tahunakademik',
        ]);

        $idTahun = (int) $request->tahun_akademik_id;

        $jadwalList = Jadwal::with([
            'kelas.matakuliah',
            'kelas.dosen',
            'kelas.prodi',
            'kelas.matakuliah.ruangans',
            'slotMulai',
            'hari',
            'ruangan',
        ])
        ->where('id_tahunakademik', $idTahun)
        ->get();

        $bentrokList = $service->deteksiBentrok($jadwalList);

        return response()->json([
            'success'        => true,
            'jumlah_bentrok' => count($bentrokList),
            'detail'         => $bentrokList,
        ]);
    }
}
