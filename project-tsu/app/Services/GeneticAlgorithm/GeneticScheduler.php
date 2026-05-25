<?php

namespace App\Services\GeneticAlgorithm;

use Illuminate\Support\Collection;

class GeneticScheduler
{
    private const SLOT_ISTIRAHAT   = 6;   // id_slot yang tidak boleh dipakai
    private const MENIT_BATAS_MALAM = 990; // 16:30 dalam menit (16*60+30)

    private array $slots;       // [id => ['id','waktu_mulai','waktu_selesai','menit_mulai']]
    private array $slotIds;     // [1,2,3,4,...] sorted
    private array $hariList;    // [1,2,3,4,5]
    private array $kelasList;   // collection kelas

    public function __construct(
        private int   $populasiSize,
        private int   $maxGenerasi,
        private float $crossoverRate = 0.8,
        private float $mutationRate  = 0.15,
    ) {}

    // ============================================================
    // ENTRY POINT
    // ============================================================
    public function run(Collection $kelas, array $slots, array $hariList, ?callable $onProgress = null): array
    {
        // Bangun slot map dengan info menit_mulai untuk cek pagi/malam
        $this->slots = [];
        foreach ($slots as $s) {
            $menit = $this->jamKeMenit($s['waktu_mulai']);
            $this->slots[$s['id']] = [
                'id'           => $s['id'],
                'waktu_mulai'  => $s['waktu_mulai'],
                'waktu_selesai'=> $s['waktu_selesai'],
                'menit_mulai'  => $menit,
            ];
        }

        $this->slotIds    = array_keys($this->slots);
        sort($this->slotIds);
        $this->hariList   = $hariList;
        $this->kelasList  = $kelas->values()->all();

        // 1. Init populasi
        $populasi = $this->initPopulasi();

        // 2. Evaluasi awal
        foreach ($populasi as $chr) {
            $chr->fitness = $this->evaluate($chr);
        }

        $best  = $this->getBest($populasi);
        $genke = 0;

        // 3. Evolusi
        for ($gen = 0; $gen < $this->maxGenerasi; $gen++) {
            $genke  = $gen + 1;
            $newPop = [];

            // Elitism — pertahankan kromosom terbaik
            $newPop[] = $best->clone();

            while (count($newPop) < $this->populasiSize) {
                $p1 = $this->seleksi($populasi);
                $p2 = $this->seleksi($populasi);

                [$c1, $c2] = $this->crossover($p1, $p2);

                $this->mutasi($c1);
                $this->mutasi($c2);

                $c1->fitness = $this->evaluate($c1);
                $c2->fitness = $this->evaluate($c2);

                $newPop[] = $c1;
                if (count($newPop) < $this->populasiSize) {
                    $newPop[] = $c2;
                }
            }

            $populasi = $newPop;
            $best     = $this->getBest($populasi);

            // ← Kirim progress tiap 5 generasi atau saat ada peningkatan
            if ($onProgress && ($genke % 5 === 0 || $genke === 1 || $best->fitness >= 1.0)) {
                $pelanggaran = $this->hitungPelanggaran($best);
                $onProgress([
                    'gen'         => $genke,
                    'max_gen'     => $this->maxGenerasi,
                    'fitness'     => round($best->fitness * 100, 2),
                    'pelanggaran' => $pelanggaran,
                    'done'        => false,
                ]);
            }

            if ($best->fitness >= 1.0) break; // sempurna, stop awal
        }

        return [
            'genes'       => $best->genes,
            'fitness'     => $best->fitness,
            'fitness_pct' => round($best->fitness * 100, 2),
            'total_kelas' => count($best->genes),
            'generasi'    => $genke ?? $this->maxGenerasi,
        ];
    }

    private function hitungPelanggaran(Chromosome $chr): int
    {
        $p     = 0;
        $genes = $chr->genes;
        $n     = count($genes);
        $byHari = [];
        foreach ($genes as $g) $byHari[$g->hariId][] = $g;
        foreach ($byHari as $hg) {
            $c = count($hg);
            for ($i = 0; $i < $c; $i++) {
                for ($j = $i + 1; $j < $c; $j++) {
                    $a = $hg[$i]; $b = $hg[$j];
                    $aEnd = $a->slotMulai + $a->durasi - 1;
                    $bEnd = $b->slotMulai + $b->durasi - 1;
                    if (!($a->slotMulai <= $bEnd && $b->slotMulai <= $aEnd)) continue;
                    if ($a->dosenId && $b->dosenId && $a->dosenId === $b->dosenId) $p++;
                    if ($a->ruangId && $b->ruangId && $a->ruangId === $b->ruangId) $p++;
                    if ($a->kelasId === $b->kelasId) $p++;
                }
            }
        }
        return $p;
    }

