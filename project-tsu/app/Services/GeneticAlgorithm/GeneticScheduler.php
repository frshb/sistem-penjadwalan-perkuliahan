<?php

namespace App\Services\GeneticAlgorithm;

use Illuminate\Support\Collection;

class GeneticScheduler
{
    private const SLOT_ISTIRAHAT    = 6;
    private const MENIT_BATAS_MALAM = 990;
    private const MAX_KELAS_PER_SLOT = 8;
    private const MAX_MK_PER_PRODI  = 4;

    private array $slots;
    private array $slotIds;
    private array $hariList;
    private array $kelasList;
    private array $kelasMap = [];
    private array $slotIndex = [];

    // ── Lookup grup C9 yang dibangun sekali di awal ──────────────
    // grupC9[key] = [ kelasId, kelasId, ... ] sudah diurutkan nama kelas
    private array $grupC9 = [];
    // kelasKeyC9[kelasId] = key C9-nya (null jika tidak punya saudara)
    private array $kelasKeyC9 = [];

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
        // Bangun slot map
        $this->slots = [];
        foreach ($slots as $s) {
            $menit = $this->jamKeMenit($s['waktu_mulai']);
            $this->slots[$s['id']] = [
                'id'            => $s['id'],
                'waktu_mulai'   => $s['waktu_mulai'],
                'waktu_selesai' => $s['waktu_selesai'],
                'menit_mulai'   => $menit,
            ];
        }

        $this->slotIds   = array_keys($this->slots);
        sort($this->slotIds);
        // Di run(), setelah sort slotIds:
        sort($this->slotIds);
        $this->slotIndex = array_flip($this->slotIds); // O(1) lookup
        $this->hariList  = $hariList;
        $this->kelasList = $kelas->values()->all();

        foreach ($this->kelasList as $k) {
            $this->kelasMap[$k->id_kelas] = $k;
        }

        // Bangun lookup C9 sekali — dipakai di init & mutasi
        $this->bangunLookupC9();

        $populasi = $this->initPopulasi();
        foreach ($populasi as $chr) {
            $chr->fitness = $this->evaluate($chr);
        }

        $best  = $this->getBest($populasi);
        $genke = 0;

        for ($gen = 0; $gen < $this->maxGenerasi; $gen++) {
            $genke  = $gen + 1;
            $newPop = [];
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

            if ($best->fitness >= 1.0) break;
        }

