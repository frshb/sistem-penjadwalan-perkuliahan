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
use Illuminate\Support\Facades\DB;
use App\Helpers\ProdiFilter;

class JadwalController extends Controller
{
    /**
     * Helper privat untuk menentukan redirect otomatis tahun akademik berdasarkan role.
     */
    private function getTargetTahunForUser()
    {
        $user = Auth::user();
        $roleName = strtolower($user->role->nama_role ?? '');

        if ($roleName === 'dekan') {
            // Untuk Dekan: utamakan tahun akademik aktif yang baru saja diajukan oleh Admin (menunggu_persetujuan)
            $targetYear = TahunAkademik::where('status_aktif', 1)
                ->where('status_validasi', 'menunggu_persetujuan')
                ->orderBy('validated_at', 'desc')
                ->orderBy('id_tahunakademik', 'desc')
                ->first();

            // Jika tidak ada yang sedang menunggu persetujuan, ambil tahun akademik aktif yang disetujui / paling baru diperbarui
            if (!$targetYear) {
                $targetYear = TahunAkademik::where('status_aktif', 1)
                    ->orderBy('id_tahunakademik', 'desc')
                    ->first();
            }
            return $targetYear;
        }

        if ($roleName === 'sekretaris prodi') {
            $targetYear = TahunAkademik::where('status_aktif', 1)
                ->where('status_validasi', 'review_sekprodi')
                ->orderBy('id_tahunakademik', 'desc')
                ->first();

            if (!$targetYear) {
                $targetYear = TahunAkademik::where('status_aktif', 1)
                    ->orderBy('id_tahunakademik', 'desc')
                    ->first();
            }
            return $targetYear;
        }

        if ($roleName === 'kaprodi') {
            $targetYear = TahunAkademik::where('status_aktif', 1)
                ->where('status_validasi', 'review_kaprodi')
                ->orderBy('id_tahunakademik', 'desc')
                ->first();

            if (!$targetYear) {
                $targetYear = TahunAkademik::where('status_aktif', 1)
                    ->orderBy('id_tahunakademik', 'desc')
                    ->first();
            }
            return $targetYear;
        }

        if ($roleName === 'dosen') {
            // Untuk Dosen: utamakan tahun akademik aktif yang baru saja di-ACC / disetujui oleh Dekan
            $targetYear = TahunAkademik::where('status_aktif', 1)
                ->where('status_validasi', 'disetujui')
                ->orderBy('validated_at', 'desc')
                ->orderBy('id_tahunakademik', 'desc')
                ->first();

            // Jika tidak ada yang disetujui, fallback ke tahun akademik aktif paling baru diperbarui
            if (!$targetYear) {
                $targetYear = TahunAkademik::where('status_aktif', 1)
                    ->orderBy('id_tahunakademik', 'desc')
                    ->first();
            }
            return $targetYear;
        }

        return null;
    }

    /**
     * Cek hak akses ke tahun akademik (non-admin tidak dapat mengakses tahun akademik nonaktif).
     */
    private function checkAccessTahunAkademik($idTahun)
    {
        $tahunAkademik = TahunAkademik::findOrFail($idTahun);

        if (!$tahunAkademik->status_aktif) {
            abort(403, 'Anda tidak memiliki akses ke tahun akademik yang dinonaktifkan.');
        }

        return $tahunAkademik;
    }

    /**
     * Endpoint utama /modul-penjadwalan
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $roleName = strtolower($user->role->nama_role ?? '');

        if (in_array($roleName, ['dekan', 'dosen', 'sekretaris prodi', 'kaprodi'])) {
            $targetYear = $this->getTargetTahunForUser();
            if ($targetYear) {
                return redirect()->route('jadwal.manual', ['tahun' => $targetYear->id_tahunakademik]);
            }
        }

        return redirect()->route('jadwal.pilih-tahun');
    }

    /**
     * Halaman pilih tahun akademik penjadwalan
     */
    public function pilihTahun()
    {
        $user = Auth::user();
        $roleName = strtolower($user->role->nama_role ?? '');

        // Role Dekan, Dosen, Kaprodi & Sekprodi tidak perlu pilih tahun dulu, langsung masuk ke penyesuaian jadwal aktif!
        if (in_array($roleName, ['dekan', 'dosen', 'sekretaris prodi', 'kaprodi'])) {
            $targetYear = $this->getTargetTahunForUser();
            if ($targetYear) {
                return redirect()->route('jadwal.manual', ['tahun' => $targetYear->id_tahunakademik]);
            }
        }

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
            $user = Auth::user();
            $roleName = strtolower($user->role->nama_role ?? '');

            if (in_array($roleName, ['dekan', 'dosen', 'sekretaris prodi', 'kaprodi'])) {
                $targetYear = $this->getTargetTahunForUser();
                if ($targetYear) {
                    return redirect()->route('jadwal.manual', ['tahun' => $targetYear->id_tahunakademik]);
                }
            }

            return redirect()->route('jadwal.pilih-tahun');
        }

