<?php

namespace App\Services\GeneticAlgorithm;

/**
 * Chromosome — solusi jadwal lengkap (kumpulan Gene).
 *
 * ════════════════════════════════════════════════════════════════════════
 * FITNESS FORMULA
 *
 *   fitness = 100 - (Σ penalti_hard / N) * 100 - (Σ penalti_soft / N) * 100
 *
 * Hard constraints diberi bobot tinggi; soft constraints bobot rendah.
 * Semua constraint diukur per-gen (/ N) agar skala konsisten untuk
 * problem dengan 20 atau 200 kelas.
 *
 * HARD CONSTRAINTS (diakumulasi di sini):
 *   HC1/HC2  → conflictsWith() return 1/2        [bobot 5.0/konflik]
 *   HC6      → dosenId === 0                      [bobot 3.0/gene]
 *   HC10     → ruangId tidak di validRuangIds     [bobot 3.0/gene]
 *   HC12/HC13→ time/room-type violation           [bobot 2.5/gene]
 *   HC14     → dosen mengajar > 8 SKS/hari        [bobot 4.0/hari/dosen]
 *   HC15     → pasangan kelas paralel (matkul+dosen sama) tidak di hari
 *              sama / gap ≠ 1 slot               [bobot 4.0/pasangan]
 *   HC16     → kelas overlap slot Sholat Jumat (safety-net struktural)
 *                                                  [bobot 6.0/gene]
 *
 * SOFT CONSTRAINTS (diakumulasi di sini dari semua genes):
 *   SC1  Distribusi beban dosen merata (variansi kelas/hari)   [bobot 0.8]
 *   SC2  Minimasi gap jam kosong dosen per hari                [bobot 0.6]
 *   SC3  Dosen > 3 kelas/hari                                  [bobot 0.7]
 *   SC4  Ruangan berbeda dosen per hari                        [bobot 0.5]
 *   SC5  Selisih kapasitas ruang vs peserta                    [bobot 0.8]
 *   SC6  Variansi penggunaan ruangan                           [bobot 0.4]
 *   SC11 Selisih jumlah kelas antar hari                       [bobot 0.5]
 *   SC13 Matkul berat berturutan satu hari                     [bobot 0.6]
 *   SC15 Matkul berat di slot pagi                             [bobot 0.5]
 *
 * CONSTRAINT YANG DIHAPUS (bertolak belakang):
 *   SC7  ✗ bertolak dengan SC2 (SC2 minta berturutan, SC7 menghukum berturutan)
 *   SC9  ✗ bertolak dengan SC16 (SC9 menghukum slot pagi, SC16 mendorong ke pagi)
 *   SC10 ✗ bertolak dengan SC3 (SC3 minta banyak hari, SC10 minta sedikit hari)
 *
 * CONSTRAINT YANG DIUBAH:
 *   SC12 → DIHAPUS sebagai soft constraint dan DIGANTIKAN oleh HC15 (hard).
 *          SC12 lama menghukum keberurutan tanpa jeda; sekarang justru
 *          pasangan kelas paralel (matkul+dosen sama, persis 2 kelas
 *          non-S) WAJIB berurutan dengan gap tepat 1 slot di hari yang
 *          sama. Ini memastikan dosen istirahat 1 slot di antara dua
 *          kelas paralelnya, tanpa bertolak belakang dengan SC2 (gap
 *          1-slot ini dikecualikan dari penalti SC2 — lihat kode di bawah).
 * ════════════════════════════════════════════════════════════════════════
 */
class Chromosome
{
    /** @var Gene[] */
    public array $genes = [];

    // ── Fitness state ──────────────────────────────────────────────────
    public float $fitness              = 0.0;
    public int   $conflicts            = 0;
    public int   $dosenConflicts       = 0;
    public int   $ruanganConflicts     = 0;
    public int   $constraintViolations = 0;  // total soft violations (SC)
    public int   $hardViolations       = 0;  // HC6/HC10/HC12/HC14/HC15