        return [
            'genes'       => $best->genes,
            'fitness'     => $best->fitness,
            'fitness_pct' => round($best->fitness * 100, 2),
            'total_kelas' => count($best->genes),
            'generasi'    => $genke ?? $this->maxGenerasi,
        ];
    }

    // ============================================================
    // BANGUN LOOKUP C9 — dijalankan sekali sebelum init populasi
    // Mengelompokkan kelas yang punya MK+dosen+prodi+semester sama,
    // lalu mengurutkannya A→B→C agar posisi berurutan sudah diketahui.
    // ============================================================
    private function bangunLookupC9(): void
    {
        $this->grupC9    = [];
        $this->kelasKeyC9 = [];

        foreach ($this->kelasList as $k) {
            $key = $this->buatKeyC9DariKelas($k);
            if (!$key) continue;
            $this->grupC9[$key][] = $k->id_kelas;
        }

        // Hapus grup yang hanya punya 1 anggota — tidak perlu diatur
        foreach ($this->grupC9 as $key => $ids) {
            if (count($ids) <= 1) {
                unset($this->grupC9[$key]);
                continue;
            }
            // Urutkan nama kelas ascending (A < B < C < S1 < S2)
            usort($this->grupC9[$key], function ($idA, $idB) {
                $namaA = $this->kelasMap[$idA]->nama_kelas ?? '';
                $namaB = $this->kelasMap[$idB]->nama_kelas ?? '';
                return strcmp($namaA, $namaB);
            });
            // Catat key untuk setiap kelasId
            foreach ($this->grupC9[$key] as $kelasId) {
                $this->kelasKeyC9[$kelasId] = $key;
            }
        }
    }

    private function buatKeyC9DariKelas(object $kelas): ?string
    {
        $namaMK   = $kelas->matakuliah->nama_matkul ?? null;
        $prodi    = $kelas->prodi->nama_prodi       ?? null;
        $semester = (string) ($kelas->semester      ?? '');
        $dosenId  = (string) ($kelas->id_dosen      ?? '');

        if (!$namaMK || !$prodi) return null;
        return "{$namaMK}|{$dosenId}|{$prodi}|{$semester}";
    }

    // ============================================================
    // INISIALISASI POPULASI — Cerdas untuk C9
    //
    // Strategi:
    // - Kelas yang punya saudara C9 (paralel) dijadwalkan sebagai
    //   satu blok berurutan: pilih hari 1x, lalu slot A = slot awal,
    //   slot B = slotAkhir(A), slot C = slotAkhir(B), dst.
    // - Kelas tanpa saudara tetap diacak normal.
    // - Setiap individu dalam populasi mendapat pilihan hari & slot
    //   awal yang berbeda agar populasi tetap beragam.
    // ============================================================
    private function initPopulasi(): array
    {
        $pop = [];
        for ($i = 0; $i < $this->populasiSize; $i++) {
            $pop[] = $this->buatKromosomCerdas();
        }
        return $pop;
    }

    private function buatKromosomCerdas(): Chromosome
    {
        // geneMap[kelasId] = Gene — diisi bertahap
        $geneMap = [];

        // --- Langkah 1: Jadwalkan semua grup C9 sebagai blok berurutan ---
        foreach ($this->grupC9 as $key => $kelasIds) {
            $sksTotal = 0;
            foreach ($kelasIds as $kid) {
                $k = $this->kelasMap[$kid];
                $sksTotal += (int) ($k->matakuliah->sks ?? 2);
            }

            // Cari slot awal yang cukup menampung semua kelas berurutan
            // tanpa melewati slot istirahat
            $kelas0     = $this->kelasMap[$kelasIds[0]];
            $namaKelas0 = $kelas0->nama_kelas ?? '';
            $slotAwalPool = $this->getSlotAwalGrup($kelasIds, $namaKelas0);

            if (empty($slotAwalPool)) {
                // Fallback: jadwalkan acak masing-masing
                foreach ($kelasIds as $kid) {
                    $geneMap[$kid] = $this->buatGeneAcak($this->kelasMap[$kid]);
                }
                continue;
            }

            $hariId    = $this->hariList[array_rand($this->hariList)];
            $slotAwal  = $slotAwalPool[array_rand($slotAwalPool)];

            $slotKursor = $slotAwal;
            foreach ($kelasIds as $kid) {
                $k    = $this->kelasMap[$kid];
                $sks  = (int) ($k->matakuliah->sks ?? 2);

                $ruangans = $k->matakuliah->ruangans ?? collect();
                $ruangan  = $ruangans->isNotEmpty() ? $ruangans->random() : null;

                $geneMap[$kid] = new Gene(
                    kelasId:    $kid,
                    hariId:     $hariId,
                    slotMulai:  $slotKursor,
                    ruangId:    $ruangan?->id_ruang ?? 0,
                    dosenId:    $k->id_dosen ?? 0,
                    durasi:     $sks,
                    kodeMatkul: $k->kode_matkul ?? '',
                );

                $slotKursor += $sks; // langsung setelah kelas sebelumnya
            }
        }

        // --- Langkah 2: Jadwalkan kelas tanpa saudara C9 secara acak ---
        foreach ($this->kelasList as $kelas) {
            $kid = $kelas->id_kelas;
            if (isset($geneMap[$kid])) continue; // sudah diisi di langkah 1
            $geneMap[$kid] = $this->buatGeneAcak($kelas);
        }

        // Susun genes sesuai urutan kelasList agar index konsisten
        $genes = [];
        foreach ($this->kelasList as $kelas) {
            $genes[] = $geneMap[$kelas->id_kelas];
        }

        return new Chromosome($genes);
    }

    // Cari slot awal yang valid untuk seluruh blok C9
    // Valid = semua slot dari slotAwal hingga slotAwal+sksTotal-1 ada,
    //         tidak melewati istirahat, dan sesuai jenis kelas (pagi/malam)
    private function getSlotAwalGrup(array $kelasIds, string $namaKelas): array
    {
        // Hitung total SKS blok
        $sksTotal = 0;
        foreach ($kelasIds as $kid) {
            $sksTotal += (int) ($this->kelasMap[$kid]->matakuliah->sks ?? 2);
        }

        $jenisKelas = strtoupper(substr(trim($namaKelas), -1));
        $valid = [];

        foreach ($this->slotIds as $s) {
            $rentang = range($s, $s + $sksTotal - 1);

            // Semua slot harus ada di DB
            $semuaAda = true;
            foreach ($rentang as $sid) {
                if (!isset($this->slots[$sid])) { $semuaAda = false; break; }
            }
            if (!$semuaAda) continue;

            // Tidak melewati slot istirahat
            if (in_array(self::SLOT_ISTIRAHAT, $rentang)) continue;

            // Tidak ada gap antar slot
            $adaGap = false;
            for ($i = 0; $i < count($rentang) - 1; $i++) {
                $idxA = $this->slotIndex[$rentang[$i]]     ?? -1;
                $idxB = $this->slotIndex[$rentang[$i + 1]] ?? -1;
                if ($idxB - $idxA !== 1) { $adaGap = true; break; }
            }
            if ($adaGap) continue;

            // Sesuai jenis kelas (C7)
            $menitMulai = $this->slots[$s]['menit_mulai'];
            if ($jenisKelas === 'S') {
                if ($menitMulai < self::MENIT_BATAS_MALAM) continue;
            } else {
                if ($menitMulai >= self::MENIT_BATAS_MALAM) continue;
            }

            $valid[] = $s;
        }

        return $valid;
    }

    private function buatGeneAcak(object $kelas): Gene
    {
        $sks        = (int) ($kelas->matakuliah->sks ?? 2);
        $validSlots = $this->getValidSlots($sks, $kelas->nama_kelas ?? '');

        $slotMulai = $validSlots[array_rand($validSlots)];
        $hariId    = $this->hariList[array_rand($this->hariList)];

        $ruangans = $kelas->matakuliah->ruangans ?? collect();
        $ruangan  = $ruangans->isNotEmpty() ? $ruangans->random() : null;

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
    // SLOT VALID (untuk kelas tunggal)
    // ============================================================
    private function getValidSlots(int $sks, string $namaKelas): array
    {
        $jenisKelas = strtoupper(substr(trim($namaKelas), -1));
        $valid = [];

        foreach ($this->slotIds as $s) {
            $rentang = range($s, $s + $sks - 1);

            $semuaAda = true;
            foreach ($rentang as $sid) {
                if (!isset($this->slots[$sid])) { $semuaAda = false; break; }
            }
            if (!$semuaAda) continue;

            if (in_array(self::SLOT_ISTIRAHAT, $rentang)) continue;

            $adaGap = false;
            for ($i = 0; $i < count($rentang) - 1; $i++) {
                $idxA = $this->slotIndex[$rentang[$i]]     ?? -1;
                $idxB = $this->slotIndex[$rentang[$i + 1]] ?? -1;
                if ($idxB - $idxA !== 1) { $adaGap = true; break; }
            }
            if ($adaGap) continue;

            $menitMulai = $this->slots[$s]['menit_mulai'];
            if ($jenisKelas === 'S') {
                if ($menitMulai < self::MENIT_BATAS_MALAM) continue;
            } else {
                if ($menitMulai >= self::MENIT_BATAS_MALAM) continue;
            }

            $valid[] = $s;
        }

        return $valid ?: $this->slotIds;
    }

    // ============================================================
    // EVALUASI FITNESS — Bobot pelanggaran diperbesar untuk C9
    // ============================================================
    private function evaluate(Chromosome $chr): float
    {
        $pelanggaran = 0;
        $genes       = $chr->genes;

        $byHari = [];
        foreach ($genes as $g) {
            $byHari[$g->hariId][] = $g;
        }

        $kelasInfo = [];
        foreach ($genes as $g) {
            if (isset($kelasInfo[$g->kelasId])) continue;
            $k = $this->kelasMap[$g->kelasId] ?? null;
            if (!$k) continue;
            $kelasInfo[$g->kelasId] = [
                'prodi'    => $k->prodi->nama_prodi       ?? '-',
                'semester' => (string) ($k->semester      ?? ''),
                'namaMK'   => $k->matakuliah->nama_matkul ?? '-',
                'dosenId'  => (string) ($g->dosenId       ?? ''),
            ];
        }

        // H1–H5: cek overlap per hari
        foreach ($byHari as $hariId => $hariGenes) {
            $cnt = count($hariGenes);

            // H5: kelas per slot
            $slotCount = [];
            foreach ($hariGenes as $g) {
                for ($s = $g->slotMulai; $s < $g->slotMulai + $g->durasi; $s++) {
                    $slotCount[$s] = ($slotCount[$s] ?? 0) + 1;
                }
            }
            foreach ($slotCount as $jmlKelas) {
                if ($jmlKelas > self::MAX_KELAS_PER_SLOT) {
                    $pelanggaran += ($jmlKelas - self::MAX_KELAS_PER_SLOT);
                }
            }

            // Pairwise
            for ($i = 0; $i < $cnt; $i++) {
                $a     = $hariGenes[$i];
                $aEnd  = $a->slotMulai + $a->durasi - 1;
                $infoA = $kelasInfo[$a->kelasId] ?? null;

                for ($j = $i + 1; $j < $cnt; $j++) {
                    $b     = $hariGenes[$j];
                    $bEnd  = $b->slotMulai + $b->durasi - 1;
                    $infoB = $kelasInfo[$b->kelasId] ?? null;

                    if (!($a->slotMulai <= $bEnd && $b->slotMulai <= $aEnd)) continue;

                    // H1: ruangan bentrok
                    if ($a->ruangId && $b->ruangId && $a->ruangId === $b->ruangId) {
                        $pelanggaran++;
                    }
                    // H2: dosen bentrok
                    if ($a->dosenId && $b->dosenId && $a->dosenId === $b->dosenId) {
                        $pelanggaran++;
                    }
                    // H3: kelas sama overlap
                    if ($a->kelasId === $b->kelasId) {
                        $pelanggaran++;
                    }
                    // H4: MK sama overlap — DIHAPUS untuk kelas paralel
                    // Kelas paralel (MK+dosen sama) BOLEH di slot berbeda,
                    // yang dilarang hanya dosen mengajar BERSAMAAN (sudah di H2)
                }
            }

            // H6: prodi maks MK per hari
            $mkPerProdi = [];
            foreach ($hariGenes as $g) {
                $info = $kelasInfo[$g->kelasId] ?? null;
                if (!$info) continue;
                $prodi = $info['prodi'];
                $mk    = $info['namaMK'];
                if (!isset($mkPerProdi[$prodi])) $mkPerProdi[$prodi] = [];
                if (!in_array($mk, $mkPerProdi[$prodi])) {
                    $mkPerProdi[$prodi][] = $mk;
                }
            }
            foreach ($mkPerProdi as $mkList) {
                if (count($mkList) > self::MAX_MK_PER_PRODI) {
                    $pelanggaran += count($mkList) - self::MAX_MK_PER_PRODI;
                }
            }
        }

        // S1: dosen maks SKS per hari
        $sksDosenPerHari = [];
        foreach ($genes as $g) {
            if (!$g->dosenId) continue;
            $key = "{$g->hariId}_{$g->dosenId}";
            $sksDosenPerHari[$key] = ($sksDosenPerHari[$key] ?? 0) + $g->durasi;
        }
        foreach ($sksDosenPerHari as $total) {
            if ($total > 8) $pelanggaran++;
        }

        // H7: C9 — hari sama + berurutan langsung
        // Bobot x3 karena ini constraint paling penting yang ingin diprioritaskan
        foreach ($this->grupC9 as $key => $kelasIds) {
            // Ambil gene untuk setiap kelasId di grup ini
            $geneGrup = [];
            foreach ($genes as $g) {
                if (($this->kelasKeyC9[$g->kelasId] ?? null) === $key) {
                    $geneGrup[$g->kelasId] = $g;
                }
            }
            if (count($geneGrup) <= 1) continue;

            // (a) Semua harus hari yang sama
            $hariIds = array_unique(array_map(fn($g) => $g->hariId, $geneGrup));
            if (count($hariIds) > 1) {
                // Bobot x3: hari beda adalah pelanggaran berat
                $pelanggaran += (count($hariIds) - 1) * 3;
                continue;
            }

            // (b) Slot harus berurutan langsung, dalam urutan A→B→C
            $geneUrut = [];
            foreach ($kelasIds as $kid) {
                if (isset($geneGrup[$kid])) $geneUrut[] = $geneGrup[$kid];
            }

            for ($i = 0; $i < count($geneUrut) - 1; $i++) {
                $curr          = $geneUrut[$i];
                $next          = $geneUrut[$i + 1];
                $slotAkhirCurr = $curr->slotMulai + $curr->durasi;

                if ($next->slotMulai !== $slotAkhirCurr) {
                    // Bobot x3: jeda antar kelas paralel = pelanggaran berat
                    $pelanggaran += 3;
                }
            }
        }

        if ($pelanggaran === 0) return 1.0;
        return 1.0 / (1.0 + $pelanggaran);
    }

    // ============================================================
    // MUTASI — Terarah untuk C9
    //
    // Jika gen yang dimutasi adalah bagian dari grup C9:
    //   - Mutasi hari → selaraskan seluruh grup ke hari yang sama
    //   - Mutasi slot → pindahkan seluruh blok sekaligus (tetap berurutan)
    //   - Mutasi ruangan → hanya gene ini saja (tidak mempengaruhi grup)
    // ============================================================
    private function mutasi(Chromosome $chr): void
    {
        // Index gene berdasarkan kelasId untuk akses O(1)
        $geneIndex = [];
        foreach ($chr->genes as $i => $g) {
            $geneIndex[$g->kelasId] = $i;
        }

        $sudahDimutasiGrup = []; // Cegah 1 grup dimutasi lebih dari sekali per pass

        foreach ($chr->genes as $i => $gene) {
            if ((mt_rand() / mt_getrandmax()) > $this->mutationRate) continue;

            $kelas = $this->kelasMap[$gene->kelasId] ?? null;
            if (!$kelas) continue;

            $keyC9    = $this->kelasKeyC9[$gene->kelasId] ?? null;
            $ruangans = $kelas->matakuliah->ruangans ?? collect();
            $target   = mt_rand(0, 2);

            // ── Kelas yang termasuk grup C9 ──────────────────────
            if ($keyC9 && isset($this->grupC9[$keyC9])) {
                if (isset($sudahDimutasiGrup[$keyC9])) continue;
                $sudahDimutasiGrup[$keyC9] = true;

                $kelasIds = $this->grupC9[$keyC9];

                if ($target === 0) {
                    // Mutasi hari: pindahkan SELURUH grup ke hari yang sama (baru)
                    $hariIdBaru = $this->hariList[array_rand($this->hariList)];
                    foreach ($kelasIds as $kid) {
                        if (isset($geneIndex[$kid])) {
                            $chr->genes[$geneIndex[$kid]]->hariId = $hariIdBaru;
                        }
                    }

                } elseif ($target === 1) {
                    // Mutasi slot: pindahkan SELURUH blok ke slot awal baru
                    // Slot awal harus valid untuk total SKS blok
                    $namaKelas0  = $this->kelasMap[$kelasIds[0]]->nama_kelas ?? '';
                    $slotAwalPool = $this->getSlotAwalGrup($kelasIds, $namaKelas0);

                    if (!empty($slotAwalPool)) {
                        $slotBaru   = $slotAwalPool[array_rand($slotAwalPool)];
                        $slotKursor = $slotBaru;
                        foreach ($kelasIds as $kid) {
                            if (!isset($geneIndex[$kid])) continue;
                            $idx = $geneIndex[$kid];
                            $chr->genes[$idx]->slotMulai = $slotKursor;
                            $slotKursor += $chr->genes[$idx]->durasi;
                        }
                    }

                } else {
                    // Mutasi ruangan: hanya gene ini saja
                    if ($ruangans->isNotEmpty()) {
                        $chr->genes[$i]->ruangId = $ruangans->random()->id_ruang;
                    }
                }

            // ── Kelas tanpa saudara C9 — mutasi normal ───────────
            } else {
                $sks        = (int) ($kelas->matakuliah->sks ?? 2);
                $validSlots = $this->getValidSlots($sks, $kelas->nama_kelas ?? '');

                if ($target === 0) {
                    $chr->genes[$i]->hariId = $this->hariList[array_rand($this->hariList)];
                } elseif ($target === 1) {
                    $chr->genes[$i]->slotMulai = $validSlots[array_rand($validSlots)];
                } else {
                    if ($ruangans->isNotEmpty()) {
                        $chr->genes[$i]->ruangId = $ruangans->random()->id_ruang;
                    }
                }
            }
        }
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
    // HITUNG PELANGGARAN (untuk log progress)
    // ============================================================
    private function hitungPelanggaran(Chromosome $chr): int
    {
        $p      = 0;
        $genes  = $chr->genes;
        $byHari = [];
        foreach ($genes as $g) {
            $byHari[$g->hariId][] = $g;
        }

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

        // Tambahkan pelanggaran C9
        foreach ($this->grupC9 as $key => $kelasIds) {
            $geneGrup = [];
            foreach ($genes as $g) {
                if (in_array($g->kelasId, $kelasIds)) {
                    $geneGrup[$g->kelasId] = $g;
                }
            }
            if (count($geneGrup) <= 1) continue;

            $hariIds = array_unique(array_map(fn($g) => $g->hariId, $geneGrup));
            if (count($hariIds) > 1) { $p += count($hariIds) - 1; continue; }

            $geneUrut = [];
            foreach ($kelasIds as $kid) {
                if (isset($geneGrup[$kid])) $geneUrut[] = $geneGrup[$kid];
            }
            for ($i = 0; $i < count($geneUrut) - 1; $i++) {
                $slotAkhir = $geneUrut[$i]->slotMulai + $geneUrut[$i]->durasi;
                if ($geneUrut[$i + 1]->slotMulai !== $slotAkhir) $p++;
            }
        }

        return $p;
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
        $parts = explode(':', $jam);
        return ((int)($parts[0] ?? 0)) * 60 + ((int)($parts[1] ?? 0));
    }
}
