<?php

namespace App\Services\GeneticAlgorithm;

/**
 * Gene — satu penugasan jadwal untuk satu kelas.
 *
 * ════════════════════════════════════════════════════════════════════════
 * CONSTRAINT REGISTRY
 * ════════════════════════════════════════════════════════════════════════
 *
 * HARD CONSTRAINTS (ditangani di Gene atau Chromosome)
 * ─────────────────────────────────────────────────────────────────────
 * HC1  Dosen tidak boleh mengajar dua kelas pada waktu yang sama
 *      → conflictsWith() return 1
 * HC2  Ruangan tidak boleh dipakai dua kelas pada waktu yang sama
 *      → conflictsWith() return 2
 * HC3  Slot waktu harus valid (tidak null/kosong)
 *      → boundary guard: slotMulai ≥ 1, slotAkhir ≤ maxSlot
 * HC4  Satu kelas hanya menempati satu ruangan per slot
 *      → satu Gene per kelas, ruangId tunggal
 * HC5  Dosen hanya mengajar satu matakuliah per slot
 *      → identik dengan HC1
 * HC6  Setiap kelas harus punya dosen pengampu
 *      → validasi di prepareData: kelas tanpa dosenId valid ditolak
 *        (catatan: dosenId=0 berarti "TBA", gene tetap dibuat tapi mendapat penalti)
 * HC7  Durasi slot = SKS matakuliah
 *      → gene.durasi = kelasData['sks'], ditegakkan saat konstruksi
 * HC8  Tidak ada overlap slot untuk entitas yang sama
 *      → identik dengan HC1+HC2
 * HC9  Total slot yang digunakan ≤ slot tersedia dalam sistem
 *      → boundary guard, ditegakkan di GeneticScheduler
 * HC10 Ruangan yang dipakai harus terdaftar di data ruangan
 *      → validateRoomExists() — penalti jika ruangId tidak ada di ruanganList
 * HC11 Kelas paralel tidak boleh berbagi dosen/ruangan di slot yang sama
 *      → identik dengan HC1+HC2, diperiksa di conflictsWith()
 * HC12 Kelas sore hanya di slot sore/malam
 *      → validateTimeConstraint() menggunakan isKelasS flag
 * HC13 Praktikum/lab hanya di ruangan lab; teori/hybrid boleh di reguler
 *      → validateRoomType() — cek tipe_ruangan vs jenis matkul
 * HC14 Total SKS seorang dosen dalam satu hari ≤ 8
 *      → diperiksa di Chromosome::calculateFitness() secara agregat
 * HC15 Pasangan kelas paralel (matkul sama + dosen sama, persis 2 kelas
 *      non-S) wajib di hari yang sama, berurutan (A lalu B menurut abjad
 *      nama_kelas) dengan gap TEPAT 1 slot kosong di antaranya.
 *      → dihitung di Chromosome::calculateFitness() secara agregat
 *        (membutuhkan info pasangan dari GeneticScheduler)
 * HC16 Slot ke-6 (jam 12:00, waktu Sholat Jumat) pada hari Jumat tidak
 *      boleh ditempati/dilewati kelas APAPUN (reguler, praktikum, maupun
 *      kelas malam -S). Ini BUKAN sekadar dihindari (preferensi) — slot
 *      ini secara struktural dikeluarkan dari pool kandidat slot di
 *      GeneticScheduler (pickSmartSlot, createPairedGenes, repair, local
 *      search, dll), sehingga GA tidak akan pernah menghasilkan kandidat
 *      yang melanggarnya. validateFridayPrayerSlot() di bawah berfungsi
 *      sebagai safety-net (double-check) di level fitness, bukan satu-
 *      satunya penegak aturan ini.
 *      → validateFridayPrayerSlot() — dicek di Chromosome sebagai hard
 *        penalty pengaman jika somehow ada gene yang lolos dari exclusion.
 *
 * SOFT CONSTRAINTS (penalti di Chromosome::calculateFitness)
 * ─────────────────────────────────────────────────────────────────────
 * SC1  Beban dosen tersebar merata sepanjang minggu    → lihat Chromosome
 * SC2  Minimasi gap/jam kosong dosen                  → lihat Chromosome
 * SC3  Minimasi dosen > 3 kelas/hari                  → lihat Chromosome
 * SC4  Minimasi ruangan berbeda untuk 1 dosen/hari    → lihat Chromosome
 * SC5  Selisih kapasitas ruang vs peserta minimal     → validateRoomFit()
 * SC6  Minimasi variansi penggunaan ruangan           → lihat Chromosome
 * SC7  Minimasi perpindahan ruangan berurutan/dosen   → lihat Chromosome
 * SC9  Minimasi kelas di slot pertama dan terakhir    → validateEdgeSlot()
 * SC10 Minimasi jumlah hari kerja dosen               → lihat Chromosome
 * SC11 Minimasi selisih jumlah kelas antar hari       → lihat Chromosome
 * SC13 Hindari matkul berat berturutan satu hari      → lihat Chromosome
 * SC15 Matkul berat di slot siang/sore, bukan pagi   → validateHeavySlot()
 * SC16 Matkul ringan/sedang prioritaskan slot pagi    → validateLightSlot()
 *
 * CATATAN: SC12 (lama: "minimasi mata kuliah berturutan tanpa jeda")
 * telah DIHAPUS dan DIGANTIKAN oleh HC15 di atas — alih-alih menghukum
 * keberurutan, sekarang justru MEWAJIBKAN keberurutan dengan gap 1 slot
 * khusus untuk pasangan kelas paralel matkul+dosen sama.
 * ════════════════════════════════════════════════════════════════════════
 */
