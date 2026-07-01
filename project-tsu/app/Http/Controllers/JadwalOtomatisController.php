<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use App\Models\Slot_waktu;
use App\Models\JadwalTrial;
use App\Models\PengampuKelas;
use App\Services\GeneticAlgorithm\GeneticScheduler;

class JadwalOtomatisController extends Controller
{
    public function index()
    {
        $tahunAkademikList = TahunAkademik::where('status_aktif', 1)->orderByDesc('id_tahunakademik')->get();
        $tahunAkademikAktif = TahunAkademik::where('status_aktif', 1)->first();

        $user = auth()->user();
        $prodiId = ($user && !$user->isAdmin() && !$user->isDekan()) ? $user->getProdiId() : null;

        foreach ($tahunAkademikList as $ta) {
            $query = \App\Models\Kelas::where('id_tahunakademik', $ta->id_tahunakademik);
            if ($prodiId) {
                $query->where('id_prodi', $prodiId);
            }
            $ta->setAttribute('kelas_count', $query->count());
        }

        return view('jadwal-otomatis.index', compact('tahunAkademikList', 'tahunAkademikAktif'));
    }

    // ── Jalankan GA ──────────────────────────────────────────
    public function proses(Request $request)
    {
        $request->validate([
            'tahun_akademik_id' => 'required|exists:tahun_akademik,id_tahunakademik',
            'populasi'          => 'required|integer|min:10|max:500',
            'generasi'          => 'required|integer|min:10|max:1000',
        ]);

        $tahunAkademikId = $request->tahun_akademik_id;

        $user = auth()->user();
        $prodiId = ($user && !$user->isAdmin() && !$user->isDekan()) ? $user->getProdiId() : null;

        // Ambil data kelas untuk tahun akademik ini
        $kelasQuery = \App\Models\Kelas::with([
                'matakuliah.ruangans',
                'prodi',
                'pengampuMatkul.dosen',
            ])
            ->where('id_tahunakademik', $tahunAkademikId);

        if ($prodiId) {
            $kelasQuery->where('id_prodi', $prodiId);
        }

        $kelas = $kelasQuery->get();

        if ($kelas->isEmpty()) {
            return back()->withErrors(['kelas' => 'Tidak ada data kelas yang terdaftar untuk tahun akademik ini.']);
        }


        $slots = Slot_waktu::orderBy('id_slot')
            ->get()
            ->map(fn($s) => [
                'id'           => $s->id_slot,
                'waktu_mulai'  => $s->waktu_mulai,
                'waktu_selesai'=> $s->waktu_selesai,
            ])
            ->all();

        $hariList = \App\Models\Hari::where('is_active', true)->pluck('id_hari')->toArray();

        $populasiProses  = (int) $request->populasi;
        $generasiProses  = (int) $request->generasi;
        $timeLimitProses = (int) min(1500, max(300, ($populasiProses * $generasiProses) / 400));

        $scheduler = new GeneticScheduler(
            populationSize: $populasiProses,
            maxGenerations: $generasiProses,
            crossoverRate: $request->filled('crossover') ? (float) $request->crossover : 0.8,
            mutationRate: $request->filled('mutation') ? (float) $request->mutation : 0.1,
        );
        $scheduler->setTimeLimit((float) $timeLimitProses);
        
        if ($request->filled('elite')) $scheduler->setEliteK((int) $request->elite);
        if ($request->filled('stagnation')) $scheduler->setStagnationThreshold((int) $request->stagnation);
        if ($request->filled('temp')) $scheduler->setTemp((int) $request->temp);
        if ($request->filled('early_exit')) $scheduler->setEarlyExitFitness((float) $request->early_exit);

        set_time_limit($timeLimitProses + 60);
        ini_set('memory_limit', '512M');
        $hasil = $scheduler->run($kelas, $slots, $hariList);

        // Susun data untuk ditampilkan
        $hariNama   = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat'];
        $slotMap    = collect($slots)->keyBy('id');
        $kelasMap   = $kelas->keyBy('id_kelas');
        $ruangansAll = \App\Models\Ruangan::all()->keyBy('id_ruang');

        $jadwalRows = [];
        foreach ($hasil['genes'] as $gene) {
            $k          = $kelasMap[$gene->kelasId] ?? null;
            if (!$k) continue;

            $slotMulai   = $slotMap[$gene->slotMulai]   ?? null;
            // Slot terakhir = slotMulai + durasi - 1
            $idSlotAkhir = $gene->slotMulai + $gene->durasi - 1;
            $slotAkhir   = $slotMap[$idSlotAkhir] ?? null;

            $ruangan = $ruangansAll[$gene->ruangId] ?? null;

           $jadwalRows[] = [
                'kelas_id'    => $gene->kelasId,
                'nama_kelas'  => $k->nama_kelas,
                'kode_mk'     => $k->matakuliah->kode_matkul ?? '-',
                'nama_mk'     => $k->matakuliah->nama_matkul ?? '-',
                'jenis'       => $k->matakuliah->jenis       ?? 'Teori',
                'sks'         => $gene->durasi,
                'dosen'       => $k->pengampuMatkul->first()?->dosen?->nama_dosen ?? '-',
                'prodi'       => $k->prodi->nama_prodi ?? '-',
                'semester'    => $k->semester,
                'hari'        => $hariNama[$gene->hariId]    ?? '-',
                'hari_id'     => $gene->hariId,
                'slot_id'     => $gene->slotMulai,
                'jam_mulai'   => $slotMulai  ? \Carbon\Carbon::parse($slotMulai['waktu_mulai'])->format('H:i')   : '-',
                'jam_selesai' => $slotAkhir  ? \Carbon\Carbon::parse($slotAkhir['waktu_selesai'])->format('H:i') : '-',
                'ruangan'     => $ruangan?->nama_ruang ?? '-',
                'ruangan_id'  => $gene->ruangId,
                'tipe_ruangan'=> $ruangan?->tipe_ruangan ?? 'Reguler',
                'dosen_id'    => $gene->dosenId,
            ];
        }

        // Urutkan: hari → jam mulai
        usort($jadwalRows, fn($a, $b) =>
            $a['hari_id'] !== $b['hari_id']
                ? $a['hari_id'] <=> $b['hari_id']
                : strcmp($a['jam_mulai'], $b['jam_mulai'])
        );

        $tahunAkademik = TahunAkademik::find($tahunAkademikId);

        return view('jadwal-otomatis.hasil', [
            'jadwalRows'         => $jadwalRows,
            'fitness'            => $hasil['fitness_pct'],
            'generasi'           => $hasil['generasi'],
            'totalKelas'         => $hasil['total_kelas'],
            'populasi'           => $request->populasi,
            'tahunAkademik'      => $tahunAkademik,
            'jadwalJson'         => json_encode($jadwalRows),
            'dosenConflicts'     => $hasil['dosen_conflicts']      ?? 0,
            'ruanganConflicts'   => $hasil['ruangan_conflicts']    ?? 0,
            'kelasConflicts'     => $hasil['kelas_conflicts']      ?? 0,
            'softViolations'     => $hasil['constraint_violations'] ?? 0,
            'problemLog'         => $hasil['problem_log']          ?? [],
            'diagnosa'           => $this->diagnoseConflicts($jadwalRows),
        ]);
    }

