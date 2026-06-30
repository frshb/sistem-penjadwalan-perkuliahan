<?php

namespace App\Services\GeneticAlgorithm;

use App\Models\Jadwal;
use App\Models\Slot_waktu;
use App\Models\Ruangan;
use Illuminate\Support\Collection;

/**
 * OptimizeJadwal
 *
 * Mengoptimalkan jadwal hasil penjadwalan (otomatis ATAU manual) dengan
 * mendeteksi dan memperbaiki SEMUA pelanggaran constraint, level setara
 * dengan Algoritma Genetika (App\Services\GeneticAlgorithm\GeneticScheduler
 * & Chromosome). Constraint yang ditegakkan di sini:
 *
 *  [HC1/HC2/HC4/HC5/HC8/HC11] Dosen / Ruangan / Kelas bentrok waktu
 *      → tipe: bentrok_dosen, bentrok_ruangan, bentrok_kelas
 *  [HC6]  Kelas harus punya dosen pengampu
 *      → tipe: dosen_kosong (TIDAK bisa auto-fix — perlu assign manual)
 *  [HC10] Ruangan harus terdaftar di pivot matkul_ruang (atau ruangan global jika kosong)
 *      → tipe: ruangan_tidak_valid
 *  [HC12] Kelas sore (-S) wajib di slot malam; kelas reguler wajib selesai sebelum malam
 *      → tipe: slot_waktu_salah
 *  [HC13] Jenis matkul harus sesuai tipe ruangan (praktikum→lab/hybrid, teori→reguler/hybrid)
 *      → tipe: tipe_ruangan_salah
 *  [HC14] Dosen tidak boleh mengajar > 8 SKS dalam satu hari
 *      → tipe: dosen_kelebihan_sks
 *  [HC15] Pasangan kelas paralel (matkul sama + dosen sama, persis 2 kelas non-S)
 *         wajib hari sama, berurutan dengan gap TEPAT 1 slot
 *      → tipe: pasangan_tidak_sesuai
 *  [HC16] Slot ke-6 (≈12:00, Sholat Jumat) pada hari Jumat (id_hari=5) tidak boleh
 *         ditempati/dilewati kelas apapun
 *      → tipe: bentrok_jumat_sholat
 *
 * Catatan desain: logika deteksi (terutama HC12, HC15, HC16) DISENGAJA dibuat
 * seidentik mungkin dengan GeneticScheduler/Chromosome/Gene supaya hasil
 * "Optimasi Jadwal" di Penyesuaian Jadwal (manual) konsisten dengan jadwal
 * yang dihasilkan Algoritma Genetika.
 */
class OptimizeJadwal
{
    /** ID slot istirahat (slot ke-6 berdasarkan urutan jam_ke) */
    private int $slotIstirahat;

    /** Jam mulai sesi malam (menit dari 00:00) */
    private int $menitBatasMalam;

    /** [HC16] id_hari untuk Jumat & id_slot yang wajib steril dari kelas apapun */
    private int $hariJumat       = 5;
    private int $slotSholatJumat = 6;

    /** [HC14] Maksimum total SKS yang boleh diajar satu dosen dalam satu hari */
    private int $maxSksDosenPerHari = 8;

    public function __construct()
    {
        // Slot istirahat = slot dengan jam_ke = 6
        $slotIstirahat = Slot_waktu::orderBy('jam_ke')->skip(5)->first();
        $this->slotIstirahat = $slotIstirahat?->id_slot ?? 0;

        // Batas malam: 16:30 (dalam menit)
        $this->menitBatasMalam = 16 * 60 + 30;
    }

    // ─────────────────────────────────────────────────────────────
    //  ENTRY POINT UTAMA
    // ─────────────────────────────────────────────────────────────