class Gene
{
    // ── Identitas jadwal ─────────────────────────────────────────────────
    public int $kelasId;
    public int $hariId;
    public int $slotMulai;
    public int $durasi;       // = SKS matakuliah (HC7)
    public int $ruangId;
    public int $dosenId;

    // ── Metadata validasi ────────────────────────────────────────────────
    public int    $kapasitasKelas;
    public int    $kapasitasRuang;
    public string $namaKelas;
    public int    $maxSlot;
    public bool   $isKelasS;        // HC12: flag kelas sore/malam

    // ── Jenis & tipe ruangan (HC13) ───────────────────────────────────────
    public string $jenisMatkul;     // 'teori' | 'praktikum'
    public string $tipeRuangan;     // 'reguler' | 'lab' | 'studio' | 'hybrid'

    // ── Kategori matkul (SC13, SC15, SC16) ───────────────────────────────
    public string $kategoriMatkul;  // 'ringan' | 'sedang' | 'berat' | 'praktikum'

    // ── Konstanta ────────────────────────────────────────────────────────
    public const JENIS_TEORI     = 'teori';
    public const JENIS_PRAKTIKUM = 'praktikum';

    public const TIPE_REGULER = 'reguler';
    public const TIPE_LAB     = 'lab';
    public const TIPE_STUDIO  = 'studio';
    public const TIPE_HYBRID  = 'hybrid';

    public const KATEGORI_RINGAN    = 'ringan';
    public const KATEGORI_SEDANG    = 'sedang';
    public const KATEGORI_BERAT     = 'berat';
    public const KATEGORI_PRAKTIKUM = 'praktikum';

    public function __construct(
        int    $kelasId,
        int    $hariId,
        int    $slotMulai,
        int    $durasi,
        int    $ruangId,
        int    $dosenId,
        int    $kapasitasKelas  = 0,
        string $jenisMatkul     = self::JENIS_TEORI,
        int    $kapasitasRuang  = 0,
        string $namaKelas       = '',
        int    $maxSlot         = 14,
        string $tipeRuangan     = self::TIPE_REGULER,
        string $kategoriMatkul  = self::KATEGORI_SEDANG
    ) {
        $this->kelasId        = $kelasId;
        $this->hariId         = $hariId;
        $this->slotMulai      = max(1, $slotMulai);  // HC3: minimal slot 1
        $this->durasi         = max(1, $durasi);       // HC7
        $this->ruangId        = $ruangId;
        $this->dosenId        = $dosenId;
        $this->kapasitasKelas = $kapasitasKelas;
        $this->kapasitasRuang = $kapasitasRuang;
        $this->namaKelas      = $namaKelas;
        $this->maxSlot        = max(1, $maxSlot);
        $this->jenisMatkul    = strtolower(trim($jenisMatkul))    ?: self::JENIS_TEORI;
        $this->tipeRuangan    = strtolower(trim($tipeRuangan))    ?: self::TIPE_REGULER;
        $this->kategoriMatkul = strtolower(trim($kategoriMatkul)) ?: self::KATEGORI_SEDANG;
        // HC12: deteksi kelas sore dari suffix nama ATAU kata "sore"/"malam"
        // Suffix: -S, -S1, -S2, -SI, -4S, dst. (case-insensitive)
        // Format: A2-S, A2-S1, A2-4S, dll.
        // Kata: "sore" atau "malam" sebagai kata penuh dalam nama kelas
        $namaNorm = strtolower(trim($namaKelas));
        $this->isKelasS = (bool) preg_match('/-\d*s[i\d]*$/i', $namaNorm) // -S, -SI, -4S, -S1, -4S1
            || (bool) preg_match('/\bsore\b|\bmalam\b/', $namaNorm);
    }