    // ── Simpan hasil GA ke database (lanjut ke workspace) ────
    public function simpan(Request $request)
    {
        $request->validate([
            'jadwal_json'       => 'required|string',
            'tahun_akademik_id' => 'required',
        ]);

        $jadwals         = json_decode($request->jadwal_json, true);
        $tahunAkademikId = $request->tahun_akademik_id;

        // Hapus jadwal lama untuk tahun akademik ini dulu
        \App\Models\Jadwal::where('id_tahunakademik', $tahunAkademikId)->delete();

        foreach ($jadwals as $row) {
            \App\Models\Jadwal::create([
                'id_kelas'         => $row['kelas_id'],
                'kode_matkul'      => $row['kode_mk'],
                'id_dosen'         => $row['dosen_id'] ?: null,
                'id_hari'          => $row['hari_id'],
                'id_slot_mulai'    => $row['slot_id'],
                'id_ruang'         => $row['ruangan_id'] ?: null,
                'durasi_sks'       => $row['sks'],
                'id_tahunakademik' => $tahunAkademikId,
                'is_manual'        => 0,
            ]);
        }

        // Redirect ke workspace manual agar bisa diedit
        return redirect()
            ->route('jadwal.manual', ['tahun' => $tahunAkademikId])
            ->with('success', 'Jadwal hasil GA berhasil disimpan! Silakan review di workspace.');
    }

