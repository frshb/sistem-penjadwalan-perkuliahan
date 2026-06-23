<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use App\Models\Slot_waktu;
use App\Services\GeneticAlgorithm\GeneticScheduler;

class JadwalOtomatisController extends Controller
{
    // ── Tampilkan form parameter GA ─────────────────────────
    public function index()
    {
        $tahunAkademikList = TahunAkademik::orderByDesc('id_tahunakademik')->get();
        $tahunAkademikAktif = TahunAkademik::where('status_aktif', 1)->first();

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

        // Ambil semua kelas untuk tahun akademik ini beserta relasi yang dibutuhkan
        $kelasQuery = Kelas::with([
                'matakuliah.ruangans',
                'dosen',
                'prodi',
            ])
            ->where('id_tahunakademik', $tahunAkademikId);

        if ($prodiId) {
            $kelasQuery->where('id_prodi', $prodiId);
        }

        $kelas = $kelasQuery->get();

        if ($kelas->isEmpty()) {
            return back()->withErrors(['kelas' => 'Tidak ada data kelas untuk tahun akademik ini.']);
        }


        $slots = Slot_waktu::orderBy('id_slot')
            ->get()
            ->map(fn($s) => [
                'id'           => $s->id_slot,
                'waktu_mulai'  => $s->waktu_mulai,
                'waktu_selesai'=> $s->waktu_selesai,
            ])
            ->all();

        $hariList = [1, 2, 3, 4, 5];

        $populasiProses  = (int) $request->populasi;
        $generasiProses  = (int) $request->generasi;
        $timeLimitProses = (int) min(1500, max(300, ($populasiProses * $generasiProses) / 400));

        $scheduler = new GeneticScheduler(
            populationSize: $populasiProses,
            maxGenerations: $generasiProses,
            crossoverRate: 0.8,
            mutationRate: 0.1,
        );
        $scheduler->setTimeLimit((float) $timeLimitProses);

        set_time_limit($timeLimitProses + 60);
        ini_set('memory_limit', '512M');
        $hasil = $scheduler->run($kelas, $slots, $hariList);

        // Susun data untuk ditampilkan
        $hariNama   = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat'];
        $slotMap    = collect($slots)->keyBy('id');
        $kelasMap   = $kelas->keyBy('id_kelas');

        $jadwalRows = [];
        foreach ($hasil['genes'] as $gene) {
            $k          = $kelasMap[$gene->kelasId] ?? null;
            if (!$k) continue;

            $slotMulai   = $slotMap[$gene->slotMulai]   ?? null;
            // Slot terakhir = slotMulai + durasi - 1
            $idSlotAkhir = $gene->slotMulai + $gene->durasi - 1;
            $slotAkhir   = $slotMap[$idSlotAkhir] ?? null;

            // Guard untuk matakuliah null
            $ruangan = null;
            if ($k->matakuliah && isset($k->matakuliah->ruangans)) {
                $ruangan = $k->matakuliah->ruangans->firstWhere('id_ruang', $gene->ruangId);
            }

           $jadwalRows[] = [
                'kelas_id'    => $gene->kelasId,
                'nama_kelas'  => $k->nama_kelas,
                'kode_mk'     => $k->matakuliah->kode_matkul ?? '-',
                'nama_mk'     => $k->matakuliah->nama_matkul ?? '-',
                'jenis'       => $k->matakuliah->jenis       ?? 'Teori',
                'sks'         => $gene->durasi,
                'dosen'       => $k->dosen->nama_dosen       ?? '-',
                'prodi'       => $k->prodi->nama_prodi       ?? '-',
                'semester'    => $k->semester,
                'hari'        => $hariNama[$gene->hariId]    ?? '-',
                'hari_id'     => $gene->hariId,
                'slot_id'     => $gene->slotMulai,
                'jam_mulai'   => $slotMulai  ? \Carbon\Carbon::parse($slotMulai['waktu_mulai'])->format('H:i')   : '-',
                'jam_selesai' => $slotAkhir  ? \Carbon\Carbon::parse($slotAkhir['waktu_selesai'])->format('H:i') : '-',
                'ruangan'     => $ruangan?->nama_ruang ?? '-',
                'ruangan_id'  => $gene->ruangId,
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
            'softViolations'     => $hasil['constraint_violations'] ?? 0,
            'problemLog'         => $hasil['problem_log']          ?? [],
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

        $kelasQuery = Kelas::with(['matakuliah.ruangans', 'dosen', 'prodi'])
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
            crossoverRate:  0.8,
            mutationRate:   0.15,
        );
        $scheduler->setTimeLimit((float) $timeLimitSek);

        return response()->stream(function () use ($scheduler, $kelas, $slots, $tahunAkademikId, $timeLimitSek) {
            // Set PHP execution limit = timeLimit + 60 detik buffer
            set_time_limit($timeLimitSek + 60);

            // Fungsi kirim SSE event
            $send = function (array $data) {
                echo "data: " . json_encode($data) . "\n\n";
                ob_flush();
                flush();
            };

            // Fungsi build jadwalRows dari hasil genes — dipakai di beberapa titik
            $buildJadwalRows = function (array $genes) use ($kelas, $slots) {
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

                    // Guard untuk matakuliah null
                    $ruangan = null;
                    if ($k->matakuliah && isset($k->matakuliah->ruangans)) {
                        $ruangan = $k->matakuliah->ruangans->firstWhere('id_ruang', $gene->ruangId);
                    }
                    $rows[] = [
                        'kelas_id'    => $gene->kelasId,
                        'nama_kelas'  => $k->nama_kelas,
                        'kode_mk'     => $k->matakuliah->kode_matkul ?? '-',
                        'nama_mk'     => $k->matakuliah->nama_matkul ?? '-',
                        'jenis'       => $k->matakuliah->jenis       ?? 'Teori',
                        'sks'         => $gene->durasi,
                        'dosen'       => $k->dosen->nama_dosen       ?? '-',
                        'prodi'       => $k->prodi->nama_prodi       ?? '-',
                        'semester'    => $k->semester,
                        'hari'        => $hariNama[$gene->hariId]    ?? '-',
                        'hari_id'     => $gene->hariId,
                        'slot_id'     => $gene->slotMulai,
                        'jam_mulai'   => $slotMulai ? \Carbon\Carbon::parse($slotMulai['waktu_mulai'])->format('H:i')   : '-',
                        'jam_selesai' => $slotAkhir ? \Carbon\Carbon::parse($slotAkhir['waktu_selesai'])->format('H:i') : '-',
                        'ruangan'     => $ruangan?->nama_ruang ?? '-',
                        'ruangan_id'  => $gene->ruangId,
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
                    'soft_violations'   => $hasil['constraint_violations'] ?? 0,
                    'problem_log'       => $hasil['problem_log']           ?? [],
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
                    ]);
                }
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

}