    // ────────────────────────────────────────────────────────────────────
    // Accessor
    // ────────────────────────────────────────────────────────────────────

    /** HC3: slot akhir dijaga ≤ maxSlot */
    public function getSlotAkhir(): int
    {
        return min($this->slotMulai + $this->durasi - 1, $this->maxSlot);
    }

    // ────────────────────────────────────────────────────────────────────
    // HARD CONSTRAINTS — deteksi konflik antar-gene
    // ────────────────────────────────────────────────────────────────────

    /**
     * HC1/HC2/HC8/HC11 — Cek konflik dengan gene lain.
     *
     * Return:
     *   0 = tidak ada konflik
     *   1 = konflik dosen (HC1/HC5/HC11)
     *   2 = konflik ruangan (HC2/HC4/HC11)
     */
    public function conflictsWith(Gene $other): int
    {
        if ($this->hariId !== $other->hariId) {
            return 0;
        }

        // Cek overlap waktu
        if ($this->getSlotAkhir() < $other->slotMulai
            || $other->getSlotAkhir() < $this->slotMulai) {
            return 0;
        }

        // HC1/HC5: konflik dosen
        if ($this->dosenId !== 0 && $this->dosenId === $other->dosenId) {
            return 1;
        }

        // HC2/HC4: konflik ruangan
        if ($this->ruangId !== 0 && $this->ruangId === $other->ruangId) {
            return 2;
        }

        return 0;
    }

    // ────────────────────────────────────────────────────────────────────
    // HARD CONSTRAINTS — validasi per-gene (return false = ada pelanggaran HC)
    // ────────────────────────────────────────────────────────────────────

    /**
     * HC6 — Dosen pengampu harus ada (dosenId > 0).
     */
    public function validateDosenExists(): bool
    {
        return $this->dosenId > 0;
    }

    /**
     * HC10 — Ruangan harus terdaftar ($validRuangIds dari GeneticScheduler).
     */
    public function validateRoomExists(array $validRuangIds): bool
    {
        if ($this->ruangId === 0) {
            return false; // Belum ada ruangan = pelanggaran HC10
        }
        return in_array($this->ruangId, $validRuangIds, true);
    }

    /**
     * HC16 — Slot Sholat Jumat: kelas tidak boleh berlangsung (atau overlap)
     * di slot tertentu pada hari Jumat.
     *
     * Ini adalah SAFETY-NET di level fitness. Penegakan utama dilakukan
     * secara struktural di GeneticScheduler dengan mengeluarkan slot ini
     * dari pool kandidat — method ini hanya untuk mendeteksi jika somehow
     * sebuah gene tetap lolos overlap (misal akibat crossover/mutasi yang
     * belum tersaring repair).
     *
     * @param int $hariJumat      id_hari yang merepresentasikan Jumat (default 5)
     * @param int $slotSholatJumat  id_slot yang wajib steril dari kelas (default 6)
     */
    public function validateFridayPrayerSlot(int $hariJumat = 5, int $slotSholatJumat = 6): bool
    {
        if ($this->hariId !== $hariJumat) {
            return true; // Bukan hari Jumat, tidak relevan
        }
        $slotAkhir = $this->getSlotAkhir();
        // Pelanggaran jika rentang gene overlap slot sholat (tidak boleh "melompati")
        return !($this->slotMulai <= $slotSholatJumat && $slotAkhir >= $slotSholatJumat);
    }