    // ── Simpan hasil GA sebagai Uji Coba (Trial Run) ────
    public function simpanTrial(Request $request)
    {
        $request->validate([
            'tahun_akademik_id' => 'required|exists:tahun_akademik,id_tahunakademik',
            'label'             => 'required|string|max:255',
            'fitness'           => 'required|numeric',
            'generasi'          => 'required|integer',
            'total_kelas'       => 'required|integer',
            'dosen_conflicts'   => 'required|integer',
            'ruangan_conflicts' => 'required|integer',
            'soft_violations'   => 'required|integer',
            'jadwal_json'       => 'required|string',
        ]);

        JadwalTrial::create([
            'id_tahunakademik'  => $request->tahun_akademik_id,
            'label'             => $request->label,
            'fitness'           => $request->fitness,
            'generasi'          => $request->generasi,
            'total_kelas'       => $request->total_kelas,
            'dosen_conflicts'   => $request->dosen_conflicts,
            'ruangan_conflicts' => $request->ruangan_conflicts,
            'kelas_conflicts'   => $request->kelas_conflicts,
            'soft_violations'   => $request->soft_violations,
            'jadwal_json'       => $request->jadwal_json,
        ]);

        return redirect()
            ->route('jadwal.otomatis.compare_trials', ['tahun_akademik_id' => $request->tahun_akademik_id])
            ->with('success', 'Uji coba berhasil disimpan sementara!');
    }

    // ── Halaman Perbandingan Hasil Uji Coba (Trial Run) ────
    public function compareTrials(Request $request)
    {
        $tahunAkademikList = TahunAkademik::where('status_aktif', 1)->orderByDesc('id_tahunakademik')->get();

        $tahunAkademikId = $request->tahun_akademik_id;
        $selectedTahun = null;
        if ($tahunAkademikId) {
            $selectedTahun = TahunAkademik::find($tahunAkademikId);
        }

        if (!$selectedTahun) {
            $selectedTahun = TahunAkademik::where('status_aktif', 1)->first()
                ?? TahunAkademik::orderByDesc('id_tahunakademik')->first();
        }

        $trials = collect();
        if ($selectedTahun) {
            $trials = JadwalTrial::where('id_tahunakademik', $selectedTahun->id_tahunakademik)
                ->orderByDesc('created_at')
                ->get();
        }

        return view('jadwal-otomatis.compare', compact('tahunAkademikList', 'selectedTahun', 'trials'));
    }

    // ── Terapkan hasil Uji Coba (Trial Run) ke Jadwal Utama ────
    public function applyTrial(Request $request, $id)
    {
        $trial = JadwalTrial::findOrFail($id);
        $jadwals = json_decode($trial->jadwal_json, true);
        $tahunAkademikId = $trial->id_tahunakademik;

        // Hapus jadwal lama untuk tahun akademik ini dulu
        \App\Models\Jadwal::where('id_tahunakademik', $tahunAkademikId)->delete();

        foreach ($jadwals as $row) {
            \App\Models\Jadwal::create([
                'id_kelas'         => $row['kelas_id'],
                'kode_matkul'      => $row['kode_mk'],
                'id_dosen'         => $row['dosen_id'] ?: null,
                'id_hari'          => $row['hari_id'],
                'id_slot_mulai'    => $row['slot_id'],
                'id_ruang'         => $row['ruangan_id'] ?: null,
                'durasi_sks'       => $row['sks'],
                'id_tahunakademik' => $tahunAkademikId,
                'is_manual'        => 0,
            ]);
        }

        return redirect()
            ->route('jadwal.manual', ['tahun' => $tahunAkademikId])
            ->with('success', "Jadwal dari uji coba '{$trial->label}' berhasil diterapkan ke workspace!");
    }

    // ── Hapus data Uji Coba (Trial Run) ────
    public function deleteTrial(Request $request, $id)
    {
        $trial = JadwalTrial::findOrFail($id);
        $tahunAkademikId = $trial->id_tahunakademik;
        $trial->delete();

        return redirect()
            ->route('jadwal.otomatis.compare_trials', ['tahun_akademik_id' => $tahunAkademikId])
            ->with('success', 'Uji coba berhasil dihapus!');
    }

    // ── Hapus banyak data Uji Coba (Trial Run) ────
    public function deleteMultipleTrials(Request $request)
    {
        $request->validate([
            'trial_ids' => 'required|array',
            'trial_ids.*' => 'exists:jadwal_trials,id',
        ]);

        $tahunAkademikId = $request->input('tahun_akademik_id');

        JadwalTrial::whereIn('id', $request->trial_ids)->delete();

        return redirect()
            ->route('jadwal.otomatis.compare_trials', ['tahun_akademik_id' => $tahunAkademikId])
            ->with('success', count($request->trial_ids) . ' uji coba berhasil dihapus!');
    }

