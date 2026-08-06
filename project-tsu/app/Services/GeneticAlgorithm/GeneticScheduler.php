<?php

namespace App\Services\GeneticAlgorithm;

/**
 * GeneticScheduler — Mesin utama Algoritma Genetika untuk penjadwalan kuliah.
 *
 * ═══════════════════════════════════════════════════════════════════════════
 * SMART SOLVING INVENTORY
 * ═══════════════════════════════════════════════════════════════════════════
 *
 * ── LOW-LEVEL (data integrity & input safety) ───────────────────────────────
 *
 * L1 · Break-slot dari DB, bukan hardcode
 *      Slot istirahat ditentukan dari kolom `sesi` di tabel slot_waktu
 *      (nilainya 'istirahat' atau waktu 12:00–13:00).  Hardcode [4,5]
 *      diganti dengan deteksi otomatis dari data yang dikirim scheduler.
 *
 * L2 · Evening-start-slot dari DB
 *      Slot pertama sesi malam (dipakai kelas-S) dihitung dari data slot,
 *      bukan angka magis "11" atau "12".
 *
 * L3 · Validasi kelengkapan kelas
 *      Kelas tanpa matakuliah, tanpa ruangan-options, atau dengan SKS=0
 *      di-skip dengan pesan warning, bukan menyebabkan gene invalid.
 *
 * L4 · Gene boundary guard
 *      `slotMulai + durasi - 1` tidak boleh melebihi maxSlot.  Slot mulai
 *      dipilih dari rentang [1 .. maxSlot - durasi + 1].
 *
 * L5 · Fallback ruangan global
 *      Jika matakuliah tidak punya ruangan di pivot matkul_ruang, cari
 *      ruangan global yang kapasitasnya cukup daripada mengembalikan id=0.
 *
 * ── MEDIUM-LEVEL (convergence & population management) ──────────────────────
 *
 * M1 · Two-point crossover selang-seling dengan single-point
 *      Single-point sering menghasilkan anak yang terlalu mirip salah satu
 *      orang-tua.  Dengan dua titik potong, gen tengah dari parent-2 masuk
 *      ke child-1 → diversitas lebih tinggi tanpa kehilangan segmen baik.
 *
 * M2 · Repair operator pasca-crossover
 *      Setelah crossover, cek setiap pasang gen dalam offspring yang punya
 *      konflik dosen/ruangan.  Ganti slot/hari salah satu gen secara greedy
 *      sehingga offspring langsung lebih baik dari hasil crossover murni.
 *
 * M3 · Stagnation solver berlapis
 *      Stagnasi level-1 (10 gen) → naikkan mutation-rate 1.5×.
 *      Stagnasi level-2 (25 gen) → inject 30% kromosom acak baru.
 *      Stagnasi level-3 (50 gen) → re-seed seluruh populasi kecuali elite-5.
 *
 * M4 · Diversity via hash-set
 *      Diversity dihitung dari |unique_hashes| / populasiSize — O(n) bukan
 *      O(n²). Mutasi hanya diperkuat jika diversity < 0.25.
 *
 * M5 · Elitism adaptif
 *      Simpan top-K elite (default K=3) bukan hanya 1, sehingga setelah
 *      populasi di-reset, beberapa solusi terbaik tetap tersimpan.
 *
 * ── HIGH-LEVEL (solution quality & termination) ─────────────────────────────
 *
 * H1 · Greedy local search pasca-GA
 *      Setelah loop GA, untuk setiap gene yang masih konflik, coba semua
 *      kombinasi (hari, slot) yang valid dan pilih yang mengurangi konflik
 *      paling banyak.  Lebih efektif dari "slot+1" saja.
 *
 * H2 · Feasibility target bertahap
 *      Berhenti awal jika: (a) conflicts=0 DAN violations=0 (sempurna),
 *      ATAU (b) conflicts=0 DAN fitness≥90 (dapat diterima).
 *      Tidak langsung berhenti di fitness≥80 karena bisa ada soft violation.
 *
 * H3 · Timeout-aware loop
 *      Rekam waktu mulai; jika sudah > 270 detik (dari limit 300s SSE),
 *      kembalikan solusi terbaik saat itu tanpa menunggu generasi selesai.
 *
 * H4 · Fallback solution yang bermakna
 *      Jika terjadi exception fatal, bangun solusi greedy (bukan semua
 *      di hari=1 slot=1) dengan distribusi merata ke semua hari.
 *
 * ── ANTI-TRAPPING ────────────────────────────────────────────────────────────
 *
 * T1 · Simulated-Annealing Local Search (SA-LS)
 *      Ganti greedy-only local search dengan SA-LS: terima solusi yang
 *      lebih buruk dengan probabilitas exp(-Δf/T) di mana T menurun seiring
 *      iterasi. Ini memungkinkan escape dari local optimum tanpa re-seed penuh.
 *
 * T2 · Conflict-Graph Deadlock Breaker
 *      Bangun graf konflik (adjacency set per gene). Deteksi siklus (deadlock
 *      segitiga/persegi): jika gene A konflik B, B konflik C, C konflik A,
 *      tandai semua sebagai "locked". Selesaikan deadlock dengan memindahkan
 *      seluruh cluster ke hari berbeda sekaligus, bukan satu per satu.
 *
 * T3 · Dosen-Load Balancer
 *      Sebelum inisialisasi populasi, hitung beban dosen (jumlah kelas).
 *      Dosen dengan beban > threshold mendapat slot pre-assigned yang dijamin
 *      tidak overlap, sehingga gen mereka tidak pernah masuk conflict-loop.
 *
 * T4 · Fitness-Plateau Detector + Niche Pressure
 *      Jika variance fitness populasi < 0.001 (semua individu identik dalam
 *      fitness), aktifkan "niche pressure": kromosom yang hash-nya identik
 *      dengan yang lain mendapat penalti tambahan sehingga seleksi memaksa
 *      eksplorasi ke kromosom yang berbeda.
 *
 * T5 · Guided Re-Seed dari Elite Archive
 *      Re-seed (stagnation L3) tidak lagi murni acak. 50% populasi baru
 *      dibangun dengan "elite-guided perturbation": ambil elite terbaik,
 *      acak ulang hanya gen-gen yang masih berkonflik, pertahankan gen
 *      yang sudah aman. Jauh lebih efektif daripada kromosom sepenuhnya acak.
 *
 * ── PARALLEL-PAIR SEQUENCING (HC15) ─────────────────────────────────────────
 *
 * P1 · Deteksi pasangan kelas paralel (matkul sama + dosen sama, persis 2
 *      kelas non-S) saat prepareData(). Kelas suffix -S dikecualikan total.
 *      Disimpan sebagai $parallelPairs = [[kelasIdA, kelasIdB], ...] dengan
 *      A = urutan abjad nama_kelas lebih dulu.
 *
 * P2 · Saat inisialisasi gene untuk kelasId yang merupakan bagian dari
 *      pasangan, gunakan slot yang konsisten dengan pasangannya jika
 *      pasangannya sudah diinisialisasi (createPairedGene).
 *
 * P3 · Repair operator memprioritaskan perbaikan pasangan HC15 sebelum
 *      memperbaiki konflik biasa, karena re-slot satu sisi pasangan bisa
 *      merusak hubungan gap-1 dengan sisi lainnya.
 *
 * ═══════════════════════════════════════════════════════════════════════════
 */
class GeneticScheduler
{

    // ── Parameter GA ────────────────────────────────────────────────────────
    private int   $populationSize;
    private int   $maxGenerations;
    private float $crossoverRate;
    private float $mutationRate;
    private float $originalMutationRate;
    private float $originalCrossoverRate;

    // ── Data problem ─────────────────────────────────────────────────────────
    private array $kelasData   = [];
    private array $slots       = [];
    private array $hariList    = [];
    private array $ruanganList = [];
    private array $hariSlotMap = []; // Pemetaan Hari -> Slot

    // ── [L1][L2] Parameter constraint dari data, bukan hardcode ─────────────
    private int   $maxSlot          = 14;
    private int   $eveningStartSlot = 11;
    private array $breakSlots       = [];
    private array $validRuangIds    = [];  // HC10: semua id_ruang terdaftar
    private int   $morningEndSlot   = 6;   // SC15/SC16: batas slot pagi
    private int   $maxSksDayDosen   = 8;   // HC14

    // ── Constraints Toggles dari UI ──────────────────────────────────
    private ?array $activeConstraints = null;

    /**
     * [BARU][HC16] Slot Sholat Jumat — dikeluarkan secara struktural dari
     * SEMUA pool kandidat slot pada hari Jumat untuk SEMUA jenis kelas
     * (reguler, praktikum, kelas malam -S). Tidak ada gene yang boleh
     * overlap slot ini pada hari ini — lihat overlapsBlockedFridaySlot().
     */
    private int $hariJumat       = 5; // id_hari untuk Jumat
    private int $slotSholatJumat = 6; // id_slot batas akhir steril Sholat Jumat (slot 5 dan 6 dilarang)

    /**
     * [BARU][P1] HC15 — Pasangan kelas paralel (matkul sama + dosen sama).
     * Format: [ [kelasIdA, kelasIdB], ... ] — A = abjad nama_kelas lebih dulu.
     * Hanya berisi grup yang punya PERSIS 2 kelas non-S untuk (kode_matkul, id_dosen) yang sama.
     */
    private array $parallelPairs = [];

    /**
     * [BARU][P1] Lookup cepat: kelasId => kelasId pasangannya (HC15).
     * Dibangun bersamaan dengan $parallelPairs untuk akses O(1) saat repair/init.
     */
    private array $pairPartnerOf = [];

    /**
     * [BARU] Lookup kelas gabungan: primary_kelasId => [secondary_kelasId1, ...]
     * Kelas secondary tidak dimasukkan ke GA, tapi jadwalnya diduplikasi di akhir (gabungan).
     */
    private array $gabunganMap = [];

    // ── Populasi & elite ─────────────────────────────────────────────────────
    /** @var Chromosome[] */
    private array $population = [];
    /** @var Chromosome[] [M5] Simpan top-K elite */
    private array $elites     = [];
    private int   $eliteK     = 3;

    // ── Stagnation tracking [M3] ─────────────────────────────────────────────
    private float $lastBestFitness           = 0.0;
    private int   $stagnationCounter         = 0;
    private int   $stagnationRestartCount    = 0;
    private int   $stagnationLevel1          = 10;
    private float $earlyExitFitness          = 90.0;

    // ── Progress callback ────────────────────────────────────────────────────
    private $progressCallback = null;

    // ── [H3] Waktu mulai untuk timeout protection ────────────────────────────
    private float $startTime   = 0.0;
    private float $timeLimit   = 480.0; // 8 menit — dikonfigurasi via setTimeLimit()

    // ── Log masalah ──────────────────────────────────────────────────────────
    private array $problemLog = [];

    // ── [T3] Pre-assigned slots untuk dosen beban tinggi ────────────────────
    // [ dosenId => [ hariId => [slotMulai, ...] ] ]  — slot yang sudah dipakai
    private array $dosenPreassigned = [];
    private int   $dosenLoadThreshold = 3; // Dosen dengan ≥ N kelas → pre-assign

    // ── [T4] Fitness plateau tracking ────────────────────────────────────────
    private float $plateauVarianceThreshold = 0.5;  // variance fitness < ini = plateau
    private bool  $nichePressureActive      = false;

    // ── [T2] Conflict-graph deadlock tracking ────────────────────────────────
    private int $deadlockCheckInterval = 15; // cek setiap N generasi


    public function __construct(
        int   $populationSize  = 100,
        int   $maxGenerations  = 200,
        float $crossoverRate   = 0.8,
        float $mutationRate    = 0.1
    ) {
        $this->populationSize        = max(4, $populationSize);
        $this->maxGenerations        = max(1, $maxGenerations);
        $this->crossoverRate         = min(1.0, max(0.0, $crossoverRate));
        $this->mutationRate          = min(1.0, max(0.0, $mutationRate));
        $this->originalMutationRate  = $this->mutationRate;
        $this->originalCrossoverRate = $this->crossoverRate;

        // Auto-set timeLimit berdasarkan ukuran problem
        // Populasi 200 × 500 gen butuh ~5-8 menit tergantung jumlah kelas
        $this->timeLimit = min(600.0, max(270.0, ($populationSize * $maxGenerations) / 500.0));
    }

    /** Override batas waktu (detik). */
    public function setTimeLimit(float $seconds): void
    {
        $this->timeLimit = max(30.0, $seconds);
    }

    public function setEliteK(int $k): void { $this->eliteK = max(1, $k); }
    public function setStagnationThreshold(int $val): void { $this->stagnationLevel1 = max(5, $val); }
    public function setTemp(int $temp): void { /* placeholder for future SA integration */ }
    public function setEarlyExitFitness(float $val): void { $this->earlyExitFitness = $val; }
    public function setMaxSksDayDosen(int $val): void { $this->maxSksDayDosen = max(2, $val); }
    public function setCrossoverRate(float $rate): void {
        $this->crossoverRate = min(1.0, max(0.0, $rate));
        $this->originalCrossoverRate = $this->crossoverRate;
    }
    public function setMutationRate(float $rate): void {
        $this->mutationRate = min(1.0, max(0.0, $rate));
        $this->originalMutationRate = $this->mutationRate;
    }

    private array $lockedRuangIndex = [];
    private array $lockedDosenIndex = [];

    public function setOccupiedJadwals($existingJadwals): void
    {
        $this->lockedRuangIndex = [];
        $this->lockedDosenIndex = [];

        if (!$existingJadwals) return;

        foreach ($existingJadwals as $j) {
            $hari = (int) $j->id_hari;
            $slotMulai = (int) $j->id_slot_mulai;
            $durasi = (int) ($j->durasi_sks ?? 1);
            $ruangId = (int) ($j->id_ruang ?? 0);
            $dosenId = (int) ($j->id_dosen ?: ($j->kelas?->pengampuKelas?->first()?->id_dosen ?? 0));

            for ($s = $slotMulai; $s < $slotMulai + $durasi; $s++) {
                if ($ruangId > 0 && $hari > 0) {
                    $this->lockedRuangIndex[$ruangId][$hari][$s] = true;
                }
                if ($dosenId > 0 && $hari > 0) {
                    $this->lockedDosenIndex[$dosenId][$hari][$s] = true;
                }
            }
        }
    }

    public function setActiveConstraints(?array $constraints): void
    {
        $this->activeConstraints = $constraints;
    }

    public function isConstraintActive(string $code): bool
    {
        if ($this->activeConstraints === null) {
            return true;
        }
        return in_array($code, $this->activeConstraints);
    }

    /**
     * Kembalikan hasil dari kromosom terbaik saat ini (untuk early-exit SSE).
     * Dipanggil dari progress callback ketika sinyal 'optimal' diterima.
     */
    public function getBestResult(): ?array
    {
        if (empty($this->population) && empty($this->elites)) {
            return null;
        }

        // Ambil dari elites jika ada (lebih reliable dari current population)
        $best = !empty($this->elites) ? $this->elites[0] : $this->getBestChromosome();

        if ($best === null || count($best->getGenes()) === 0) {
            return null;
        }

        return [
            'genes'                => $this->expandGabunganGenes($best->getGenes()),
            'fitness_pct'          => round($best->getFitness(), 2),
            'generasi'             => 0, // akan diisi oleh caller dari progress data
            'total_kelas'          => count($best->getGenes()),
            'pelanggaran'          => $best->getConflicts(),
            'dosen_conflicts'      => $best->getDosenConflicts(),
            'ruangan_conflicts'    => $best->getRuanganConflicts(),
            'kelas_conflicts'      => $best->getKelasConflicts(),
            'constraint_violations'=> $best->getConstraintViolations(),
            'problem_log'          => $this->problemLog,
        ];
    }