    /**
     * HC12 — Kelas sore hanya boleh di slot sore/malam.
     * HC3  — Slot tidak di breakSlots.
     */
    public function validateTimeConstraint(int $eveningStartSlot = 12, array $breakSlots = []): bool
    {
        // HC3: slot break selalu melanggar jika ada slot kelas yang bertabrakan dengan break slots
        $slotAkhir = $this->getSlotAkhir();
        for ($slot = $this->slotMulai; $slot <= $slotAkhir; $slot++) {
            if (in_array($slot, $breakSlots, true)) {
                return false;
            }
        }

        // HC12: gunakan isKelasS yang sudah di-fix (mencakup suffix -S dan kata sore/malam)
        if ($this->isKelasS) {
            // Kelas sore/malam: slotMulai harus ≥ eveningStartSlot
            return $this->slotMulai >= $eveningStartSlot;
        }

        // Kelas reguler: kelas harus selesai sebelum eveningStartSlot (tidak boleh overlap ke malam)
        return $slotAkhir < $eveningStartSlot;
    }

    /**
     * HC13 — Jenis matkul harus sesuai tipe ruangan:
     *   praktikum → hanya lab atau hybrid
     *   teori     → hanya reguler atau hybrid
     *   studio    → hanya studio atau hybrid
     */
    public function validateRoomType(): bool
    {
        if ($this->ruangId === 0) {
            return true; // Belum ditugaskan, skip
        }

        $jenis = $this->jenisMatkul;
        $tipe  = $this->tipeRuangan;

        if ($jenis === self::JENIS_PRAKTIKUM) {
            // Praktikum butuh lab atau hybrid
            return in_array($tipe, [self::TIPE_LAB, self::TIPE_HYBRID], true);
        }

        // Teori tidak boleh di lab eksklusif
        return in_array($tipe, [self::TIPE_REGULER, self::TIPE_HYBRID], true);
    }

    // ────────────────────────────────────────────────────────────────────
    // SOFT CONSTRAINTS — validasi per-gene (return pelanggaran count)
    // ────────────────────────────────────────────────────────────────────

    /**
     * SC5 — Selisih kapasitas ruangan vs peserta minimal.
     * Pelanggaran jika kapasitasRuang < kapasitasKelas (ruangan terlalu kecil)
     * atau kapasitasRuang > kapasitasKelas * 3 (ruangan sangat boros).
     */
    public function validateRoomFit(): int
    {
        if ($this->ruangId === 0 || $this->kapasitasRuang === 0) {
            return 0;
        }
        if ($this->kapasitasRuang < $this->kapasitasKelas) {
            return 2; // Terlalu kecil — penalti besar
        }
        if ($this->kapasitasKelas > 0 && $this->kapasitasRuang > $this->kapasitasKelas * 3) {
            return 1; // Terlalu besar — penalti kecil
        }
        return 0;
    }

    /**
     * SC9 — Minimasi kelas di slot pertama (1) dan slot terakhir (maxSlot).
     */
    public function validateEdgeSlot(): bool
    {
        return $this->slotMulai !== 1 && $this->getSlotAkhir() !== $this->maxSlot;
    }

    /**
     * SC15 — Matkul berat tidak di slot pagi (slot < morningEndSlot).
     *
     * @param int $morningEndSlot Slot terakhir sesi pagi (inklusif), default 6
     */
    public function validateCategorySlotPreference(int $morningEndSlot = 6): int
    {
        $violations = 0;

        // SC15: matkul berat jangan di slot pagi (1–morningEndSlot)
        if ($this->kategoriMatkul === self::KATEGORI_BERAT
            && $this->slotMulai <= $morningEndSlot) {
            $violations++;
        }

        // SC16 dihapus — bertolak belakang dengan HC12:
        // HC12 mengharuskan kelas sore di slot malam,
        // SC16 mendorong matkul ringan/sedang ke slot pagi.
        // HC12 dipertahankan karena lebih kritis (hard constraint).

        return $violations;
    }

    // ────────────────────────────────────────────────────────────────────
    // HC15 — Pasangan kelas paralel (matkul sama + dosen sama)
    // ────────────────────────────────────────────────────────────────────