    // ============================================================
    // INISIALISASI POPULASI
    // ============================================================
    private function initPopulasi(): array
    {
        $pop = [];
        for ($i = 0; $i < $this->populasiSize; $i++) {
            $pop[] = $this->buatKromosomAcak();
        }
        return $pop;
    }

    private function buatKromosomAcak(): Chromosome
    {
        $genes = [];
        foreach ($this->kelasList as $kelas) {
            $genes[] = $this->buatGeneAcak($kelas);
        }
        return new Chromosome($genes);
    }

    private function buatGeneAcak(object $kelas): Gene
    {
        $sks        = (int) ($kelas->matakuliah->sks ?? 2);
        $namaKelas  = $kelas->nama_kelas ?? '';
        $validSlots = $this->getValidSlots($sks, $namaKelas);

        // Pilih slot mulai secara acak dari yang valid
        $slotMulai  = $validSlots[array_rand($validSlots)];
        $hariId     = $this->hariList[array_rand($this->hariList)];

        // Pilih ruangan dari daftar ruangan matakuliah
        $ruangans  = $kelas->matakuliah->ruangans ?? collect();
        $ruangan   = $ruangans->isNotEmpty() ? $ruangans->random() : null;

        return new Gene(
            kelasId:    $kelas->id_kelas,
            hariId:     $hariId,
            slotMulai:  $slotMulai,
            ruangId:    $ruangan?->id_ruang ?? 0,
            dosenId:    $kelas->id_dosen    ?? 0,
            durasi:     $sks,
            kodeMatkul: $kelas->kode_matkul ?? '',
        );
    }

    // ============================================================
    // SLOT VALID
    // Memastikan:
    //   1. Seluruh slot yang dibutuhkan (mulai s.d. mulai+sks-1) tersedia
    //   2. Tidak ada slot istirahat di rentang tersebut
    //   3. Pagi/siang untuk kelas reguler, malam untuk kelas S
    // ============================================================
    private function getValidSlots(int $sks, string $namaKelas): array
    {
        $jenisKelas = strtoupper(substr(trim($namaKelas), -1)); // 'A','B','S', dsb
        $valid = [];

        foreach ($this->slotIds as $s) {
            // Bangun rentang slot yang akan dipakai
            $rentang = range($s, $s + $sks - 1);

            // [V1] Semua slot dalam rentang harus ada di DB
            $semuaAda = collect($rentang)->every(fn($id) => isset($this->slots[$id]));
            if (!$semuaAda) continue;

            // [V2] Tidak boleh melewati slot istirahat
            if (in_array(self::SLOT_ISTIRAHAT, $rentang)) continue;

            // [V3] Tidak boleh ada "lompatan" slot (harus berurutan di DB)
            // Cek bahwa id slot benar-benar berurutan tanpa gap
            $sortedRentang = $rentang;
            sort($sortedRentang);
            $adaGap = false;
            for ($i = 0; $i < count($sortedRentang) - 1; $i++) {
                // Cek apakah ada slot yang terlewat di antara dua slot berurutan
                $idxA = array_search($sortedRentang[$i],   $this->slotIds);
                $idxB = array_search($sortedRentang[$i+1], $this->slotIds);
                if ($idxB - $idxA !== 1) { $adaGap = true; break; }
            }
            if ($adaGap) continue;

            // [V4] Kelas S = slot malam (>= batas malam), lainnya = pagi/siang
            $menitMulai = $this->slots[$s]['menit_mulai'];
            if ($jenisKelas === 'S') {
                if ($menitMulai < self::MENIT_BATAS_MALAM) continue;
            } else {
                if ($menitMulai >= self::MENIT_BATAS_MALAM) continue;
            }

            $valid[] = $s;
        }

        // Fallback: jika tidak ada slot valid (data edge case), pakai semua slot
        return $valid ?: $this->slotIds;
    }