    /**
     * [BARU] Expands genes for merged classes (gabungan)
     */
    private function expandGabunganGenes(array $genes): array
    {
        if (empty($this->gabunganMap)) {
            return $genes;
        }

        $expandedGenes = [];
        foreach ($genes as $gene) {
            $expandedGenes[] = $gene;
            if (isset($this->gabunganMap[$gene->kelasId])) {
                foreach ($this->gabunganMap[$gene->kelasId] as $secondaryId) {
                    $clonedGene = clone $gene;
                    $clonedGene->kelasId = $secondaryId;
                    $expandedGenes[] = $clonedGene;
                }
            }
        }
        return $expandedGenes;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // PUBLIC: Entry Point
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Jalankan GA dan kembalikan solusi terbaik.
     *
     * @param  mixed         $kelas            Collection Kelas (Eloquent)
     * @param  array         $slots            [['id'=>int,'waktu_mulai'=>str,'waktu_selesai'=>str], ...]
     * @param  array         $hariList         [1,2,3,4,5]
     * @param  callable|null $progressCallback function(array $data): void
     * @return array
     */
    public function run($kelas, array $slots, array $hariList, ?callable $progressCallback = null): array
    {
        $this->startTime        = microtime(true);
        $this->progressCallback = $progressCallback;
        $generation             = 0;

        try {
            // ── [LOW] Validasi & persiapan ───────────────────────────────────
            $this->validateInputData($kelas, $slots, $hariList);
            $this->prepareData($kelas, $slots, $hariList);

            if (empty($this->kelasData)) {
                throw new \RuntimeException('Tidak ada kelas valid yang bisa dijadwalkan.');
            }

            // [T3] Pre-assign slot dosen beban tinggi sebelum inisialisasi
            $this->preAssignHighLoadDosen();

            // ── Inisialisasi populasi ────────────────────────────────────────
            $this->initializePopulation();
            $this->evaluatePopulation(0);

            $bestChromosome = $this->getBestChromosome()->copy();
            $this->updateElites($bestChromosome);
            $this->lastBestFitness = $bestChromosome->getFitness();

            // ── Main GA loop ──────────────────────────────────────────────────
            for ($generation = 1; $generation <= $this->maxGenerations; $generation++) {

                // [H3] Timeout check
                if ((microtime(true) - $this->startTime) >= $this->timeLimit) {
                    $this->logProblem('timeout', "Timeout di generasi {$generation}");
                    break;
                }

                // [M3] Deteksi & selesaikan stagnasi
                $this->handleStagnation($generation, $bestChromosome);

                // [T4] Deteksi & tangani fitness plateau
                $this->handleFitnessPlateauAndNiche();

                // [T2] Deadlock breaker periodik (setiap N generasi)
                if ($generation % $this->deadlockCheckInterval === 0) {
                    $this->breakDeadlocksInPopulation();
                }

                // [Bug1-fix] Evaluasi populasi setelah kemungkinan dimodifikasi oleh stagnasi/niche pressure/deadlock breaker
                $this->evaluatePopulation($generation);

                // Seleksi
                $parents  = $this->selection();

                // [M1] Crossover dua-titik selang-seling
                $offspring = $this->crossover($parents);

                // [M2] Repair operator
                $this->repairOffspring($offspring);

                // Mutasi adaptif [M4]
                $this->mutate($offspring);

                // [M5] Ganti populasi + inject elites
                $this->replacePopulation($offspring);
                $this->evaluatePopulation($generation);

                // Update best
                $currentBest = $this->getBestChromosome();
                if ($currentBest->getFitness() > $bestChromosome->getFitness()) {
                    $bestChromosome = $currentBest->copy();
                    $this->updateElites($bestChromosome);
                    $this->stagnationCounter = 0;
                    $this->lastBestFitness   = $bestChromosome->getFitness();
                }

                // Progress report
                $this->reportProgress($generation, $bestChromosome);

                // [H2] Early exit — solusi sempurna atau layak
                if ($bestChromosome->isFeasible()) {
                    if ($bestChromosome->getConstraintViolations() === 0) {
                        // Sempurna — kirim sinyal optimal ke callback
                        $this->reportOptimalFound($generation, $bestChromosome, 'perfect');
                        break;
                    }
                    if ($bestChromosome->getFitness() >= $this->earlyExitFitness) {
                        // Layak — kirim sinyal optimal ke callback
                        $this->reportOptimalFound($generation, $bestChromosome, 'acceptable');
                        break;
                    }
                }

                // Early exit jika fitness sudah sangat baik (≥ +5%) tanpa hard conflict
                if ($bestChromosome->getConflicts() === 0 && $bestChromosome->getFitness() >= min(100.0, $this->earlyExitFitness + 5.0)) {
                    $this->reportOptimalFound($generation, $bestChromosome, 'excellent');
                    break;
                }

                // [USER REQUEST] Early exit jika sudah tidak ada physical conflicts dan solusi sudah stagnan (tidak membaik).
                // Artinya ini adalah solusi 'terbaik' yang bisa didapat secara alami oleh GA tanpa perlu memaksa sampai maxGenerations.
                if ($bestChromosome->getConflicts() === 0 && $this->stagnationCounter >= ($this->stagnationLevel1 * 1.5)) {
                    $this->reportOptimalFound($generation, $bestChromosome, 'acceptable');
                    break;
                }
            }

            // [H1] Local search pasca-GA
            $bestChromosome = $this->localSearch($bestChromosome);

            return [
                'genes'               => $this->expandGabunganGenes($bestChromosome->getGenes()),
                'fitness_pct'         => round($bestChromosome->getFitness(), 2),
                'generasi'            => $generation,
                'total_kelas'         => count($this->kelasData),
                'pelanggaran'         => $bestChromosome->getConflicts(),
                'dosen_conflicts'     => $bestChromosome->getDosenConflicts(),
                'ruangan_conflicts'   => $bestChromosome->getRuanganConflicts(),
                'kelas_conflicts'     => $bestChromosome->getKelasConflicts(),
                'constraint_violations' => $bestChromosome->getConstraintViolations(),
                'problem_log'         => $this->problemLog,
            ];

        } catch (\Exception $e) {
            // [H4] Fallback bermakna
            return $this->handleCriticalError($e, $generation);
        }
    }


    // ═══════════════════════════════════════════════════════════════════════
    // LOW-LEVEL: Validasi & Persiapan Data
    // ═══════════════════════════════════════════════════════════════════════

    /** [L-all] Validasi argumen sebelum memproses. */
    private function validateInputData($kelas, array $slots, array $hariList): void
    {
        if (empty($kelas) || (is_countable($kelas) && count($kelas) === 0)) {
            throw new \InvalidArgumentException('Data kelas tidak boleh kosong.');
        }
        if (empty($slots)) {
            throw new \InvalidArgumentException('Data slot waktu tidak boleh kosong.');
        }
        if (empty($hariList)) {
            throw new \InvalidArgumentException('Data hari tidak boleh kosong.');
        }
        if ($this->populationSize < 4) {
            throw new \InvalidArgumentException('Ukuran populasi minimal 4.');
        }
    }

    /**
     * [L1][L2][L3][L4][L5] Bangun struktur data internal dari input Eloquent.
     */
    private function prepareData($kelas, array $slots, array $hariList): void
    {
        $this->slots    = $slots;
        $this->hariList = array_values($hariList);

        // Load mapping dari database
        $haris = \App\Models\Hari::whereIn('id_hari', $this->hariList)->with('slotWaktus')->get();
        foreach ($haris as $h) {
            $this->hariSlotMap[$h->id_hari] = $h->slotWaktus->pluck('id_slot')->toArray();
        }

        // ── Hitung maxSlot & [L1] break-slots dari data ──────────────────────
        $slotIds = array_column($slots, 'id');
        $this->maxSlot = !empty($slotIds) ? (int) max($slotIds) : 14;

        // [L1] Jam istirahat diabaikan sesuai request user (penjadwalan tidak terikat jam istirahat)
        // [HC-NEW] Slot 14 (**) dilarang ditempati (kecuali untuk kelas 5 SKS yang diatur di Gene)
        $this->breakSlots = [14];

        // [L2] Satu loop — set eveningStartSlot DAN morningEndSlot sekaligus
        // Tidak ada duplikat; loop sebelumnya dihapus.
        // [FIX] eveningStartSlot diubah ke 11 agar kelas pagi maks slot 10, kelas sore mulai slot 11
        $this->eveningStartSlot = 11;
        $this->morningEndSlot   = 1;  // fallback: slot pagi pertama
        foreach ($slots as $s) {
            $mulai = substr($s['waktu_mulai'] ?? '00:00', 0, 5);
            // Slot pagi: terakhir yang < 12:00 (batas atas pagi)
            if ($mulai < '12:00') {
                $this->morningEndSlot = (int) $s['id'];
            }
        }

        $this->logProblem('slot_config',
            "eveningStart={$this->eveningStartSlot} morningEnd={$this->morningEndSlot} "
          . "maxSlot={$this->maxSlot} breakSlots=[" . implode(',', $this->breakSlots) . "]"
        );
        $this->ruanganList  = [];
        $this->validRuangIds = [];
        foreach ($kelas as $k) {
            $mk = $k->matakuliah ?? null;
            if (!$mk) { continue; }
            try {
                foreach ($mk->ruangans ?? [] as $r) {
                    if (!isset($this->ruanganList[$r->id_ruang])) {
                        $this->ruanganList[$r->id_ruang] = [
                            'id'           => (int) $r->id_ruang,
                            'nama'         => $r->nama_ruang ?? "Ruang-{$r->id_ruang}",
                            'kapasitas'    => (int) ($r->kapasitas ?? 0),
                            'tipe_ruangan' => $r->tipe_ruangan ?? Gene::TIPE_REGULER,
                        ];
                        $this->validRuangIds[] = (int) $r->id_ruang;  // HC10
                    }
                }
            } catch (\Exception $e) { /* abaikan */ }
        }
        $this->validRuangIds = array_unique($this->validRuangIds);

        $this->gabunganMap = [];
        $rawKelasData = [];

        foreach ($kelas as $k) {
            $mk  = $k->matakuliah ?? null;
            $sks = (int) ($mk->sks ?? 0);

            // [L3] Skip kelas dengan data tidak valid
            if (!$mk || $sks <= 0) {
                $this->logProblem('skip_kelas',
                    "Kelas id={$k->id_kelas} dilewati: matakuliah/SKS tidak valid.");
                continue;
            }

            // [NEW] Ambil id_dosen dari relasi pengampuKelas (bukan kolom id_dosen lama)
            $idDosen = 0;
            if (!empty($k->pengampuKelas) && $k->pengampuKelas->count() > 0) {
                $idDosen = (int) $k->pengampuKelas->first()->id_dosen;
            }
            
            $originalRuangans = [];
            try {
                $originalRuangans = $mk->ruangans
                    ? $mk->ruangans->pluck('id_ruang')->map(fn($id) => (int) $id)->toArray()
                    : [];
            } catch (\Exception $e) {
                // Abaikan error relasi ruangan
            }

            $rawKelasData[] = [
                'id'              => (int) $k->id_kelas,
                'nama'            => (string) ($k->nama_kelas ?? "Kelas-{$k->id_kelas}"),
                'sks'             => $sks,
                'jenis'           => strtolower(trim($mk->jenis ?? Gene::JENIS_TEORI)),
                'kategori'        => strtolower(trim($mk->kategori ?? Gene::KATEGORI_SEDANG)),
                'id_dosen'        => $idDosen,
                'kapasitas'       => (int) ($k->kapasitas ?? 0),
                'kode_matkul'     => (string) ($mk->kode_matkul ?? ''),
                'nama_matkul'     => trim($mk->nama_matkul ?? ''),
                'original_ruangans' => $originalRuangans,
            ];
        }

        // Proses penggabungan kelas (Class Merging)
        $this->kelasData = [];
        $groupedKelas = [];
        foreach ($rawKelasData as $info) {
            $groupedKelas['ungrouped_' . $info['id']][] = $info; // kelas tidak digabung secara otomatis sesuai permintaan
        }

        foreach ($groupedKelas as $key => $group) {
            $primary = $group[0];
            $secondaryIds = [];
            $totalKapasitas = $primary['kapasitas'];
            
            for ($i = 1; $i < count($group); $i++) {
                $secondaryIds[] = $group[$i]['id'];
                $totalKapasitas += $group[$i]['kapasitas'];
                $this->logProblem('kelas_gabungan', "Kelas id={$group[$i]['id']} digabung ke id={$primary['id']} (Kapasitas total: {$totalKapasitas})");
            }
            
            $primary['kapasitas'] = $totalKapasitas;
            
            // Hitung ruanganOptions berdasarkan total kapasitas
            $ruanganOptions = [];
            // Filter original_ruangans yang kapasitasnya masih cukup
            foreach ($primary['original_ruangans'] as $rId) {
                if (isset($this->ruanganList[$rId]) && $this->ruanganList[$rId]['kapasitas'] >= $totalKapasitas) {
                    $ruanganOptions[] = $rId;
                }
            }
            
            // [L5] Fallback: cari ruangan global yang cukup kapasitasnya
            if (empty($ruanganOptions) && !empty($this->ruanganList)) {
                if (empty($primary['original_ruangans'])) {
                    foreach ($this->ruanganList as $r) {
                        if ($r['kapasitas'] >= $totalKapasitas) {
                            $ruanganOptions[] = $r['id'];
                        }
                    }
                    if (!empty($ruanganOptions) && $totalKapasitas > $group[0]['kapasitas']) {
                        $this->logProblem('fallback_ruangan',
                            "Kelas id={$primary['id']} (Gabungan): ruangan dari global fallback karena butuh kapasitas {$totalKapasitas}.");
                    }
                } else {
                    // Kelas ini memiliki ruangan spesifik di matkul_ruang, tetapi kapasitasnya tidak mencukupi.
                    // Jangan fallback ke ruangan global (seperti Perpustakaan) yang tidak diinginkan user.
                    // Tetap gunakan ruangan aslinya, dan biarkan constraint kapasitas (SC) yang menanggung penaltinya.
                    $ruanganOptions = $primary['original_ruangans'];
                    $this->logProblem('capacity_override', "Kelas id={$primary['id']} dipaksa menggunakan ruangan pilihannya meskipun kapasitas tidak mencukupi.");
                }
            }
            
            $primary['ruangan_options'] = $ruanganOptions;
            unset($primary['original_ruangans']);
            
            $this->kelasData[$primary['id']] = $primary;
            if (!empty($secondaryIds)) {
                $this->gabunganMap[$primary['id']] = $secondaryIds;
            }
        }

        // [BARU][P1] Bangun pasangan kelas paralel (HC15) setelah kelasData siap
        $this->buildParallelPairs();
    }

    /**
     * [BARU][P1] HC15 — Deteksi pasangan kelas paralel: matkul sama + dosen
     * sama, persis 2 kelas NON-S (kelas malam/suffix -S dikecualikan total).
     *
     * Grup dengan 1 kelas (tidak ada pasangan) atau 3+ kelas non-S diabaikan
     * dari constraint ini (kasus tidak biasa — biarkan GA bebas mengatur).
     *
     * Urutan A/B ditentukan dari nama_kelas diurutkan alfabetis (string
     * comparison biasa): yang lebih dulu secara abjad = A.
     */
    private function buildParallelPairs(): void
    {
        $this->parallelPairs = [];
        
        if (!$this->isConstraintActive('HC15')) {
            return;
        }
        $this->pairPartnerOf = [];

        // Group by "kode_matkul|id_dosen", hanya kelas non-S
        $groups = []; // key => [kelasId, ...]
        foreach ($this->kelasData as $kelasId => $info) {
            $nama = trim($info['nama'] ?? '');
            $isKelasS = $this->detectKelasS($nama);
            if ($isKelasS) {
                continue; // HC15 tidak berlaku untuk kelas malam
            }

            // Ekstrak prefix kelas (misal: "A1-2A" -> "A1-2") dengan menghapus 1 karakter terakhir
            $prefix = substr($nama, 0, -1);

            $kodeMk = $info['kode_matkul'] ?? '';
            $did    = $info['id_dosen'] ?? 0;
            if ($kodeMk === '' || $did <= 0) {
                continue; // Data tidak lengkap, skip dari grouping
            }
            // Gabungkan kode, dosen, dan prefix kelas agar "A1-2A" hanya berpasangan dengan "A1-2B", BUKAN "A2-2B"
            $key = $kodeMk . '|' . $did . '|' . $prefix;
            $groups[$key][] = $kelasId;
        }

        foreach ($groups as $key => $kelasIds) {
            if (count($kelasIds) < 2) {
                // 1 kelas = tidak ada pasangan untuk dibandingkan.
                continue;
            }

            // Urutkan berdasarkan nama_kelas alfabetis → A, B, C, D...
            usort($kelasIds, function ($a, $b) {
                $namaA = $this->kelasData[$a]['nama'] ?? '';
                $namaB = $this->kelasData[$b]['nama'] ?? '';
                return strcasecmp($namaA, $namaB);
            });

            // Bagi menjadi pasangan-pasangan (A-B, C-D, dst.)
            // Jika ganjil (misal 3), kelas terakhir tidak dipasangkan secara eksplisit.
            for ($i = 0; $i < count($kelasIds) - 1; $i += 2) {
                $kelasIdA = $kelasIds[$i];
                $kelasIdB = $kelasIds[$i + 1];
                
                $this->parallelPairs[] = [$kelasIdA, $kelasIdB];
                $this->pairPartnerOf[$kelasIdA] = $kelasIdB;
                $this->pairPartnerOf[$kelasIdB] = $kelasIdA;
            }
        }

        if (!empty($this->parallelPairs)) {
            $this->logProblem('hc15_pairs',
                count($this->parallelPairs) . ' pasangan kelas paralel terdeteksi untuk HC15 (gap 1 slot).');
        }
    }

    /**
     * Mengembalikan daftar hari yang valid untuk mata kuliah tertentu.
     * Kelas Praktikum hanya boleh Senin, Selasa, Rabu (1, 2, 3).
     */
    public function getValidHariList(string $jenisMatkul = 'Teori'): array
    {
        return $this->hariList;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // LOW-LEVEL: Inisialisasi Gen & Kromosom
    // ═══════════════════════════════════════════════════════════════════════

    private function initializePopulation(): void
    {
        $this->population = [];
        for ($i = 0; $i < $this->populationSize; $i++) {
            // Gunakan pre-assignment untuk 50% kromosom pertama
            $usePreassign = ($i < $this->populationSize / 2);
            $this->population[] = $this->createRandomChromosome($usePreassign);
        }
    }

    private function createRandomChromosome(bool $usePreassign = true): Chromosome
    {
        $genes = [];
        // [BARU][P2] Lacak kelasId yang sudah diberi gene pada kromosom ini,
        // supaya pasangan HC15 bisa langsung disinkronkan slotnya saat dibuat.
        $assignedGenes = [];

        foreach ($this->kelasData as $kelasId => $info) {
            if (isset($assignedGenes[$kelasId])) {
                continue; // Sudah dibuat sebagai bagian dari pasangan sebelumnya
            }

            $partnerId = $this->pairPartnerOf[$kelasId] ?? null;

            if ($partnerId !== null && isset($this->kelasData[$partnerId]) && !isset($assignedGenes[$partnerId])) {
                // [P2] Buat pasangan A & B sekaligus dengan slot yang konsisten (gap 1)
                [$geneA, $geneB] = $this->createPairedGenes($kelasId, $partnerId, $usePreassign);
                $assignedGenes[$kelasId]   = $geneA;
                $assignedGenes[$partnerId] = $geneB;
            } else {
                $assignedGenes[$kelasId] = $this->createRandomGene($kelasId, $info, $usePreassign);
            }
        }

        // Bangun ulang $genes sesuai urutan asli $this->kelasData agar konsisten
        foreach ($this->kelasData as $kelasId => $info) {
            $genes[] = $assignedGenes[$kelasId];
        }

        $c = new Chromosome($genes);
        $c->setConstraintParams(
            $this->eveningStartSlot,
            $this->breakSlots,
            $this->validRuangIds,
            $this->morningEndSlot,
            8,
            $this->parallelPairs,
            $this->activeConstraints,
            $this->lockedRuangIndex,
            $this->lockedDosenIndex
        );
        return $c;
    }

    /**
     * [BARU][P2] Buat dua Gene (A dan B) untuk pasangan HC15 sekaligus,
     * langsung dengan hari sama + gap 1 slot, supaya populasi awal sudah
     * banyak yang feasible terhadap HC15 sejak generasi pertama.
     *
     * @return Gene[]  [$geneA, $geneB]
     */
    private function createPairedGenes(int $kelasIdA, int $kelasIdB, bool $usePreassign = true): array
    {
        $infoA = $this->kelasData[$kelasIdA];
        $infoB = $this->kelasData[$kelasIdB];

        $durasiA = $infoA['sks'];
        $durasiB = $infoB['sks'];

        // Pilih hari acak yang valid untuk pasangan ini
        $validHari = $this->getValidHariList($infoA['jenis'] ?? Gene::JENIS_TEORI);
        $hari = $validHari[array_rand($validHari)];

        // Cari slotMulai untuk A sedemikian sehingga B (gap 1) masih dalam batas
        // A dan B keduanya kelas reguler (non-S), jadi harus selesai < eveningStartSlot.
        $totalSpan = $durasiA + 1 + $durasiB; // durasi A + gap 1 + durasi B
        $maxStartA = max(1, $this->eveningStartSlot - $totalSpan);

        // [BARU][HC16] Kumpulkan kandidat slotMulaiA yang membuat A MAUPUN B
        // sama sekali tidak overlap slot Sholat Jumat (hanya relevan jika $hari = Jumat).
        if ($maxStartA >= 1) {
            $validStartsA = [];
            for ($s = 1; $s <= $maxStartA; $s++) {
                $slotBCandidate = $s + $durasiA + 1;
                $aOverlap = $this->overlapsBlockedFridaySlot($hari, $s, $durasiA) || !$this->isSlotActive($hari, $s, $durasiA);
                $bOverlap = $this->overlapsBlockedFridaySlot($hari, $slotBCandidate, $durasiB) || !$this->isSlotActive($hari, $slotBCandidate, $durasiB);
                if (!$aOverlap && !$bOverlap) {
                    $validStartsA[] = $s;
                }
            }
            if (!empty($validStartsA)) {
                $slotMulaiA = $validStartsA[array_rand($validStartsA)];
            } else {
                // Tidak ada kombinasi yang lolos filter Jumat di hari ini —
                // pindahkan pasangan ke hari lain yang bukan Jumat (lebih aman
                // daripada memaksa overlap slot sholat).
                $nonFridayHari = array_values(array_diff($validHari, [$this->hariJumat]));
                if (!empty($nonFridayHari)) {
                    $hari = $nonFridayHari[array_rand($nonFridayHari)];
                }
                $slotMulaiA = random_int(1, $maxStartA);
            }
        } else {
            // Tidak cukup ruang di sesi reguler — fallback ke slot 1 saja
            // (akan dihukum HC15 sedikit, tapi GA/repair akan mencoba perbaiki nanti)
            $slotMulaiA = 1;
        }

        $slotMulaiB = $slotMulaiA + $durasiA + 1; // gap tepat 1 slot

        $ruangIdA  = $this->pickSmartRoom($infoA['ruangan_options'], $infoA['kapasitas'], $infoA['jenis'] ?? Gene::JENIS_TEORI);
        $ruangIdB  = $this->pickSmartRoom($infoB['ruangan_options'], $infoB['kapasitas'], $infoB['jenis'] ?? Gene::JENIS_TEORI);

        $kapRuangA = $this->ruanganList[$ruangIdA]['kapasitas']    ?? 0;
        $tipeA     = $this->ruanganList[$ruangIdA]['tipe_ruangan'] ?? Gene::TIPE_REGULER;
        $kapRuangB = $this->ruanganList[$ruangIdB]['kapasitas']    ?? 0;
        $tipeB     = $this->ruanganList[$ruangIdB]['tipe_ruangan'] ?? Gene::TIPE_REGULER;

        $geneA = new Gene(
            $kelasIdA, $hari, $slotMulaiA, $durasiA, $ruangIdA, $infoA['id_dosen'],
            $infoA['kapasitas'], $infoA['jenis'], $kapRuangA, $infoA['nama'],
            $this->maxSlot, $tipeA, $infoA['kategori'] ?? Gene::KATEGORI_SEDANG,
            $infoA['nama_matkul'] ?? '', $this->ruanganList[$ruangIdA]['nama'] ?? ''
        );
        $geneB = new Gene(
            $kelasIdB, $hari, $slotMulaiB, $durasiB, $ruangIdB, $infoB['id_dosen'],
            $infoB['kapasitas'], $infoB['jenis'], $kapRuangB, $infoB['nama'],
            $this->maxSlot, $tipeB, $infoB['kategori'] ?? Gene::KATEGORI_SEDANG,
            $infoB['nama_matkul'] ?? '', $this->ruanganList[$ruangIdB]['nama'] ?? ''
        );

        return [$geneA, $geneB];
    }

    /**
     * [L4] Buat gen dengan slot yang dijaga agar tidak melebihi batas.
     * [T3] Gunakan pre-assigned slot untuk dosen beban tinggi jika tersedia.
     */
    private function createRandomGene(int $kelasId, array $info, bool $usePreassign = true): Gene
    {
        $durasi   = $info['sks'];
        $dosenId  = $info['id_dosen'];

        // [T3] Cek pre-assignment untuk dosen beban tinggi
        $preassign = $this->getPreassignedSlot($kelasId, $dosenId, $usePreassign);
        if ($preassign !== null) {
            $hariId    = $preassign[0];
            $slotMulai = $preassign[1];
        } else {
            $validHari = $this->getValidHariList($info['jenis'] ?? Gene::JENIS_TEORI);
            $hariId    = $validHari[array_rand($validHari)];
            $slotMulai = $this->pickSmartSlot($info['nama'], $durasi, $hariId);
        }

        $ruangId  = $this->pickSmartRoom($info['ruangan_options'], $info['kapasitas'], $info['jenis'] ?? Gene::JENIS_TEORI);
        $kapRuang = isset($this->ruanganList[$ruangId])
                    ? $this->ruanganList[$ruangId]['kapasitas'] : 0;
        $tipeRuangan = $this->ruanganList[$ruangId]['tipe_ruangan'] ?? Gene::TIPE_REGULER;

        return new Gene(
            $kelasId,
            $hariId,
            $slotMulai,
            $durasi,
            $ruangId,
            $dosenId,
            $info['kapasitas'],
            $info['jenis'],
            $kapRuang,
            $info['nama'],
            $this->maxSlot,
            $tipeRuangan,
            $info['kategori'] ?? Gene::KATEGORI_SEDANG,
            $info['nama_matkul'] ?? '',
            $this->ruanganList[$ruangId]['nama'] ?? ''
        );
    }

    /**
     * [BARU][HC16] $hari ditambahkan agar pool slot bisa langsung
     * mengeluarkan slot Sholat Jumat ketika hari yang dipilih adalah Jumat.
     * Default null untuk backward-compat (pemanggil lama tanpa hari spesifik
     * akan tetap berjalan, hanya saja filter Jumat tidak diterapkan — semua
     * pemanggilan baru WAJIB menyertakan $hari yang sudah dipilih).
     */
    private function pickSmartSlot(string $namaKelas, int $durasi, ?int $hari = null): int
    {
        // [L4] Batas atas slot mulai agar slot akhir tidak melampaui maxSlot
        $maxStart = max(1, $this->maxSlot - $durasi + 1);

        // [BARU][HC16] Filter tambahan: slot yang overlap Sholat Jumat (jika $hari = Jumat)
        $fridayFilter = function (int $s) use ($durasi, $hari) {
            if ($hari === null) {
                return true;
            }
            return !$this->overlapsBlockedFridaySlot($hari, $s, $durasi);
        };

        // Filter tambahan: slot harus dipetakan ke hari tersebut
        $mappedFilter = function (int $s) use ($durasi, $hari) {
            if ($hari === null) return true;
            return $this->isSlotActive($hari, $s, $durasi);
        };

        // Deteksi kelas sore: suffix -S/-S1/-S2/-SI, ATAU mengandung kata "sore"/"malam"
        $isKelasS = $this->detectKelasS($namaKelas);

        // Enforce HC12 structurally:
        if ($isKelasS) {
            // Untuk kelas 5 SKS malam, wajib mulai dari slot 14 (**)
            if ($durasi >= 5) {
                return 14;
            }
            // Kelas malam: slot HARUS ≥ eveningStartSlot
            $pool = array_filter(
                range($this->eveningStartSlot, $maxStart),
                function ($s) use ($durasi, $fridayFilter, $mappedFilter, $isKelasS) {
                    if ($this->hasBreakOverlap($s, $durasi, $isKelasS)) {
                        return false;
                    }
                    return $fridayFilter($s) && $mappedFilter($s);
                }
            );

            // Fallback TETAP di slot malam — jangan campur dengan slot pagi
            if (empty($pool)) {
                $pool = array_filter(
                    range($this->eveningStartSlot, $this->maxSlot),
                    function ($s) use ($durasi, $fridayFilter, $mappedFilter, $isKelasS) {
                        if ($this->hasBreakOverlap($s, $durasi, $isKelasS)) {
                            return false;
                        }
                        return $fridayFilter($s) && $mappedFilter($s);
                    }
                );
            }
            // Last resort: jika masih kosong, gunakan eveningStartSlot saja
            // (kecuali itu sendiri overlap slot Jumat — coba slot setelahnya)
            if (empty($pool)) {
                $fallbackSlot = $this->eveningStartSlot;
                if ($hari !== null && $this->overlapsBlockedFridaySlot($hari, $fallbackSlot, $durasi)) {
                    $fallbackSlot = $this->slotSholatJumat + 1;
                }
                $pool = [$fallbackSlot];
            }
        } else {
            // Kelas reguler: slot harus SELESAI sebelum eveningStartSlot (tidak boleh overlap ke malam)
            // Hitung batas slot mulai agar slotAkhir = slotMulai + durasi - 1 < eveningStartSlot
            $upperBound = min($maxStart, $this->eveningStartSlot - $durasi);
            $pool = array_filter(
                range(1, max(1, $upperBound)),
                function ($s) use ($durasi, $fridayFilter, $mappedFilter, $isKelasS) {
                    if ($this->hasBreakOverlap($s, $durasi, $isKelasS)) {
                        return false;
                    }
                    return $fridayFilter($s) && $mappedFilter($s);
                }
            );

            // Fallback reguler: jika tidak ada slot valid, coba semua slot di bawah evening
            if (empty($pool)) {
                $pool = array_filter(
                    range(1, $this->eveningStartSlot - $durasi),
                    function ($s) use ($durasi, $fridayFilter, $mappedFilter, $isKelasS) {
                            if ($this->hasBreakOverlap($s, $durasi, $isKelasS)) {
                                return false;
                            }
                        return $fridayFilter($s) && $mappedFilter($s);
                    }
                );
            }
            // Last resort: slot manapun yang valid (sistem mungkin tidak punya pembagian pagi/malam)
            if (empty($pool)) {
                $pool = array_filter(
                    range(1, $maxStart),
                    function ($s) use ($durasi, $fridayFilter, $isKelasS) {
                            if ($this->hasBreakOverlap($s, $durasi, $isKelasS)) {
                                return false;
                            }
                        return $fridayFilter($s);
                    }
                );
            }
        }

        if (empty($pool)) {
            return 1;
        }

        $pool = array_values($pool);
        return $pool[array_rand($pool)];
    }

    /**
     * [Bug4-fix] Deteksi kelas sore/malam yang komprehensif.
     *
     * Mendeteksi:
     * - Suffix "-S", "-S1", "-S2", "-SI", "-4S", "-4S1", dsb. (format umum)
     * - Kata "sore" di manapun dalam nama kelas
     * - Kata "malam" di manapun dalam nama kelas
     */
    private function detectKelasS(string $namaKelas): bool
    {
        $nama = strtolower(trim($namaKelas));
        // Suffix -S, -S1, -S2, -SI, -4S, -4S1, -4S*, dll.
        if (preg_match('/-\d*s[i\d]*[^\w]*$/i', $nama)) {
            return true;
        }
        // Mengandung kata "sore" atau "malam" (sebagai kata penuh)
        if (preg_match('/\bsore\b|\bmalam\b/', $nama)) {
            return true;
        }
        return false;
    }

    /**
     * [BARU][HC16] Cek apakah rentang slot (hari, slotMulai..slotMulai+durasi-1)
     * overlap dengan slot Sholat Jumat. Dipakai SEBAGAI FILTER di semua
     * tempat yang men-generate kandidat slot (pickSmartSlot, createPairedGenes,
     * repairGene, repairParallelPairs, buildCandidateSlots, preAssignHighLoadDosen,
     * handleCriticalError) — bukan sebagai penalti, tapi sebagai EXCLUSION
     * struktural sehingga slot ini tidak pernah masuk ke pool kandidat sama
     * sekali. Berlaku untuk SEMUA jenis kelas tanpa kecuali (reguler,
     * praktikum, kelas malam -S).
     */
    private function overlapsBlockedFridaySlot(int $hari, int $slotMulai, int $durasi): bool
    {
        if ($hari !== $this->hariJumat) {
            return false;
        }
        $slotAkhir = $slotMulai + $durasi - 1;
        if ($slotMulai < 14 && $slotAkhir >= 14) {
            $slotAkhir += 1;
        }
        // Slot 5 dan 6 pada hari Jumat tidak boleh ditempati (waktu Sholat Jumat)
        return $slotMulai <= 6 && $slotAkhir >= 5;
    }

    /**
     * [BARU][HC3] Cek apakah rentang slot menabrak breakSlots ATAU jam sholat Maghrib.
     * Sholat Maghrib berada di antara slot 12 dan 13, kelas dilarang melewati batas tersebut.
     */
    private function hasBreakOverlap(int $slotMulai, int $durasi): bool
    {
        for ($i = 0; $i < $durasi; $i++) {
            if (in_array($slotMulai + $i, $this->breakSlots, true)) {
                return true;
            }
        }
        $slotAkhir = $slotMulai + $durasi - 1;
        if ($slotMulai <= 12 && $slotAkhir >= 13) {
            return true;
        }
        return false;
    }

    /**
     * Mengecek apakah slot aktif pada hari tertentu berdasarkan konfigurasi user di $hariSlotMap.
     */
    private function isSlotActive(int $hari, int $slotMulai, int $durasi): bool
    {
        $validSlots = $this->hariSlotMap[$hari] ?? [];
        if (empty($validSlots)) {
            return false;
        }
        for ($i = 0; $i < $durasi; $i++) {
            if (!in_array($slotMulai + $i, $validSlots, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * [L5] Pilih ruangan dengan kapasitas cukup dan tipe sesuai jenis matkul; fallback bertahap.
     *
     * HC13: praktikum → lab/hybrid, teori → reguler/hybrid
     */
    private function pickSmartRoom(array $ruanganOptions, int $kapKelas, string $jenisMatkul = Gene::JENIS_TEORI): int
    {
        if (empty($ruanganOptions)) {
            return 0;
        }

        $isPraktikum = $jenisMatkul === Gene::JENIS_PRAKTIKUM;

        // Tier 1: kapasitas cukup + tipe sesuai
        $tier1 = array_filter($ruanganOptions, function (int $id) use ($kapKelas, $isPraktikum) {
            $r   = $this->ruanganList[$id] ?? null;
            if (!$r) { return false; }
            $kapOk  = $r['kapasitas'] >= $kapKelas;
            $tipe   = $r['tipe_ruangan'] ?? Gene::TIPE_REGULER;
            $tipeOk = $isPraktikum
                ? in_array($tipe, [Gene::TIPE_LAB, Gene::TIPE_HYBRID], true)
                : in_array($tipe, [Gene::TIPE_REGULER, Gene::TIPE_HYBRID], true);
            return $kapOk && $tipeOk;
        });

        if (!empty($tier1)) {
            $pool = array_values($tier1);
            return $pool[array_rand($pool)];
        }

        // Tier 2: tipe sesuai saja (abaikan kapasitas)
        $tier2 = array_filter($ruanganOptions, function (int $id) use ($isPraktikum) {
            $r = $this->ruanganList[$id] ?? null;
            if (!$r) { return false; }
            $tipe = $r['tipe_ruangan'] ?? Gene::TIPE_REGULER;
            return $isPraktikum
                ? in_array($tipe, [Gene::TIPE_LAB, Gene::TIPE_HYBRID], true)
                : in_array($tipe, [Gene::TIPE_REGULER, Gene::TIPE_HYBRID], true);
        });

        if (!empty($tier2)) {
            $pool = array_values($tier2);
            return $pool[array_rand($pool)];
        }

        // Tier 3: kapasitas cukup saja
        $tier3 = array_filter($ruanganOptions, function (int $id) use ($kapKelas) {
            return ($this->ruanganList[$id]['kapasitas'] ?? 0) >= $kapKelas;
        });

        if (!empty($tier3)) {
            $pool = array_values($tier3);
            return $pool[array_rand($pool)];
        }

        // Tier 4: fallback apapun
        return $ruanganOptions[array_rand($ruanganOptions)];
    }


    // ═══════════════════════════════════════════════════════════════════════
    // MEDIUM-LEVEL: Evaluasi & Seleksi
    // ═══════════════════════════════════════════════════════════════════════

    private function evaluatePopulation(int $generation = 0): void
    {
        $progress = $this->maxGenerations > 0 ? ($generation / $this->maxGenerations) : 1.0;
        foreach ($this->population as $chromosome) {
            $chromosome->setConstraintParams(
                $this->eveningStartSlot,
                $this->breakSlots,
                $this->validRuangIds,
                $this->morningEndSlot,
                $this->maxSksDayDosen,
                $this->parallelPairs,
                $this->activeConstraints,
                $this->lockedRuangIndex,
                $this->lockedDosenIndex
            );
            $chromosome->calculateFitness($progress);
        }
    }

    /**
     * Tournament selection — ukuran turnamen dinamis berdasarkan populasi.
     *
     * @return Chromosome[]
     */
    private function selection(): array
    {
        $tournamentSize = max(2, (int) round(sqrt($this->populationSize)));
        $selected       = [];
        $popCount       = count($this->population);

        if ($popCount === 0) {
            return []; // Tidak ada populasi untuk seleksi
        }

        for ($i = 0; $i < $this->populationSize; $i++) {
            $best = null;
            for ($j = 0; $j < $tournamentSize; $j++) {
                $candidate = $this->population[random_int(0, $popCount - 1)];
                if ($best === null || $candidate->getFitness() > $best->getFitness()) {
                    $best = $candidate;
                }
            }
            if ($best !== null) {
                $selected[] = $best->copy();
            }
        }

        return $selected;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // MEDIUM-LEVEL: Crossover
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * [M1] Crossover dua-titik selang-seling dengan Conflict-Aware Uniform Crossover.
     *
     * Pasangan genap → two-point crossover.
     * Pasangan ganjil → conflict-aware uniform crossover.
     * Ini memastikan kombinasi optimal tingkat makro (blok) dan mikro (gen bebas konflik) bekerja bersama.
     *
     * @param  Chromosome[] $parents
     * @return Chromosome[]
     */
    private function crossover(array $parents): array
    {
        $offspring = [];
        $count     = count($parents);

        for ($i = 0; $i < $count - 1; $i += 2) {
            $p1 = $parents[$i];
            $p2 = $parents[$i + 1];

            if ((mt_rand() / mt_getrandmax()) <= $this->crossoverRate) {
                // Selang-seling: pasangan ke-0 dua-titik, ke-1 Conflict-Aware Uniform Crossover, dst.
                if (($i / 2) % 2 === 0) {
                    [$c1, $c2] = $this->twoPointCrossover($p1, $p2);
                } else {
                    [$c1, $c2] = $this->conflictAwareUniformCrossover($p1, $p2);
                }
            } else {
                $c1 = $p1->copy();
                $c2 = $p2->copy();
            }

            $offspring[] = $c1;
            $offspring[] = $c2;
        }

        // Jika jumlah parents ganjil, salin yang terakhir
        if ($count % 2 !== 0) {
            $offspring[] = $parents[$count - 1]->copy();
        }

        return $offspring;
    }

    /**
     * Conflict-Aware Uniform Crossover
     *
     * Membangun keturunan dengan memprioritaskan gen yang bebas dari konflik pada masing-masing parent.
     * Pasangan kelas paralel (HC15) diproses bersamaan agar gap-1 tidak terputus secara struktural.
     */
    private function conflictAwareUniformCrossover(Chromosome $p1, Chromosome $p2): array
    {
        $g1  = $p1->getGenes();
        $g2  = $p2->getGenes();
        $len = min(count($g1), count($g2));

        if ($len === 0) {
            return [$p1->copy(), $p2->copy()];
        }

        // Dapatkan index/posisi gen yang terlibat konflik pada masing-masing parent
        $p1Conflicts = array_flip($this->buildConflictingIndices($g1));
        $p2Conflicts = array_flip($this->buildConflictingIndices($g2));

        // Mapping kelasId ke index gen untuk mempermudah pencarian pasangan paralel (HC15)
        $kelasIndexMap = [];
        foreach ($g1 as $idx => $gene) {
            $kelasIndexMap[$gene->kelasId] = $idx;
        }

        $child1Genes = [];
        $child2Genes = [];
        $processed   = array_fill(0, $len, false);

        for ($j = 0; $j < $len; $j++) {
            if ($processed[$j]) {
                continue;
            }

            $kelasId    = $g1[$j]->kelasId;
            $partnerId  = $this->pairPartnerOf[$kelasId] ?? null;
            $partnerIdx = $partnerId !== null ? ($kelasIndexMap[$partnerId] ?? null) : null;

            if ($partnerIdx !== null && $partnerIdx < $len && !$processed[$partnerIdx]) {
                // Tipe A: Pasangan kelas paralel (HC15) - diproses berpasangan agar tidak terpisah
                $p1PairValid = ($g1[$j]->validatePairSequencing($g1[$partnerIdx], true) === 0);
                $p2PairValid = ($g2[$j]->validatePairSequencing($g2[$partnerIdx], true) === 0);

                $p1HasOtherConflict = isset($p1Conflicts[$j]) || isset($p1Conflicts[$partnerIdx]);
                $p2HasOtherConflict = isset($p2Conflicts[$j]) || isset($p2Conflicts[$partnerIdx]);

                $p1Good = $p1PairValid && !$p1HasOtherConflict;
                $p2Good = $p2PairValid && !$p2HasOtherConflict;

                if ($p1Good && !$p2Good) {
                    // Parent 1 bagus (valid dan aman), Parent 2 jelek
                    $child1Genes[$j]          = $g1[$j]->copy();
                    $child1Genes[$partnerIdx] = $g1[$partnerIdx]->copy();

                    if ((mt_rand() / mt_getrandmax()) < 0.8) {
                        $child2Genes[$j]          = $g1[$j]->copy();
                        $child2Genes[$partnerIdx] = $g1[$partnerIdx]->copy();
                    } else {
                        $child2Genes[$j]          = $g2[$j]->copy();
                        $child2Genes[$partnerIdx] = $g2[$partnerIdx]->copy();
                    }
                } elseif (!$p1Good && $p2Good) {
                    // Parent 1 jelek, Parent 2 bagus
                    $child1Genes[$j]          = $g2[$j]->copy();
                    $child1Genes[$partnerIdx] = $g2[$partnerIdx]->copy();

                    if ((mt_rand() / mt_getrandmax()) < 0.8) {
                        $child2Genes[$j]          = $g2[$j]->copy();
                        $child2Genes[$partnerIdx] = $g2[$partnerIdx]->copy();
                    } else {
                        $child2Genes[$j]          = $g1[$j]->copy();
                        $child2Genes[$partnerIdx] = $g1[$partnerIdx]->copy();
                    }
                } else {
                    // Keduanya sama-sama bagus atau sama-sama jelek
                    if (mt_rand(0, 1) === 0) {
                        $child1Genes[$j]          = $g1[$j]->copy();
                        $child1Genes[$partnerIdx] = $g1[$partnerIdx]->copy();

                        $child2Genes[$j]          = $g2[$j]->copy();
                        $child2Genes[$partnerIdx] = $g2[$partnerIdx]->copy();
                    } else {
                        $child1Genes[$j]          = $g2[$j]->copy();
                        $child1Genes[$partnerIdx] = $g2[$partnerIdx]->copy();

                        $child2Genes[$j]          = $g1[$j]->copy();
                        $child2Genes[$partnerIdx] = $g1[$partnerIdx]->copy();
                    }
                }

                $processed[$j]          = true;
                $processed[$partnerIdx] = true;
            } else {
                // Tipe B: Gen tunggal biasa
                $p1Conf = isset($p1Conflicts[$j]);
                $p2Conf = isset($p2Conflicts[$j]);

                if (!$p1Conf && $p2Conf) {
                    // Parent 1 aman, Parent 2 bentrok
                    $child1Genes[$j] = $g1[$j]->copy();
                    $child2Genes[$j] = ((mt_rand() / mt_getrandmax()) < 0.8) ? $g1[$j]->copy() : $g2[$j]->copy();
                } elseif ($p1Conf && !$p2Conf) {
                    // Parent 1 bentrok, Parent 2 aman
                    $child1Genes[$j] = $g2[$j]->copy();
                    $child2Genes[$j] = ((mt_rand() / mt_getrandmax()) < 0.8) ? $g2[$j]->copy() : $g1[$j]->copy();
                } else {
                    // Keduanya sama-sama aman / bentrok
                    if (mt_rand(0, 1) === 0) {
                        $child1Genes[$j] = $g1[$j]->copy();
                        $child2Genes[$j] = $g2[$j]->copy();
                    } else {
                        $child1Genes[$j] = $g2[$j]->copy();
                        $child2Genes[$j] = $g1[$j]->copy();
                    }
                }

                $processed[$j] = true;
            }
        }

        // Urutkan kembali berdasarkan index gen agar susunan gen tidak berubah
        ksort($child1Genes);
        ksort($child2Genes);

        return [$this->makeChromosome(array_values($child1Genes)), $this->makeChromosome(array_values($child2Genes))];
    }

    private function twoPointCrossover(Chromosome $p1, Chromosome $p2): array
    {
        $g1  = $p1->getGenes();
        $g2  = $p2->getGenes();
        $len = min(count($g1), count($g2));

        if ($len < 3) {
            return $this->conflictAwareUniformCrossover($p1, $p2);
        }

        $pt1 = random_int(1, $len - 2);
        $pt2 = random_int($pt1 + 1, $len - 1);

        $child1Genes = array_merge(
            array_map(fn($g) => $g->copy(), array_slice($g1, 0, $pt1)),
            array_map(fn($g) => $g->copy(), array_slice($g2, $pt1, $pt2 - $pt1)),
            array_map(fn($g) => $g->copy(), array_slice($g1, $pt2))
        );
        $child2Genes = array_merge(
            array_map(fn($g) => $g->copy(), array_slice($g2, 0, $pt1)),
            array_map(fn($g) => $g->copy(), array_slice($g1, $pt1, $pt2 - $pt1)),
            array_map(fn($g) => $g->copy(), array_slice($g2, $pt2))
        );

        return [$this->makeChromosome($child1Genes), $this->makeChromosome($child2Genes)];
    }

    private function makeChromosome(array $genes): Chromosome
    {
        $c = new Chromosome($genes);
        $c->setConstraintParams(
            $this->eveningStartSlot,
            $this->breakSlots,
            $this->validRuangIds,
            $this->morningEndSlot,
            8,
            $this->parallelPairs
        );
        return $c;
    }


    // ═══════════════════════════════════════════════════════════════════════
    // MEDIUM-LEVEL: Repair Operator [M2]
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * [M2][P3] Untuk setiap offspring, cek pasang-gen yang berkonflik DAN
     * pasangan HC15 yang melanggar gap-1. HC15 diperbaiki LEBIH DULU karena
     * memindahkan satu sisi pasangan bisa merusak hubungan gap-1 dengan sisi
     * lainnya — memperbaikinya dulu memberi starting point yang lebih stabil
     * untuk repair konflik dosen/ruangan biasa setelahnya.
     *
     * @param Chromosome[] $offspring
     */
    private function repairOffspring(array $offspring): void
    {
        foreach ($offspring as $chromosome) {
            $genes = $chromosome->getGenes();

            // Dapatkan indeks gen berkonflik dengan O(n) index
            $conflictIndices = $this->buildConflictingIndices($genes);

            // [P3] Repair HC15 dulu (pasangan kelas paralel gap-1), pass conflictIndices agar
            // pasangan yang sudah memenuhi HC15 tetapi bentrok dengan kelas lain TETAP dipindah.
            $hc15Repaired = $this->repairParallelPairs($genes, $conflictIndices);

            // Re-build conflict indices jika ada yang dipindah oleh repairParallelPairs
            if ($hc15Repaired) {
                $conflictIndices = $this->buildConflictingIndices($genes);
            }

            $repaired = $hc15Repaired;

            if (!empty($conflictIndices)) {
                foreach ($conflictIndices as $j) {
                    $isPaired = isset($this->pairPartnerOf[$genes[$j]->kelasId]);
                    $fixed = $this->repairGene($genes[$j], $genes, $j, $isPaired);
                    if ($fixed) {
                        $repaired = true;
                    }
                }
            }

            if ($repaired) {
                $chromosome->markDirty();
            }
        }
    }

    /**
     * [BARU][P3] Perbaiki pasangan HC15 yang melanggar gap-1/hari-sama.
     *
     * Untuk setiap pasangan terdaftar di $this->parallelPairs, cek apakah
     * gene A dan gene B di kromosom ini sudah memenuhi HC15. Jika belum,
     * coba tempatkan ulang KEDUA gene sekaligus (hari sama + gap 1 slot)
     * di slot pertama yang valid dan (sebisa mungkin) tidak menambah
     * konflik dosen/ruangan baru.
     *
     * @param  Gene[] $genes  Reference array gen milik satu kromosom
     * @return bool           True jika ada perbaikan yang dilakukan
     */
    private function repairParallelPairs(array $genes, array $conflictIndices = []): bool
    {
        if (empty($this->parallelPairs)) {
            return false;
        }

        $conflictKelasIds = [];
        foreach ($conflictIndices as $idx) {
            $conflictKelasIds[$genes[$idx]->kelasId] = true;
        }

        // Index gene by kelasId untuk lookup cepat
        $byKelasId = [];
        foreach ($genes as $idx => $g) {
            $byKelasId[$g->kelasId] = $idx;
        }

        $anyRepaired = false;

        foreach ($this->parallelPairs as [$kelasIdA, $kelasIdB]) {
            $idxA = $byKelasId[$kelasIdA] ?? null;
            $idxB = $byKelasId[$kelasIdB] ?? null;
            if ($idxA === null || $idxB === null) {
                continue;
            }

            $geneA = $genes[$idxA];
            $geneB = $genes[$idxB];

            $isConflicting = isset($conflictKelasIds[$kelasIdA]) || isset($conflictKelasIds[$kelasIdB]);

            // Sudah valid DAN tidak konflik? skip.
            if (!$isConflicting && $geneA->validatePairSequencing($geneB, true) === 0) {
                continue;
            }

            $durasiA = $geneA->durasi;
            $durasiB = $geneB->durasi;
            $totalSpan = $durasiA + 1 + $durasiB;

            // Bangun index konflik dari SEMUA gene lain (selain A & B) untuk
            // mencari slot yang tidak menambah konflik baru.
            $othersIdx = array_diff(array_keys($genes), [$idxA, $idxB]);
            $others    = array_map(fn($i) => $genes[$i], $othersIdx);
            [$dosenIdx, $ruangIdx, $kelasIdx] = $this->buildConflictIndex($others);

            $maxStartA = max(1, $this->eveningStartSlot - $totalSpan);

            // Coba semua hari × slotMulaiA untuk kombinasi yang valid & minim konflik
            $infoA = $this->kelasData[$geneA->kelasId] ?? null;
            $validHari = $this->getValidHariList($infoA['jenis'] ?? Gene::JENIS_TEORI);

            // [BARU][HC16] Exclude kombinasi di mana A ATAU B overlap slot Sholat Jumat
            $candidates = [];
            foreach ($validHari as $hari) {
                for ($slotA = 1; $slotA <= $maxStartA; $slotA++) {
                    $slotB = $slotA + $durasiA + 1;
                    // Cek overlap hari Jumat atau slot nonaktif
                    if ($this->overlapsBlockedFridaySlot($hari, $slotA, $durasiA) || !$this->isSlotActive($hari, $slotA, $durasiA)
                        || $this->overlapsBlockedFridaySlot($hari, $slotB, $durasiB) || !$this->isSlotActive($hari, $slotB, $durasiB)) {
                        continue;
                    }
                    $candidates[] = [$hari, $slotA, $slotB];
                }
            }

            if (empty($candidates)) {
                // Tidak ada kombinasi yang lolos filter Jumat — coba hari non-Jumat
                // dengan slot 1 sebagai fallback terakhir.
                $nonFridayHari = array_values(array_diff($validHari, [$this->hariJumat]));
                $fallbackHari  = !empty($nonFridayHari) ? $nonFridayHari[0] : ($validHari[0] ?? 1);
                $candidates[] = [$fallbackHari, 1, 1 + $durasiA + 1];
            }

            shuffle($candidates);

            $validRoomsA = $this->getValidRoomsForGene($geneA);
            $validRoomsB = $this->getValidRoomsForGene($geneB);

            $bestCandidate   = null;
            $bestConflictCnt = PHP_INT_MAX;

            foreach ($candidates as [$hari, $slotA, $slotB]) {
                foreach ($validRoomsA as $rA) {
                    foreach ($validRoomsB as $rB) {
                        $origA = [$geneA->hariId, $geneA->slotMulai, $geneA->ruangId];
                        $origB = [$geneB->hariId, $geneB->slotMulai, $geneB->ruangId];

                        $geneA->hariId = $hari; $geneA->slotMulai = $slotA; $geneA->ruangId = $rA;
                        $geneB->hariId = $hari; $geneB->slotMulai = $slotB; $geneB->ruangId = $rB;

                        $conflictCnt = 0;
                        $conflictCnt += ($this->geneConflictsWithIndex($geneA, $dosenIdx, $ruangIdx, $kelasIdx) > 0) ? 1 : 0;
                        $conflictCnt += ($this->geneConflictsWithIndex($geneB, $dosenIdx, $ruangIdx, $kelasIdx) > 0) ? 1 : 0;

                        $geneA->hariId = $origA[0]; $geneA->slotMulai = $origA[1]; $geneA->ruangId = $origA[2];
                        $geneB->hariId = $origB[0]; $geneB->slotMulai = $origB[1]; $geneB->ruangId = $origB[2];

                        if ($conflictCnt < $bestConflictCnt) {
                            $bestConflictCnt = $conflictCnt;
                            $bestCandidate   = [$hari, $slotA, $slotB, $rA, $rB];
                            if ($conflictCnt === 0) {
                                break 3; // Sudah sempurna, tidak perlu cari lagi
                            }
                        }
                    }
                }
            }

            if ($bestCandidate !== null) {
                [$hari, $slotA, $slotB, $rA, $rB] = $bestCandidate;
                $geneA->hariId = $hari; $geneA->slotMulai = $slotA; $geneA->ruangId = $rA;
                $geneB->hariId = $hari; $geneB->slotMulai = $slotB; $geneB->ruangId = $rB;
                $anyRepaired = true;
            }
        }

        return $anyRepaired;
    }

    /**
     * Coba ubah hari+slot gene agar tidak konflik dengan gen lain.
     * Iterasi semua hari × slot; berhenti di kombinasi pertama yang aman.
     *
     * [P3] Catatan: gene yang merupakan bagian dari pasangan HC15 tetap bisa
     * diproses oleh repair konflik biasa ini (misal karena masih ada konflik
     * dosen/ruangan dengan gene lain di luar pasangannya). repairParallelPairs()
     * sudah dijalankan lebih dulu sehingga di titik ini hubungan gap-1 sudah
     * sebisa mungkin terjaga; namun jika repair konflik di sini terpaksa
     * memindahkan gene tersebut, hubungan HC15-nya akan dicek ulang dan
     * diperbaiki lagi pada generasi/repair berikutnya.
     *
     * @param  Gene   $gene    Gene yang akan di-repair
     * @param  Gene[] $others  Semua gen dalam kromosom
     * @param  int    $selfIdx Indeks gene di $others (agar tidak dibandingkan dengan diri sendiri)
     * @return bool            True jika berhasil diperbaiki
     */
    private function repairGene(Gene $gene, array $others, int $selfIdx, bool $roomOnly = false): bool
    {
        $info     = $this->kelasData[$gene->kelasId] ?? null;
        if (!$info) {
            return false;
        }

        $durasi   = $gene->durasi;
        $maxStart = max(1, $this->maxSlot - $durasi + 1);
        $isKelasS = $gene->isKelasS;

        if ($roomOnly) {
            $candidates = [ [$gene->hariId, $gene->slotMulai] ];
        } else {
            $validHari = $this->getValidHariList($info['jenis'] ?? Gene::JENIS_TEORI);

            // Bangun kandidat hari × slot sesuai constraint HC12
            $candidates = [];
            foreach ($validHari as $hari) {
                for ($slot = 1; $slot <= $maxStart; $slot++) {
                    // Cek overlap break slots
                    $hasBreakOverlap = $this->hasBreakOverlap($slot, $durasi);
                    if ($hasBreakOverlap) {
                        continue;
                    }
                    // [BARU][HC16] Exclude slot yang overlap Sholat Jumat
                    if ($this->overlapsBlockedFridaySlot($hari, $slot, $durasi)) {
                        continue;
                    }
                    // Cek apakah slot diaktifkan oleh user
                    if (!$this->isSlotActive($hari, $slot, $durasi)) {
                        continue;
                    }
                    if ($isKelasS && $slot < $this->eveningStartSlot) {
                        continue;   // HC12: kelas sore wajib di slot malam
                    }
                    if (!$isKelasS && ($slot + $durasi - 1) >= $this->eveningStartSlot) {
                        continue;   // HC12: kelas reguler wajib selesai sebelum malam
                    }
                    $candidates[] = [$hari, $slot];
                }
            }

            // [Bug3-fix] Jika pool kelas-S kosong, perluas ke seluruh slot malam
            if (empty($candidates) && $isKelasS) {
                foreach ($validHari as $hari) {
                    for ($slot = $this->eveningStartSlot; $slot <= $this->maxSlot; $slot++) {
                        $hasBreakOverlap = $this->hasBreakOverlap($slot, $durasi);
                        // [BARU][HC16] Exclude slot yang overlap Sholat Jumat dan pastikan slot aktif
                        if (!$hasBreakOverlap && !$this->overlapsBlockedFridaySlot($hari, $slot, $durasi) && $this->isSlotActive($hari, $slot, $durasi)) {
                            $candidates[] = [$hari, $slot];
                        }
                    }
                }
            }

            if (empty($candidates)) {
                return false;
            }

            // Acak urutan agar tidak bias
            shuffle($candidates);
        }

        // --- ROOM OPTIONS BUILDING ---
        $validRooms = $this->getValidRoomsForGene($gene);


        // Bangun index dari semua gen KECUALI gen yang di-repair
        $othersWithoutSelf = $others;
        unset($othersWithoutSelf[$selfIdx]);
        [$dosenIdx, $ruangIdx, $kelasIdx] = $this->buildConflictIndex(array_values($othersWithoutSelf));

        // Pass 1: cari slot bebas konflik menggunakan index O(SKS) bukan O(n)
        foreach ($candidates as [$hari, $slot]) {
            $origHari = $gene->hariId;
            $origSlot = $gene->slotMulai;
            $origRuang = $gene->ruangId;
            
            $gene->hariId    = $hari;
            $gene->slotMulai = $slot;

            if ($this->geneConflictsWithIndex($gene, $dosenIdx, $ruangIdx, $kelasIdx) === 0) {
                return true;  // Repair sukses
            }

            // Cek apakah dosen atau kelas bentrok di slot ini.
            $isHardKonflik = false;
            $did = $gene->dosenId;
            $nk = $gene->namaKelas;
            for ($s = $slot; $s < $slot + $durasi; $s++) {
                 if ($did !== 0 && isset($dosenIdx[$did][$hari][$s])) { $isHardKonflik = true; break; }
                 if ($nk !== '' && isset($kelasIdx[$nk][$hari][$s])) { $isHardKonflik = true; break; }
            }

            if (!$isHardKonflik) {
                // Berarti HANYA ruangan yang bentrok. Coba ruangan lain dari validRooms.
                foreach ($validRooms as $rId) {
                    if ($rId === $origRuang) continue;
                    $gene->ruangId = $rId;
                    if ($this->geneConflictsWithIndex($gene, $dosenIdx, $ruangIdx, $kelasIdx) === 0) {
                        $gene->kapasitasRuang = $this->ruanganList[$rId]['kapasitas'] ?? 0;
                        $gene->tipeRuangan    = $this->ruanganList[$rId]['tipe_ruangan'] ?? Gene::TIPE_REGULER;
                        $gene->namaRuang      = $this->ruanganList[$rId]['nama'] ?? '';
                        return true; // Sukses ganti ruangan!
                    }
                }
            }
            
            $gene->hariId    = $origHari;
            $gene->slotMulai = $origSlot;
            $gene->ruangId   = $origRuang;
        }

        // Pass 2 (khusus kelas-S): jika semua slot malam berkonflik, tetap
        // pindahkan ke slot malam untuk memenuhi HC12, walaupun masih ada
        // konflik dosen/ruangan. Konflik ini lebih mudah diselesaikan mutasi
        // berikutnya daripada melanggar HC12.
        if (!$roomOnly && $isKelasS) {
            [$bestHari, $bestSlot] = $candidates[0];
            $gene->hariId    = $bestHari;
            $gene->slotMulai = $bestSlot;
            // Pilih ruangan acak yang valid
            if (!empty($validRooms)) {
                $rId = $validRooms[array_rand($validRooms)];
                $gene->ruangId = $rId;
                $gene->kapasitasRuang = $this->ruanganList[$rId]['kapasitas'] ?? 0;
                $gene->tipeRuangan    = $this->ruanganList[$rId]['tipe_ruangan'] ?? Gene::TIPE_REGULER;
                $gene->namaRuang      = $this->ruanganList[$rId]['nama'] ?? '';
            }
            return true; // Pindah ke malam, konflik lain diselesaikan nanti
        }

        return false;
    }

    private function getValidRoomsForGene(Gene $gene): array
    {
        $info = $this->kelasData[$gene->kelasId] ?? null;
        if (!$info) {
            return array_keys($this->ruanganList);
        }

        $ruangOptions = $info['ruangan_options'];
        if (empty($ruangOptions)) {
            $ruangOptions = array_keys($this->ruanganList);
        }
        $kapKelas    = $info['kapasitas'];
        $isPraktikum = ($info['jenis'] ?? Gene::JENIS_TEORI) === Gene::JENIS_PRAKTIKUM;
        
        $validRooms = [];
        foreach ($ruangOptions as $rid) {
            $r = $this->ruanganList[$rid] ?? null;
            if (!$r) continue;
            $kapOk = $r['kapasitas'] >= $kapKelas;
            $tipe = $r['tipe_ruangan'] ?? Gene::TIPE_REGULER;
            $tipeOk = $isPraktikum
                ? in_array($tipe, [Gene::TIPE_LAB, Gene::TIPE_HYBRID], true)
                : in_array($tipe, [Gene::TIPE_REGULER, Gene::TIPE_HYBRID], true);
            if ($kapOk && $tipeOk) $validRooms[] = $rid;
        }
        if (empty($validRooms)) {
            foreach ($ruangOptions as $rid) {
                $r = $this->ruanganList[$rid] ?? null;
                if (!$r) continue;
                $tipe = $r['tipe_ruangan'] ?? Gene::TIPE_REGULER;
                $tipeOk = $isPraktikum
                    ? in_array($tipe, [Gene::TIPE_LAB, Gene::TIPE_HYBRID], true)
                    : in_array($tipe, [Gene::TIPE_REGULER, Gene::TIPE_HYBRID], true);
                if ($tipeOk) $validRooms[] = $rid;
            }
        }
        if (empty($validRooms)) {
            foreach ($ruangOptions as $rid) {
                if (($this->ruanganList[$rid]['kapasitas'] ?? 0) >= $kapKelas) $validRooms[] = $rid;
            }
        }
        if (empty($validRooms)) {
            $validRooms = $ruangOptions;
        }
        
        shuffle($validRooms);
        return $validRooms;
    }


    // ═══════════════════════════════════════════════════════════════════════
    // MEDIUM-LEVEL: Mutasi Adaptif [M4]
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * [M4] Hitung diversity via hash-set (O(n)) lalu sesuaikan mutation rate.
     *
     * [P3] Gene yang merupakan bagian dari pasangan HC15 di-skip dari mutasi
     * acak biasa (case 0/1 — ganti hari/slot) untuk menjaga hubungan gap-1
     * yang sudah dibangun oleh init/repair; mutasi ruangan (case 2) masih
     * diperbolehkan karena tidak memengaruhi waktu.
     *
     * @param Chromosome[] $offspring
     */
    private function mutate(array $offspring): void
    {
        // Hitung diversity
        $hashes    = [];
        foreach ($offspring as $c) {
            $hashes[$c->buildGenesHash()] = true;
        }
        $diversity = count($hashes) / max(1, count($offspring));

        // Sesuaikan mutation rate
        if ($diversity < 0.25) {
            $this->mutationRate = min(0.45, $this->originalMutationRate * 3.0);
        } elseif ($diversity < 0.5) {
            $this->mutationRate = min(0.30, $this->originalMutationRate * 1.5);
        } else {
            // Kembalikan ke asal jika diversity sudah cukup
            $this->mutationRate = $this->originalMutationRate;
        }

        foreach ($offspring as $chromosome) {
            $genes = $chromosome->getGenes();
            // [P3] Index kelasId yang merupakan bagian dari pasangan HC15 untuk skip cepat
            foreach ($genes as $gene) {
                if ((mt_rand() / mt_getrandmax()) < $this->mutationRate) {
                    $isPaired = isset($this->pairPartnerOf[$gene->kelasId]);
                    $this->mutateGene($gene, $isPaired);
                    $chromosome->markDirty();
                }
            }

            // [BARU][P3] Mutasi khusus pasangan: dengan probabilitas kecil,
            // geser KEDUA gene pasangan bersamaan (hari baru / slotA baru)
            // sambil tetap menjaga gap=1 — supaya pasangan juga bisa explore
            // posisi baru tanpa pernah melanggar HC15 akibat mutasi.
            if (!empty($this->parallelPairs) && (mt_rand() / mt_getrandmax()) < $this->mutationRate) {
                $this->mutatePairTogether($chromosome);
            }
        }
    }

    /**
     * Mutasi satu gen: pilih salah satu dari tiga tipe secara acak.
     *
     * [P3] Jika $isPaired true, mutasi hari (case 0) dan slot (case 1)
     * di-skip agar tidak merusak hubungan gap-1 dengan pasangannya secara
     * tidak terkendali — perubahan posisi pasangan ditangani khusus oleh
     * mutatePairTogether(). Mutasi ruangan (case 2) tetap diizinkan.
     */
    private function mutateGene(Gene $gene, bool $isPaired = false): void
    {
        $info = $this->kelasData[$gene->kelasId] ?? null;
        if (!$info) {
            return;
        }

        $type = $isPaired ? 2 : random_int(0, 2);

        switch ($type) {
            case 0: // Ganti hari
                $validHari = $this->getValidHariList($info['jenis'] ?? Gene::JENIS_TEORI);
                if (!empty($validHari)) {
                    $gene->hariId = $validHari[array_rand($validHari)];
                    // [BARU][HC16] Jika hari baru = Jumat dan slot lama overlap
                    // slot Sholat Jumat, pilih ulang slot agar tetap aman.
                    if ($this->overlapsBlockedFridaySlot($gene->hariId, $gene->slotMulai, $gene->durasi)) {
                        $gene->slotMulai = $this->pickSmartSlot($info['nama'], $gene->durasi, $gene->hariId);
                    }
                }
                break;

            case 1: // Ganti slot (smart, sesuai jenis kelas) [L4]
                $gene->slotMulai = $this->pickSmartSlot($info['nama'], $gene->durasi, $gene->hariId);
                break;

            case 2: // Ganti ruangan (smart, filter kapasitas + tipe) [L5]
                $ruangId = $this->pickSmartRoom(
                    $info['ruangan_options'],
                    $info['kapasitas'],
                    $info['jenis'] ?? Gene::JENIS_TEORI
                );
                $gene->ruangId        = $ruangId;
                $gene->kapasitasRuang = $this->ruanganList[$ruangId]['kapasitas']    ?? 0;
                $gene->tipeRuangan    = $this->ruanganList[$ruangId]['tipe_ruangan'] ?? Gene::TIPE_REGULER;
                $gene->namaRuang      = $this->ruanganList[$ruangId]['nama'] ?? '';
                break;
        }
    }

    /**
     * [BARU][P3] Geser satu pasangan HC15 secara bersamaan ke hari/slot baru,
     * tetap menjaga gap tepat 1 slot di antaranya. Dipanggil dengan
     * probabilitas mutationRate per kromosom (bukan per-gene) agar tidak
     * terlalu sering mengganggu pasangan yang sudah baik.
     */
    private function mutatePairTogether(Chromosome $chromosome): void
    {
        if (empty($this->parallelPairs)) {
            return;
        }

        [$kelasIdA, $kelasIdB] = $this->parallelPairs[array_rand($this->parallelPairs)];

        $geneA = null; $geneB = null;
        foreach ($chromosome->getGenes() as $g) {
            if ($g->kelasId === $kelasIdA) { $geneA = $g; }
            if ($g->kelasId === $kelasIdB) { $geneB = $g; }
        }
        if (!$geneA || !$geneB) {
            return;
        }

        $durasiA   = $geneA->durasi;
        $durasiB   = $geneB->durasi;
        $totalSpan = $durasiA + 1 + $durasiB;

        $maxStartA = max(1, $this->eveningStartSlot - $totalSpan);
        if ($maxStartA < 1) {
            return; // Tidak cukup ruang, biarkan repair operator yang menangani
        }

        $infoA      = $this->kelasData[$kelasIdA] ?? null;
        $validHari  = $this->getValidHariList($infoA['jenis'] ?? Gene::JENIS_TEORI);
        $hari       = $validHari[array_rand($validHari)];

        // [BARU][HC16] Cari kandidat slotMulaiA yang membuat A MAUPUN B
        // sama sekali tidak overlap slot Sholat Jumat (relevan jika $hari = Jumat).
        $validStartsA = [];
        for ($s = 1; $s <= $maxStartA; $s++) {
            $slotBCandidate = $s + $durasiA + 1;
            if (!$this->overlapsBlockedFridaySlot($hari, $s, $durasiA) && $this->isSlotActive($hari, $s, $durasiA)
                && !$this->overlapsBlockedFridaySlot($hari, $slotBCandidate, $durasiB) && $this->isSlotActive($hari, $slotBCandidate, $durasiB)) {
                $validStartsA[] = $s;
            }
        }

        if (!empty($validStartsA)) {
            $slotMulaiA = $validStartsA[array_rand($validStartsA)];
        } else {
            // Tidak ada kombinasi valid di hari ini — pindahkan ke hari lain.
            $otherDays = array_values(array_diff($validHari, [$hari]));
            shuffle($otherDays);
            $found = false;
            foreach ($otherDays as $altHari) {
                $altValidStarts = [];
                for ($s = 1; $s <= $maxStartA; $s++) {
                    $slotBCandidate = $s + $durasiA + 1;
                    if (!$this->overlapsBlockedFridaySlot($altHari, $s, $durasiA) && $this->isSlotActive($altHari, $s, $durasiA)
                        && !$this->overlapsBlockedFridaySlot($altHari, $slotBCandidate, $durasiB) && $this->isSlotActive($altHari, $slotBCandidate, $durasiB)) {
                        $altValidStarts[] = $s;
                    }
                }
                if (!empty($altValidStarts)) {
                    $hari = $altHari;
                    $slotMulaiA = $altValidStarts[array_rand($altValidStarts)];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return; // Gagal mutasi pasangan ke slot valid manapun, skip.
            }
        }

        $slotMulaiB = $slotMulaiA + $durasiA + 1;

        $geneA->hariId = $hari; $geneA->slotMulai = $slotMulaiA;
        $geneB->hariId = $hari; $geneB->slotMulai = $slotMulaiB;

        // [BARU] Mutasi ruangan secara bersamaan agar bisa terlepas dari jebakan ruangan.
        $validRoomsA = $this->getValidRoomsForGene($geneA);
        $validRoomsB = $this->getValidRoomsForGene($geneB);

        if (!empty($validRoomsA)) {
            $rA = $validRoomsA[array_rand($validRoomsA)];
            $geneA->ruangId = $rA;
            $geneA->kapasitasRuang = $this->ruanganList[$rA]['kapasitas'] ?? 0;
            $geneA->tipeRuangan    = $this->ruanganList[$rA]['tipe_ruangan'] ?? Gene::TIPE_REGULER;
            $geneA->namaRuang      = $this->ruanganList[$rA]['nama'] ?? '';
        }
        if (!empty($validRoomsB)) {
            $rB = $validRoomsB[array_rand($validRoomsB)];
            $geneB->ruangId = $rB;
            $geneB->kapasitasRuang = $this->ruanganList[$rB]['kapasitas'] ?? 0;
            $geneB->tipeRuangan    = $this->ruanganList[$rB]['tipe_ruangan'] ?? Gene::TIPE_REGULER;
            $geneB->namaRuang      = $this->ruanganList[$rB]['nama'] ?? '';
        }

        $chromosome->markDirty();
    }

    // ═══════════════════════════════════════════════════════════════════════
    // MEDIUM-LEVEL: Penggantian Populasi & Elitism [M5]
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * [M5] Ganti populasi dengan offspring + elite.
     *
     * @param Chromosome[] $offspring
     */
    private function replacePopulation(array $offspring): void
    {
        // Sort offspring terbaik di depan
        usort($offspring, fn($a, $b) => $b->getFitness() <=> $a->getFitness());

        // Ambil N-eliteK terbaik dari offspring
        // Guard jika count(elites) >= populationSize
        $offspringCount = max(0, $this->populationSize - count($this->elites));
        $newPop = array_slice($offspring, 0, $offspringCount);

        // Inject elite agar tidak hilang
        foreach ($this->elites as $elite) {
            $newPop[] = $elite->copy();
        }

        $this->population = array_slice($newPop, 0, $this->populationSize);
    }

    /** [M5] Perbarui daftar elite (top-K) setelah setiap generasi. */
    private function updateElites(Chromosome $candidate): void
    {
        // Tambahkan kandidat
        $this->elites[] = $candidate->copy();

        // Sort dan ambil top-K
        usort($this->elites, fn($a, $b) => $b->getFitness() <=> $a->getFitness());
        $this->elites = array_slice($this->elites, 0, $this->eliteK);
    }

    private function getBestChromosome(): Chromosome
    {
        if (empty($this->population)) {
            return new Chromosome([]);
        }
        $best = $this->population[0];
        foreach ($this->population as $c) {
            if ($c->getFitness() > $best->getFitness()) {
                $best = $c;
            }
        }
        return $best;
    }


    // ═══════════════════════════════════════════════════════════════════════
    // MEDIUM-LEVEL: Stagnation Solver Berlapis [M3]
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * [M3] Tiga lapisan penanganan stagnasi:
     *
     * Level 1 (10 gen stagnan) → naikkan mutation rate 1.5×.
     * Level 2 (25 gen stagnan) → inject 30% kromosom acak baru.
     * Level 3 (50 gen stagnan) → re-seed seluruh populasi, pertahankan elite-K.
     */
    private function handleStagnation(int $generation, Chromosome $best): void
    {
        $currentFitness = $best->getFitness();

        if (abs($currentFitness - $this->lastBestFitness) < 0.05) {
            $this->stagnationCounter++;
        } else {
            $this->stagnationCounter = 0;
            $this->lastBestFitness   = $currentFitness;
        }

        $cnt = $this->stagnationCounter;

        if ($cnt > 0 && $cnt % ($this->stagnationLevel1 * 5) === 0) {
            // Level 3: [T5] Guided re-seed — 50% elite-guided, 50% acak
            $this->logProblem('stagnation_L3',
                "Gen {$generation}: stagnan {$cnt}× — guided re-seed populasi.");

            $elite   = !empty($this->elites) ? $this->elites[0] : null;
            $newPop  = [];

            // Pertahankan semua elite
            foreach ($this->elites as $e) {
                $newPop[] = $e->copy();
            }

            $remaining = $this->populationSize - count($newPop);
            $halfGuided = (int) ($remaining / 2);

            // 50% guided perturbation dari elite [T5]
            for ($i = 0; $i < $halfGuided; $i++) {
                $usePre = (count($newPop) < $this->populationSize / 2);
                $newPop[] = $elite !== null
                    ? $this->guidedPerturbation($elite)
                    : $this->createRandomChromosome($usePre);
            }

            // 50% murni acak
            for ($i = $halfGuided; $i < $remaining; $i++) {
                $usePre = (count($newPop) < $this->populationSize / 2);
                $newPop[] = $this->createRandomChromosome($usePre);
            }

            $this->population   = array_slice($newPop, 0, $this->populationSize);
            $this->mutationRate = $this->originalMutationRate;
            $this->stagnationRestartCount++;

        } elseif ($cnt > 0 && $cnt % (int)($this->stagnationLevel1 * 2.5) === 0) {
            // Level 2: inject 30%
            $this->logProblem('stagnation_L2',
                "Gen {$generation}: stagnan {$cnt}× — inject 30% acak.");
            $injectCount = (int) ($this->populationSize * 0.3);
            for ($i = 0; $i < $injectCount; $i++) {
                $idx = random_int(0, count($this->population) - 1);
                $usePre = ($idx < $this->populationSize / 2);
                $this->population[$idx] = $this->createRandomChromosome($usePre);
            }
            $this->mutationRate = min(0.40, $this->originalMutationRate * 2.0);

        } elseif ($cnt > 0 && $cnt % $this->stagnationLevel1 === 0) {
            // Level 1: naikkan mutation rate
            $this->logProblem('stagnation_L1',
                "Gen {$generation}: stagnan {$cnt}× — naikkan mutation rate.");
            $this->mutationRate = min(0.35, $this->originalMutationRate * 1.5);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // HIGH-LEVEL: Local Search → SA-LS [H1 + T1]
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * [T1][P3] Simulated-Annealing Local Search (SA-LS).
     *
     * Berbeda dari greedy local search biasa yang selalu menolak solusi buruk,
     * SA-LS menerima solusi yang lebih buruk dengan probabilitas:
     *
     *   P(accept) = exp(-(f_baru - f_lama) / T)
     *
     * di mana T (suhu) dimulai tinggi (membolehkan banyak langkah mundur)
     * dan turun secara eksponensial hingga mendekati 0 (murni greedy).
     *
     * Ini memungkinkan GA keluar dari local optimum trap yang tidak bisa
     * diatasi hanya dengan mutasi/crossover karena populasi sudah konvergen.
     *
     * [P3] Sebelum loop SA-LS biasa berjalan, panggil repairParallelPairs()
     * sekali untuk memastikan pasangan HC15 sudah serapat mungkin ke gap-1
     * sebelum local search memperbaiki konflik/soft-violation gene lainnya.
     *
     * Parameter:
     *   T_init    = 5.0  (suhu awal — toleransi maksimum mundur ~5% fitness)
     *   T_final   = 0.01 (suhu akhir — hampir deterministik)
     *   cooling   = 0.92 (faktor pendinginan per iterasi luar)
     *   maxIter   = 40   (iterasi luar SA)
     */
    private function localSearch(Chromosome $chromosome): Chromosome
    {
        $chromosome->setConstraintParams(
            $this->eveningStartSlot,
            $this->breakSlots,
            $this->validRuangIds,
            $this->morningEndSlot,
            8,
            $this->parallelPairs,
            $this->activeConstraints,
            $this->lockedRuangIndex,
            $this->lockedDosenIndex
        );

        // [P3] Perbaiki pasangan HC15 dulu sebelum local search gene biasa
        if (!empty($this->parallelPairs)) {
            $genesRef = $chromosome->getGenes();
            if ($this->repairParallelPairs($genesRef)) {
                $chromosome->markDirty();
            }
        }

        $chromosome->calculateFitness();

        if ($chromosome->getFitness() >= 100.0) {
            return $chromosome; // Sudah sempurna
        }

        $T       = 5.0;   // Suhu awal
        $Tf      = 0.01;  // Suhu akhir
        $cooling = 0.95;  // Faktor pendinginan (lebih gigih)
        $maxIter = 100;   // Iterasi lebih banyak untuk membersihkan sisa bentrok

        $bestChrom        = $chromosome->copy();
        $bestFitnessEver  = $chromosome->getFitness();

        for ($iter = 0; $iter < $maxIter && $T > $Tf; $iter++) {
            $improved = false;
            $genes    = $chromosome->getGenes();

            // Proses gen yang berkonflik dulu, lalu gen dengan soft-violation
            $conflictGenes   = $this->getConflictingGeneIndices($genes);
            $violationGenes  = empty($conflictGenes)
                               ? $this->getViolatingGeneIndices($genes)
                               : [];
            $targetIndices   = !empty($conflictGenes) ? $conflictGenes : $violationGenes;

            if (empty($targetIndices)) {
                break; // Tidak ada yang perlu diperbaiki
            }

            foreach ($targetIndices as $idx) {
                $gene     = $genes[$idx];
                $info     = $this->kelasData[$gene->kelasId] ?? null;
                if (!$info) {
                    continue;
                }

                // [P3] Skip gene yang merupakan bagian dari pasangan HC15 di
                // local search per-gene biasa ini — posisinya sudah ditata
                // oleh repairParallelPairs() di atas, dan mengubahnya sendirian
                // di sini (tanpa ikut memindahkan partner) akan merusak gap=1.
                if (isset($this->pairPartnerOf[$gene->kelasId])) {
                    continue;
                }

                $origHari    = $gene->hariId;
                $origSlot    = $gene->slotMulai;
                $origRuang   = $gene->ruangId;
                $origKap     = $gene->kapasitasRuang;
                $origTipe    = $gene->tipeRuangan;
                $origFitness = $chromosome->getFitness();

                // Ambil kandidat sesuai tipe kelas (kelas-S wajib malam)
                $candidates = $this->buildCandidateSlots($gene->isKelasS, $gene->durasi, $info['jenis'] ?? Gene::JENIS_TEORI);
                shuffle($candidates);
                $candidates = array_slice($candidates, 0, 30);

                // Opsi ruangan
                $roomOptions = $info['ruangan_options'] ?? [];
                if (empty($roomOptions)) {
                    $roomOptions = [$origRuang];
                }

                // Bangun kandidat kombinasi [hari, slot, ruangId]
                $localCandidates = [];

                // 1. Ganti ruangan saja (hari & slot tetap)
                foreach ($roomOptions as $rId) {
                    if ($rId !== $origRuang) {
                        $localCandidates[] = [$origHari, $origSlot, $rId];
                    }
                }

                // 2. Ganti hari & slot (ruangan tetap)
                foreach ($candidates as [$hari, $slot]) {
                    $localCandidates[] = [$hari, $slot, $origRuang];
                }

                // 3. Gabungan hari, slot, dan ruangan alternatif (dibatasi 10 sampel agar cepat)
                if (count($roomOptions) > 1 && count($candidates) > 0) {
                    $sampledSlots = array_slice($candidates, 0, 10);
                    foreach ($sampledSlots as [$hari, $slot]) {
                        foreach ($roomOptions as $rId) {
                            if ($rId !== $origRuang) {
                                $localCandidates[] = [$hari, $slot, $rId];
                            }
                        }
                    }
                }

                // Batasi total evaluasi kandidat per gen (max 40)
                $localCandidates = array_slice($localCandidates, 0, 40);

                $localBestFitness = $origFitness;
                $localBestHari    = $origHari;
                $localBestSlot    = $origSlot;
                $localBestRuang   = $origRuang;

                foreach ($localCandidates as [$hari, $slot, $rId]) {
                    $gene->hariId    = $hari;
                    $gene->slotMulai = $slot;
                    $gene->ruangId   = $rId;

                    $rInfo = $this->ruanganList[$rId] ?? null;
                    $gene->kapasitasRuang = $rInfo['kapasitas'] ?? 0;
                    $gene->tipeRuangan    = $rInfo['tipe_ruangan'] ?? Gene::TIPE_REGULER;
                    $gene->namaRuang      = $rInfo['nama'] ?? '';

                    $chromosome->markDirty();
                    $chromosome->calculateFitness();

                    $newFitness = $chromosome->getFitness();
                    $delta      = $origFitness - $newFitness; // positif = lebih buruk

                    // Terima jika lebih baik (greedy) ATAU probabilistik SA
                    $accept = $newFitness > $origFitness
                        || ($T > $Tf && $delta > 0 && (mt_rand() / mt_getrandmax()) < exp(-$delta / $T));

                    if ($accept && $newFitness > $localBestFitness) {
                        $localBestFitness = $newFitness;
                        $localBestHari    = $hari;
                        $localBestSlot    = $slot;
                        $localBestRuang   = $rId;
                        $improved         = true;
                    }
                }

                // Terapkan posisi terbaik yang ditemukan
                $gene->hariId    = $localBestHari;
                $gene->slotMulai = $localBestSlot;
                $gene->ruangId   = $localBestRuang;
                $rInfo = $this->ruanganList[$localBestRuang] ?? null;
                $gene->kapasitasRuang = $rInfo['kapasitas'] ?? 0;
                $gene->tipeRuangan    = $rInfo['tipe_ruangan'] ?? Gene::TIPE_REGULER;
                $gene->namaRuang      = $rInfo['nama'] ?? '';

                $chromosome->markDirty();
                $chromosome->calculateFitness();

                // Update best-ever
                if ($chromosome->getFitness() > $bestFitnessEver) {
                    $bestFitnessEver = $chromosome->getFitness();
                    $bestChrom       = $chromosome->copy();
                }
            }

            // Dinginkan suhu
            $T *= $cooling;

            if (!$improved && $T < 0.1) {
                break; // Sudah sangat dingin dan tidak ada perbaikan
            }
        }

        // Kembalikan yang terbaik antara chromosome final dan best-ever
        return $bestChrom->getFitness() >= $chromosome->getFitness()
               ? $bestChrom
               : $chromosome;
    }

    /**
     * Dapatkan indeks gen yang terlibat konflik hard-constraint — O(n) via index.
     *
     * @param  Gene[] $genes
     * @return int[]
     */
    private function getConflictingGeneIndices(array $genes): array
    {
        return $this->buildConflictingIndices($genes);
    }

    /**
     * Dapatkan indeks gen yang punya soft-constraint violation.
     *
     * @param  Gene[] $genes
     * @return int[]
     */
    private function getViolatingGeneIndices(array $genes): array
    {
        $violating = [];
        foreach ($genes as $idx => $gene) {
            if ($gene->getSoftViolations(
                $this->eveningStartSlot,
                $this->breakSlots,
                $this->validRuangIds,
                $this->morningEndSlot
            ) > 0) {
                $violating[] = $idx;
            }
        }
        return $violating;
    }

    /**
     * Bangun pool kandidat hari×slot yang valid untuk satu gen.
     *
     * @param  bool $isKelasS
     * @param  int  $durasi
     * @return array  [[$hariId, $slotMulai], ...]
     */
    private function buildCandidateSlots(bool $isKelasS, int $durasi, string $jenisMatkul = 'Teori'): array
    {
        $maxStart   = max(1, $this->maxSlot - $durasi + 1);
        $candidates = [];
        $validHari  = $this->getValidHariList($jenisMatkul);

        foreach ($validHari as $hari) {
            for ($slot = 1; $slot <= $maxStart; $slot++) {
                // Cek overlap break slots
                $hasBreakOverlap = $this->hasBreakOverlap($slot, $durasi, $isKelasS);
                if ($hasBreakOverlap) {
                    continue;
                }
                // [BARU][HC16] Exclude slot yang overlap Sholat Jumat
                if ($this->overlapsBlockedFridaySlot($hari, $slot, $durasi)) {
                    continue;
                }
                // Cek apakah slot diaktifkan oleh user
                if (!$this->isSlotActive($hari, $slot, $durasi)) {
                    continue;
                }
                // HC12: Enforce unconditionally
                if ($isKelasS) {
                    if ($durasi >= 5) {
                        if ($slot !== 14) continue;
                    } else if ($slot < $this->eveningStartSlot) {
                        continue;
                    }
                }
                if (!$isKelasS && ($slot + $durasi - 1) >= $this->eveningStartSlot) {
                    continue;
                }
                $candidates[] = [$hari, $slot];
            }
        }

        // [Bug3-fix] Jika pool kelas-S kosong (eveningStartSlot > maxStart),
        // perluas pencarian hingga maxSlot tanpa filter durasi-boundary
        if (empty($candidates) && $isKelasS) {
            foreach ($validHari as $hari) {
                for ($slot = $this->eveningStartSlot; $slot <= $this->maxSlot; $slot++) {
                    $hasBreakOverlap = $this->hasBreakOverlap($slot, $durasi);
                    // [BARU][HC16] Exclude slot yang overlap Sholat Jumat dan pastikan slot aktif
                    if (!$hasBreakOverlap && !$this->overlapsBlockedFridaySlot($hari, $slot, $durasi) && $this->isSlotActive($hari, $slot, $durasi)) {
                        $candidates[] = [$hari, $slot];
                    }
                }
            }
        }

        // [FIX] Last resort untuk kelas sore: gunakan eveningStartSlot saja (tetap di slot malam)
        if (empty($candidates) && $isKelasS) {
            foreach ($validHari as $hari) {
                if (!in_array($this->eveningStartSlot, $this->breakSlots, true)
                    && !$this->overlapsBlockedFridaySlot($hari, $this->eveningStartSlot, $durasi)) {
                    $candidates[] = [$hari, $this->eveningStartSlot];
                }
            }
        }

        // Last resort untuk kelas reguler: jika masih kosong, pakai semua slot valid
        if (empty($candidates) && !$isKelasS) {
            foreach ($validHari as $hari) {
                for ($slot = 1; $slot <= $maxStart; $slot++) {
                    $hasBreakOverlap = false;
                    for ($i = 0; $i < $durasi; $i++) {
                        if (in_array($slot + $i, $this->breakSlots, true)) {
                            $hasBreakOverlap = true;
                            break;
                        }
                    }
                    // [BARU][HC16] Exclude slot yang overlap Sholat Jumat
                    if (!$hasBreakOverlap && !$this->overlapsBlockedFridaySlot($hari, $slot, $durasi)) {
                        $candidates[] = [$hari, $slot];
                    }
                }
            }
        }

        return $candidates;
    }


    // ═══════════════════════════════════════════════════════════════════════
    // HIGH-LEVEL: Feasibility Check [H2]
    // ═══════════════════════════════════════════════════════════════════════

    // Diperiksa inline di run() — tidak perlu method terpisah.
    // Kriteria:
    //   Sempurna : conflicts=0 AND violations=0
    //   Diterima : conflicts=0 AND fitness≥90

    // ═══════════════════════════════════════════════════════════════════════
    // ANTI-TRAPPING T2: Conflict-Graph Deadlock Breaker
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * [T2] Deteksi dan pecahkan deadlock konflik dalam seluruh populasi.
     *
     * Deadlock terjadi ketika sekelompok gen saling mengunci satu sama lain:
     *   Gene A konflik B, B konflik C, C konflik A (siklus 3).
     * Repair satu per satu tidak pernah berhasil karena memindahkan satu gen
     * langsung menciptakan konflik baru dengan anggota cluster lain.
     *
     * Solusi: identifikasi cluster konflik, lalu pindahkan SELURUH cluster
     * ke hari yang berbeda dari hari saat ini secara bersamaan.
     */
    private function breakDeadlocksInPopulation(): void
    {
        $topN = min(5, count($this->population));
        usort($this->population, fn($a, $b) => $b->getFitness() <=> $a->getFitness());

        for ($pi = 0; $pi < $topN; $pi++) {
            $chromosome = $this->population[$pi];
            if ($chromosome->getConflicts() === 0) {
                continue;
            }

            $genes  = $chromosome->getGenes();
            $n      = count($genes);
            $broken = false;

            // Bangun adjacency list menggunakan slot-index — O(n * SKS)
            $adj = array_fill(0, $n, []);

            // dosenConflict[dosenId][hariId][slot] = [geneIdx, ...]
            $dosenOcc = [];
            $ruangOcc = [];

            foreach ($genes as $idx => $gene) {
                $did  = $gene->dosenId;
                $rid  = $gene->ruangId;
                $hari = $gene->hariId;
                $end  = $gene->getSlotAkhir();

                for ($slot = $gene->slotMulai; $slot <= $end; $slot++) {
                    if ($did !== 0) {
                        if (isset($dosenOcc[$did][$hari][$slot])) {
                            foreach ($dosenOcc[$did][$hari][$slot] as $other) {
                                $adj[$idx][] = $other;
                                $adj[$other][] = $idx;
                            }
                        }
                        $dosenOcc[$did][$hari][$slot][] = $idx;
                    }
                    if ($rid !== 0) {
                        if (isset($ruangOcc[$rid][$hari][$slot])) {
                            foreach ($ruangOcc[$rid][$hari][$slot] as $other) {
                                $adj[$idx][] = $other;
                                $adj[$other][] = $idx;
                            }
                        }
                        $ruangOcc[$rid][$hari][$slot][] = $idx;
                    }
                }
            }

            // Hapus duplikat di adjacency list
            foreach ($adj as $i => $neighbors) {
                $adj[$i] = array_unique($neighbors);
            }

            // Cari cluster (connected components) di conflict graph
            $visited  = array_fill(0, $n, false);
            $clusters = [];
            for ($i = 0; $i < $n; $i++) {
                if (!$visited[$i] && !empty($adj[$i])) {
                    // BFS untuk temukan cluster
                    $cluster = [];
                    $queue   = [$i];
                    $visited[$i] = true;
                    while (!empty($queue)) {
                        $node = array_shift($queue);
                        $cluster[] = $node;
                        foreach ($adj[$node] as $neighbor) {
                            if (!$visited[$neighbor]) {
                                $visited[$neighbor] = true;
                                $queue[] = $neighbor;
                            }
                        }
                    }
                    if (count($cluster) >= 2) {
                        $clusters[] = $cluster;
                    }
                }
            }

            // Selesaikan setiap cluster dengan memindahkan ke hari yang bebas
            foreach ($clusters as $cluster) {
                $usedHari = array_unique(array_map(fn($i) => $genes[$i]->hariId, $cluster));
                // Cari hari yang belum dipakai oleh cluster ini
                $freeHari = array_values(array_diff($this->hariList, $usedHari));

                if (empty($freeHari)) {
                    // Semua hari sudah terpakai, pakai hari yang paling sedikit konfliknya
                    $freeHari = $this->hariList;
                }

                // Distribusi cluster ke hari bebas secara round-robin
                foreach ($cluster as $idx) {
                    $gene      = $genes[$idx];

                    // [P3] Jika gene ini bagian dari pasangan HC15, jangan dipindah
                    // sendirian di sini — biarkan repairParallelPairs() yang menangani
                    // pasangan ini secara konsisten (hari sama + gap 1) di siklus berikutnya.
                    if (isset($this->pairPartnerOf[$gene->kelasId])) {
                        continue;
                    }

                    $newHari   = $freeHari[$idx % count($freeHari)];
                    $newSlot   = $this->pickSmartSlot($gene->namaKelas, $gene->durasi, $newHari);
                    $gene->hariId    = $newHari;
                    $gene->slotMulai = $newSlot;
                    $broken = true;
                }
            }

            if ($broken) {
                $chromosome->setConstraintParams(
                    $this->eveningStartSlot, $this->breakSlots,
                    $this->validRuangIds, $this->morningEndSlot, 8,
                    $this->parallelPairs
                );
                $chromosome->markDirty();
                $chromosome->calculateFitness();
                $this->logProblem('deadlock_break',
                    "Deadlock di ".count($clusters)." cluster diselesaikan pada kromosom #{$pi}.");
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ANTI-TRAPPING T3: Dosen-Load Pre-Assigner
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * [T3] Pre-assign slot untuk dosen dengan beban tinggi.
     *
     * Masalah: dosen yang mengajar 5+ kelas hampir selalu menyebabkan
     * konflik karena ruang slot valid habis. GA berputar-putar mencoba
     * repair yang mustahil.
     *
     * Solusi: sebelum populasi dibuat, alokasikan slot-slot berbeda untuk
     * setiap kelas dosen beban tinggi secara deterministik. Semua gen
     * untuk dosen ini akan diinisialisasi dari pre-assignment ini.
     *
     * [P3] Catatan: kelas yang merupakan bagian dari pasangan HC15 TIDAK
     * di-pre-assign di sini — posisinya akan ditentukan oleh
     * createPairedGenes() (P2) supaya hubungan gap-1 selalu terjaga sejak
     * awal. Hanya kelas non-pasangan dari dosen beban tinggi yang diproses.
     */
    private function preAssignHighLoadDosen(): void
    {
        // Hitung beban per dosen
        $beban = []; // dosenId => [kelasId, ...]
        foreach ($this->kelasData as $kelasId => $info) {
            $did = $info['id_dosen'];
            if ($did === 0) {
                continue;
            }
            // [P3] Skip kelas yang merupakan bagian dari pasangan HC15
            if (isset($this->pairPartnerOf[$kelasId])) {
                continue;
            }
            $beban[$did][] = $kelasId;
        }

        $this->dosenPreassigned = [];

        foreach ($beban as $dosenId => $kelasList) {
            if (count($kelasList) < $this->dosenLoadThreshold) {
                continue; // Beban rendah, biarkan GA yang mengatur
            }

            // Bangun semua slot valid yang bisa dipakai
            $allSlots = [];
            foreach ($this->hariList as $hari) {
                for ($slot = 1; $slot <= $this->maxSlot; $slot++) {
                    if (!in_array($slot, $this->breakSlots, true)) {
                        $allSlots[] = [$hari, $slot];
                    }
                }
            }

            // Assign slot berbeda untuk setiap kelas dosen ini
            // Pastikan tidak overlap: jika SKS=3, slot X dipakai → blok X, X+1, X+2
            $assigned    = []; // [ [$hari, $slotMulai, $slotAkhir], ... ]
            $preassigned = []; // kelasId => [$hariId, $slotMulai]
            shuffle($allSlots);

            foreach ($kelasList as $kelasId) {
                $durasi = $this->kelasData[$kelasId]['sks'];
                $nama   = $this->kelasData[$kelasId]['nama'];
                $jenis  = $this->kelasData[$kelasId]['jenis'] ?? Gene::JENIS_TEORI;
                $validHariForClass = $this->getValidHariList($jenis);

                foreach ($allSlots as $idx => [$hari, $slot]) {
                    // Filter berdasarkan hari yang valid untuk jenis matkul ini
                    if (!in_array($hari, $validHariForClass, true)) {
                        continue;
                    }

                    $slotAkhir = $slot + $durasi - 1;
                    if ($slotAkhir > $this->maxSlot) {
                        continue;
                    }

                    // Cek overlap dengan breakSlots
                    $hasBreakOverlap = $this->hasBreakOverlap($slot, $durasi);
                    if ($hasBreakOverlap) {
                        continue;
                    }

                    // Cek overlap dengan yang sudah di-assign
                    $overlap = false;
                    foreach ($assigned as [$aHari, $aStart, $aEnd]) {
                        if ($aHari === $hari && $slot <= $aEnd && $slotAkhir >= $aStart) {
                            $overlap = true;
                            break;
                        }
                    }

                    if (!$overlap) {
                        // Validasi jenis kelas menggunakan detectKelasS() agar konsisten
                        $isS = $this->detectKelasS($nama);
                        if ($isS && $slot < $this->eveningStartSlot) {
                            continue;
                        }
                        if (!$isS && $slotAkhir >= $this->eveningStartSlot) {
                            continue;
                        }

                        // [BARU][HC16] Exclude slot yang overlap Sholat Jumat
                        if ($this->overlapsBlockedFridaySlot($hari, $slot, $durasi)) {
                            continue;
                        }
                        if (!$this->isSlotActive($hari, $slot, $durasi)) {
                            continue;
                        }

                        $preassigned[$kelasId] = [$hari, $slot];
                        $assigned[] = [$hari, $slot, $slotAkhir];
                        break;
                    }
                }
            }

            if (!empty($preassigned)) {
                $this->dosenPreassigned[$dosenId] = $preassigned;
                $this->logProblem('preassign',
                    "Dosen {$dosenId} (beban ".count($kelasList)." kelas): ".count($preassigned)." slot pre-assigned.");
            }
        }
    }

    /**
     * Ambil pre-assigned slot untuk kelas tertentu (jika ada).
     * Dipanggil dari createRandomGene() — hanya untuk 50% populasi awal
     * agar tetap ada variasi.
     *
     * @return int[]|null  [$hariId, $slotMulai] atau null
     */
    private function getPreassignedSlot(int $kelasId, int $dosenId, bool $usePreassign = true): ?array
    {
        if (empty($this->dosenPreassigned[$dosenId])) {
            return null;
        }
        if (!$usePreassign) {
            return null;
        }
        return $this->dosenPreassigned[$dosenId][$kelasId] ?? null;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ANTI-TRAPPING T4: Fitness Plateau Detector + Niche Pressure
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * [T4] Deteksi plateau fitness dan aktifkan niche pressure.
     *
     * Plateau terjadi ketika semua kromosom konvergen ke fitness yang hampir
     * sama — seleksi tidak bisa membedakan individu, crossover hanya
     * menghasilkan klon, dan GA berhenti berkembang.
     *
     * Niche pressure: kromosom yang hash-nya identik dengan individu lain
     * mendapat "bonus penalti" melalui replacement — yang unik dipertahankan,
     * yang duplikat diganti kromosom acak baru.
     */
    private function handleFitnessPlateauAndNiche(): void
    {
        if (count($this->population) < 4) {
            return;
        }

        // Hitung variance fitness populasi
        $fitnesses = array_map(fn($c) => $c->getFitness(), $this->population);
        $popCount  = count($fitnesses);
        if ($popCount === 0) {
            return; // Tidak ada populasi, skip plateau detection
        }
        $mean      = array_sum($fitnesses) / $popCount;
        $variance  = array_sum(array_map(fn($f) => ($f - $mean) ** 2, $fitnesses))
                     / $popCount;

        $isPlateauNow = $variance < $this->plateauVarianceThreshold;

        if ($isPlateauNow && !$this->nichePressureActive) {
            $this->nichePressureActive = true;
            $varStr = number_format($variance, 4);
            $this->logProblem('plateau_detected',
                "Fitness variance={$varStr} → niche pressure diaktifkan.");

            // Hapus duplikat berdasarkan hash, ganti dengan kromosom baru
            $seen    = [];
            $newPop  = [];
            $elite   = $this->elites[0] ?? null;

            foreach ($this->population as $chromosome) {
                $hash = $chromosome->buildGenesHash();
                if (!isset($seen[$hash])) {
                    $seen[$hash] = true;
                    $newPop[] = $chromosome;
                } else {
                    // Duplikat → ganti dengan guided perturbation [T5 inline]
                    $usePre = (count($newPop) < $this->populationSize / 2);
                    $newPop[] = $elite !== null
                        ? $this->guidedPerturbation($elite)
                        : $this->createRandomChromosome($usePre);
                }
            }
            $this->population = $newPop;

        } elseif (!$isPlateauNow && $this->nichePressureActive) {
            // Plateau sudah teratasi
            $this->nichePressureActive = false;
            $varStr2 = number_format($variance, 4);
            $this->logProblem('plateau_resolved',
                "Fitness variance={$varStr2} → niche pressure dinonaktifkan.");
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ANTI-TRAPPING T5: Guided Re-Seed dari Elite Archive
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * [T5][P3] Buat kromosom baru dengan elite-guided perturbation.
     *
     * Alih-alih membuat kromosom sepenuhnya acak (yang kemungkinan besar
     * buruk), ambil kromosom elite terbaik dan hanya acak-ulang gen-gen
     * yang masih berkonflik atau melanggar soft constraint.
     * Gen yang sudah "baik" (tidak konflik) dipertahankan persis.
     *
     * Ini jauh lebih efektif daripada random restart karena:
     * - 60-80% gen biasanya sudah benar di kromosom elite
     * - Hanya ~20-40% gen yang perlu diubah
     * - Titik awal sudah dekat dengan solusi optimal
     *
     * [P3] Gene yang merupakan bagian dari pasangan HC15 dan terdeteksi
     * bermasalah di-regenerasi BERSAMA partnernya (bukan sendirian) supaya
     * hasil perturbation tetap memenuhi gap-1, menggunakan createPairedGenes().
     */
    private function guidedPerturbation(Chromosome $elite): Chromosome
    {
        $elite->setConstraintParams(
            $this->eveningStartSlot, $this->breakSlots,
            $this->validRuangIds, $this->morningEndSlot, 8,
            $this->parallelPairs
        );
        $elite->calculateFitness();

        $eliteGenes  = $elite->getGenes();
        $byKelasId   = [];
        foreach ($eliteGenes as $idx => $g) {
            $byKelasId[$g->kelasId] = $idx;
        }

        // Tandai gen bermasalah menggunakan O(n) index
        $conflictSet = [];
        foreach ($this->buildConflictingIndices($eliteGenes) as $idx) {
            $conflictSet[$idx] = true;
        }
        foreach ($eliteGenes as $idx => $g) {
            if ($g->getSoftViolations($this->eveningStartSlot, $this->breakSlots, $this->validRuangIds, $this->morningEndSlot) > 0) {
                $conflictSet[$idx] = true;
            }
        }
        // [P3] Tandai juga gen yang melanggar HC15 (pasangan)
        foreach ($this->parallelPairs as [$kelasIdA, $kelasIdB]) {
            $idxA = $byKelasId[$kelasIdA] ?? null;
            $idxB = $byKelasId[$kelasIdB] ?? null;
            if ($idxA === null || $idxB === null) {
                continue;
            }
            if ($eliteGenes[$idxA]->validatePairSequencing($eliteGenes[$idxB], true) > 0) {
                $conflictSet[$idxA] = true;
                $conflictSet[$idxB] = true;
            }
        }

        $newGenesByKelasId = [];
        $handledPairs      = [];

        foreach ($eliteGenes as $idx => $gene) {
            if (isset($newGenesByKelasId[$gene->kelasId])) {
                continue; // Sudah dibuat sebagai bagian dari pasangan
            }

            $partnerId = $this->pairPartnerOf[$gene->kelasId] ?? null;

            if (isset($conflictSet[$idx]) && $partnerId !== null && isset($this->kelasData[$partnerId])) {
                // [P3] Regenerasi pasangan bersamaan agar gap-1 terjaga
                [$newA, $newB] = $this->createPairedGenes($gene->kelasId, $partnerId);
                $newGenesByKelasId[$gene->kelasId] = $newA;
                $newGenesByKelasId[$partnerId]      = $newB;
            } elseif (isset($conflictSet[$idx])) {
                $info = $this->kelasData[$gene->kelasId] ?? null;
                $newGenesByKelasId[$gene->kelasId] = $info
                    ? $this->createRandomGene($gene->kelasId, $info)
                    : $gene->copy();
            } else {
                $newGenesByKelasId[$gene->kelasId] = $gene->copy();
            }
        }

        // Bangun ulang sesuai urutan asli kelasData
        $newGenes = [];
        foreach ($this->kelasData as $kelasId => $info) {
            $newGenes[] = $newGenesByKelasId[$kelasId] ?? $this->createRandomGene($kelasId, $info);
        }

        $c = new Chromosome($newGenes);
        $c->setConstraintParams(
            $this->eveningStartSlot, $this->breakSlots,
            $this->validRuangIds, $this->morningEndSlot, 8,
            $this->parallelPairs
        );
        return $c;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // HIGH-LEVEL: Error Recovery [H4]
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * [H4][P3] Fallback solution yang bermakna — distribusikan kelas ke semua
     * hari secara round-robin daripada tumpuk semua di hari=1 slot=1.
     * Pasangan HC15 dibangun bersamaan via createPairedGenes() agar fallback
     * pun tetap menjaga gap-1 sebisa mungkin.
     */
    private function handleCriticalError(\Exception $e, int $generation = 0): array
    {
        $this->logProblem('critical_error', $e->getMessage());

        $fallbackByKelasId = [];
        $hariIndex         = 0;
        $hariCount         = max(1, count($this->hariList));

        foreach ($this->kelasData as $kelasId => $info) {
            if (isset($fallbackByKelasId[$kelasId])) {
                continue;
            }

            $partnerId = $this->pairPartnerOf[$kelasId] ?? null;
            if ($partnerId !== null && isset($this->kelasData[$partnerId]) && !isset($fallbackByKelasId[$partnerId])) {
                [$geneA, $geneB] = $this->createPairedGenes($kelasId, $partnerId);
                $fallbackByKelasId[$kelasId]   = $geneA;
                $fallbackByKelasId[$partnerId] = $geneB;
                continue;
            }

            $hari      = $this->hariList[$hariIndex % $hariCount];
            $slotMulai = $this->pickSmartSlot($info['nama'], $info['sks'], $hari);
            $ruangId   = $this->pickSmartRoom($info['ruangan_options'], $info['kapasitas'], $info['jenis'] ?? Gene::JENIS_TEORI);
            $kapRuang  = $this->ruanganList[$ruangId]['kapasitas'] ?? 0;
            $tipeRuang = $this->ruanganList[$ruangId]['tipe_ruangan'] ?? Gene::TIPE_REGULER;

            $fallbackByKelasId[$kelasId] = new Gene(
                $kelasId, $hari, $slotMulai, $info['sks'],
                $ruangId, $info['id_dosen'],
                $info['kapasitas'], $info['jenis'], $kapRuang,
                $info['nama'], $this->maxSlot,
                $tipeRuang, $info['kategori'] ?? Gene::KATEGORI_SEDANG,
                $info['nama_matkul'] ?? '', $this->ruanganList[$ruangId]['nama'] ?? ''
            );
            $hariIndex++;
        }

        $fallbackGenes = [];
        foreach ($this->kelasData as $kelasId => $info) {
            $fallbackGenes[] = $fallbackByKelasId[$kelasId];
        }

        $fallback = new Chromosome($fallbackGenes);
        $fallback->setConstraintParams(
            $this->eveningStartSlot, $this->breakSlots,
            $this->validRuangIds, $this->morningEndSlot, 8,
            $this->parallelPairs
        );
        $fallback->calculateFitness();

        return [
            'genes'                => $fallbackGenes,
            'fitness_pct'          => round($fallback->getFitness(), 2),
            'generasi'             => $generation,
            'total_kelas'          => count($this->kelasData),
            'pelanggaran'          => $fallback->getConflicts(),
            'dosen_conflicts'      => $fallback->getDosenConflicts(),
            'ruangan_conflicts'    => $fallback->getRuanganConflicts(),
            'kelas_conflicts'      => $fallback->getKelasConflicts(),
            'constraint_violations' => $fallback->getConstraintViolations(),
            'problem_log'          => $this->problemLog,
            'error'                => $e->getMessage(),
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    // UTILITY
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Kirim progress via SSE callback.
     */
    private function reportProgress(int $generation, Chromosome $best): void
    {
        if ($this->progressCallback !== null) {
            ($this->progressCallback)([
                'gen'             => $generation,
                'fitness'         => round($best->getFitness(), 2),
                'pelanggaran'     => $best->getConflicts(),
                'dosen_konflik'   => $best->getDosenConflicts(),
                'ruangan_konflik' => $best->getRuanganConflicts(),
                'kelas_konflik'   => $best->getKelasConflicts(),
                'soft_violations' => $best->getConstraintViolations(),
                'optimal'         => false,
            ]);
        }
    }

    /**
     * Kirim sinyal bahwa solusi optimal sudah ditemukan sebelum generasi habis.
     * Frontend akan langsung render hasil tanpa menunggu event 'done' dari akhir run().
     */
    private function reportOptimalFound(int $generation, Chromosome $best, string $reason): void
    {
        if ($this->progressCallback !== null) {
            ($this->progressCallback)([
                'gen'             => $generation,
                'fitness'         => round($best->getFitness(), 2),
                'pelanggaran'     => $best->getConflicts(),
                'dosen_konflik'   => $best->getDosenConflicts(),
                'ruangan_konflik' => $best->getRuanganConflicts(),
                'kelas_konflik'   => $best->getKelasConflicts(),
                'soft_violations' => $best->getConstraintViolations(),
                'optimal'         => true,
                'optimal_reason'  => $reason, // 'perfect' | 'acceptable' | 'excellent'
            ]);
        }
    }

    /**
     * Catat kejadian masalah internal untuk debugging / audit.
     */
    private function logProblem(string $type, string $message): void
    {
        $this->problemLog[] = [
            'type'    => $type,
            'message' => $message,
            'elapsed' => round(microtime(true) - $this->startTime, 2) . 's',
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    // UTILITY: O(n) Conflict Index Builder
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Bangun slot-conflict index dari array Gene — O(n * SKS_rata) ≈ O(n).
     *
     * Mengembalikan dua struktur:
     *   $dosenIndex[dosenId][hariId][slot] = true
     *   $ruangIndex[ruangId][hariId][slot] = true
     *
     * Dipakai oleh semua fungsi yang perlu mendeteksi konflik:
     * repairOffspring, getConflictingGeneIndices, breakDeadlocksInPopulation,
     * guidedPerturbation — menggantikan O(n²) pair-loop.
     *
     * @param  Gene[] $genes
     * @return array  [$dosenIndex, $ruangIndex]
     */
    private function buildConflictIndex(array $genes): array
    {
        $dosenIndex = $this->lockedDosenIndex;
        $ruangIndex = $this->lockedRuangIndex;
        $kelasIndex = [];

        foreach ($genes as $gene) {
            $did  = $gene->dosenId;
            $rid  = $gene->ruangId;
            $nk   = $gene->namaKelas;
            $hari = $gene->hariId;
            $end  = $gene->getSlotAkhir();

            for ($slot = $gene->slotMulai; $slot <= $end; $slot++) {
                if ($did !== 0) {
                    $dosenIndex[$did][$hari][$slot] = true;
                }
                if ($rid !== 0) {
                    $ruangIndex[$rid][$hari][$slot] = true;
                }
                if ($nk !== '') {
                    $kelasIndex[$nk][$hari][$slot] = true;
                }
            }
        }

        return [$dosenIndex, $ruangIndex, $kelasIndex];
    }

    /**
     * Cek apakah satu Gene berkonflik dengan index yang sudah dibangun.
     * Dipakai untuk cek cepat tanpa loop O(n).
     *
     * @param  Gene  $gene
     * @param  array $dosenIndex  dari buildConflictIndex()
     * @param  array $ruangIndex  dari buildConflictIndex()
     * @return int   0=aman, 1=konflik dosen, 2=konflik ruangan
     */
    private function geneConflictsWithIndex(Gene $gene, array $dosenIndex, array $ruangIndex, array $kelasIndex = []): int
    {
        $did  = $gene->dosenId;
        $rid  = $gene->ruangId;
        $nk   = $gene->namaKelas;
        $hari = $gene->hariId;
        $end  = $gene->getSlotAkhir();

        for ($slot = $gene->slotMulai; $slot <= $end; $slot++) {
            if ($did !== 0 && isset($dosenIndex[$did][$hari][$slot])) {
                return 1;
            }
            if ($rid !== 0 && isset($ruangIndex[$rid][$hari][$slot])) {
                return 2;
            }
            if ($nk !== '' && isset($kelasIndex[$nk][$hari][$slot])) {
                return 3;
            }
        }
        return 0;
    }

    /**
     * Dapatkan indeks gen yang terlibat konflik — O(n) via index.
     * Menggantikan O(n²) loop di getConflictingGeneIndices().
     *
     * @param  Gene[] $genes
     * @return int[]
     */
    private function buildConflictingIndices(array $genes): array
    {
        $conflicting = [];
        $dosenSeen   = []; // [dosenId][hariId][slot] = firstGeneIdx
        $ruangSeen   = []; // [ruangId][hariId][slot] = firstGeneIdx
        $kelasSeen   = []; // [namaKelas][hariId][slot] = firstGeneIdx

        // Pre-fill $dosenSeen & $ruangSeen dengan slot terkunci dari prodi lain
        foreach ($this->lockedDosenIndex as $did => $haris) {
            foreach ($haris as $hari => $slots) {
                foreach ($slots as $slot => $val) {
                    $dosenSeen[$did][$hari][$slot] = 'locked';
                }
            }
        }
        foreach ($this->lockedRuangIndex as $rid => $haris) {
            foreach ($haris as $hari => $slots) {
                foreach ($slots as $slot => $val) {
                    $ruangSeen[$rid][$hari][$slot] = 'locked';
                }
            }
        }

        // Hitung beban SKS per dosen per hari untuk HC14
        $dosenDaySks = []; // [dosenId][hariId] = SKS
        $dosenDayGenes = []; // [dosenId][hariId][] = geneIdx

        foreach ($genes as $idx => $gene) {
            $did  = $gene->dosenId;
            $rid  = $gene->ruangId;
            $nk   = $gene->namaKelas;
            $hari = $gene->hariId;
            $end  = $gene->getSlotAkhir();

            if ($did > 0) {
                $dosenDaySks[$did][$hari] = ($dosenDaySks[$did][$hari] ?? 0) + $gene->durasi;
                $dosenDayGenes[$did][$hari][] = $idx;
            }

            for ($slot = $gene->slotMulai; $slot <= $end; $slot++) {
                if ($did !== 0) {
                    if (isset($dosenSeen[$did][$hari][$slot])) {
                        $conflicting[$idx] = true;
                        if ($dosenSeen[$did][$hari][$slot] !== 'locked') {
                            $conflicting[$dosenSeen[$did][$hari][$slot]] = true;
                        }
                    } else {
                        $dosenSeen[$did][$hari][$slot] = $idx;
                    }
                }
                if ($rid !== 0) {
                    if (isset($ruangSeen[$rid][$hari][$slot])) {
                        $conflicting[$idx] = true;
                        if ($ruangSeen[$rid][$hari][$slot] !== 'locked') {
                            $conflicting[$ruangSeen[$rid][$hari][$slot]] = true;
                        }
                    } else {
                        $ruangSeen[$rid][$hari][$slot] = $idx;
                    }
                }
                if ($nk !== '') {
                    if (isset($kelasSeen[$nk][$hari][$slot])) {
                        $conflicting[$idx] = true;
                        $conflicting[$kelasSeen[$nk][$hari][$slot]] = true;
                    } else {
                        $kelasSeen[$nk][$hari][$slot] = $idx;
                    }
                }
            }
        }

        // Tandai gen yang berkontribusi terhadap pelanggaran HC14 (SKS/hari > maxSksDayDosen)
        if ($this->activeConstraints === null || in_array('HC14', $this->activeConstraints)) {
            foreach ($dosenDaySks as $did => $hariSks) {
                foreach ($hariSks as $hari => $totalSks) {
                    if ($totalSks > $this->maxSksDayDosen) {
                        foreach ($dosenDayGenes[$did][$hari] as $idx) {
                            $conflicting[$idx] = true;
                        }
                    }
                }
            }
        }

        return array_keys($conflicting);
    }
}