    /**
     * HC15 — Hitung pelanggaran pasangan kelas paralel untuk Gene ini
     * relatif ke Gene pasangannya ($pair).
     *
     * Aturan:
     *   - Harus di hari yang sama → jika beda hari, pelanggaran besar (treat
     *     sebagai "tidak berurutan sama sekali").
     *   - Harus berurutan: kelas A (alfabetis lebih dulu) → gap 1 slot → kelas B.
     *   - Gap harus TEPAT 1 slot kosong (bukan 0/nempel, bukan >1).
     *
     * Gene ini dianggap "A" jika $isFirst true (urutan abjad nama_kelas),
     * gene $pair dianggap "B".
     *
     * @return int  0 = tidak ada pelanggaran, >0 = besaran pelanggaran
     */
    public function validatePairSequencing(Gene $pair, bool $isFirst): int
    {
        // Pastikan kita selalu menghitung dari sisi "A" (yang lebih dulu menurut abjad)
        // supaya tidak dihitung dua kali (sekali dari A, sekali dari B).
        if (!$isFirst) {
            return 0; // Biarkan sisi A yang menghitung untuk pasangan ini
        }

        $a = $this;
        $b = $pair;

        // Beda hari = pelanggaran besar
        if ($a->hariId !== $b->hariId) {
            return 5;
        }

        // Tentukan siapa yang lebih dulu berdasarkan slot aktual
        // (urutan abjad menentukan "identitas" A/B, tapi posisi slot tetap
        // harus dicek dari yang paling awal ke yang paling akhir)
        if ($a->slotMulai <= $b->slotMulai) {
            $earlier = $a;
            $later   = $b;
        } else {
            $earlier = $b;
            $later   = $a;
        }

        $gap = $later->slotMulai - $earlier->getSlotAkhir() - 1;

        if ($gap === 1) {
            return 0; // Persis sesuai aturan — tidak ada pelanggaran
        }

        // Pelanggaran proporsional terhadap seberapa jauh dari gap=1 yang diinginkan
        // (gap=0 atau gap negatif/overlap, atau gap>1 — semua dihukum)
        return 3 + abs($gap - 1);
    }

    // ────────────────────────────────────────────────────────────────────
    // Aggregator — dipanggil dari Chromosome::calculateFitness()
    // ────────────────────────────────────────────────────────────────────

    /**
     * Hitung total pelanggaran soft constraint per-gene.
     *
     * @param int   $eveningStartSlot
     * @param int[] $breakSlots
     * @param int[] $validRuangIds      untuk HC10
     * @param int   $morningEndSlot     untuk SC15/SC16
     */
    public function getSoftViolations(
        int   $eveningStartSlot = 12,
        array $breakSlots       = [],
        array $validRuangIds    = [],
        int   $morningEndSlot   = 6
    ): int {
        $v = 0;

        // HC6 sebagai soft (penalti ringan jika dosen belum ada, bukan reject)
        if (!$this->validateDosenExists()) {
            $v += 2;
        }

        // HC10
        if (!empty($validRuangIds) && !$this->validateRoomExists($validRuangIds)) {
            $v += 3;
        }

        // HC12/HC3
        if (!$this->validateTimeConstraint($eveningStartSlot, $breakSlots)) {
            $v += 2;
        }

        // HC13
        if (!$this->validateRoomType()) {
            $v += 2;
        }

        // HC16 (safety-net — seharusnya tidak pernah terjadi karena exclusion struktural)
        if (!$this->validateFridayPrayerSlot()) {
            $v += 5;
        }

        // SC5
        $v += $this->validateRoomFit();

        // SC9 dihapus — bertolak belakang dengan SC16

        // SC15
        $v += $this->validateCategorySlotPreference($morningEndSlot);

        return $v;
    }

    /**
     * @deprecated Gunakan getSoftViolations() — lebih lengkap.
     * Dipertahankan untuk backward compatibility dengan kode lama.
     */
    public function getConstraintViolations(int $eveningStartSlot = 12, array $breakSlots = []): int
    {
        return $this->getSoftViolations($eveningStartSlot, $breakSlots);
    }

    // ────────────────────────────────────────────────────────────────────
    // Utility
    // ────────────────────────────────────────────────────────────────────

    public function copy(): Gene
    {
        $clone = new Gene(
            $this->kelasId, $this->hariId, $this->slotMulai, $this->durasi,
            $this->ruangId, $this->dosenId, $this->kapasitasKelas,
            $this->jenisMatkul, $this->kapasitasRuang, $this->namaKelas,
            $this->maxSlot, $this->tipeRuangan, $this->kategoriMatkul
        );
        $clone->isKelasS = $this->isKelasS;
        return $clone;
    }

    public function toArray(): array
    {
        return [
            'kelasId'   => $this->kelasId,
            'hariId'    => $this->hariId,
            'slotMulai' => $this->slotMulai,
            'durasi'    => $this->durasi,
            'ruangId'   => $this->ruangId,
            'dosenId'   => $this->dosenId,
        ];
    }
}
