<?php

namespace App\Services\GeneticAlgorithm;

class Gene
{
    public function __construct(
        public int    $kelasId,
        public int    $hariId,      // 1–5
        public int    $slotMulai,   // id_slot
        public int    $ruangId,
        public int    $dosenId,
        public int    $durasi,      // = SKS
        public string $kodeMatkul,
    ) {}

    public function clone(): static
    {
        return new static(
            $this->kelasId,
            $this->hariId,
            $this->slotMulai,
            $this->ruangId,
            $this->dosenId,
            $this->durasi,
            $this->kodeMatkul,
        );
    }

        private function buatGeneAcak(object $kelas): Gene
    {
        $sks        = (int) ($kelas->matakuliah->sks ?? 2);
        $namaKelas  = $kelas->nama_kelas ?? '';
        $validSlots = $this->getValidSlots($sks, $namaKelas);
        $slotMulai  = $validSlots[array_rand($validSlots)];
        $hariId     = $this->hariList[array_rand($this->hariList)];

        $ruangans = $kelas->matakuliah->ruangans ?? collect();

        // Jika tidak ada ruangan, ruangId = 0 (akan disimpan null di DB)
        $ruangan  = $ruangans->isNotEmpty() ? $ruangans->random() : null;

        if ($ruangans->isEmpty()) {
            \Log::warning("[GA] Kelas {$kelas->nama_kelas} ({$kelas->matakuliah->nama_matkul}) tidak punya ruangan.");
        }

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
}