    /**
     * Jalankan optimasi jadwal untuk satu tahun akademik.
     *
     * @param int $idTahunAkademik
     * @return array{
     *   total_jadwal: int,
     *   bentrok_awal: int,
     *   diperbaiki: int,
     *   gagal: int,
     *   detail_bentrok_sisa: array
     * }
     */
    public function optimasi(int $idTahunAkademik): array
    {
        // Ambil semua jadwal otomatis (is_manual = 0) untuk tahun akademik ini
        $jadwalList = Jadwal::with([
            'kelas.matakuliah',
            'kelas.dosen',
            'kelas.prodi',
            'kelas.matakuliah.ruangans',
            'slotMulai',
            'hari',
            'ruangan',
        ])
        ->where('id_tahunakademik', $idTahunAkademik)
        ->where('is_manual', 0)
        ->get();

        if ($jadwalList->isEmpty()) {
            return [
                'total_jadwal'       => 0,
                'bentrok_awal'       => 0,
                'diperbaiki'         => 0,
                'gagal'              => 0,
                'detail_bentrok_sisa'=> [],
                'pesan'              => 'Tidak ada jadwal dari penjadwalan otomatis.',
            ];
        }

        // Deteksi semua pelanggaran constraint (HC1-HC16)
        $bentrokList = $this->deteksiBentrok($jadwalList);
        $bentrokAwal = count($bentrokList);

        if ($bentrokAwal === 0) {
            return [
                'total_jadwal'       => $jadwalList->count(),
                'bentrok_awal'       => 0,
                'diperbaiki'         => 0,
                'gagal'              => 0,
                'detail_bentrok_sisa'=> [],
                'pesan'              => 'Jadwal sudah optimal, tidak ada bentrok.',
            ];
        }

        // Ambil semua slot & ruangan yang tersedia
        $semuaSlot    = Slot_waktu::orderBy('jam_ke')->get();
        $semuaRuangan = Ruangan::all();

        // Kumpulkan ID jadwal yang melanggar constraint apapun (tanpa duplikasi)
        $idBentrok = collect($bentrokList)
            ->pluck('jadwal_id')
            ->unique()
            ->values();

        $diperbaiki = 0;
        $gagal      = 0;
        $sisaBentrok = [];

        // [HC15] Tangani pasangan paralel LEBIH DULU & sekaligus (bukan satu-satu),
        // karena memperbaiki satu sisi pasangan tanpa partner-nya akan merusak gap-1.
        $jadwalTerkiniAwal = Jadwal::with([
                'kelas.matakuliah',
                'kelas.dosen',
                'kelas.matakuliah.ruangans',
                'slotMulai',
                'hari',
                'ruangan',
            ])
            ->where('id_tahunakademik', $idTahunAkademik)
            ->where('is_manual', 0)
            ->get();
        $pasanganList = $this->buildParallelPairs($jadwalTerkiniAwal);
        $idJadwalTertanganiPasangan = [];

        foreach ($pasanganList as [$jadwalA, $jadwalB]) {
            // Refresh kondisi terkini (jadwal lain mungkin sudah berubah akibat
            // perbaikan pasangan sebelumnya di loop ini), TANPA relasi penuh
            // karena hanya dipakai untuk hitung bentrok/SKS, bukan ditampilkan.
            $jadwalTerkini = Jadwal::where('id_tahunakademik', $idTahunAkademik)
                ->where('is_manual', 0)
                ->get();

            if ($this->pasanganSudahValid($jadwalA, $jadwalB)) {
                continue; // Sudah sesuai HC15, tidak perlu diperbaiki
            }

            $berhasil = $this->perbaikiPasangan(
                $jadwalA, $jadwalB, $idTahunAkademik, $semuaSlot, $semuaRuangan, $jadwalTerkini
            );

            $idJadwalTertanganiPasangan[] = $jadwalA->id_jadwal;
            $idJadwalTertanganiPasangan[] = $jadwalB->id_jadwal;

            if ($berhasil) {
                $diperbaiki += 2;
            } else {
                $gagal += 2;
                // $jadwalA/$jadwalB sudah punya semua relasi (dari $jadwalTerkiniAwal),
                // jadi langsung dipakai tanpa query ulang.
                $jadwalTerkiniRefresh = Jadwal::where('id_tahunakademik', $idTahunAkademik)
                    ->where('is_manual', 0)->get();
                $sisaBentrok[] = $this->formatDetailBentrok($jadwalA, $jadwalTerkiniRefresh);
                $sisaBentrok[] = $this->formatDetailBentrok($jadwalB, $jadwalTerkiniRefresh);
            }
        }

        // Coba perbaiki sisa pelanggaran satu per satu (di luar pasangan HC15 yang sudah ditangani)
        foreach ($idBentrok as $jadwalId) {
            if (in_array($jadwalId, $idJadwalTertanganiPasangan, true)) {
                continue; // Sudah ditangani di tahap pasangan
            }

            // Refresh dari DB agar state selalu aktual setelah perbaikan sebelumnya
            $jadwal = Jadwal::with([
                'kelas.matakuliah',
                'kelas.dosen',
                'kelas.matakuliah.ruangans',
                'slotMulai',
                'hari',
            ])->find($jadwalId);

            if (!$jadwal) continue;

            // Cek apakah jadwal ini masih melanggar constraint apapun setelah perbaikan sebelumnya
            $jadwalTerkini = Jadwal::where('id_tahunakademik', $idTahunAkademik)
                ->where('is_manual', 0)
                ->get();

            $pelanggaran = $this->cekPelanggaranSatuJadwal($jadwal, $jadwalTerkini);
            if (empty($pelanggaran)) {
                $diperbaiki++;
                continue;
            }

            // [HC6] Dosen kosong tidak bisa auto-fix — langsung masuk sisa manual
            if (in_array('dosen_kosong', $pelanggaran, true)) {
                $gagal++;
                $sisaBentrok[] = $this->formatDetailBentrok($jadwal, $jadwalTerkini);
                continue;
            }

            $berhasil = $this->perbaikiJadwal($jadwal, $idTahunAkademik, $semuaSlot, $semuaRuangan, $pelanggaran);

            if ($berhasil) {
                $diperbaiki++;
            } else {
                $gagal++;
                $sisaBentrok[] = $this->formatDetailBentrok($jadwal, $jadwalTerkini);
            }
        }

        return [
            'total_jadwal'       => $jadwalList->count(),
            'bentrok_awal'       => $bentrokAwal,
            'diperbaiki'         => $diperbaiki,
            'gagal'              => $gagal,
            'detail_bentrok_sisa'=> $sisaBentrok,
            'pesan'              => $this->pesanHasil($diperbaiki, $gagal),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  DETEKSI BENTROK / PELANGGARAN CONSTRAINT (HC1-HC16)
    // ─────────────────────────────────────────────────────────────

    /**
     * Deteksi semua pelanggaran constraint dari collection jadwal.
     * Mengembalikan array bentrok, masing-masing berisi:
     *   jadwal_id, tipe, keterangan, jadwal_lawan (opsional)
     */
    public function deteksiBentrok(Collection $jadwalList): array
    {
        $bentrokList = [];
        $jadwalArr   = $jadwalList->values();
        $total       = $jadwalArr->count();

        // ── HC1/HC2/HC8/HC11: bentrok dosen/ruangan/kelas (pairwise) ──────────
        for ($i = 0; $i < $total; $i++) {
            for ($j = $i + 1; $j < $total; $j++) {
                $a = $jadwalArr[$i];
                $b = $jadwalArr[$j];

                // Hanya cek jika hari sama
                if ($a->id_hari !== $b->id_hari) continue;

                // Hitung range slot masing-masing
                $aStart = $a->id_slot_mulai;
                $aEnd   = $a->id_slot_mulai + $a->durasi_sks;
                $bStart = $b->id_slot_mulai;
                $bEnd   = $b->id_slot_mulai + $b->durasi_sks;

                $overlap = $aStart < $bEnd && $aEnd > $bStart;
                if (!$overlap) continue;

                // [HC1] Dosen sama
                if (
                    $a->id_dosen &&
                    $b->id_dosen &&
                    $a->id_dosen === $b->id_dosen
                ) {
                    $bentrokList[] = [
                        'jadwal_id'     => $a->id_jadwal,
                        'jadwal_lawan'  => $b->id_jadwal,
                        'tipe'          => 'bentrok_dosen',
                        'keterangan'    => 'Dosen ' . ($a->kelas->dosen->nama_dosen ?? '-') . ' mengajar dua kelas bersamaan',
                    ];
                    $bentrokList[] = [
                        'jadwal_id'     => $b->id_jadwal,
                        'jadwal_lawan'  => $a->id_jadwal,
                        'tipe'          => 'bentrok_dosen',
                        'keterangan'    => 'Dosen ' . ($b->kelas->dosen->nama_dosen ?? '-') . ' mengajar dua kelas bersamaan',
                    ];
                }

                // [HC2] Ruangan sama
                if (
                    $a->id_ruang &&
                    $b->id_ruang &&
                    $a->id_ruang === $b->id_ruang
                ) {
                    $bentrokList[] = [
                        'jadwal_id'     => $a->id_jadwal,
                        'jadwal_lawan'  => $b->id_jadwal,
                        'tipe'          => 'bentrok_ruangan',
                        'keterangan'    => 'Ruangan ' . ($a->ruangan?->nama_ruang ?? '-') . ' dipakai dua kelas sekaligus',
                    ];
                    $bentrokList[] = [
                        'jadwal_id'     => $b->id_jadwal,
                        'jadwal_lawan'  => $a->id_jadwal,
                        'tipe'          => 'bentrok_ruangan',
                        'keterangan'    => 'Ruangan ' . ($b->ruangan?->nama_ruang ?? '-') . ' dipakai dua kelas sekaligus',
                    ];
                }

                // [HC8/HC-New] Mahasiswa di kelas yang sama tidak boleh overlap jadwal
                $namaA = $a->kelas->nama_kelas ?? '';
                $namaB = $b->kelas->nama_kelas ?? '';
                if ($namaA !== '' && $namaA === $namaB) {
                    $bentrokList[] = [
                        'jadwal_id'     => $a->id_jadwal,
                        'jadwal_lawan'  => $b->id_jadwal,
                        'tipe'          => 'bentrok_kelas',
                        'keterangan'    => 'Kelompok Mahasiswa ' . $namaA . ' dijadwalkan pada dua kelas berbeda di waktu yang bersamaan',
                    ];
                    $bentrokList[] = [
                        'jadwal_id'     => $b->id_jadwal,
                        'jadwal_lawan'  => $a->id_jadwal,
                        'tipe'          => 'bentrok_kelas',
                        'keterangan'    => 'Kelompok Mahasiswa ' . $namaB . ' dijadwalkan pada dua kelas berbeda di waktu yang bersamaan',
                    ];
                }
            }
        }

        // ── Pelanggaran per-jadwal (tidak perlu pairwise) ─────────────────────
        foreach ($jadwalArr as $j) {

            // [HC6] Dosen harus ada
            if (!$j->id_dosen) {
                $bentrokList[] = [
                    'jadwal_id'    => $j->id_jadwal,
                    'jadwal_lawan' => null,
                    'tipe'         => 'dosen_kosong',
                    'keterangan'   => 'Kelas ' . ($j->kelas->nama_kelas ?? '-') . ' belum punya dosen pengampu',
                ];
            }

            // [HC10] Ruangan harus terdaftar di pivot matkul_ruang (jika MK punya daftar ruangan)
            $ruanganMk = $j->kelas->matakuliah->ruangans ?? collect();
            if ($ruanganMk->isNotEmpty() && $j->id_ruang) {
                $validIds = $ruanganMk->pluck('id_ruang')->map(fn($id) => (int) $id)->toArray();
                if (!in_array((int) $j->id_ruang, $validIds, true)) {
                    $bentrokList[] = [
                        'jadwal_id'    => $j->id_jadwal,
                        'jadwal_lawan' => null,
                        'tipe'         => 'ruangan_tidak_valid',
                        'keterangan'   => 'Ruangan ' . ($j->ruangan?->nama_ruang ?? '-') . ' tidak terdaftar untuk mata kuliah ini',
                    ];
                }
            }

            // [HC12] Slot waktu sesuai jenis kelas (reguler vs malam -S)
            if ($j->slotMulai && !$this->slotSesuaiJenisKelas($j->slotMulai, $j->kelas->nama_kelas ?? '')) {
                $bentrokList[] = [
                    'jadwal_id'    => $j->id_jadwal,
                    'jadwal_lawan' => null,
                    'tipe'         => 'slot_waktu_salah',
                    'keterangan'   => 'Kelas ' . ($j->kelas->nama_kelas ?? '-') . ' ditempatkan di slot waktu yang tidak sesuai jenisnya (pagi/malam)',
                ];
            }

            // [HC13] Tipe ruangan harus sesuai jenis matkul
            if ($j->ruangan && !$this->tipeRuanganSesuai($j->kelas->matakuliah->jenis ?? 'teori', $j->ruangan->tipe_ruangan ?? 'reguler')) {
                $bentrokList[] = [
                    'jadwal_id'    => $j->id_jadwal,
                    'jadwal_lawan' => null,
                    'tipe'         => 'tipe_ruangan_salah',
                    'keterangan'   => 'Ruangan ' . ($j->ruangan->nama_ruang ?? '-') . ' (tipe: ' . ($j->ruangan->tipe_ruangan ?? '-') . ') tidak sesuai untuk mata kuliah jenis ' . ($j->kelas->matakuliah->jenis ?? '-'),
                ];
            }

            // [HC16] Slot Sholat Jumat
            if ($this->overlapsSlotSholatJumat((int) $j->id_hari, (int) $j->id_slot_mulai, (int) $j->durasi_sks)) {
                $bentrokList[] = [
                    'jadwal_id'    => $j->id_jadwal,
                    'jadwal_lawan' => null,
                    'tipe'         => 'bentrok_jumat_sholat',
                    'keterangan'   => 'Kelas ' . ($j->kelas->nama_kelas ?? '-') . ' berlangsung pada slot Sholat Jumat',
                ];
            }
        }

        // ── [HC14] Dosen > 8 SKS dalam satu hari ──────────────────────────────
        $sksPerDosenHari = []; // [id_dosen][id_hari] = ['total' => int, 'jadwal_ids' => []]
        foreach ($jadwalArr as $j) {
            if (!$j->id_dosen) continue;
            $key = $j->id_dosen . '|' . $j->id_hari;
            $sksPerDosenHari[$key]['total'] = ($sksPerDosenHari[$key]['total'] ?? 0) + $j->durasi_sks;
            $sksPerDosenHari[$key]['jadwal_ids'][] = $j->id_jadwal;
            $sksPerDosenHari[$key]['nama_dosen']   = $j->kelas->dosen->nama_dosen ?? '-';
        }
        foreach ($sksPerDosenHari as $info) {
            if ($info['total'] > $this->maxSksDosenPerHari) {
                foreach ($info['jadwal_ids'] as $jid) {
                    $bentrokList[] = [
                        'jadwal_id'    => $jid,
                        'jadwal_lawan' => null,
                        'tipe'         => 'dosen_kelebihan_sks',
                        'keterangan'   => 'Dosen ' . $info['nama_dosen'] . ' mengajar ' . $info['total'] . ' SKS dalam satu hari (maks ' . $this->maxSksDosenPerHari . ')',
                    ];
                }
            }
        }

        // ── [HC15] Pasangan kelas paralel (matkul+dosen sama) ─────────────────
        $pasanganList = $this->buildParallelPairs($jadwalArr);
        foreach ($pasanganList as [$jadwalA, $jadwalB]) {
            if (!$this->pasanganSudahValid($jadwalA, $jadwalB)) {
                $namaA = $jadwalA->kelas->nama_kelas ?? '-';
                $namaB = $jadwalB->kelas->nama_kelas ?? '-';
                $bentrokList[] = [
                    'jadwal_id'    => $jadwalA->id_jadwal,
                    'jadwal_lawan' => $jadwalB->id_jadwal,
                    'tipe'         => 'pasangan_tidak_sesuai',
                    'keterangan'   => "Kelas paralel {$namaA} & {$namaB} (dosen sama) harus di hari sama dengan jeda 1 slot",
                ];
                $bentrokList[] = [
                    'jadwal_id'    => $jadwalB->id_jadwal,
                    'jadwal_lawan' => $jadwalA->id_jadwal,
                    'tipe'         => 'pasangan_tidak_sesuai',
                    'keterangan'   => "Kelas paralel {$namaA} & {$namaB} (dosen sama) harus di hari sama dengan jeda 1 slot",
                ];
            }
        }

        // Deduplikasi berdasarkan jadwal_id (ambil satu entry per jadwal —
        // entry pertama yang ditemukan menentukan tipe yang dilaporkan)
        $seen  = [];
        $unik  = [];
        foreach ($bentrokList as $b) {
            if (!isset($seen[$b['jadwal_id']])) {
                $seen[$b['jadwal_id']] = true;
                $unik[] = $b;
            }
        }

        return $unik;
    }

    // ─────────────────────────────────────────────────────────────
    //  CEK SEMUA PELANGGARAN UNTUK SATU JADWAL (dipakai saat perbaikan iteratif)
    // ─────────────────────────────────────────────────────────────

    /**
     * Cek constraint apa saja yang dilanggar oleh satu jadwal, relatif
     * terhadap kondisi $semuaJadwal saat ini. Return array tipe pelanggaran
     * (string), kosong jika tidak ada pelanggaran.
     */
    private function cekPelanggaranSatuJadwal(Jadwal $jadwal, Collection $semuaJadwal): array
    {
        $pelanggaran = [];

        // [HC1/HC2/HC8] Bentrok dengan jadwal lain
        foreach ($semuaJadwal as $lain) {
            if ($lain->id_jadwal === $jadwal->id_jadwal) continue;
            if ($lain->id_hari  !== $jadwal->id_hari)   continue;

            $aStart = $jadwal->id_slot_mulai;
            $aEnd   = $jadwal->id_slot_mulai + $jadwal->durasi_sks;
            $bStart = $lain->id_slot_mulai;
            $bEnd   = $lain->id_slot_mulai + $lain->durasi_sks;

            if (!($aStart < $bEnd && $aEnd > $bStart)) continue;

            if ($jadwal->id_dosen && $lain->id_dosen && $jadwal->id_dosen === $lain->id_dosen) {
                $pelanggaran[] = 'bentrok_dosen';
            }
            if ($jadwal->id_ruang && $lain->id_ruang && $jadwal->id_ruang === $lain->id_ruang) {
                $pelanggaran[] = 'bentrok_ruangan';
            }
            if ($jadwal->id_kelas === $lain->id_kelas) {
                $pelanggaran[] = 'bentrok_kelas';
            }
        }

        // [HC6] Dosen kosong
        if (!$jadwal->id_dosen) {
            $pelanggaran[] = 'dosen_kosong';
        }

        // [HC10] Ruangan tidak valid
        $ruanganMk = $jadwal->kelas->matakuliah->ruangans ?? collect();
        if ($ruanganMk->isNotEmpty() && $jadwal->id_ruang) {
            $validIds = $ruanganMk->pluck('id_ruang')->map(fn($id) => (int) $id)->toArray();
            if (!in_array((int) $jadwal->id_ruang, $validIds, true)) {
                $pelanggaran[] = 'ruangan_tidak_valid';
            }
        }

        // [HC12] Slot waktu salah
        if ($jadwal->slotMulai && !$this->slotSesuaiJenisKelas($jadwal->slotMulai, $jadwal->kelas->nama_kelas ?? '')) {
            $pelanggaran[] = 'slot_waktu_salah';
        }

        // [HC13] Tipe ruangan salah
        $ruanganObj = $jadwal->id_ruang ? Ruangan::find($jadwal->id_ruang) : null;
        if ($ruanganObj && !$this->tipeRuanganSesuai($jadwal->kelas->matakuliah->jenis ?? 'teori', $ruanganObj->tipe_ruangan ?? 'reguler')) {
            $pelanggaran[] = 'tipe_ruangan_salah';
        }

        // [HC14] Dosen kelebihan SKS di hari ini
        if ($jadwal->id_dosen) {
            $totalSksHariIni = $semuaJadwal
                ->where('id_dosen', $jadwal->id_dosen)
                ->where('id_hari', $jadwal->id_hari)
                ->sum('durasi_sks');
            if ($totalSksHariIni > $this->maxSksDosenPerHari) {
                $pelanggaran[] = 'dosen_kelebihan_sks';
            }
        }

        // [HC16] Slot Sholat Jumat
        if ($this->overlapsSlotSholatJumat((int) $jadwal->id_hari, (int) $jadwal->id_slot_mulai, (int) $jadwal->durasi_sks)) {
            $pelanggaran[] = 'bentrok_jumat_sholat';
        }

        // [HC15] Pasangan paralel — dicek terpisah (lihat perbaikiPasangan),
        // tidak dimasukkan di sini karena penanganannya butuh kedua sisi sekaligus.

        return array_unique($pelanggaran);
    }

    /** @deprecated gunakan cekPelanggaranSatuJadwal() — dipertahankan untuk kompatibilitas internal lama */
    private function apakahMasihBentrok(Jadwal $jadwal, Collection $semuaJadwal): bool
    {
        $p = $this->cekPelanggaranSatuJadwal($jadwal, $semuaJadwal);
        // Hanya hitung jenis bentrok pairwise klasik untuk method lama ini
        return in_array('bentrok_dosen', $p, true)
            || in_array('bentrok_ruangan', $p, true)
            || in_array('bentrok_kelas', $p, true);
    }

    // ─────────────────────────────────────────────────────────────
    //  PERBAIKI SATU JADWAL (HC1/HC2/HC8/HC10/HC12/HC13/HC14/HC16)
    // ─────────────────────────────────────────────────────────────

    /**
     * Perbaiki satu jadwal sesuai jenis pelanggaran yang terdeteksi.
     * Strategi berbeda untuk tiap kategori pelanggaran:
     *  - bentrok_dosen / bentrok_ruangan / bentrok_kelas / slot_waktu_salah /
     *    dosen_kelebihan_sks / bentrok_jumat_sholat
     *      → cari slot+hari baru yang aman dari SEMUA constraint sekaligus.
     *  - ruangan_tidak_valid / tipe_ruangan_salah
     *      → coba ganti ruangan saja dulu (hari/slot tetap), baru fallback
     *        cari slot+hari+ruangan baru jika tidak ada ruangan pengganti yang aman.
     */
    private function perbaikiJadwal(
        Jadwal $jadwal,
        int $idTahunAkademik,
        Collection $semuaSlot,
        Collection $semuaRuangan,
        array $pelanggaran
    ): bool {
        $sks         = $jadwal->durasi_sks;
        $hariAsal    = $jadwal->id_hari;
        $dosenId     = $jadwal->id_dosen;
        $kelasId     = $jadwal->id_kelas;
        $namaKelas   = $jadwal->kelas->nama_kelas ?? '';

        // Pool ruangan: utamakan ruangan yang terkait MK, fallback semua ruangan
        $ruanganMk   = $jadwal->kelas->matakuliah->ruangans ?? collect();
        $jenisMatkul = $jadwal->kelas->matakuliah->jenis ?? 'teori';
        $poolRuanganMk = $ruanganMk->isNotEmpty() ? $ruanganMk : $semuaRuangan;
        // Filter pool agar tipe ruangan sesuai jenis matkul (HC13) sejak awal
        $poolRuanganValid = $poolRuanganMk->filter(
            fn($r) => $this->tipeRuanganSesuai($jenisMatkul, $r->tipe_ruangan ?? 'reguler')
        );
        if ($poolRuanganValid->isEmpty()) {
            // Tidak ada ruangan dengan tipe yang cocok — pakai pool asli sebagai fallback
            $poolRuanganValid = $poolRuanganMk;
        }

        // ── Pelanggaran yang HANYA soal ruangan (hari/slot tetap valid) ───────
        $hanyaSoalRuangan = empty(array_diff($pelanggaran, ['ruangan_tidak_valid', 'tipe_ruangan_salah', 'bentrok_ruangan']));

        if ($hanyaSoalRuangan) {
            $ruanganBaru = $this->cariRuanganBebas(
                $jadwal->id_hari, $jadwal->id_slot_mulai, $sks,
                $idTahunAkademik, $jadwal->id_jadwal, $poolRuanganValid
            );
            if ($ruanganBaru) {
                $jadwal->update(['id_ruang' => $ruanganBaru->id_ruang]);
                return true;
            }
            // Tidak ketemu ruangan pengganti di slot yang sama — lanjut ke
            // strategi penuh (cari hari+slot+ruangan baru) di bawah.
        }

        // ── Strategi penuh: cari hari+slot+ruangan baru ───────────────────────
        $hariList = $this->urutanHari($hariAsal, $namaKelas);

        foreach ($hariList as $hariId) {
            // Cari slot kosong di hari ini yang aman dari HC12 & HC16 & HC14
            $slotKosong = $this->cariSlotKosong(
                $hariId, $sks, $dosenId, $kelasId,
                $idTahunAkademik, $jadwal->id_jadwal,
                $semuaSlot, $namaKelas
            );

            foreach ($slotKosong as $slotMulai) {
                // [HC14] Pastikan dosen tidak melebihi 8 SKS/hari jika dipindah ke sini
                if ($dosenId && !$this->dosenAmanSksDiHari(
                    $dosenId, $hariId, $sks, $idTahunAkademik, $jadwal->id_jadwal
                )) {
                    continue;
                }

                // Cari ruangan yang bebas & valid di slot ini
                $ruangan = $this->cariRuanganBebas(
                    $hariId, $slotMulai, $sks,
                    $idTahunAkademik, $jadwal->id_jadwal,
                    $poolRuanganValid
                );

                if (!$ruangan) continue;

                // Update jadwal ke slot + ruangan baru
                $jadwal->update([
                    'id_hari'       => $hariId,
                    'id_slot_mulai' => $slotMulai,
                    'id_ruang'      => $ruangan->id_ruang,
                ]);

                return true;
            }
        }

        return false;
    }

    // ─────────────────────────────────────────────────────────────
    //  [HC15] PASANGAN KELAS PARALEL — deteksi & perbaikan
    // ─────────────────────────────────────────────────────────────

    /**
     * [HC15] Bangun daftar pasangan kelas paralel dari collection jadwal:
     * kelompokkan berdasarkan (kode_matkul, id_dosen), exclude kelas malam
     * (-S), ambil grup yang punya PERSIS 2 kelas. Identik dengan logika
     * GeneticScheduler::buildParallelPairs().
     *
     * @param Collection $jadwalList
     * @return array  [[Jadwal $a, Jadwal $b], ...] — $a = abjad nama_kelas lebih dulu
     */
    private function buildParallelPairs(Collection $jadwalList): array
    {
        $groups = []; // "kode_matkul|id_dosen" => [Jadwal, ...]

        foreach ($jadwalList as $j) {
            $namaKelas = $j->kelas->nama_kelas ?? '';
            if ($this->detectKelasS($namaKelas)) {
                continue; // HC15 tidak berlaku untuk kelas malam
            }
            $kodeMk = $j->kelas->matakuliah->kode_matkul ?? null;
            $did    = $j->id_dosen;
            if (!$kodeMk || !$did) {
                continue;
            }
            $key = $kodeMk . '|' . $did;
            $groups[$key][] = $j;
        }

        $pairs = [];
        foreach ($groups as $list) {
            if (count($list) !== 2) {
                continue; // Hanya proses pasangan persis 2 kelas non-S
            }
            usort($list, fn($a, $b) => strcasecmp($a->kelas->nama_kelas ?? '', $b->kelas->nama_kelas ?? ''));
            $pairs[] = [$list[0], $list[1]];
        }

        return $pairs;
    }

    /**
     * [HC15] Cek apakah pasangan jadwal A & B sudah memenuhi constraint:
     * hari sama + gap tepat 1 slot.
     */
    private function pasanganSudahValid(Jadwal $a, Jadwal $b): bool
    {
        if ($a->id_hari !== $b->id_hari) {
            return false;
        }

        $aEnd = $a->id_slot_mulai + $a->durasi_sks - 1;
        $bEnd = $b->id_slot_mulai + $b->durasi_sks - 1;

        if ($a->id_slot_mulai <= $b->id_slot_mulai) {
            $earlierEnd  = $aEnd;
            $laterStart  = $b->id_slot_mulai;
        } else {
            $earlierEnd  = $bEnd;
            $laterStart  = $a->id_slot_mulai;
        }

        $gap = $laterStart - $earlierEnd - 1;
        return $gap === 1;
    }

    /**
     * [HC15] Reposisi pasangan A & B sekaligus: cari hari + slotMulaiA yang
     * membuat keduanya hari sama, gap tepat 1 slot, bebas dari bentrok
     * dosen/ruangan dengan jadwal lain, dan tidak overlap slot Sholat Jumat.
     */
    private function perbaikiPasangan(
        Jadwal $jadwalA,
        Jadwal $jadwalB,
        int $idTahunAkademik,
        Collection $semuaSlot,
        Collection $semuaRuangan,
        Collection $jadwalTerkini
    ): bool {
        $durasiA = $jadwalA->durasi_sks;
        $durasiB = $jadwalB->durasi_sks;
        $totalSpan = $durasiA + 1 + $durasiB;

        $maxSlotId = $semuaSlot->max('id_slot') ?? 14;
        // Asumsi sesi malam mulai di slot setelah jam 16:30 — cari slot pertama yang ≥ batas malam
        $slotMalamPertama = $semuaSlot
            ->filter(fn($s) => $this->menitDariWaktu($s->waktu_mulai) >= $this->menitBatasMalam)
            ->sortBy('id_slot')->first()?->id_slot ?? ($maxSlotId + 1);

        $maxStartA = max(1, $slotMalamPertama - $totalSpan);

        $dosenId = $jadwalA->id_dosen; // Dosen sama (syarat pasangan)

        // Pool ruangan untuk masing-masing kelas
        $ruanganMkA = $jadwalA->kelas->matakuliah->ruangans ?? collect();
        $ruanganMkB = $jadwalB->kelas->matakuliah->ruangans ?? collect();
        $poolA = $ruanganMkA->isNotEmpty() ? $ruanganMkA : $semuaRuangan;
        $poolB = $ruanganMkB->isNotEmpty() ? $ruanganMkB : $semuaRuangan;

        $hariList = $this->urutanHari((int) $jadwalA->id_hari, $jadwalA->kelas->nama_kelas ?? '');

        foreach ($hariList as $hariId) {
            for ($slotA = 1; $slotA <= $maxStartA; $slotA++) {
                $slotB = $slotA + $durasiA + 1;

                // [HC16] Pastikan A maupun B tidak overlap slot Sholat Jumat
                if ($this->overlapsSlotSholatJumat($hariId, $slotA, $durasiA)
                    || $this->overlapsSlotSholatJumat($hariId, $slotB, $durasiB)) {
                    continue;
                }

                // [HC14] Pastikan dosen tidak melebihi 8 SKS/hari di hari ini
                // (hitung total SKS dosen di hari ini TANPA A & B, lalu tambah durasiA+durasiB)
                $totalSksLain = $jadwalTerkini
                    ->where('id_dosen', $dosenId)
                    ->where('id_hari', $hariId)
                    ->whereNotIn('id_jadwal', [$jadwalA->id_jadwal, $jadwalB->id_jadwal])
                    ->sum('durasi_sks');
                if (($totalSksLain + $durasiA + $durasiB) > $this->maxSksDosenPerHari) {
                    continue;
                }

                // Cek bentrok dosen/ruangan dengan jadwal LAIN (bukan A & B sendiri)
                $jadwalLainHariIni = $jadwalTerkini
                    ->where('id_hari', $hariId)
                    ->whereNotIn('id_jadwal', [$jadwalA->id_jadwal, $jadwalB->id_jadwal]);

                $slotRangeA = range($slotA, $slotA + $durasiA - 1);
                $slotRangeB = range($slotB, $slotB + $durasiB - 1);

                $dosenBentrok = $jadwalLainHariIni->first(function ($lain) use ($dosenId, $slotRangeA, $slotRangeB) {
                    if (!$lain->id_dosen || $lain->id_dosen !== $dosenId) return false;
                    $lainRange = range($lain->id_slot_mulai, $lain->id_slot_mulai + $lain->durasi_sks - 1);
                    return array_intersect($lainRange, $slotRangeA) || array_intersect($lainRange, $slotRangeB);
                });
                if ($dosenBentrok) continue;

                // Cari ruangan bebas untuk A
                $ruanganTerpakaiSlotA = $jadwalLainHariIni
                    ->filter(fn($lain) => array_intersect(
                        range($lain->id_slot_mulai, $lain->id_slot_mulai + $lain->durasi_sks - 1),
                        $slotRangeA
                    ))
                    ->pluck('id_ruang')->unique()->toArray();
                $ruanganA = $poolA->whereNotIn('id_ruang', $ruanganTerpakaiSlotA)->first();
                if (!$ruanganA) continue;

                // Cari ruangan bebas untuk B (jangan sampai sama dengan ruangan A jika overlap slot,
                // tapi karena ada gap 1 slot, A & B tidak overlap waktu — boleh ruangan sama)
                $ruanganTerpakaiSlotB = $jadwalLainHariIni
                    ->filter(fn($lain) => array_intersect(
                        range($lain->id_slot_mulai, $lain->id_slot_mulai + $lain->durasi_sks - 1),
                        $slotRangeB
                    ))
                    ->pluck('id_ruang')->unique()->toArray();
                $ruanganB = $poolB->whereNotIn('id_ruang', $ruanganTerpakaiSlotB)->first();
                if (!$ruanganB) continue;

                // Semua aman — terapkan
                $jadwalA->update(['id_hari' => $hariId, 'id_slot_mulai' => $slotA, 'id_ruang' => $ruanganA->id_ruang]);
                $jadwalB->update(['id_hari' => $hariId, 'id_slot_mulai' => $slotB, 'id_ruang' => $ruanganB->id_ruang]);

                return true;
            }
        }

        return false;
    }

    // ─────────────────────────────────────────────────────────────
    //  CARI SLOT KOSONG (HC1/HC2/HC8/HC12/HC16 aman)
    // ─────────────────────────────────────────────────────────────
    private function cariSlotKosong(
        int $hariId,
        int $sks,
        ?int $dosenId,
        int $kelasId,
        int $idTahunAkademik,
        int $jadwalIdDikecualikan,
        Collection $semuaSlot,
        string $namaKelas
    ): array {
        // Jadwal yang sudah ada di hari ini (kecuali jadwal yang sedang diperbaiki)
        $jadwalHariIni = Jadwal::where('id_tahunakademik', $idTahunAkademik)
            ->where('id_hari', $hariId)
            ->where('id_jadwal', '!=', $jadwalIdDikecualikan)
            ->get();

        // Slot yang sudah dipakai dosen ini
        $slotDosenTerpakai = $dosenId
            ? $jadwalHariIni->where('id_dosen', $dosenId)
                ->flatMap(fn($j) => range($j->id_slot_mulai, $j->id_slot_mulai + $j->durasi_sks - 1))
                ->unique()->toArray()
            : [];

        // Slot yang sudah dipakai kelas ini
        $slotKelasTerpakai = $jadwalHariIni->where('id_kelas', $kelasId)
            ->flatMap(fn($j) => range($j->id_slot_mulai, $j->id_slot_mulai + $j->durasi_sks - 1))
            ->unique()->toArray();

        $hasil = [];

        foreach ($semuaSlot as $slot) {
            $slotMulai = $slot->id_slot;
            $slotRange = range($slotMulai, $slotMulai + $sks - 1);
            $slotIds   = $semuaSlot->pluck('id_slot')->toArray();

            // Pastikan semua slot dalam range ada
            if (count(array_diff($slotRange, $slotIds)) > 0) continue;

            // Jangan lewati slot istirahat
            if ($this->melewatiIstirahat($slotRange)) continue;

            // [HC16] Jangan overlap slot Sholat Jumat
            if ($this->overlapsSlotSholatJumat($hariId, $slotMulai, $sks)) continue;

            // [HC12] Sesuai jenis kelas (A/B pagi, S malam)
            if (!$this->slotSesuaiJenisKelas($slot, $namaKelas)) continue;

            // [HC1] Dosen tidak bentrok
            if (array_intersect($slotRange, $slotDosenTerpakai)) continue;

            // [HC8] Kelas tidak dobel
            if (array_intersect($slotRange, $slotKelasTerpakai)) continue;

            $hasil[] = $slotMulai;
        }

        return $hasil;
    }

    // ─────────────────────────────────────────────────────────────
    //  CARI RUANGAN BEBAS
    // ─────────────────────────────────────────────────────────────
    private function cariRuanganBebas(
        int $hariId,
        int $slotMulai,
        int $sks,
        int $idTahunAkademik,
        int $jadwalIdDikecualikan,
        Collection $poolRuangan
    ): ?object {
        $slotRange = range($slotMulai, $slotMulai + $sks - 1);

        // Ruangan yang sudah terpakai di slot-slot tersebut
        $ruanganTerpakai = Jadwal::where('id_tahunakademik', $idTahunAkademik)
            ->where('id_hari', $hariId)
            ->where('id_jadwal', '!=', $jadwalIdDikecualikan)
            ->get()
            ->filter(function ($j) use ($slotRange) {
                $jRange = range($j->id_slot_mulai, $j->id_slot_mulai + $j->durasi_sks - 1);
                return (bool) array_intersect($slotRange, $jRange);
            })
            ->pluck('id_ruang')
            ->unique()
            ->toArray();

        return $poolRuangan->whereNotIn('id_ruang', $ruanganTerpakai)->first();
    }

    /**
     * [HC14] Cek apakah memindahkan $sks SKS milik $dosenId ke hari $hariId
     * masih aman (tidak melebihi maxSksDosenPerHari), MENGECUALIKAN jadwal
     * yang sedang diperbaiki dari hitungan SKS yang sudah ada.
     */
    private function dosenAmanSksDiHari(
        int $dosenId,
        int $hariId,
        int $sks,
        int $idTahunAkademik,
        int $jadwalIdDikecualikan
    ): bool {
        $totalSksLain = Jadwal::where('id_tahunakademik', $idTahunAkademik)
            ->where('id_dosen', $dosenId)
            ->where('id_hari', $hariId)
            ->where('id_jadwal', '!=', $jadwalIdDikecualikan)
            ->sum('durasi_sks');

        return ($totalSksLain + $sks) <= $this->maxSksDosenPerHari;
    }

    // ─────────────────────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────────────────────

    /** Urutan hari: coba hari asal dulu, lalu hari lain */
    private function urutanHari(int $hariAsal, string $namaKelas): array
    {
        $semua = [1, 2, 3, 4, 5];
        // Kelas S (malam) bebas di semua hari
        // Kelas A/B (pagi) coba hari asal dulu
        return array_unique(array_merge([$hariAsal], $semua));
    }

    /** Cek apakah range slot mencakup slot istirahat */
    private function melewatiIstirahat(array $slotRange): bool
    {
        return $this->slotIstirahat > 0 && in_array($this->slotIstirahat, $slotRange);
    }

    /**
     * [HC16] Cek apakah rentang slot (hari, slotMulai..slotMulai+durasi-1)
     * overlap dengan slot Sholat Jumat (id_hari=5, id_slot=6). Identik
     * dengan GeneticScheduler::overlapsBlockedFridaySlot() — berlaku untuk
     * SEMUA jenis kelas tanpa kecuali.
     */
    private function overlapsSlotSholatJumat(int $hariId, int $slotMulai, int $durasi): bool
    {
        if ($hariId !== $this->hariJumat) {
            return false;
        }
        $slotAkhir = $slotMulai + $durasi - 1;
        return $slotMulai <= $this->slotSholatJumat && $slotAkhir >= $this->slotSholatJumat;
    }

    /**
     * [HC13] Cek apakah tipe ruangan sesuai jenis matkul:
     *   praktikum → hanya lab atau hybrid
     *   teori     → hanya reguler atau hybrid
     * Identik dengan Gene::validateRoomType().
     */
    private function tipeRuanganSesuai(string $jenisMatkul, string $tipeRuangan): bool
    {
        $jenis = strtolower(trim($jenisMatkul));
        $tipe  = strtolower(trim($tipeRuangan));

        if ($jenis === 'praktikum') {
            return in_array($tipe, ['lab', 'hybrid'], true);
        }
        return in_array($tipe, ['reguler', 'hybrid'], true);
    }

    /**
     * [Bug4-fix versi OptimizeJadwal] Deteksi kelas sore/malam yang
     * komprehensif — IDENTIK dengan GeneticScheduler::detectKelasS().
     */
    private function detectKelasS(string $namaKelas): bool
    {
        $nama = strtolower(trim($namaKelas));
        if (preg_match('/-\d*s[i\d]*$/i', $nama)) {
            return true;
        }
        if (preg_match('/\bsore\b|\bmalam\b/', $nama)) {
            return true;
        }
        return false;
    }

    /** Cek apakah slot sesuai jenis kelas (A/B=pagi, S=malam) — [HC12] */
    private function slotSesuaiJenisKelas(Slot_waktu $slot, string $namaKelas): bool
    {
        if (!$slot->waktu_mulai || $slot->waktu_mulai === '-') return true;

        $menitMulai  = $this->menitDariWaktu($slot->waktu_mulai);
        $isKelasS    = $this->detectKelasS($namaKelas);

        if ($isKelasS) {
            return $menitMulai >= $this->menitBatasMalam;
        }

        return $menitMulai < $this->menitBatasMalam;
    }

    /** Konversi string waktu "HH:MM[:SS]" menjadi total menit dari 00:00. */
    private function menitDariWaktu(?string $waktu): int
    {
        if (!$waktu) return 0;
        $parts = explode(':', $waktu);
        if (count($parts) < 2) return 0;
        return (int) $parts[0] * 60 + (int) $parts[1];
    }

    /** Format data bentrok sisa untuk response ke frontend */
    private function formatDetailBentrok(Jadwal $jadwal, Collection $semuaJadwal): array
    {
        $alasan = [];

        foreach ($semuaJadwal as $lain) {
            if ($lain->id_jadwal === $jadwal->id_jadwal) continue;
            if ($lain->id_hari  !== $jadwal->id_hari)   continue;

            $aStart = $jadwal->id_slot_mulai;
            $aEnd   = $jadwal->id_slot_mulai + $jadwal->durasi_sks;
            $bStart = $lain->id_slot_mulai;
            $bEnd   = $lain->id_slot_mulai + $lain->durasi_sks;

            if (!($aStart < $bEnd && $aEnd > $bStart)) continue;

            if ($jadwal->id_dosen && $lain->id_dosen && $jadwal->id_dosen === $lain->id_dosen) {
                $alasan[] = 'Dosen bentrok dengan kelas ' . ($lain->kelas->nama_kelas ?? '-');
            }
            if ($jadwal->id_ruang && $lain->id_ruang && $jadwal->id_ruang === $lain->id_ruang) {
                $alasan[] = 'Ruangan bentrok dengan kelas ' . ($lain->kelas->nama_kelas ?? '-');
            }
            if ($jadwal->id_kelas === $lain->id_kelas) {
                $alasan[] = 'Kelas dobel slot';
            }
        }

        // Tambahkan alasan non-pairwise jika relevan
        if (!$jadwal->id_dosen) {
            $alasan[] = 'Belum punya dosen pengampu';
        }
        $ruanganMk = $jadwal->kelas->matakuliah->ruangans ?? collect();
        if ($ruanganMk->isNotEmpty() && $jadwal->id_ruang) {
            $validIds = $ruanganMk->pluck('id_ruang')->map(fn($id) => (int) $id)->toArray();
            if (!in_array((int) $jadwal->id_ruang, $validIds, true)) {
                $alasan[] = 'Ruangan tidak terdaftar untuk mata kuliah ini';
            }
        }
        if ($jadwal->slotMulai && !$this->slotSesuaiJenisKelas($jadwal->slotMulai, $jadwal->kelas->nama_kelas ?? '')) {
            $alasan[] = 'Slot waktu tidak sesuai jenis kelas (pagi/malam)';
        }
        if ($jadwal->ruangan && !$this->tipeRuanganSesuai($jadwal->kelas->matakuliah->jenis ?? 'teori', $jadwal->ruangan->tipe_ruangan ?? 'reguler')) {
            $alasan[] = 'Tipe ruangan tidak sesuai jenis mata kuliah';
        }
        if ($this->overlapsSlotSholatJumat((int) $jadwal->id_hari, (int) $jadwal->id_slot_mulai, (int) $jadwal->durasi_sks)) {
            $alasan[] = 'Bertabrakan dengan slot Sholat Jumat';
        }
        if ($jadwal->id_dosen) {
            $totalSksHariIni = $semuaJadwal
                ->where('id_dosen', $jadwal->id_dosen)
                ->where('id_hari', $jadwal->id_hari)
                ->sum('durasi_sks');
            if ($totalSksHariIni > $this->maxSksDosenPerHari) {
                $alasan[] = 'Dosen mengajar lebih dari ' . $this->maxSksDosenPerHari . ' SKS di hari ini';
            }
        }

        return [
            'jadwal_id'  => $jadwal->id_jadwal,
            'kelas'      => $jadwal->kelas->nama_kelas       ?? '-',
            'mata_kuliah'=> $jadwal->kelas->matakuliah->nama_matkul ?? '-',
            'dosen'      => $jadwal->kelas->dosen->nama_dosen ?? '-',
            'hari'       => $jadwal->hari->nama_hari          ?? '-',
            'slot_asal'  => $jadwal->id_slot_mulai,
            'ruangan'    => $jadwal->ruangan?->nama_ruang      ?? '-',
            'alasan'     => implode('; ', array_unique($alasan)),
        ];
    }

    /** Pesan ringkas hasil optimasi */
    private function pesanHasil(int $diperbaiki, int $gagal): string
    {
        if ($gagal === 0) {
            return "Semua {$diperbaiki} pelanggaran constraint berhasil diperbaiki. Jadwal sudah optimal.";
        }
        if ($diperbaiki === 0) {
            return "Tidak ada pelanggaran yang bisa diperbaiki otomatis ({$gagal} memerlukan penanganan manual).";
        }
        return "{$diperbaiki} pelanggaran diperbaiki otomatis. {$gagal} pelanggaran perlu diselesaikan manual.";
    }
}