    // ── Bobot hard constraint ─────────────────────────────────────────
    private float $wHC1_dosen    = 5.0;
    private float $wHC2_ruangan  = 5.0;
    private float $wHC6_dosen0   = 3.0;
    private float $wHC10_noRoom  = 3.0;
    private float $wHC12_time    = 5.0;  // Dinaikkan dari 2.5 ke 5.0 agar lebih kuat
    private float $wHC13_type    = 2.5;
    private float $wHC14_sks_day = 4.0;
    private float $wHC15_pair    = 4.0;  // [BARU] Pasangan kelas paralel matkul+dosen sama
    private float $wHC16_friday  = 6.0;  // [BARU] Slot Sholat Jumat (safety-net, seharusnya tidak terjadi)

    // ── Bobot soft constraint ─────────────────────────────────────────
    private float $wSC1_load     = 0.8;  // Distribusi beban dosen merata
    private float $wSC2_gap      = 0.6;  // Gap jam kosong dosen
    private float $wSC3_max3     = 0.7;  // Dosen > 3 kelas/hari
    private float $wSC4_room_var = 0.5;  // Ruangan berbeda per dosen/hari
    private float $wSC5_fit      = 0.8;  // Selisih kapasitas ruangan
    private float $wSC6_room_use = 0.8;  // Variansi penggunaan ruangan (dinaikkan dari 0.4)
    // SC7 dihapus — bertolak dengan SC2
    // SC9 dihapus — bertolak dengan SC16
    // SC10 dihapus — bertolak dengan SC3
    // SC12 dihapus sebagai soft — digantikan HC15 (hard, lihat atas)
    private float $wSC11_balance  = 0.5;  // Selisih kelas antar hari
    private float $wSC13_heavy    = 0.6;  // Matkul berat berturutan
    private float $wSC15_heavy_am = 0.5;  // Matkul berat di slot pagi
    private float $wSC17_start8   = 0.6;  // Jam 8 pagi (slot 1) diutamakan

    // ── Parameter constraint dari GeneticScheduler ───────────────────
    private int   $eveningStartSlot = 12;
    private array $breakSlots       = [];
    private array $validRuangIds    = [];
    private int   $morningEndSlot   = 6;
    private int   $maxSksDayDosen   = 8;   // HC14

    /**
     * [BARU] HC15 — Daftar pasangan kelas paralel (matkul+dosen sama).
     * Format: [ [kelasIdA, kelasIdB], ... ]
     * kelasIdA = kelas pertama secara abjad (nama_kelas), kelasIdB = kedua.
     * Di-set dari GeneticScheduler::buildParallelPairs().
     */
    private array $parallelPairs = [];

    // ── Fitness cache ─────────────────────────────────────────────────
    private bool   $fitnessIsDirty = true;
    private string $genesHash      = '';
    private float  $lastProgress   = -1.0;

    public function __construct(array $genes = [])
    {
        $this->genes          = $genes;
        $this->fitnessIsDirty = true;
    }

    // ────────────────────────────────────────────────────────────────────
    // Konfigurasi
    // ────────────────────────────────────────────────────────────────────

    public function setConstraintParams(
        int   $eveningStartSlot,
        array $breakSlots,
        array $validRuangIds = [],
        int   $morningEndSlot = 6,
        int   $maxSksDayDosen = 8,
        array $parallelPairs  = null
    ): void {
        $changed = $this->eveningStartSlot !== $eveningStartSlot
            || $this->breakSlots      !== $breakSlots
            || $this->validRuangIds   !== $validRuangIds
            || $this->morningEndSlot  !== $morningEndSlot
            || $this->maxSksDayDosen  !== $maxSksDayDosen
            || ($parallelPairs !== null && $parallelPairs !== $this->parallelPairs);

        if ($changed) {
            $this->eveningStartSlot = $eveningStartSlot;
            $this->breakSlots       = $breakSlots;
            $this->validRuangIds    = $validRuangIds;
            $this->morningEndSlot   = $morningEndSlot;
            $this->maxSksDayDosen   = $maxSksDayDosen;
            if ($parallelPairs !== null) {
                $this->parallelPairs = $parallelPairs;
            }
            $this->fitnessIsDirty   = true;
        }
    }

    public function markDirty(): void
    {
        $this->fitnessIsDirty = true;
    }

