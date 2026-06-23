<?php

namespace App\Services\GeneticAlgorithm;

use App\Models\Jadwal;
use App\Models\Slot_waktu;
use App\Models\Ruangan;
use Illuminate\Support\Collection;

/**
 * OptimizeJadwalService
 *
 * Mengoptimalkan jadwal hasil penjadwalan otomatis (is_manual = 0)
 * dengan mendeteksi dan memperbaiki bentrok secara otomatis.
 *
 * Tiga tipe bentrok yang ditangani:
 *  [B1] Dosen bentrok  — dosen yang sama di slot yang sama (hari+slot overlap)
 *  [B2] Ruangan bentrok — ruangan yang sama di slot yang sama
 *  [B3] Kelas dobel    — kelas yang sama punya lebih dari satu jadwal di slot overlap
 */
class OptimizeJadwal
{
    /** ID slot istirahat (slot ke-6 berdasarkan urutan jam_ke) */
    private int $slotIstirahat;

    /** Jam mulai sesi malam (menit dari 00:00) */
    private int $menitBatasMalam;

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

        // Deteksi semua bentrok
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

        // Kumpulkan ID jadwal yang bentrok (tanpa duplikasi)
        $idBentrok = collect($bentrokList)
            ->pluck('jadwal_id')
            ->unique()
            ->values();

        $diperbaiki = 0;
        $gagal      = 0;
        $sisaBentrok = [];

        // Coba perbaiki satu per satu
        foreach ($idBentrok as $jadwalId) {
            // Refresh dari DB agar state selalu aktual setelah perbaikan sebelumnya
            $jadwal = Jadwal::with([
                'kelas.matakuliah',
                'kelas.dosen',
                'kelas.matakuliah.ruangans',
                'slotMulai',
                'hari',
            ])->find($jadwalId);

            if (!$jadwal) continue;

            // Cek apakah jadwal ini masih bentrok setelah perbaikan sebelumnya
            $jadwalTerkini = Jadwal::where('id_tahunakademik', $idTahunAkademik)
                ->where('is_manual', 0)
                ->get();

            $masihBentrok = $this->apakahMasihBentrok($jadwal, $jadwalTerkini);
            if (!$masihBentrok) {
                $diperbaiki++;
                continue;
            }

            $berhasil = $this->perbaikiJadwal($jadwal, $idTahunAkademik, $semuaSlot, $semuaRuangan);

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
    //  DETEKSI BENTROK
    // ─────────────────────────────────────────────────────────────

    /**
     * Deteksi semua bentrok dari collection jadwal.
     * Mengembalikan array bentrok, masing-masing berisi:
     *   jadwal_id, tipe, keterangan, jadwal_lawan_id
     */
    public function deteksiBentrok(Collection $jadwalList): array
    {
        $bentrokList = [];
        $jadwalArr   = $jadwalList->values();
        $total       = $jadwalArr->count();

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

                // [B1] Dosen sama
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

                // [B2] Ruangan sama
                if (
                    $a->id_ruang &&
                    $b->id_ruang &&
                    $a->id_ruang === $b->id_ruang
                ) {
                    $bentrokList[] = [
                        'jadwal_id'     => $a->id_jadwal,
                        'jadwal_lawan'  => $b->id_jadwal,
                        'tipe'          => 'bentrok_ruangan',
                        'keterangan'    => 'Ruangan ' . ($a->ruangan->nama_ruang ?? '-') . ' dipakai dua kelas sekaligus',
                    ];
                    $bentrokList[] = [
                        'jadwal_id'     => $b->id_jadwal,
                        'jadwal_lawan'  => $a->id_jadwal,
                        'tipe'          => 'bentrok_ruangan',
                        'keterangan'    => 'Ruangan ' . ($b->ruangan->nama_ruang ?? '-') . ' dipakai dua kelas sekaligus',
                    ];
                }

                // [B3] Kelas sama di slot yang overlap
                if ($a->id_kelas === $b->id_kelas) {
                    $bentrokList[] = [
                        'jadwal_id'     => $a->id_jadwal,
                        'jadwal_lawan'  => $b->id_jadwal,
                        'tipe'          => 'bentrok_kelas',
                        'keterangan'    => 'Kelas ' . ($a->kelas->nama_kelas ?? '-') . ' dijadwalkan dua kali di waktu yang sama',
                    ];
                    $bentrokList[] = [
                        'jadwal_id'     => $b->id_jadwal,
                        'jadwal_lawan'  => $a->id_jadwal,
                        'tipe'          => 'bentrok_kelas',
                        'keterangan'    => 'Kelas ' . ($b->kelas->nama_kelas ?? '-') . ' dijadwalkan dua kali di waktu yang sama',
                    ];
                }
            }
        }