        public function stream(Request $request)
    {
        $request->validate([
            'tahun_akademik_id' => 'required|exists:tahun_akademik,id_tahunakademik',
            'populasi'          => 'required|integer|min:10|max:500',
            'generasi'          => 'required|integer|min:10|max:1000',
        ]);

        $tahunAkademikId = $request->tahun_akademik_id;

        $user = auth()->user();
        $prodiId = ($user && !$user->isAdmin() && !$user->isDekan()) ? $user->getProdiId() : null;

        $kelasQuery = \App\Models\Kelas::with([
                'matakuliah.ruangans',
                'prodi',
                'pengampuMatkul.dosen',
            ])
            ->where('id_tahunakademik', $tahunAkademikId);

        if ($prodiId) {
            $kelasQuery->where('id_prodi', $prodiId);
        }

        $kelas = $kelasQuery->get();

        $slots = Slot_waktu::orderBy('id_slot')->get()
            ->map(fn($s) => [
                'id'            => $s->id_slot,
                'waktu_mulai'   => $s->waktu_mulai,
                'waktu_selesai' => $s->waktu_selesai,
            ])->all();

        $populasi  = (int) $request->populasi;
        $generasi  = (int) $request->generasi;

        // Hitung timeLimit adaptif: minimal 5 menit, naik sesuai beban
        // Populasi 200 × Gen 500 = ~8 menit; cap di 25 menit
        $timeLimitSek = (int) min(1500, max(300, ($populasi * $generasi) / 400));

        $scheduler = new GeneticScheduler(
            populationSize: $populasi,
            maxGenerations: $generasi,
            crossoverRate:  $request->filled('crossover') ? (float) $request->crossover : 0.8,
            mutationRate:   $request->filled('mutation') ? (float) $request->mutation : 0.15,
        );
        $scheduler->setTimeLimit((float) $timeLimitSek);
        
        if ($request->filled('elite')) $scheduler->setEliteK((int) $request->elite);
        if ($request->filled('stagnation')) $scheduler->setStagnationThreshold((int) $request->stagnation);
        if ($request->filled('temp')) $scheduler->setTemp((int) $request->temp);
        if ($request->filled('early_exit')) $scheduler->setEarlyExitFitness((float) $request->early_exit);

        $ruangansAll = \App\Models\Ruangan::all()->keyBy('id_ruang');

        return response()->stream(function () use ($scheduler, $kelas, $slots, $tahunAkademikId, $timeLimitSek, $ruangansAll) {
            // Set PHP execution limit = timeLimit + 60 detik buffer
            set_time_limit($timeLimitSek + 60);

            // Fungsi kirim SSE event
            $send = function (array $data) {
                echo "data: " . json_encode($data) . "\n\n";
                ob_flush();
                flush();
            };

            // Fungsi build jadwalRows dari hasil genes — dipakai di beberapa titik
            $buildJadwalRows = function (array $genes) use ($kelas, $slots, $ruangansAll) {
                $hariNama  = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat'];
                $slotMap   = collect($slots)->keyBy('id');
                $kelasMap  = $kelas->keyBy('id_kelas');
                $rows = [];
                foreach ($genes as $gene) {
                    $k = $kelasMap[$gene->kelasId] ?? null;
                    if (!$k) { continue; }
                    $slotMulai   = $slotMap[$gene->slotMulai] ?? null;
                    $idSlotAkhir = $gene->slotMulai + $gene->durasi - 1;
                    $slotAkhir   = $slotMap[$idSlotAkhir] ?? null;

                    $ruangan = $ruangansAll[$gene->ruangId] ?? null;

                    $rows[] = [
                        'kelas_id'    => $gene->kelasId,
                        'nama_kelas'  => $k->nama_kelas,
                        'kode_mk'     => $k->matakuliah->kode_matkul ?? '-',
                        'nama_mk'     => $k->matakuliah->nama_matkul ?? '-',
                        'jenis'       => $k->matakuliah->jenis       ?? 'Teori',
                        'sks'         => $gene->durasi,
                        'dosen'       => $k->pengampuMatkul->first()?->dosen?->nama_dosen ?? '-',
                        'prodi'       => $k->prodi->nama_prodi ?? '-',
                        'semester'    => $k->semester,
                        'hari'        => $hariNama[$gene->hariId]    ?? '-',
                        'hari_id'     => $gene->hariId,
                        'slot_id'     => $gene->slotMulai,
                        'jam_mulai'   => $slotMulai ? \Carbon\Carbon::parse($slotMulai['waktu_mulai'])->format('H:i')   : '-',
                        'jam_selesai' => $slotAkhir ? \Carbon\Carbon::parse($slotAkhir['waktu_selesai'])->format('H:i') : '-',
                        'ruangan'     => $ruangan?->nama_ruang ?? '-',
                        'ruangan_id'  => $gene->ruangId,
                        'tipe_ruangan'=> $ruangan?->tipe_ruangan ?? 'Reguler',
                        'dosen_id'    => $gene->dosenId,
                    ];
                }
                usort($rows, fn($a, $b) =>
                    $a['hari_id'] !== $b['hari_id']
                        ? $a['hari_id'] <=> $b['hari_id']
                        : strcmp($a['jam_mulai'], $b['jam_mulai'])
                );
                return $rows;
            };

            // State: simpan hasil terbaik interim jika ada early-exit
            $hasilInterim = null;

            try {
                $hasil = $scheduler->run(
                    $kelas,
                    $slots,
                    [1, 2, 3, 4, 5],
                    function ($progress) use ($send, $buildJadwalRows, &$hasilInterim, $scheduler) {
                        // Kirim progress biasa
                        $send($progress);

                        // [EARLY-EXIT] Jika GA melaporkan sudah optimal di tengah proses,
                        // langsung bangun jadwal dan kirim event done — tidak perlu tunggu selesai.
                        if (!empty($progress['optimal'])) {
                            // Ambil solusi terbaik saat ini dari scheduler
                            $bestNow = $scheduler->getBestResult();
                            if ($bestNow !== null) {
                                $hasilInterim = $bestNow;
                                $rows = $buildJadwalRows($bestNow['genes']);
                                $send([
                                    'done'              => true,
                                    'early_exit'        => true,
                                    'early_reason'      => $progress['optimal_reason'] ?? 'optimal',
                                    'fitness'           => $progress['fitness'],
                                    'generasi'          => $progress['gen'],
                                    'total_kelas'       => count($bestNow['genes']),
                                    'jadwal_rows'       => $rows,
                                    'dosen_conflicts'   => $progress['dosen_konflik']   ?? 0,
                                    'ruangan_conflicts' => $progress['ruangan_konflik'] ?? 0,
                                    'soft_violations'   => $progress['soft_violations'] ?? 0,
                                    'problem_log'       => $bestNow['problem_log']      ?? [],
                                    'diagnosa'          => $this->diagnoseConflicts($rows),
                                ]);
                            }
                        }
                    }
                );

                // Jika sudah kirim via early-exit, skip pengiriman ulang
                if ($hasilInterim !== null) {
                    return;
                }

                // Build dan kirim hasil normal (loop selesai penuh)
                $jadwalRows = $buildJadwalRows($hasil['genes']);

                $send([
                    'done'              => true,
                    'early_exit'        => false,
                    'fitness'           => $hasil['fitness_pct'],
                    'generasi'          => $hasil['generasi'],
                    'total_kelas'       => $hasil['total_kelas'],
                    'jadwal_rows'       => $jadwalRows,
                    'dosen_conflicts'   => $hasil['dosen_conflicts']       ?? 0,
                    'ruangan_conflicts' => $hasil['ruangan_conflicts']     ?? 0,
                    'kelas_konflik'     => $hasil['kelas_konflik']         ?? 0,
                    'soft_violations'   => $hasil['constraint_violations'] ?? 0,
                    'problem_log'       => $hasil['problem_log']           ?? [],
                    'diagnosa'          => $this->diagnoseConflicts($jadwalRows),
                ]);

            } catch (\Throwable $e) {
                // Jika ada exception di tengah proses, kirim event error
                // dengan data terbaik yang sudah dikumpulkan (jika ada).
                \Log::error('GA stream error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);

                if ($hasilInterim !== null) {
                    // Masih ada hasil interim — kirim itu
                    $rows = $buildJadwalRows($hasilInterim['genes']);
                    $send([
                        'done'         => true,
                        'early_exit'   => true,
                        'early_reason' => 'error_fallback',
                        'fitness'      => $hasilInterim['fitness_pct']  ?? 0,
                        'generasi'     => $hasilInterim['generasi']      ?? 0,
                        'total_kelas'  => count($hasilInterim['genes']),
                        'jadwal_rows'  => $rows,
                        'error'        => $e->getMessage(),
                        'diagnosa'     => $this->diagnoseConflicts($rows),
                    ]);
                } else {
                    // Tidak ada hasil sama sekali — kirim error event
                    $send([
                        'done'         => true,
                        'early_exit'   => true,
                        'early_reason' => 'fatal_error',
                        'error'        => $e->getMessage(),
                        'fitness'      => 0,
                        'generasi'     => 0,
                        'total_kelas'  => 0,
                        'jadwal_rows'  => [],
                        'diagnosa'     => [],
                    ]);
                }
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function audit(Request $request)
    {
        $request->validate([
            'tahun_akademik_id' => 'required|exists:tahun_akademik,id_tahunakademik',
        ]);

        $tahunAkademikId = $request->tahun_akademik_id;
        $user = auth()->user();
        $prodiId = ($user && !$user->isAdmin() && !$user->isDekan()) ? $user->getProdiId() : null;

        $kelasQuery = \App\Models\Kelas::with([
                'matakuliah.ruangans',
                'prodi',
                'pengampuMatkul.dosen',
            ])
            ->where('id_tahunakademik', $tahunAkademikId);

        if ($prodiId) {
            $kelasQuery->where('id_prodi', $prodiId);
        }

        $kelas = $kelasQuery->get();

        if ($kelas->isEmpty()) {
            return response()->json([
                'feasible' => false,
                'issues' => [
                    ['type' => 'fatal', 'message' => 'Tidak ada data kelas yang terdaftar untuk tahun akademik ini.']
                ]
            ]);
        }

        $slots = Slot_waktu::orderBy('id_slot')->get()
            ->map(fn($s) => [
                'id'            => $s->id_slot,
                'waktu_mulai'   => $s->waktu_mulai,
                'waktu_selesai' => $s->waktu_selesai,
            ])->all();

        $hariList = [1, 2, 3, 4, 5];

        $issues = $this->runFeasibilityAudit($kelas, $slots, $hariList);

        $hasFatal = collect($issues)->contains('type', 'fatal');

        return response()->json([
            'feasible' => !$hasFatal,
            'issues' => $issues
        ]);
    }

    private function runFeasibilityAudit($kelas, array $slots, array $hariList): array
    {
        $issues = [];

        // 1. Ambil data ruang unik dari kelas yang dijadwalkan
        $ruanganList = [];
        foreach ($kelas as $k) {
            $mk = $k->matakuliah ?? null;
            if (!$mk) continue;
            foreach ($mk->ruangans ?? [] as $r) {
                $ruanganList[$r->id_ruang] = $r;
            }
        }

        // Jika tidak ada ruangan sama sekali di pivot, ambil fallback dari tabel Ruangan langsung
        if (empty($ruanganList)) {
            $ruanganList = \App\Models\Ruangan::all()->keyBy('id_ruang')->all();
        }

        $totalRuangan = count($ruanganList);
        if ($totalRuangan === 0) {
            $issues[] = [
                'type' => 'fatal',
                'message' => 'Tidak ada data ruangan kelas yang tersedia dalam sistem.'
            ];
            return $issues;
        }

        // 2. Hitung total kapasitas slot ruangan dalam seminggu
        $maxSlots = Slot_waktu::max('id_slot') ?: 14;
        $slotsPerWeek = count($hariList) * $maxSlots - 1; // jumat slot 6 dikecualikan
        $totalAvailableCapacity = $slotsPerWeek * $totalRuangan;

        // 3. Hitung total kebutuhan SKS dan detail dosen/praktikum
        $totalSks = 0;
        $totalLabSks = 0;
        $dosenSks = [];
        $dosenNames = [];
        $kelasDosenKosong = [];
        $kelasTanpaRuang = [];

        foreach ($kelas as $k) {
            $mk = $k->matakuliah ?? null;
            if (!$mk) continue;

            $sks = (int) ($mk->sks ?? 0);
            $totalSks += $sks;

            if (strtolower(trim($mk->jenis ?? '')) === 'praktikum') {
                $totalLabSks += $sks;
            }

            // Dosen — ambil dari relasi pengampuMatkul (kolom id_dosen di tabel kelas sudah dihapus)
            $pengampu = $k->pengampuMatkul->first();
            $dId = $pengampu?->id_dosen ?? 0;
            $dName = $pengampu?->dosen?->nama_dosen ?? null;
            if (!$dId) {
                // FIX: properti yang benar adalah nama_matkul (bukan nama_matakuliah)
                $kelasDosenKosong[] = $k->nama_kelas . ' (' . ($mk->nama_matkul ?? '-') . ')';
            } else {
                $dosenSks[$dId] = ($dosenSks[$dId] ?? 0) + $sks;
                $dosenNames[$dId] = $dName ?: 'Dosen ID: ' . $dId;
            }

            // Cek ruangan options
            $ruangOptions = $mk->ruangans ? $mk->ruangans->pluck('id_ruang')->toArray() : [];
            if (empty($ruangOptions)) {
                $kelasTanpaRuang[] = $k->nama_kelas . ' (' . ($mk->nama_matkul ?? '-') . ')';
            }
        }

        // A. Total kapasitas ruangan vs total SKS (HC2)
        if ($totalSks > $totalAvailableCapacity) {
            $issues[] = [
                'type' => 'fatal',
                'message' => "Kapasitas ruangan tidak mencukupi! Total kebutuhan adalah $totalSks SKS, namun total slot semua ruangan hanya $totalAvailableCapacity SKS per minggu."
            ];
        }

        // B. Kapasitas Lab vs SKS Praktikum (HC13)
        $totalLabRuangan = collect($ruanganList)->filter(fn($r) => strtolower(trim($r->tipe_ruangan ?? '')) === 'lab')->count();
        $totalLabAvailableCapacity = $slotsPerWeek * $totalLabRuangan;
        if ($totalLabSks > $totalLabAvailableCapacity) {
            $issues[] = [
                'type' => 'fatal',
                'message' => "Kapasitas Lab tidak mencukupi! Total SKS Praktikum adalah $totalLabSks SKS, namun total slot Lab yang tersedia hanya $totalLabAvailableCapacity SKS per minggu (Jumlah Lab: $totalLabRuangan)."
            ];
        }

        // C. Dosen Overload (HC14)
        foreach ($dosenSks as $dId => $sks) {
            $dName = $dosenNames[$dId];
            if ($sks > 40) {
                $issues[] = [
                    'type' => 'fatal',
                    'message' => "Dosen '$dName' ditugaskan mengajar $sks SKS dalam seminggu. Secara matematis mustahil dijadwalkan karena batas maksimal mengajar dosen adalah 40 SKS per minggu (maks 8 SKS/hari)."
                ];
            } elseif ($sks > 30) {
                $issues[] = [
                    'type' => 'warning',
                    'message' => "Dosen '$dName' memiliki beban mengajar sangat tinggi ($sks SKS). Algoritma mungkin akan kesulitan mencarikan slot kosong bebas bentrok."
                ];
            }
        }

        // D. Kelas Dosen Kosong (HC6)
        if (!empty($kelasDosenKosong)) {
            $count = count($kelasDosenKosong);
            $listHtml = '<div class="max-h-40 overflow-y-auto mt-2 p-2 bg-white/50 rounded border border-rose-100"><ul class="list-disc pl-4 space-y-1 font-normal text-xs">';
            foreach ($kelasDosenKosong as $item) {
                $listHtml .= "<li>$item</li>";
            }
            $listHtml .= '</ul></div>';

            $issues[] = [
                'type' => 'fatal',
                'message' => "Terdapat $count kelas yang belum memiliki dosen pengampu. Anda harus memplot dosen pengampu terlebih dahulu di menu Manajemen Pengampu:<br>" . $listHtml
            ];
        }

        // E. Kelas Tanpa Ruang Valid (HC10)
        if (!empty($kelasTanpaRuang)) {
            $count = count($kelasTanpaRuang);
            $listHtml = '<div class="max-h-40 overflow-y-auto mt-2 p-2 bg-white/50 rounded border border-rose-100"><ul class="list-disc pl-4 space-y-1 font-normal text-xs">';
            foreach ($kelasTanpaRuang as $item) {
                $listHtml .= "<li>$item</li>";
            }
            $listHtml .= '</ul></div>';

            $issues[] = [
                'type' => 'fatal',
                'message' => "Terdapat $count kelas yang matakuliahnya belum dicentang/direlasikan dengan ruangan manapun. Silakan atur relasi ruangan di menu Manajemen Mata Kuliah terlebih dahulu:<br>" . $listHtml
            ];
        }

        return $issues;
    }

    private function diagnoseConflicts(array $jadwalRows): array
    {
        $diagnoses = [];
        $n = count($jadwalRows);

        // 1. Dosen Bentrok & Ruangan Bentrok
        $dosenBentrokPairs = [];
        $ruangBentrokPairs = [];

        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $rowA = $jadwalRows[$i];
                $rowB = $jadwalRows[$j];

                // Cek overlap hari
                if ($rowA['hari_id'] !== $rowB['hari_id']) {
                    continue;
                }

                // Cek overlap waktu
                $startA = (int) $rowA['slot_id'];
                $endA = $startA + (int) $rowA['sks'] - 1;
                $startB = (int) $rowB['slot_id'];
                $endB = $startB + (int) $rowB['sks'] - 1;

                if ($startA <= $endB && $startB <= $endA) {
                    // Overlap!
                    // A. Dosen Bentrok
                    if ($rowA['dosen_id'] > 0 && $rowA['dosen_id'] === $rowB['dosen_id']) {
                        $pairKey = min($rowA['kelas_id'], $rowB['kelas_id']) . '-' . max($rowA['kelas_id'], $rowB['kelas_id']);
                        if (!isset($dosenBentrokPairs[$pairKey])) {
                            $dosenBentrokPairs[$pairKey] = true;
                            $diagnoses[] = "Dosen '{$rowA['dosen']}' bentrok mengajar pada hari {$rowA['hari']} di jam {$rowA['jam_mulai']}-{$rowA['jam_selesai']} (Kelas {$rowA['nama_kelas']}, {$rowA['nama_mk']}) dan jam {$rowB['jam_mulai']}-{$rowB['jam_selesai']} (Kelas {$rowB['nama_kelas']}, {$rowB['nama_mk']}).";
                        }
                    }

                    // B. Ruangan Bentrok
                    if ($rowA['ruangan_id'] > 0 && $rowA['ruangan_id'] === $rowB['ruangan_id']) {
                        $pairKey = min($rowA['kelas_id'], $rowB['kelas_id']) . '-' . max($rowA['kelas_id'], $rowB['kelas_id']);
                        if (!isset($ruangBentrokPairs[$pairKey])) {
                            $ruangBentrokPairs[$pairKey] = true;
                            $diagnoses[] = "Ruangan '{$rowA['ruangan']}' bentrok digunakan pada hari {$rowA['hari']} di jam {$rowA['jam_mulai']}-{$rowA['jam_selesai']} (Kelas {$rowA['nama_kelas']}, {$rowA['nama_mk']}) dan jam {$rowB['jam_mulai']}-{$rowB['jam_selesai']} (Kelas {$rowB['nama_kelas']}, {$rowB['nama_mk']}).";
                        }
                    }
                }
            }
        }

        // 2. Dosen Overload Harian (> 8 SKS/hari)
        $dosenDaySks = []; // [dosen_id][hari] = SKS
        $dosenNames = [];
        $hariNames = [];
        foreach ($jadwalRows as $row) {
            $did = $row['dosen_id'];
            $hariId = $row['hari_id'];
            if ($did > 0) {
                $dosenDaySks[$did][$hariId] = ($dosenDaySks[$did][$hariId] ?? 0) + $row['sks'];
                $dosenNames[$did] = $row['dosen'];
                $hariNames[$hariId] = $row['hari'];
            }
        }
        foreach ($dosenDaySks as $did => $hariSks) {
            foreach ($hariSks as $hariId => $sks) {
                if ($sks > 8) {
                    $dName = $dosenNames[$did];
                    $hName = $hariNames[$hariId] ?? "Hari $hariId";
                    $diagnoses[] = "Dosen '{$dName}' memiliki beban mengajar melebihi batas pada hari {$hName} yaitu {$sks} SKS (maksimal 8 SKS per hari).";
                }
            }
        }

        // 3. Sholat Jumat Bentrok (hari 5, slot 6)
        foreach ($jadwalRows as $row) {
            if ($row['hari_id'] === 5) {
                $start = (int) $row['slot_id'];
                $end = $start + (int) $row['sks'] - 1;
                if ($start <= 6 && $end >= 6) {
                    $diagnoses[] = "Kelas '{$row['nama_kelas']}' ({$row['nama_mk']}) melanggar jam Sholat Jumat (menempati slot jam 12:00 pada hari Jumat).";
                }
            }
        }

        // 4. Kelas Sore/Reguler Salah Slot
        foreach ($jadwalRows as $row) {
            $namaNorm = strtolower(trim($row['nama_kelas']));
            $isKelasS = (bool) preg_match('/-\d*s[i\d]*$/i', $namaNorm)
                || (bool) preg_match('/\bsore\b|\bmalam\b/', $namaNorm);

            $start = (int) $row['slot_id'];
            $end = $start + (int) $row['sks'] - 1;

            if ($isKelasS) {
                if ($start < 12) {
                    $diagnoses[] = "Kelas Sore/Malam '{$row['nama_kelas']}' ({$row['nama_mk']}) dijadwalkan terlalu pagi di jam {$row['jam_mulai']} (seharusnya mulai slot sore/malam, minimal slot 12).";
                }
            } else {
                if ($end >= 12) {
                    $diagnoses[] = "Kelas Reguler '{$row['nama_kelas']}' ({$row['nama_mk']}) menjangkau jam sore/malam di jam {$row['jam_selesai']} (seharusnya selesai sebelum slot 12).";
                }
            }
        }

        // 5. Tipe Ruangan Mismatch
        foreach ($jadwalRows as $row) {
            $jenis = strtolower(trim($row['jenis']));
            $tipe = strtolower(trim($row['tipe_ruangan'] ?? ''));
            $ruanganName = $row['ruangan'];

            if ($jenis === 'praktikum') {
                if (!in_array($tipe, ['lab', 'hybrid'], true)) {
                    $diagnoses[] = "Mata kuliah Praktikum '{$row['nama_mk']}' (Kelas {$row['nama_kelas']}) dijadwalkan di ruangan tipe '" . ($row['tipe_ruangan'] ?? 'Reguler') . "' yaitu '{$ruanganName}' (seharusnya di Lab atau Hybrid).";
                }
            } else if ($jenis === 'teori') {
                if ($tipe === 'lab') {
                    $diagnoses[] = "Mata kuliah Teori '{$row['nama_mk']}' (Kelas {$row['nama_kelas']}) dijadwalkan di ruangan Lab '{$ruanganName}' (seharusnya di ruangan Reguler atau Hybrid).";
                }
            }
        }

        return $diagnoses;
    }

}