    // ============================================================
    // EVALUASI FITNESS
    // Hard constraints — setiap pelanggaran mengurangi fitness
    // ============================================================
    private function evaluate(Chromosome $chr): float
    {
        $pelanggaran = 0;
        $genes       = $chr->genes;
        $n           = count($genes);

        // Buat map: hariId → list [slotMulai, slotAkhir, dosenId, ruangId, kelasId]
        // untuk pengecekan overlap yang efisien
        $byHari = [];
        foreach ($genes as $g) {
            $byHari[$g->hariId][] = $g;
        }

        foreach ($byHari as $hariGenes) {
            $cnt = count($hariGenes);
            for ($i = 0; $i < $cnt; $i++) {
                $a    = $hariGenes[$i];
                $aEnd = $a->slotMulai + $a->durasi - 1; // slot terakhir A

                for ($j = $i + 1; $j < $cnt; $j++) {
                    $b    = $hariGenes[$j];
                    $bEnd = $b->slotMulai + $b->durasi - 1; // slot terakhir B

                    // Cek overlap waktu: A dan B overlap jika rentang mereka berpotongan
                    // A: [aStart, aEnd]  B: [bStart, bEnd]
                    // Overlap jika: aStart <= bEnd AND bStart <= aEnd
                    $overlap = ($a->slotMulai <= $bEnd) && ($b->slotMulai <= $aEnd);
                    if (!$overlap) continue;

                    // [H1] Dosen sama, waktu overlap → bentrok dosen
                    if ($a->dosenId && $b->dosenId && $a->dosenId === $b->dosenId) {
                        $pelanggaran++;
                    }

                    // [H2] Ruangan sama, waktu overlap → bentrok ruangan
                    if ($a->ruangId && $b->ruangId && $a->ruangId === $b->ruangId) {
                        $pelanggaran++;
                    }

                    // [H3] Kelas sama, waktu overlap → kelas tidak bisa di 2 tempat
                    if ($a->kelasId === $b->kelasId) {
                        $pelanggaran++;
                    }
                }
            }
        }

        // [S1] Soft: dosen maks 8 SKS per hari
        $sksDosenPerHari = [];
        foreach ($genes as $g) {
            if (!$g->dosenId) continue;
            $key = "{$g->hariId}_{$g->dosenId}";
            $sksDosenPerHari[$key] = ($sksDosenPerHari[$key] ?? 0) + $g->durasi;
        }
        foreach ($sksDosenPerHari as $total) {
            if ($total > 8) $pelanggaran++;
        }

        if ($pelanggaran === 0) return 1.0;
        return 1.0 / (1.0 + $pelanggaran);
    }

    // ============================================================
    // SELEKSI — Tournament (k=3)
    // ============================================================
    private function seleksi(array $populasi): Chromosome
    {
        $k        = min(3, count($populasi));
        $kandidat = [];
        for ($i = 0; $i < $k; $i++) {
            $kandidat[] = $populasi[array_rand($populasi)];
        }
        usort($kandidat, fn($a, $b) => $b->fitness <=> $a->fitness);
        return $kandidat[0];
    }

    // ============================================================
    // CROSSOVER — One-Point
    // ============================================================
    private function crossover(Chromosome $p1, Chromosome $p2): array
    {
        if ((mt_rand() / mt_getrandmax()) > $this->crossoverRate) {
            return [$p1->clone(), $p2->clone()];
        }

        $n     = count($p1->genes);
        $point = mt_rand(1, max(1, $n - 1));

        $g1 = array_merge(
            array_map(fn($g) => $g->clone(), array_slice($p1->genes, 0, $point)),
            array_map(fn($g) => $g->clone(), array_slice($p2->genes, $point))
        );
        $g2 = array_merge(
            array_map(fn($g) => $g->clone(), array_slice($p2->genes, 0, $point)),
            array_map(fn($g) => $g->clone(), array_slice($p1->genes, $point))
        );

        return [new Chromosome($g1), new Chromosome($g2)];
    }

    // ============================================================
    // MUTASI — Acak ulang salah satu: hari, slot, atau ruangan
    // ============================================================
    private function mutasi(Chromosome $chr): void
    {
        foreach ($chr->genes as $i => $gene) {
            if ((mt_rand() / mt_getrandmax()) > $this->mutationRate) continue;

            $kelas = collect($this->kelasList)->firstWhere('id_kelas', $gene->kelasId);
            if (!$kelas) continue;

            $sks        = (int) ($kelas->matakuliah->sks ?? 2);
            $validSlots = $this->getValidSlots($sks, $kelas->nama_kelas ?? '');
            $ruangans   = $kelas->matakuliah->ruangans ?? collect();

            // Pilih apa yang dimutasi: 0=hari, 1=slot, 2=ruangan
            $target = mt_rand(0, 2);

            if ($target === 0) {
                $chr->genes[$i]->hariId    = $this->hariList[array_rand($this->hariList)];
            } elseif ($target === 1) {
                $chr->genes[$i]->slotMulai = $validSlots[array_rand($validSlots)];
            } else {
                if ($ruangans->isNotEmpty()) {
                    $r = $ruangans->random();
                    $chr->genes[$i]->ruangId = $r->id_ruang;
                }
            }
        }
    }

    // ============================================================
    // HELPER
    // ============================================================
    private function getBest(array $populasi): Chromosome
    {
        return collect($populasi)->sortByDesc('fitness')->first();
    }

    private function jamKeMenit(string $jam): int
    {
        // Format: "HH:MM:SS" atau "HH:MM"
        $parts = explode(':', $jam);
        return ((int)($parts[0] ?? 0)) * 60 + ((int)($parts[1] ?? 0));
    }


}