        $tahunAkademik = $this->checkAccessTahunAkademik($idTahun);
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

        $userRole    = strtolower(Auth::user()->role->nama_role ?? '');
        $canMoveCard = Auth::user()->role->hasPermissionAccess('modul_penjadwalan', 'Penyesuaian Jadwal', 'edit');
        $status      = $tahunAkademik->status_validasi ?? 'draft';

        // ⚠️ Aturan Visibilitas Jadwal Berdasarkan Role
        $showJadwal = false;

        if ($userRole === 'admin') {
            $showJadwal = true;
        } elseif (in_array($userRole, ['sekretaris prodi', 'kaprodi'])) {
            // Sekprodi & Kaprodi: Muncul setelah Admin klik review ke Sekprodi (status bukan 'draft')
            if ($status !== 'draft') {
                $showJadwal = true;
            }
        } elseif ($userRole === 'dekan') {
            // Dekan: Muncul setelah Admin klik ajukan validasi ke dekan
            if (in_array($status, ['menunggu_persetujuan', 'revisi', 'disetujui'])) {
                $showJadwal = true;
            }
        } elseif ($status === 'disetujui') {
            // Dosen / Pengguna lainnya HANYA dapat melihat setelah DISETUJUI & DIPUBLIKASIKAN oleh Dekan
            $showJadwal = true;
        }

        if (!$showJadwal) {
            $jadwalTersimpan = collect();
            $kelas           = collect();
        }

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
            'is_read_only' => !$canMoveCard || ($prodiId ? (($j->kelas->id_prodi ?? null) != $prodiId) : false),
            'is_other_prodi' => $prodiId ? (($j->kelas->id_prodi ?? null) != $prodiId) : false,
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