        // Deduplikasi berdasarkan jadwal_id (ambil satu entry per jadwal)
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
    //  CEK APAKAH SATU JADWAL MASIH BENTROK (setelah update lain)
    // ─────────────────────────────────────────────────────────────
    private function apakahMasihBentrok(Jadwal $jadwal, Collection $semuaJadwal): bool
    {
        foreach ($semuaJadwal as $lain) {
            if ($lain->id_jadwal === $jadwal->id_jadwal) continue;
            if ($lain->id_hari  !== $jadwal->id_hari)   continue;

            $aStart = $jadwal->id_slot_mulai;
            $aEnd   = $jadwal->id_slot_mulai + $jadwal->durasi_sks;
            $bStart = $lain->id_slot_mulai;
            $bEnd   = $lain->id_slot_mulai + $lain->durasi_sks;

            if (!($aStart < $bEnd && $aEnd > $bStart)) continue;

            if ($jadwal->id_dosen && $lain->id_dosen && $jadwal->id_dosen === $lain->id_dosen) return true;
            if ($jadwal->id_ruang && $lain->id_ruang && $jadwal->id_ruang === $lain->id_ruang) return true;
            if ($jadwal->id_kelas === $lain->id_kelas) return true;
        }

        return false;
    }

    // ─────────────────────────────────────────────────────────────
    //  PERBAIKI SATU JADWAL YANG BENTROK
    // ─────────────────────────────────────────────────────────────
    private function perbaikiJadwal(
        Jadwal $jadwal,
        int $idTahunAkademik,
        Collection $semuaSlot,
        Collection $semuaRuangan
    ): bool {
        $sks         = $jadwal->durasi_sks;
        $hariAsal    = $jadwal->id_hari;
        $dosenId     = $jadwal->id_dosen;
        $kelasId     = $jadwal->id_kelas;
        $namaKelas   = $jadwal->kelas->nama_kelas ?? '';

        // Pool ruangan: utamakan ruangan yang terkait MK, fallback semua ruangan
        $ruanganMk   = $jadwal->kelas->matakuliah->ruangans ?? collect();
        $poolRuangan = $ruanganMk->isNotEmpty() ? $ruanganMk : $semuaRuangan;

        // Urutan hari: coba hari asal dulu, kemudian hari lain
        $hariList = $this->urutanHari($hariAsal, $namaKelas);

        foreach ($hariList as $hariId) {
            // Cari slot kosong di hari ini
            $slotKosong = $this->cariSlotKosong(
                $hariId, $sks, $dosenId, $kelasId,
                $idTahunAkademik, $jadwal->id_jadwal,
                $semuaSlot, $namaKelas
            );

            foreach ($slotKosong as $slotMulai) {
                // Cari ruangan yang bebas di slot ini
                $ruangan = $this->cariRuanganBebas(
                    $hariId, $slotMulai, $sks,
                    $idTahunAkademik, $jadwal->id_jadwal,
                    $poolRuangan
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
    //  CARI SLOT KOSONG
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

            // Sesuai jenis kelas (A/B pagi, S malam)
            if (!$this->slotSesuaiJenisKelas($slot, $namaKelas)) continue;

            // Dosen tidak bentrok
            if (array_intersect($slotRange, $slotDosenTerpakai)) continue;

            // Kelas tidak dobel
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

    /** Cek apakah slot sesuai jenis kelas (A/B=pagi, S=malam) */
    private function slotSesuaiJenisKelas(Slot_waktu $slot, string $namaKelas): bool
    {
        if (!$slot->waktu_mulai || $slot->waktu_mulai === '-') return true;

        $parts = explode(':', $slot->waktu_mulai);
        if (count($parts) < 2) return true;

        $menitMulai  = (int)$parts[0] * 60 + (int)$parts[1];
        $jenisKelas  = strtoupper(substr(trim($namaKelas), -1));

        if ($jenisKelas === 'S') {
            return $menitMulai >= $this->menitBatasMalam;
        }

        return $menitMulai < $this->menitBatasMalam;
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

        return [
            'jadwal_id'  => $jadwal->id_jadwal,
            'kelas'      => $jadwal->kelas->nama_kelas       ?? '-',
            'mata_kuliah'=> $jadwal->kelas->matakuliah->nama_matkul ?? '-',
            'dosen'      => $jadwal->kelas->dosen->nama_dosen ?? '-',
            'hari'       => $jadwal->hari->nama_hari          ?? '-',
            'slot_asal'  => $jadwal->id_slot_mulai,
            'ruangan'    => $jadwal->ruangan->nama_ruang      ?? '-',
            'alasan'     => implode('; ', array_unique($alasan)),
        ];
    }

    /** Pesan ringkas hasil optimasi */
    private function pesanHasil(int $diperbaiki, int $gagal): string
    {
        if ($gagal === 0) {
            return "Semua {$diperbaiki} bentrok berhasil diperbaiki. Jadwal sudah optimal.";
        }
        if ($diperbaiki === 0) {
            return "Tidak ada bentrok yang bisa diperbaiki otomatis ({$gagal} memerlukan penanganan manual).";
        }
        return "{$diperbaiki} bentrok diperbaiki otomatis. {$gagal} bentrok perlu diselesaikan manual.";
    }
}
