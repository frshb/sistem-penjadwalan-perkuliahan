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

        return view('jadwal-otomatis.index', compact('tahunAkademikList'));
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


        $scheduler = new GeneticScheduler(
            populasiSize: (int) $request->populasi,
            maxGenerasi:  (int) $request->generasi,
            crossoverRate: 0.8,
            mutationRate:  0.1,
        );

        set_time_limit(0);
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
            $ruangan     = $k->matakuliah->ruangans->firstWhere('id_ruang', $gene->ruangId);

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
            'jadwalRows'    => $jadwalRows,
            'fitness'       => $hasil['fitness_pct'],
            'generasi'      => $hasil['generasi'],
            'totalKelas'    => $hasil['total_kelas'],
            'populasi'      => $request->populasi,
            'tahunAkademik' => $tahunAkademik,
            'jadwalJson'    => json_encode($jadwalRows),
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

        $scheduler = new GeneticScheduler(
            populasiSize:  (int) $request->populasi,
            maxGenerasi:   (int) $request->generasi,
            crossoverRate: 0.8,
            mutationRate:  0.15,
        );

        return response()->stream(function () use ($scheduler, $kelas, $slots, $tahunAkademikId) {
            set_time_limit(300);

            // Fungsi kirim SSE event
            $send = function (array $data) {
                echo "data: " . json_encode($data) . "\n\n";
                ob_flush();
                flush();
            };

            $hasil = $scheduler->run(
                $kelas,
                $slots,
                [1, 2, 3, 4, 5],
                function ($progress) use ($send) {
                    $send($progress);
                }
            );

            // Susun jadwal rows
            $hariNama  = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat'];
            $slotMap   = collect($slots)->keyBy('id');
            $kelasMap  = $kelas->keyBy('id_kelas');

            $jadwalRows = [];
            foreach ($hasil['genes'] as $gene) {
                $k = $kelasMap[$gene->kelasId] ?? null;
                if (!$k) continue;
                $slotMulai   = $slotMap[$gene->slotMulai] ?? null;
                $idSlotAkhir = $gene->slotMulai + $gene->durasi - 1;
                $slotAkhir   = $slotMap[$idSlotAkhir] ?? null;
                $ruangan     = $k->matakuliah->ruangans->firstWhere('id_ruang', $gene->ruangId);

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
                    'jam_mulai'   => $slotMulai ? \Carbon\Carbon::parse($slotMulai['waktu_mulai'])->format('H:i')   : '-',
                    'jam_selesai' => $slotAkhir ? \Carbon\Carbon::parse($slotAkhir['waktu_selesai'])->format('H:i') : '-',
                    'ruangan'     => $ruangan?->nama_ruang ?? '-',
                    'ruangan_id'  => $gene->ruangId,
                    'dosen_id'    => $gene->dosenId,
                ];
            }

            usort($jadwalRows, fn($a, $b) =>
                $a['hari_id'] !== $b['hari_id']
                    ? $a['hari_id'] <=> $b['hari_id']
                    : strcmp($a['jam_mulai'], $b['jam_mulai'])
            );

            // Kirim event selesai dengan hasil lengkap
            $send([
                'done'        => true,
                'fitness'     => $hasil['fitness_pct'],
                'generasi'    => $hasil['generasi'],
                'total_kelas' => $hasil['total_kelas'],
                'jadwal_rows' => $jadwalRows,
            ]);

        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no', // penting untuk Nginx
        ]);
    }

}