        $userRole = strtolower(Auth::user()->role->nama_role ?? '');
        if (!in_array($userRole, ['admin', 'sekretaris prodi'])) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki wewenang untuk mengubah letak jadwal.',
            ], 403);
        }

        $this->checkAccessTahunAkademik($request->tahun_akademik_id);

        $kelas      = Kelas::with(['matakuliah', 'dosen'])->findOrFail($request->kelas_id);
        $prodiId    = ProdiFilter::getProdiId();
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
     * Simpan Bulk (Super Cepat — 1 Request HTTP & Otomatis Simpan ke Perbandingan Hasil)
     */
    public function simpanBulk(Request $request)
    {
        $request->validate([
            'tahun_akademik_id' => 'required|integer|exists:tahun_akademik,id_tahunakademik',
            'items'             => 'required|array',
            'label'             => 'nullable|string|max:255',
        ]);

        $userRole = strtolower(Auth::user()->role->nama_role ?? '');
        if ($userRole !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Admin yang dapat menyimpan versi jadwal.',
            ], 403);
        }

        $idTahun    = (int)$request->tahun_akademik_id;
        $this->checkAccessTahunAkademik($idTahun);
        $items      = $request->items;
        $labelInput = trim($request->input('label', ''));
        $trialLabel = $labelInput ?: ('Penyesuaian Manual (' . now()->translatedFormat('H:i') . ')');

        DB::transaction(function () use ($idTahun, $items, $trialLabel) {
            foreach ($items as $item) {
                $kelasId = $item['kelas_id'] ?? null;
                $slotId  = $item['slot_id'] ?? null;
                $hariId  = $item['hari_id'] ?? null;
                $ruangId = $item['ruang_id'] ?? null;
                $dosenId = $item['dosen_id'] ?? null;
                $durasi  = $item['durasi_sks'] ?? 1;

                if (!$kelasId || !$slotId || !$hariId) continue;

                $kelas = Kelas::with(['matakuliah', 'dosen'])->find($kelasId);
                $kodeMatkul = $kelas->matakuliah->kode_matkul ?? null;
                if (!$kodeMatkul) continue;

                Jadwal::updateOrCreate(
                    [
                        'id_kelas'         => $kelasId,
                        'id_tahunakademik' => $idTahun,
                    ],
                    [
                        'kode_matkul'      => $kodeMatkul,
                        'id_slot_mulai'    => $slotId,
                        'durasi_sks'       => $durasi,
                        'id_hari'          => $hariId,
                        'id_ruang'         => $ruangId ?: Ruangan::orderBy('id_ruang')->value('id_ruang'),
                        'id_dosen'         => $dosenId ?: ($kelas->dosen?->id_dosen ?? null),
                        'is_manual'        => 1,
                    ]
                );
            }

            // OTOMATIS SIMPAN KE PERBANDINGAN HASIL DENGAN LABEL USER
            $this->syncToJadwalTrial($idTahun, $trialLabel);
        });

        return response()->json([
            'success' => true,
            'message' => 'Jadwal & Uji Coba "' . $trialLabel . '" berhasil disimpan!'
        ]);
    }

    /**
     * Hapus satu slot jadwal
     */
    public function hapusSlot($id)
    {
        $userRole = strtolower(Auth::user()->role->nama_role ?? '');
        if (!in_array($userRole, ['admin', 'sekretaris prodi'])) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki wewenang untuk menghapus slot jadwal.',
            ], 403);
        }

        $jadwal = Jadwal::findOrFail($id);
        $this->checkAccessTahunAkademik($jadwal->id_tahunakademik);
        $jadwal->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Hapus Bulk Slot Jadwal (Super Cepat)
     */
    public function hapusSlotsBulk(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids) && $request->has('id')) {
            $ids = [$request->input('id')];
        }

        if (!empty($ids)) {
            $tahunId = Jadwal::whereIn('id_jadwal', $ids)->value('id_tahunakademik');
            if ($tahunId) {
                $this->checkAccessTahunAkademik($tahunId);
            }
            Jadwal::whereIn('id_jadwal', $ids)->delete();

        }

        return response()->json(['success' => true]);
    }

    /**
     * Helper Sinkronisasi Otomatis ke Table Jadwal Trials (Perbandingan Hasil)
     */
    protected function syncToJadwalTrial($idTahun, $sourceLabel = 'Penyesuaian Workspace')
    {
        try {
            $jadwals = Jadwal::with(['kelas.matakuliah', 'kelas.dosen', 'ruangan', 'hari', 'slotMulai'])
                ->where('id_tahunakademik', $idTahun)
                ->get();

            if ($jadwals->isEmpty()) return;

            $jadwalList = $jadwals->map(fn($j) => [
                'id_kelas'       => $j->id_kelas,
                'kelas_id'       => $j->id_kelas,
                'id_dosen'       => $j->id_dosen,
                'dosen_id'       => $j->id_dosen,
                'id_ruang'       => $j->id_ruang,
                'ruangan_id'     => $j->id_ruang,
                'id_hari'        => $j->id_hari,
                'hari_id'        => $j->id_hari,
                'id_slot_mulai'  => $j->id_slot_mulai,
                'slot_id'        => $j->id_slot_mulai,
                'durasi_sks'     => $j->durasi_sks,
                'sks'            => $j->durasi_sks,
                'kode_matkul'    => $j->kode_matkul,
                'kode_mk'        => $j->kode_matkul,
                'nama_kelas'     => $j->kelas->nama_kelas ?? '',
                'nama_matkul'    => $j->kelas->matakuliah->nama_matkul ?? '',
                'nama_dosen'     => $j->dosen?->nama_dosen ?? '',
                'nama_ruang'     => $j->ruangan?->nama_ruang ?? '',
                'nama_hari'      => $j->hari?->nama_hari ?? '',
            ]);

            \App\Models\JadwalTrial::create([
                'id_tahunakademik'  => $idTahun,
                'label'             => $sourceLabel,
                'fitness'           => 100.0,
                'generasi'          => 1,
                'total_kelas'       => $jadwals->count(),
                'dosen_conflicts'   => 0,
                'ruangan_conflicts' => 0,
                'kelas_conflicts'   => 0,
                'soft_violations'   => 0,
                'jadwal_json'       => $jadwalList->toJson(),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error syncToJadwalTrial: ' . $e->getMessage());
        }
    }

    /**
     * Hapus semua jadwal untuk satu tahun akademik
     */
    public function hapusSemua(Request $request)
    {
        $userRole = strtolower(Auth::user()->role->nama_role ?? '');
        if ($userRole !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Admin yang dapat mereset jadwal.',
            ], 403);
        }

        $request->validate([
            'tahun_akademik_id' => 'required|integer|exists:tahun_akademik,id_tahunakademik',
        ]);

        $idTahun = $request->tahun_akademik_id;
        $this->checkAccessTahunAkademik($idTahun);
        $prodiId = ProdiFilter::getProdiId();
        
        if ($prodiId) {
            $kelasIds = \App\Models\Kelas::where('id_prodi', $prodiId)->pluck('id_kelas');
            Jadwal::where('id_tahunakademik', $idTahun)->whereIn('id_kelas', $kelasIds)->delete();
        } else {
            Jadwal::where('id_tahunakademik', $idTahun)->delete();
        }

        // RESET STATUS VALIDASI KEMBALI KE DRAFT SAAT JADWAL KOSONG/DI-RESET
        TahunAkademik::where('id_tahunakademik', $idTahun)->update([
            'status_validasi' => 'draft',
            'catatan_revisi'  => null,
        ]);

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
        $userRole = strtolower(Auth::user()->role->nama_role ?? '');
        if ($userRole !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Admin yang dapat mengoptimasi jadwal.',
            ], 403);
        }

        $request->validate([
            'tahun_akademik_id' => 'required|integer|exists:tahun_akademik,id_tahunakademik',
        ]);

        $idTahun = (int) $request->tahun_akademik_id;
        $this->checkAccessTahunAkademik($idTahun);

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
        $this->checkAccessTahunAkademik($idTahun);

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
