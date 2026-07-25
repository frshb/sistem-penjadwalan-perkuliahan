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
use App\Models\PengampuKelas;
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
        $tahunAkademiks = TahunAkademik::orderBy('tahun_ajaran', 'desc')->get();
        $prodiId = ProdiFilter::getProdiId();

        foreach ($tahunAkademiks as $ta) {
            // Hitung kelas unik dari pengampu_kelas
            $kelasQuery = PengampuKelas::where('id_tahunakademik', $ta->id_tahunakademik);
            if ($prodiId) {
                $kelasQuery->whereHas('kelas', function ($q) use ($prodiId) {
                    $q->where('id_prodi', $prodiId);
                });
            }
            $ta->kelas_count = $kelasQuery->distinct('id_kelas')->count('id_kelas');

            // Hitung jadwal dari tahun akademik ini
            $jadwalQuery = Jadwal::where('id_tahunakademik', $ta->id_tahunakademik);
            if ($prodiId) {
                $jadwalQuery->whereHas('kelas', function ($q) use ($prodiId) {
                    $q->where('id_prodi', $prodiId);
                });
            }
            $ta->jadwals_count = $jadwalQuery->count();
        }

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

        $kelasQuery = Kelas::whereHas('pengampus', function ($q) use ($idTahun) {
                $q->where('id_tahunakademik', $idTahun);
            })
            ->with([
                'matakuliah.ruangans', 'prodi', 'dosen', 'matakuliah',
                'jadwals' => fn($q) => $q->where('id_tahunakademik', $idTahun),
            ])->where('id_tahunakademik', $idTahun);

        if ($prodiId) {
            $kelasQuery->where('id_prodi', $prodiId);
        }

        $kelas     = $kelasQuery->orderBy('semester')->get();
        $hari = Hari::where('is_active', true)->with('slotWaktus')->get()->sortBy(function ($h) {
            $urutan = ['senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4, 'jumat' => 5, 'sabtu' => 6, 'minggu' => 7, 'ahad' => 7];
            return $urutan[strtolower($h->nama_hari)] ?? 99;
        })->values();
        $slotWaktu = Slot_waktu::where('is_active', true)->orderBy('jam_ke')->get();
        $ruangan   = Ruangan::all();

        $jadwalQuery = Jadwal::with([
            'kelas.matakuliah', 'kelas.pengampuKelas.dosen', 'kelas.prodi', 'dosen',
            'slotMulai', 'hari', 'ruangan',
        ])->where('id_tahunakademik', $idTahun);

        $jadwalTersimpan = $jadwalQuery->get();

        $jadwalJson = $jadwalTersimpan->map(fn($j) => [
            'jadwal_id'    => $j->id_jadwal,
            'kelas_id'     => $j->id_kelas,
            'nama_kelas'   => $j->kelas->nama_kelas               ?? '-',
            'nama'         => $j->kelas->matakuliah->nama_matkul  ?? '-',
            'kode_mk'      => $j->kelas->matakuliah->kode_matkul  ?? '-',
            'dosen'        => $j->dosen?->nama_dosen ?? ($j->kelas?->pengampuKelas?->first()?->dosen?->nama_dosen ?? '-'),
            'dosen_id'     => $j->id_dosen ?? ($j->kelas?->pengampuKelas?->first()?->id_dosen ?? null),
            'prodi'        => $j->kelas->prodi->nama_prodi        ?? '-',
            'prodi_id'     => $j->kelas->id_prodi                 ?? null,
            'is_read_only' => $prodiId ? (($j->kelas->id_prodi ?? null) != $prodiId) : false,
            'ruangan'      => $j->ruangan?->nama_ruang             ?? '',
            'ruangan_id'   => $j->id_ruang,
            'slot_id'      => $j->id_slot_mulai,
            'sks'          => $j->durasi_sks,
            'hari'         => strtolower($j->hari->nama_hari ?? 'senin'),
        ])->toJson();

        $adaJadwalOtomatis = Jadwal::where('id_tahunakademik', $idTahun)->exists();

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

        // Validasi pemetaan hari & slot
        $isSlotValid = \App\Models\Hari::where('id_hari', $request->hari_id)
            ->whereHas('slotWaktus', function($q) use ($request) {
                $q->where('slot_waktu.id_slot', $request->slot_id);
            })->exists();

        if (!$isSlotValid) {
            return response()->json([
                'success' => false,
                'message' => 'Slot waktu tidak aktif atau tidak valid untuk hari tersebut.',
            ], 422);
        }

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
     * Hapus semua jadwal untuk satu tahun akademik
     */
    public function hapusSemua(Request $request)
    {
        $request->validate([
            'tahun_akademik_id' => 'required|integer|exists:tahun_akademik,id_tahunakademik',
        ]);

        $idTahun = $request->tahun_akademik_id;
        $prodiId = ProdiFilter::getProdiId();
        
        if ($prodiId) {
            $kelasIds = \App\Models\Kelas::where('id_prodi', $prodiId)->pluck('id_kelas');
            Jadwal::where('id_tahunakademik', $idTahun)->whereIn('id_kelas', $kelasIds)->delete();
        } else {
            Jadwal::where('id_tahunakademik', $idTahun)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil direset.'
        ]);
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

        // Cek ada tidaknya jadwal
        $adaOtomatis = Jadwal::where('id_tahunakademik', $idTahun)->exists();

        if (!$adaOtomatis) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada jadwal pada tahun akademik ini. Buat jadwal atau jalankan Algoritma Genetika terlebih dahulu.',
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
                'ruangan'    => $j->ruangan?->nama_ruang             ?? '',
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