    // ────────────────────────────────────────────────────────────────────
    // Fitness Calculation
    // ────────────────────────────────────────────────────────────────────

    public function calculateFitness(float $generationProgress = 1.0): void
    {
        $n = count($this->genes);
        if ($n === 0) {
            $this->fitness = 0.0;
            $this->resetCounters();
            $this->fitnessIsDirty = false;
            return;
        }

        // Cache check - pastikan re-kalkulasi jika progress generasi berubah
        $hash = $this->buildGenesHash();
        if (!$this->fitnessIsDirty && $hash === $this->genesHash && abs($this->lastProgress - $generationProgress) < 0.001) {
            return;
        }
        $this->genesHash    = $hash;
        $this->lastProgress = $generationProgress;

        // ── HC1/HC2: Konflik antar-gene — O(n) via slot index ────────────────
        //
        // Alih-alih membandingkan semua pasangan O(n²), bangun dua index:
        //   dosenSlotMap[dosenId][hariId][slot] = kelasId (pertama yang ditemukan)
        //   ruangSlotMap[ruangId][hariId][slot] = kelasId
        //
        // Untuk setiap gen, cek apakah slot yang dicakupnya sudah ada di index.
        // Kompleksitas: O(n * SKS_rata) ≈ O(n * 3) = O(n).
        //
        $this->dosenConflicts   = 0;
        $this->ruanganConflicts = 0;

        // Index: [entityId][hariId][slotId] = true jika sudah terisi
        $dosenIndex = [];
        $ruangIndex = [];

        foreach ($this->genes as $gene) {
            $did  = $gene->dosenId;
            $rid  = $gene->ruangId;
            $hari = $gene->hariId;
            $end  = $gene->getSlotAkhir();

            for ($slot = $gene->slotMulai; $slot <= $end; $slot++) {
                // Konflik dosen (HC1)
                if ($did !== 0) {
                    if (isset($dosenIndex[$did][$hari][$slot])) {
                        $this->dosenConflicts++;
                    } else {
                        $dosenIndex[$did][$hari][$slot] = true;
                    }
                }

                // Konflik ruangan (HC2)
                if ($rid !== 0) {
                    if (isset($ruangIndex[$rid][$hari][$slot])) {
                        $this->ruanganConflicts++;
                    } else {
                        $ruangIndex[$rid][$hari][$slot] = true;
                    }
                }
            }
        }
        $this->conflicts = $this->dosenConflicts + $this->ruanganConflicts;

        // ── HC lainnya & SC per-gene ─────────────────────────────────────
        $hardPenalty = 0.0;
        $softPenalty = 0.0;

        // Hard per-gene aggregates
        $hc6Count  = 0;
        // HC10 moved to soft constraint - treated as soft because data completeness varies
        $hc12Count = 0;
        // HC13 moved to soft constraint - treated as soft because data completeness varies

        // Track regular classes per room per day for SC17
        $roomHasRegularClass = [];

        // Soft aggregate structures
        // dosenSchedule[dosenId][hariId] = [slotMulai, slotAkhir, ...] (SC2/SC7/SC12/SC13)
        $dosenSchedule = [];
        // dosenDays[dosenId]   = set of hariId  (SC10)
        $dosenDays     = [];
        // dosenDaySks[dosenId][hariId] = totalSks  (HC14)
        $dosenDaySks   = [];
        // dosenDayClasses[dosenId][hariId] = count  (SC3)
        $dosenDayClasses = [];
        // dosenDayRooms[dosenId][hariId] = set of ruangId  (SC4)
        $dosenDayRooms  = [];
        // hariCount[hariId] = kelas count  (SC11)
        $hariCount      = array_fill_keys($this->getHariIds(), 0);
        // roomUsage[ruangId] = count  (SC6)
        $roomUsage      = [];

        // [BARU] Index gene by kelasId, dibutuhkan untuk HC15 (lookup pasangan)
        $geneByKelasId = [];

        foreach ($this->genes as $gene) {
            $geneByKelasId[$gene->kelasId] = $gene;

            // HC6
            if (!$gene->validateDosenExists()) { $hc6Count++; }
            // HC10 moved to soft constraint - check below
            // HC12/HC3
            if (!$gene->validateTimeConstraint($this->eveningStartSlot, $this->breakSlots)) { $hc12Count++; }
            // HC13 moved to soft constraint - check below

            $did   = $gene->dosenId;
            $hari  = $gene->hariId;
            $rid   = $gene->ruangId;

            // Dosen aggregates
            if ($did > 0) {
                $dosenDays[$did][$hari]         = true;
                $dosenDaySks[$did][$hari]        = ($dosenDaySks[$did][$hari] ?? 0) + $gene->durasi;
                $dosenDayClasses[$did][$hari]    = ($dosenDayClasses[$did][$hari] ?? 0) + 1;
                $dosenDayRooms[$did][$hari][$rid] = true;
                $dosenSchedule[$did][$hari][]    = [$gene->slotMulai, $gene->getSlotAkhir(), $gene->kategoriMatkul];
            }

            // Ruangan usage
            if ($rid > 0) {
                $roomUsage[$rid] = ($roomUsage[$rid] ?? 0) + 1;
                if (!$gene->isKelasS) {
                    $roomHasRegularClass[$rid][$hari] = true;
                }
            }

            // Hari count
            if (isset($hariCount[$hari])) {
                $hariCount[$hari]++;
            }

            // SC5 per-gene: room fit
            $fitViol = $gene->validateRoomFit();
            $softPenalty += $fitViol * $this->wSC5_fit;

            // HC10 moved to soft constraint: room exists in valid list
            // [FIX] Untuk praktikum, naikkan bobot ke 5.0 (hard level)
            if (!empty($this->validRuangIds) && !$gene->validateRoomExists($this->validRuangIds)) {
                $penalty = ($gene->jenisMatkul === Gene::JENIS_PRAKTIKUM) ? 5.0 : $this->wHC10_noRoom;
                $softPenalty += $penalty;
            }

            // HC13 moved to soft constraint: room type matching
            // [FIX] Untuk praktikum, naikkan bobot ke 5.0 (hard level)
            if (!$gene->validateRoomType()) {
                $penalty = ($gene->jenisMatkul === Gene::JENIS_PRAKTIKUM) ? 5.0 : $this->wHC13_type;
                $softPenalty += $penalty;
            }

            // SC9 dihapus — bertolak belakang dengan SC16:
            // SC9 menghukum kelas di slot pertama (pagi awal),
            // SC16 mendorong matkul ringan/sedang ke slot pagi.
            // SC16 dipertahankan karena lebih bermakna secara pedagogis.

            // SC15 per-gene: matkul berat di slot pagi
            $softPenalty += $gene->validateCategorySlotPreference($this->morningEndSlot) * $this->wSC15_heavy_am;
        }

        // Hard per-gene penalties
        $hardPenalty += $hc6Count  * $this->wHC6_dosen0;
        // HC10 moved to soft constraint - removed from hard penalty
        $hardPenalty += $hc12Count * $this->wHC12_time;
        // HC13 moved to soft constraint - removed from hard penalty
        $hardPenalty += ($this->dosenConflicts   * $this->wHC1_dosen);
        $hardPenalty += ($this->ruanganConflicts * $this->wHC2_ruangan);
        $this->hardViolations = $hc6Count + $hc12Count;

        // ── HC14: Dosen > 8 SKS/hari ────────────────────────────────────
        $hc14Count = 0;
        foreach ($dosenDaySks as $did => $hariSks) {
            foreach ($hariSks as $hari => $totalSks) {
                if ($totalSks > $this->maxSksDayDosen) {
                    $hc14Count++;
                    $hardPenalty += ($totalSks - $this->maxSksDayDosen) * $this->wHC14_sks_day;
                }
            }
        }
        $this->hardViolations += $hc14Count;

        // ── [BARU] HC15: Pasangan kelas paralel (matkul+dosen sama) ──────
        // Wajib hari sama, berurutan A→gap1→B (urutan abjad nama_kelas).
        $hc15Count     = 0;
        $hc15Violation = 0.0; // akumulasi besaran pelanggaran untuk hard penalty
        foreach ($this->parallelPairs as [$kelasIdA, $kelasIdB]) {
            $geneA = $geneByKelasId[$kelasIdA] ?? null;
            $geneB = $geneByKelasId[$kelasIdB] ?? null;
            if (!$geneA || !$geneB) {
                continue; // Salah satu kelas tidak ada di kromosom ini, skip
            }
            $viol = $geneA->validatePairSequencing($geneB, true);
            if ($viol > 0) {
                $hc15Count++;
                $hc15Violation += $viol;
            }
        }
        if ($hc15Count > 0) {
            $hardPenalty += $hc15Violation * $this->wHC15_pair;
        }
        $this->hardViolations += $hc15Count;

        // ── [BARU] HC16: Slot Sholat Jumat (safety-net) ───────────────────
        // Penegakan utama dilakukan struktural di GeneticScheduler (slot
        // dikeluarkan dari pool kandidat). Pengecekan di sini adalah lapisan
        // pengaman jika somehow ada gene yang lolos (misal dari crossover).
        $hc16Count = 0;
        foreach ($this->genes as $gene) {
            if (!$gene->validateFridayPrayerSlot()) {
                $hc16Count++;
            }
        }
        if ($hc16Count > 0) {
            $hardPenalty += $hc16Count * $this->wHC16_friday;
        }
        $this->hardViolations += $hc16Count;

        // ── SC1: Beban dosen merata (variansi kelas/hari per dosen) ─────
        foreach ($dosenDayClasses as $did => $hariKelas) {
            if (count($hariKelas) < 2) { continue; }
            $vals    = array_values($hariKelas);
            $mean    = array_sum($vals) / count($vals);
            $var     = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $vals)) / count($vals);
            $softPenalty += min($var * $this->wSC1_load, 3.0);
        }

        // ── SC2: Gap jam kosong dosen ────────────────────────────────────
        // [BARU] Bangun set pasangan kelasId (HC15) agar gap=1 slot di antara
        // pasangan ini TIDAK dihukum dua kali oleh SC2 (HC15 sudah menangani).
        $hc15KelasIdSet = [];
        foreach ($this->parallelPairs as [$kelasIdA, $kelasIdB]) {
            $hc15KelasIdSet[$kelasIdA] = $kelasIdB;
            $hc15KelasIdSet[$kelasIdB] = $kelasIdA;
        }

        foreach ($dosenSchedule as $did => $hariSlots) {
            foreach ($hariSlots as $hari => $slots) {
                if (count($slots) < 2) { continue; }
                usort($slots, fn($a, $b) => $a[0] <=> $b[0]);
                $gap = 0;
                for ($i = 1; $i < count($slots); $i++) {
                    $between = $slots[$i][0] - $slots[$i-1][1] - 1;
                    // [FIX] Resolusi konflik SC2 vs SC13: jika keduanya matkul berat dan gap=0,
                    // jangan hitung sebagai gap violation (SC13 akan menangani ini)
                    $prevHeavy = $slots[$i-1][2] === Gene::KATEGORI_BERAT;
                    $currHeavy = $slots[$i][2] === Gene::KATEGORI_BERAT;
                    if ($between === 0 && $prevHeavy && $currHeavy) {
                        continue; // Skip gap penalty untuk matkul berat berturutan
                    }
                    if ($between > 0) { $gap += $between; }
                }
                $softPenalty += min($gap * $this->wSC2_gap, 2.0);
            }
        }

        // ── SC3: Dosen > 3 kelas/hari ────────────────────────────────────
        foreach ($dosenDayClasses as $did => $hariKelas) {
            foreach ($hariKelas as $hari => $cnt) {
                if ($cnt > 3) {
                    $softPenalty += ($cnt - 3) * $this->wSC3_max3;
                }
            }
        }

        // ── SC4: Ruangan berbeda dosen per hari ───────────────────────────
        foreach ($dosenDayRooms as $did => $hariRooms) {
            foreach ($hariRooms as $hari => $rooms) {
                $diff = count($rooms) - 1;
                if ($diff > 0) {
                    $softPenalty += $diff * $this->wSC4_room_var;
                }
            }
        }

        // ── SC6: Variansi penggunaan ruangan ──────────────────────────────
        if (count($roomUsage) > 1) {
            $vals = array_values($roomUsage);
            $mean = array_sum($vals) / count($vals);
            $var  = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $vals)) / count($vals);
            $softPenalty += min(sqrt($var) * $this->wSC6_room_use, 2.0);
        }

        // SC7 dihapus — bertolak belakang dengan SC2:
        // SC2 mendorong jadwal berturutan (minimasi gap),
        // SC7 menghukum jadwal berturutan (perpindahan ruangan).
        // SC2 dipertahankan karena lebih relevan untuk kenyamanan dosen.

        // ── SC10: Minimasi hari kerja dosen ───────────────────────────────
        // SC10 dihapus — bertolak belakang dengan SC3:
        // SC3 mendorong ≤3 kelas/hari (berarti perlu lebih banyak hari),
        // SC10 mendorong sedikit hari kerja (berarti kelas dipadatkan per hari).
        // SC1 (beban merata) sudah menangani distribusi dengan lebih baik.

        // ── SC11: Selisih jumlah kelas antar hari ─────────────────────────
        $hariVals = array_values($hariCount);
        if (count($hariVals) > 1) {
            $hariMean = array_sum($hariVals) / count($hariVals);
            $hariVar  = array_sum(array_map(fn($v) => ($v - $hariMean) ** 2, $hariVals)) / count($hariVals);
            $softPenalty += min(sqrt($hariVar) * $this->wSC11_balance, 2.0);
        }

        // SC12 dihapus sebagai soft constraint — DIGANTIKAN oleh HC15 (hard, di atas).
        // SC12 lama menghukum keberurutan tanpa jeda (bertolak belakang dengan SC2);
        // sekarang pasangan kelas paralel matkul+dosen sama justru WAJIB berurutan
        // dengan gap tepat 1 slot, ditegakkan sebagai hard constraint di HC15.

        // ── SC13: Matkul berat berturutan satu hari ───────────────────────
        foreach ($dosenSchedule as $did => $hariSlots) {
            foreach ($hariSlots as $hari => $slots) {
                if (count($slots) < 2) { continue; }
                usort($slots, fn($a, $b) => $a[0] <=> $b[0]);
                for ($i = 1; $i < count($slots); $i++) {
                    $gap         = $slots[$i][0] - $slots[$i-1][1] - 1;
                    $prevHeavy   = $slots[$i-1][2] === Gene::KATEGORI_BERAT;
                    $currHeavy   = $slots[$i][2]   === Gene::KATEGORI_BERAT;
                    if ($gap === 0 && $prevHeavy && $currHeavy) {
                        $softPenalty += $this->wSC13_heavy;
                    }
                }
            }
        }

        // ── SC17: Utamakan mulai jam 8 pagi (slot 1) jika ada kuliah reguler ──
        foreach ($ruangIndex as $rid => $hariSlots) {
            foreach ($hariSlots as $hari => $slots) {
                if (isset($roomHasRegularClass[$rid][$hari]) && !isset($slots[1])) {
                    $softPenalty += $this->wSC17_start8;
                }
            }
        }

        // ── Final fitness ────────────────────────────────────────────────
        // Normalisasi penalti ke skala 0-1 menggunakan sigmoid-like clamp
        // sehingga fitness tidak pernah flat 0 meski penalti sangat besar.
        // Formula: fitness = Max / (1 + Penalty)
        // Ini memastikan kurva gradien tetap ada bahkan untuk kromosom terburuk.
        $hardPenaltyPerGene = $hardPenalty / $n;
        
        // Self-Adaptive Penalty: SC dinonaktifkan di awal, aktif bertahap hingga generasi 50%
        $softScale = min(1.0, $this->lastProgress * 2.0);
        $softPenaltyPerGene = ($softPenalty / $n) * $softScale;

        // Hard dan soft dikombinasi dengan bobot berbeda
        // Hard mendominasi; soft hanya berperan setelah hard = 0
        if ($hardPenaltyPerGene > 0) {
            // Ada hard violation: fitness max 70, menggunakan sigmoid-like curve agar tidak flat 0
            $this->fitness = max(0.0, 70.0 / (1.0 + $hardPenaltyPerGene * 2.0));
        } else {
            // Tidak ada hard violation: fitness 70-100, dikurangi soft penalty secara sigmoid (pengali diturunkan dari 3.0 ke 1.0 agar fitness realistis mencapai >90%)
            $this->fitness = 70.0 + (30.0 / (1.0 + $softPenaltyPerGene * 1.0));
        }

        // Akumulasi soft violations (unit count untuk reporting)
        // Hitung soft violations dari penalti yang sudah dikumpulkan
        $softViolationCount = 0;
        // SC5: room fit violations & SC15: heavy subjects in morning & HC10/HC13 (single loop)
        foreach ($this->genes as $gene) {
            $softViolationCount += $gene->validateRoomFit();
            $softViolationCount += $gene->validateCategorySlotPreference($this->morningEndSlot);

            // Tambahkan HC10 dan HC13 karena sekarang diperlakukan sebagai soft constraint
            if (!empty($this->validRuangIds) && !$gene->validateRoomExists($this->validRuangIds)) {
                $softViolationCount++;
            }
            if (!$gene->validateRoomType()) {
                $softViolationCount++;
            }
        }
        // SC17: slot 1 empty count
        foreach ($ruangIndex as $rid => $hariSlots) {
            foreach ($hariSlots as $hari => $slots) {
                if (isset($roomHasRegularClass[$rid][$hari]) && !isset($slots[1])) {
                    $softViolationCount++;
                }
            }
        }
        // Hitung actual count untuk soft constraints lainnya (SC1, SC2, SC3, SC4, SC6, SC11, SC13)
        // SC1: variance penalty count (setiap dosen dengan variance > 0)
        foreach ($dosenDayClasses as $hariKelas) {
            if (count($hariKelas) >= 2) {
                $softViolationCount++; // Satu violation per dosen dengan distribusi tidak merata
            }
        }
        // SC2: gap count (setiap gap antara kelas)
        foreach ($dosenSchedule as $hariSlots) {
            foreach ($hariSlots as $slots) {
                if (count($slots) >= 2) {
                    usort($slots, fn($a, $b) => $a[0] <=> $b[0]);
                    for ($i = 1; $i < count($slots); $i++) {
                        $between = $slots[$i][0] - $slots[$i-1][1] - 1;
                        if ($between > 0) { $softViolationCount += $between; }
                    }
                }
            }
        }
        // SC3: kelas > 3 per hari
        foreach ($dosenDayClasses as $hariKelas) {
            foreach ($hariKelas as $cnt) {
                if ($cnt > 3) {
                    $softViolationCount += ($cnt - 3);
                }
            }
        }
        // SC4: ruangan berbeda per hari
        foreach ($dosenDayRooms as $hariRooms) {
            foreach ($hariRooms as $rooms) {
                $diff = count($rooms) - 1;
                if ($diff > 0) {
                    $softViolationCount += $diff;
                }
            }
        }
        // SC6: variansi penggunaan ruangan (satu violation jika variance > threshold)
        if (count($roomUsage) > 1) {
            $vals = array_values($roomUsage);
            $mean = array_sum($vals) / count($vals);
            $var  = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $vals)) / count($vals);
            if ($var > 1.0) { $softViolationCount++; }
        }
        // SC11: selisih kelas antar hari (satu violation jika variance > threshold)
        $hariVals = array_values($hariCount);
        if (count($hariVals) > 1) {
            $hariMean = array_sum($hariVals) / count($hariVals);
            $hariVar  = array_sum(array_map(fn($v) => ($v - $hariMean) ** 2, $hariVals)) / count($hariVals);
            if ($hariVar > 2.0) { $softViolationCount++; }
        }
        // SC13: matkul berat berturutan
        foreach ($dosenSchedule as $hariSlots) {
            foreach ($hariSlots as $slots) {
                if (count($slots) < 2) { continue; }
                usort($slots, fn($a, $b) => $a[0] <=> $b[0]);
                for ($i = 1; $i < count($slots); $i++) {
                    $gap         = $slots[$i][0] - $slots[$i-1][1] - 1;
                    $prevHeavy   = $slots[$i-1][2] === Gene::KATEGORI_BERAT;
                    $currHeavy   = $slots[$i][2]   === Gene::KATEGORI_BERAT;
                    if ($gap === 0 && $prevHeavy && $currHeavy) {
                        $softViolationCount++;
                    }
                }
            }
        }

        $this->constraintViolations = $softViolationCount;

        $this->fitnessIsDirty = false;
    }

    // ────────────────────────────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────────────────────────────

    private function resetCounters(): void
    {
        $this->conflicts = $this->dosenConflicts = $this->ruanganConflicts = 0;
        $this->constraintViolations = $this->hardViolations = 0;
    }

    /** Dapatkan set hari unik dari semua genes. */
    private function getHariIds(): array
    {
        $set = [];
        foreach ($this->genes as $g) { $set[$g->hariId] = true; }
        return array_keys($set);
    }

    /**
     * Hash ringkas — hanya pakai hariId+slotMulai+dosenId (tanpa ruangId)
     * untuk cache check. Ruangan jarang berubah, jadi ini tetap akurat
     * sambil lebih cepat di-compute.
     * markDirty() dipanggil eksplisit saat ruangan berubah.
     */
    public function buildGenesHash(): string
    {
        $parts = [];
        foreach ($this->genes as $g) {
            $parts[] = "{$g->hariId}:{$g->slotMulai}:{$g->dosenId}:{$g->ruangId}";
        }
        return implode('|', $parts);
    }

    // ────────────────────────────────────────────────────────────────────
    // Accessors
    // ────────────────────────────────────────────────────────────────────

    public function getFitness(): float         { return $this->fitness; }
    public function getConflicts(): int         { return $this->conflicts; }
    public function getDosenConflicts(): int    { return $this->dosenConflicts; }
    public function getRuanganConflicts(): int  { return $this->ruanganConflicts; }
    public function getConstraintViolations(): int { return $this->constraintViolations; }
    public function getHardViolations(): int    { return $this->hardViolations; }
    /**
     * Solusi feasible jika tidak ada konflik hard (HC1/HC2) dan
     * tidak ada pelanggaran HC struktural (HC6, HC12, HC14, HC15).
     * HC10 (ruangan exists) dan HC13 (tipe ruangan) bersifat lunak
     * karena bergantung pada kelengkapan data yang bisa saja belum lengkap.
     */
    public function isFeasible(): bool
    {
        return $this->conflicts === 0 && $this->hardViolations === 0;
    }

    public function getGenes(): array           { return $this->genes; }
    public function count(): int                { return count($this->genes); }

    public function getGeneByKelasId(int $kelasId): ?Gene
    {
        foreach ($this->genes as $gene) {
            if ($gene->kelasId === $kelasId) { return $gene; }
        }
        return null;
    }

    public function copy(): Chromosome
    {
        $c = new Chromosome(array_map(fn($g) => $g->copy(), $this->genes));
        // Salin semua state fitness
        $c->fitness              = $this->fitness;
        $c->conflicts            = $this->conflicts;
        $c->dosenConflicts       = $this->dosenConflicts;
        $c->ruanganConflicts     = $this->ruanganConflicts;
        $c->constraintViolations = $this->constraintViolations;
        $c->hardViolations       = $this->hardViolations;
        $c->fitnessIsDirty       = $this->fitnessIsDirty;
        $c->genesHash            = $this->genesHash;
        // Salin parameter constraint via setConstraintParams agar cache ter-invalidate jika beda
        $c->setConstraintParams(
            $this->eveningStartSlot,
            $this->breakSlots,
            $this->validRuangIds,
            $this->morningEndSlot,
            $this->maxSksDayDosen,
            $this->parallelPairs
        );
        return $c;
    }

    public function toArray(): array
    {
        return [
            'genes'           => array_map(fn($g) => $g->toArray(), $this->genes),
            'fitness'         => $this->fitness,
            'conflicts'       => $this->conflicts,
            'dosenConflicts'  => $this->dosenConflicts,
            'ruanganConflicts'=> $this->ruanganConflicts,
            'hardViolations'  => $this->hardViolations,
            'softViolations'  => $this->constraintViolations,
        ];
    }
}
