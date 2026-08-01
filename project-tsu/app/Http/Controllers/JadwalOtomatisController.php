<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use App\Models\Slot_waktu;
use App\Models\JadwalTrial;
use App\Models\PengampuKelas;
use App\Services\GeneticAlgorithm\GeneticScheduler;
use App\Helpers\ProdiFilter;

class JadwalOtomatisController extends Controller
{
    public function index()
    {
        $tahunAkademikList = TahunAkademik::where('status_aktif', 1)->orderByDesc('id_tahunakademik')->get();
        $tahunAkademikAktif = TahunAkademik::where('status_aktif', 1)->first();

        $prodiId = ProdiFilter::getProdiId();

        foreach ($tahunAkademikList as $ta) {
            $query = PengampuKelas::where('id_tahunakademik', $ta->id_tahunakademik);
            if ($prodiId) {
                $query->whereHas('kelas', function ($q) use ($prodiId) {
                    $q->where('id_prodi', $prodiId);
                });
            }
            $ta->setAttribute('kelas_count', $query->distinct('id_kelas')->count('id_kelas'));
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
        $prodiId = ProdiFilter::getProdiId();

        // Ambil data kelas untuk tahun akademik ini (sumber utama dari kelas)
        $kelasQuery = \App\Models\Kelas::with([
                'matakuliah.ruangans',
                'prodi',
                'pengampuKelas.dosen',
                'pengampus.dosen',
            ])
            ->where('id_tahunakademik', $tahunAkademikId);

        if ($prodiId) {
            $kelasQuery->where('id_prodi', $prodiId);
        }

        $kelas = $kelasQuery->get();

        if ($kelas->isEmpty()) {
            return back()->withErrors(['kelas' => 'Tidak ada data kelas dengan dosen pengampu untuk tahun akademik ini.']);
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
        if ($request->filled('max_sks_dosen')) $scheduler->setMaxSksDayDosen((int) $request->max_sks_dosen);
        if ($request->filled('early_exit')) $scheduler->setEarlyExitFitness((float) $request->early_exit);
        if ($request->filled('active_constraints')) {
            $scheduler->setActiveConstraints(explode(',', $request->active_constraints));
        }

        // Ambil jadwal prodi lain yang sudah ada di database pada tahun akademik ini agar tidak ditabrak oleh GA
        $otherProdiQuery = \App\Models\Jadwal::with(['kelas.pengampuKelas', 'dosen'])
            ->where('id_tahunakademik', $tahunAkademikId);
        if ($prodiId) {
            $otherProdiQuery->whereHas('kelas', fn($q) => $q->where('id_prodi', '!=', $prodiId));
        } else {
            $otherProdiQuery->whereRaw('1 = 0');
        }
        $scheduler->setOccupiedJadwals($otherProdiQuery->get());

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
                'dosen'       => $k->pengampuKelas->first()?->dosen?->nama_dosen ?? '-',
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
        $prodiId         = ProdiFilter::getProdiId();

        // Hapus jadwal lama untuk prodi ini pada tahun akademik ini
        $deleteQuery = \App\Models\Jadwal::where('id_tahunakademik', $tahunAkademikId);
        if ($prodiId) {
            $deleteQuery->whereHas('kelas', fn($q) => $q->where('id_prodi', $prodiId));
        }
        $deleteQuery->delete();

        foreach ($jadwals as $row) {
            $idKelas = $row['kelas_id'] ?? $row['id_kelas'] ?? null;
            $kodeMk  = $row['kode_mk'] ?? $row['kode_matkul'] ?? null;
            $idDosen = $row['dosen_id'] ?? $row['id_dosen'] ?? null;
            $idHari  = $row['hari_id'] ?? $row['id_hari'] ?? null;
            $idSlot  = $row['slot_id'] ?? $row['id_slot_mulai'] ?? null;
            $idRuang = $row['ruangan_id'] ?? $row['id_ruang'] ?? null;
            $sks     = $row['sks'] ?? $row['durasi_sks'] ?? 2;

            if (!$idKelas || !$idHari || !$idSlot) {
                continue;
            }

            \App\Models\Jadwal::create([
                'id_kelas'         => $idKelas,
                'kode_matkul'      => $kodeMk,
                'id_dosen'         => $idDosen ?: null,
                'id_hari'          => $idHari,
                'id_slot_mulai'    => $idSlot,
                'id_ruang'         => $idRuang ?: null,
                'durasi_sks'       => $sks,
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
        $tahunAkademikList = \App\Models\TahunAkademik::where('status_aktif', 1)->orderByDesc('id_tahunakademik')->get();
        $tahunAkademikId = $request->tahun_akademik_id;
        
        if (!$tahunAkademikId && $tahunAkademikList->isNotEmpty()) {
            $tahunAkademikId = $tahunAkademikList->first()->id_tahunakademik;
        }

        $trials = JadwalTrial::where('id_tahunakademik', $tahunAkademikId)
            ->orderByDesc('created_at')
            ->get();

        $selectedTahun = \App\Models\TahunAkademik::find($tahunAkademikId);
        $tahunAkademik = $selectedTahun;

        return view('jadwal-otomatis.compare', compact('trials', 'tahunAkademik', 'selectedTahun', 'tahunAkademikList'));
    }

    // ── Terapkan hasil Uji Coba (Trial Run) ke Jadwal Utama ────
    public function applyTrial(Request $request, $id)
    {
        $trial = JadwalTrial::findOrFail($id);
        $jadwals = json_decode($trial->jadwal_json, true);
        $tahunAkademikId = $trial->id_tahunakademik;
        $prodiId         = ProdiFilter::getProdiId();

        // Hapus jadwal lama untuk prodi ini pada tahun akademik ini
        $deleteQuery = \App\Models\Jadwal::where('id_tahunakademik', $tahunAkademikId);
        if ($prodiId) {
            $deleteQuery->whereHas('kelas', fn($q) => $q->where('id_prodi', $prodiId));
        }
        $deleteQuery->delete();

        foreach ($jadwals as $row) {
            $idKelas = $row['kelas_id'] ?? $row['id_kelas'] ?? null;
            $kodeMk  = $row['kode_mk'] ?? $row['kode_matkul'] ?? null;
            $idDosen = $row['dosen_id'] ?? $row['id_dosen'] ?? null;
            $idHari  = $row['hari_id'] ?? $row['id_hari'] ?? null;
            $idSlot  = $row['slot_id'] ?? $row['id_slot_mulai'] ?? null;
            $idRuang = $row['ruangan_id'] ?? $row['id_ruang'] ?? null;
            $sks     = $row['sks'] ?? $row['durasi_sks'] ?? 2;

            if (!$idKelas || !$idHari || !$idSlot) {
                continue;
            }

            \App\Models\Jadwal::create([
                'id_kelas'         => $idKelas,
                'kode_matkul'      => $kodeMk,
                'id_dosen'         => $idDosen ?: null,
                'id_hari'          => $idHari,
                'id_slot_mulai'    => $idSlot,
                'id_ruang'         => $idRuang ?: null,
                'durasi_sks'       => $sks,
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
                'pengampuKelas.dosen',
                'pengampus.dosen',
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

        // Hitung timeLimit adaptif: minimal 10 menit, naik sesuai beban
        // Populasi 200 × Gen 500 = 100000; / 40 = 2500 detik (~41 menit)
        $timeLimitSek = (int) min(3600, max(600, ($populasi * $generasi) / 40));

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
        if ($request->filled('temp')) $scheduler->setTemp((int) $request->temp);
        if ($request->filled('max_sks_dosen')) $scheduler->setMaxSksDayDosen((int) $request->max_sks_dosen);
        if ($request->filled('early_exit')) $scheduler->setEarlyExitFitness((float) $request->early_exit);
        if ($request->filled('active_constraints')) {
            $scheduler->setActiveConstraints(explode(',', $request->active_constraints));
        }

        $ruangansAll = \App\Models\Ruangan::all()->keyBy('id_ruang');

        return response()->stream(function () use ($scheduler, $kelas, $slots, $tahunAkademikId, $timeLimitSek, $ruangansAll) {
            // Bebaskan limit eksekusi PHP agar algoritma bisa graceful exit (diatur oleh $timeLimitSek)
            set_time_limit(0);

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
                    
                    $start = $gene->slotMulai;
                    $parts = [
                        ['start' => $start, 'sks' => $gene->durasi]
                    ];

                    foreach ($parts as $p) {
                        if ($p['sks'] <= 0) continue;
                        $slotMulai = $slotMap[$p['start']] ?? null;
                        $idSlotAkhir = $p['start'] + $p['sks'] - 1;
                        $slotAkhir = $slotMap[$idSlotAkhir] ?? null;
                        
                        $ruangan = $ruangansAll[$gene->ruangId] ?? null;

                        $rows[] = [
                            'kelas_id'    => $gene->kelasId,
                            'nama_kelas'  => $k->nama_kelas,
                            'kode_mk'     => $k->matakuliah->kode_matkul ?? '-',
                            'nama_mk'     => $k->matakuliah->nama_matkul ?? '-',
                            'jenis'       => $k->matakuliah->jenis       ?? 'Teori',
                            'sks'         => $p['sks'],
                            'dosen'       => $k->pengampuKelas->first()?->dosen?->nama_dosen ?? '-',
                            'prodi'       => $k->prodi->nama_prodi ?? '-',
                            'semester'    => $k->semester,
                            'hari'        => $hariNama[$gene->hariId]    ?? '-',
                            'hari_id'     => $gene->hariId,
                            'slot_id'     => $p['start'],
                            'jam_mulai'   => $slotMulai ? \Carbon\Carbon::parse($slotMulai['waktu_mulai'])->format('H:i')   : '-',
                            'jam_selesai' => $slotAkhir ? \Carbon\Carbon::parse($slotAkhir['waktu_selesai'])->format('H:i') : '-',
                            'ruangan'     => $ruangan?->nama_ruang ?? '-',
                            'ruangan_id'  => $gene->ruangId,
                            'tipe_ruangan'=> $ruangan?->tipe_ruangan ?? 'Reguler',
                            'dosen_id'    => $gene->dosenId,
                        ];
                    }
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
                
                $finalFitness = $hasil['fitness_pct'];
                $dosenConf = $hasil['dosen_conflicts'] ?? 0;
                $ruangConf = $hasil['ruangan_conflicts'] ?? 0;
                $kelasConf = $hasil['kelas_konflik'] ?? 0;

                // [USER REQUEST] Jika jadwal murni tidak ada pelanggaran fisik, 
                // tampilan akhir disuntik min 90% agar user tahu ini layak.
                if ($dosenConf === 0 && $ruangConf === 0 && $kelasConf === 0) {
                    $finalFitness = max(90.0, $finalFitness);
                }

                $send([
                    'done'              => true,
                    'early_exit'        => false,
                    'fitness'           => $finalFitness,
                    'generasi'          => $hasil['generasi'],
                    'total_kelas'       => $hasil['total_kelas'],
                    'jadwal_rows'       => $jadwalRows,
                    'dosen_conflicts'   => $dosenConf,
                    'ruangan_conflicts' => $ruangConf,
                    'kelas_konflik'     => $kelasConf,
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
                'pengampuKelas.dosen',
                'pengampus.dosen',
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
                    ['type' => 'fatal', 'message' => 'Tidak ada data kelas dengan dosen pengampu untuk tahun akademik ini.']
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

        $maxSksDay = $request->filled('max_sks_dosen') ? (int) $request->max_sks_dosen : 8;
        $auditResult = $this->runFeasibilityAudit($kelas, $slots, $hariList, $maxSksDay);
        $issues = $auditResult['issues'] ?? [];
        $stats = $auditResult['stats'] ?? [];

        $hasFatal = collect($issues)->contains('type', 'fatal');

        return response()->json([
            'feasible' => !$hasFatal,
            'issues' => $issues,
            'stats' => $stats
        ]);
    }

    private function runFeasibilityAudit($kelas, array $slots, array $hariList, int $maxSksDay = 8): array
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
        
        // 2. Kapasitas Slot Ruangan per Tipe Kelas
        $maxSlots = \App\Models\Slot_waktu::where('is_active', 1)->count();
        $activePagiSlots = \App\Models\Slot_waktu::where('is_active', 1)->where('sesi', 'pagi')->count();
        // Slot 14 adalah slot istirahat Maghrib, sehingga tidak dihitung sebagai kapasitas kelas
        $activeMalamSlots = \App\Models\Slot_waktu::where('is_active', 1)->where('sesi', 'malam')->where('id_slot', '!=', 14)->count();

        $activeHari = \App\Models\Hari::where('is_active', 1)->count() ?: 5;

        $slotsPerWeekPagi = ($activeHari * $activePagiSlots) - 1; // minus friday slot 6
        $slotsPerWeekMalam = $activeHari * $activeMalamSlots;
        
        $labRoomsColl = collect($ruanganList)->filter(fn($r) => strtolower(trim($r->tipe_ruangan ?? '')) === 'lab');
        $teoriRoomsColl = collect($ruanganList)->filter(fn($r) => strtolower(trim($r->tipe_ruangan ?? '')) !== 'lab');

        $totalLabRuangan = $labRoomsColl->count();
        $totalTeoriRuangan = $teoriRoomsColl->count();

        $labRoomNames = $labRoomsColl->pluck('nama_ruang')->implode(', ');
        $teoriRoomNames = $teoriRoomsColl->pluck('nama_ruang')->implode(', ');
        $totalRoomNames = collect($ruanganList)->pluck('nama_ruang')->implode(', ');

        $stats = [
            'pagi' => [
                'total_sks' => 0,
                'total_capacity' => $slotsPerWeekPagi * $totalRuangan,
                'total_ruangan' => $totalRuangan,
                'total_room_names' => $totalRoomNames,
                'lab_sks' => 0,
                'lab_capacity' => $slotsPerWeekPagi * $totalLabRuangan,
                'lab_ruangan' => $totalLabRuangan,
                'lab_room_names' => $labRoomNames,
                'teori_sks' => 0,
                'teori_capacity' => $slotsPerWeekPagi * $totalTeoriRuangan,
                'teori_ruangan' => $totalTeoriRuangan,
                'teori_room_names' => $teoriRoomNames,
                'slots_per_week' => $slotsPerWeekPagi,
            ],
            'malam' => [
                'total_sks' => 0,
                'total_capacity' => $slotsPerWeekMalam * $totalRuangan,
                'total_ruangan' => $totalRuangan,
                'total_room_names' => $totalRoomNames,
                'lab_sks' => 0,
                'lab_capacity' => $slotsPerWeekMalam * $totalLabRuangan,
                'lab_ruangan' => $totalLabRuangan,
                'lab_room_names' => $labRoomNames,
                'teori_sks' => 0,
                'teori_capacity' => $slotsPerWeekMalam * $totalTeoriRuangan,
                'teori_ruangan' => $totalTeoriRuangan,
                'teori_room_names' => $teoriRoomNames,
                'slots_per_week' => $slotsPerWeekMalam,
            ],
        ];

        if ($totalRuangan === 0) {
            $issues[] = [
                'type' => 'fatal',
                'message' => 'Tidak ada data ruangan kelas yang tersedia dalam sistem.'
            ];
            return ['issues' => $issues, 'stats' => $stats];
        }

        // 3. Hitung total kebutuhan SKS dan detail dosen/praktikum
        $dosenSks = [];
        $dosenNames = [];
        $kelasDosenKosong = [];
        $kelasTanpaRuang = [];
        $processedKelasIds = [];

        foreach ($kelas as $k) {
            $mk = $k->matakuliah ?? null;
            if (!$mk) continue;

            $idKelas = $k->id_kelas;
            $sks = (int) ($mk->sks ?? 0);
            
            // Dosen — ambil dari relasi pengampuKelas
            $idDosen = null;
            if (!empty($k->pengampuKelas) && $k->pengampuKelas->count() > 0) {
                $pk = $k->pengampus ? $k->pengampus->first() : null;
                $idDosen = $pk ? $pk->id_dosen : null;
                if ($idDosen && $pk->dosen) {
                    $dosenNames[$idDosen] = $pk->dosen->nama_dosen ?? "Dosen $idDosen";
                }
            }
            
            if ($idDosen) {
                if (!isset($dosenSks[$idDosen])) $dosenSks[$idDosen] = 0;
                $dosenSks[$idDosen] += $sks; 
            } else {
                if (!in_array($idKelas, $processedKelasIds)) {
                    $kelasDosenKosong[] = $k->nama_kelas . ' (' . ($mk->nama_matkul ?? '-') . ')';
                }
            }

            // Hitung kebutuhan ruangan 1x per kelas
            if (in_array($idKelas, $processedKelasIds)) {
                continue;
            }
            $processedKelasIds[] = $idKelas;

            $namaNorm = strtolower(trim($k->nama_kelas));
            $isKelasS = (bool) preg_match('/-\d*s[i\d]*[^\w]*$/i', $namaNorm)
                || (bool) preg_match('/\bsore\b|\bmalam\b/', $namaNorm);
            $shift = $isKelasS ? 'malam' : 'pagi';

            $stats[$shift]['total_sks'] += $sks;

            if (strtolower(trim($mk->jenis ?? '')) === 'praktikum') {
                $stats[$shift]['lab_sks'] += $sks;
            } else {
                $stats[$shift]['teori_sks'] += $sks;
            }

            // Cek ruangan options
            $ruangOptions = $mk->ruangans ? $mk->ruangans->pluck('id_ruang')->toArray() : [];
            if (empty($ruangOptions)) {
                $hasFallback = false;
                $kap = (int) ($k->kapasitas ?? 0);
                foreach ($ruanganList as $r) {
                    if (($r->kapasitas ?? 0) >= $kap) {
                        $hasFallback = true;
                        break;
                    }
                }
                if (!$hasFallback) {
                    $kelasTanpaRuang[] = $k->nama_kelas . ' (' . ($mk->nama_matkul ?? '-') . ')';
                }
            }
        }

        // A. Total kapasitas ruangan vs total SKS (HC2)
        foreach (['pagi' => 'Pagi (Reguler)', 'malam' => 'Malam (Sore)'] as $shift => $label) {
            $s = $stats[$shift];
            if ($s['total_sks'] > $s['total_capacity']) {
                $defisit = $s['total_sks'] - $s['total_capacity'];
                $issues[] = [
                    'type' => 'fatal',
                    'message' => "Kekurangan ruangan untuk Kelas $label! Butuh {$s['total_sks']} SKS, tapi ruangan yang ada hanya muat {$s['total_capacity']} SKS. Kurang ruangan sebanyak $defisit SKS."
                ];
            }
            if ($s['lab_sks'] > $s['lab_capacity']) {
                $defisit = $s['lab_sks'] - $s['lab_capacity'];
                $issues[] = [
                    'type' => 'fatal',
                    'message' => "Kekurangan Laboratorium untuk Kelas $label! Praktikum butuh {$s['lab_sks']} SKS, tapi Lab hanya muat {$s['lab_capacity']} SKS. Kurang sebanyak $defisit SKS."
                ];
            }
        }

        // C. Dosen Overload (HC14)
        $maxSksWeek = $maxSksDay * count($hariList);
        foreach ($dosenSks as $dId => $sks) {
            $dName = $dosenNames[$dId];
            if ($sks > $maxSksWeek) {
                $issues[] = [
                    'type' => 'fatal',
                    'message' => "Dosen atas nama '$dName' kelebihan jam mengajar ($sks SKS seminggu). Maksimal $maxSksWeek SKS."
                ];
            } elseif ($sks > ($maxSksWeek * 0.75)) {
                $issues[] = [
                    'type' => 'warning',
                    'message' => "Jadwal Dosen '$dName' padat ($sks SKS)."
                ];
            }
        }

        // D. Kelas Dosen Kosong (HC6)
        if (!empty($kelasDosenKosong)) {
            $count = count($kelasDosenKosong);
            $list = implode(', ', array_slice($kelasDosenKosong, 0, 3));
            if ($count > 3) $list .= '... dan ' . ($count - 3) . ' kelas lainnya';
            $issues[] = [
                'type' => 'warning',
                'message' => "Terdapat $count kelas belum memiliki dosen pengampu (Contoh: $list)."
            ];
        }

        // E. Kelas Tanpa Ruang Valid (HC10)
        if (!empty($kelasTanpaRuang)) {
            $count = count($kelasTanpaRuang);
            $list = implode(', ', array_slice($kelasTanpaRuang, 0, 3));
            if ($count > 3) $list .= '... dan ' . ($count - 3) . ' kelas lainnya';
            $issues[] = [
                'type' => 'fatal',
                'message' => "Ada $count kelas (Contoh: $list) yang kapasitasnya melebihi seluruh ruangan kampus. Pecah kelas jadi 2."
            ];
        }

        // F. Specific Room Set Bottleneck Audit
        $roomSets = [];
        $eveningStartSlot = 13;
        
        $processedRoomDemandClassIds = [];
        
        foreach ($kelas as $k) {
            $idKelas = $k->id_kelas;
            if (in_array($idKelas, $processedRoomDemandClassIds)) {
                continue;
            }
            $processedRoomDemandClassIds[] = $idKelas;

            $mk = $k->matakuliah ?? null;
            if (!$mk) continue;
            
            $sks = (int) ($mk->sks ?? 0);
            $ruangans = $mk->ruangans ? $mk->ruangans->sortBy('id_ruang')->values() : collect();
            
            if ($ruangans->isNotEmpty()) {
                $key = $ruangans->pluck('nama_ruang')->implode(', ');
                if (!isset($roomSets[$key])) {
                    $capacity = 0;
                    foreach ($ruangans as $r) {
                        $isLab = strtolower(trim($r->tipe_ruangan ?? '')) === 'lab';
                        $slotsPerDay = $isLab ? $maxSlots : ($eveningStartSlot - 1);
                        $roomCap = ($slotsPerDay * count($hariList)) - 1; // kurangi 1 untuk jumat
                        $capacity += $roomCap;
                    }
                    $roomSets[$key] = [
                        'sks' => 0,
                        'capacity' => $capacity
                    ];
                }
                $roomSets[$key]['sks'] += $sks;
            }
        }

        foreach ($roomSets as $key => $data) {
            if ($data['sks'] > $data['capacity']) {
                $deficit = $data['sks'] - $data['capacity'];
                $issues[] = [
                    'type' => 'fatal',
                    'message' => "Ruangan Kelas tidak cukup! Ada beberapa mata kuliah (total {$data['sks']} SKS) yang hanya dibolehkan masuk ke ruang [$key]. Padahal ruang tersebut kapasitas maksimalnya hanya {$data['capacity']} SKS (Kapasitas: " . count($ruangans) . " ruang × 59 slot = {$data['capacity']} SKS). Pasti akan ada kelas yang telantar (Kurang $deficit SKS). Saran: Perbanyak pilihan ruangan untuk mata kuliah tersebut di Master Data."
                ];
            } elseif ($data['sks'] > ($data['capacity'] * 0.85)) {
                $issues[] = [
                    'type' => 'warning',
                    'message' => "Kapasitas ruang [$key] hampir penuh (Terpakai {$data['sks']} SKS dari maksimal {$data['capacity']} SKS). Risiko bentrok jadwal cukup tinggi karena ruang gerak yang sangat sempit."
                ];
            }
        }

        return ['issues' => $issues, 'stats' => $stats];
    }

    private function diagnoseConflicts(array $jadwalRows, int $maxSksDay = 8): array
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

                    // C. Kelas Mahasiswa Bentrok
                    if ($rowA['nama_kelas'] !== '' && $rowA['nama_kelas'] === $rowB['nama_kelas']) {
                        $pairKey = min($rowA['kelas_id'], $rowB['kelas_id']) . '-' . max($rowA['kelas_id'], $rowB['kelas_id']);
                        if (!isset($ruangBentrokPairs['kelas-'.$pairKey])) {
                            $ruangBentrokPairs['kelas-'.$pairKey] = true;
                            $diagnoses[] = "Mahasiswa Kelas '{$rowA['nama_kelas']}' bentrok jadwal pada hari {$rowA['hari']} di jam {$rowA['jam_mulai']}-{$rowA['jam_selesai']} ({$rowA['nama_mk']}) dan jam {$rowB['jam_mulai']}-{$rowB['jam_selesai']} ({$rowB['nama_mk']}). Mahasiswa tidak bisa berada di dua tempat sekaligus.";
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
                if ($sks > $maxSksDay) {
                    $dName = $dosenNames[$did];
                    $hName = $hariNames[$hariId] ?? "Hari $hariId";
                    $diagnoses[] = "Dosen '{$dName}' memiliki beban mengajar melebihi batas pada hari {$hName} yaitu {$sks} SKS (maksimal $maxSksDay SKS per hari).";
                }
            }
        }

        // 3. Sholat Jumat Bentrok (hari 5, slot 5 dan 6)
        foreach ($jadwalRows as $row) {
            if ($row['hari_id'] === 5) {
                $start = (int) $row['slot_id'];
                $end = $start + (int) $row['sks'] - 1;
                if ($start <= 6 && $end >= 5) {
                    $diagnoses[] = "Kelas '{$row['nama_kelas']}' ({$row['nama_mk']}) melanggar jam Sholat Jumat (menempati slot ke-5/6 pada hari Jumat).";
                }
            }
        }

        // 4. Kelas Sore/Reguler Salah Slot
        foreach ($jadwalRows as $row) {
            $namaNorm = strtolower(trim($row['nama_kelas']));
            $isKelasS = (bool) preg_match('/-\d*s[i\d]*[^\w]*$/i', $namaNorm)
                || (bool) preg_match('/\bsore\b|\bmalam\b/', $namaNorm);

            $start = (int) $row['slot_id'];
            $end = $start + (int) $row['sks'] - 1;

            if ($isKelasS) {
                if ($start < 11) {
                    $diagnoses[] = "Kelas Sore/Malam '{$row['nama_kelas']}' ({$row['nama_mk']}) dijadwalkan terlalu pagi di jam {$row['jam_mulai']} (seharusnya mulai minimal slot 11).";
                }
            } else {
                if ($end >= 11) {
                    $diagnoses[] = "Kelas Reguler '{$row['nama_kelas']}' ({$row['nama_mk']}) menjangkau jam sore/malam di jam {$row['jam_selesai']} (maksimal selesai pada slot 10).";
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