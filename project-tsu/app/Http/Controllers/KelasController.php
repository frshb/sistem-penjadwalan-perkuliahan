<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use App\Models\MataKuliah;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KelasExport;
use App\Helpers\ProdiFilter;

class KelasController extends Controller
{
    /**
     * Menampilkan daftar kelas.
     */
    public function pilihTahun()
    {
        $tahunAkademiks = TahunAkademik::orderBy('tahun_ajaran', 'desc')->get();

        return view('management.kelas.pilih-tahun', compact('tahunAkademiks'));
    }

    public function index(Request $request)
    {
        $user       = auth()->user();
        $idTahun    = $request->tahun;
        $prodiId    = ProdiFilter::getProdiId();
        $searchTerm = $request->input('search');

        if (!$idTahun) {
            return redirect()->route('kelas.pilih-tahun');
        }

        $tahunAkademik  = TahunAkademik::findOrFail($idTahun);

        if (!auth()->user()->isAdmin() && !$tahunAkademik->status_aktif) {
            abort(403, 'Anda tidak memiliki akses ke tahun akademik yang dinonaktifkan.');
        }

        $query = Kelas::with(['prodi', 'tahunAkademik', 'matakuliah', 'dosen'])
            ->where('id_tahunakademik', $idTahun);

        if ($prodiId) {
            $query->where('id_prodi', $prodiId);
        }

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_kelas', 'like', '%' . $searchTerm . '%')
                ->orWhereHas('matakuliah', fn($m) => $m->where('kode_matkul', 'like', '%' . $searchTerm . '%'))
                ->orWhereHas('matakuliah', fn($m) => $m->where('nama_matkul', 'like', '%' . $searchTerm . '%'))
                ->orWhereHas('dosen', fn($d) => $d->where('nama_dosen', 'like', '%' . $searchTerm . '%'));
            });
        }

        $kelas = $query->get()
            ->sortBy('nama_kelas', SORT_NATURAL | SORT_FLAG_CASE)
            ->sortBy(function ($k) {
                return $k->matakuliah->nama_matkul ?? '';
            }, SORT_NATURAL | SORT_FLAG_CASE)
            ->sortBy('semester')
            ->values();

        $kelasByProdi = $kelas
            ->groupBy(fn($item) => $item->prodi->nama_prodi ?? 'Tanpa Prodi')
            ->sortKeys();

        // Dropdown prodi — dibatasi jika kaprodi/dosen (kecualikan dosen eksternal id_prodi 99)
        $prodis = $prodiId
            ? Prodi::where('id_prodi', $prodiId)->where('id_prodi', '!=', 99)->orderBy('nama_prodi')->get()
            : Prodi::where('id_prodi', '!=', 99)->orderBy('nama_prodi')->get();

        // =====================================================================
        // FIX UTAMA: bentuk data mataKuliahs secara eksplisit & konsisten,
        // jangan kirim objek Eloquent mentah lewat @js().
        //
        // Alasan:
        // 1. kode_matkul di-trim() supaya tidak gagal match akibat whitespace
        //    tersembunyi dari data lama (sumber paling umum dropdown "kosong"
        //    padahal data sebenarnya ada).
        // 2. pengampus dibentuk manual jadi array bersih {id_dosen, dosen{...}}
        //    supaya tidak tergantung struktur internal Eloquent (hidden attribute,
        //    casts, accessor tersembunyi) yang bisa diam-diam menghapus field
        //    yang dibutuhkan Alpine.
        // 3. dosen yang sudah terhapus / id_dosen orphan di pengampu_kelas
        //    di-filter (->filter(fn($p) => $p['dosen'] !== null)) supaya
        //    tidak ada entri dosen "null" nyangkut di dropdown.
        // 4. Tidak difilter by id_prodi di server — biar Alpine yang filter
        //    sesuai prodi yang dipilih user di form, supaya matkul prodi lain
        //    tetap tersedia saat tab "Semua Prodi" aktif.
        // =====================================================================
         $mataKuliahs = MataKuliah::with(['pengampus' => function ($query) use ($idTahun) {
            $query->where('pengampu_kelas.id_tahunakademik', $idTahun)->with('dosen');
        }])
            ->orderBy('nama_matkul')
            ->get()
            ->map(function ($m) {
                return [
                    'id'           => $m->id,
                    'id_matakuliah'  => $m->id_matakuliah,
                    'kode_matkul'  => trim($m->kode_matkul),
                    'nama_matkul'  => $m->nama_matkul,
                    'sks'          => $m->sks,
                    'id_prodi'     => $m->id_prodi,
                    'semester'     => $m->semester,
                    'id_kurikulum' => $m->id_kurikulum,
                    'pengampus'    => $m->pengampus
                        ->map(function ($p) {
                            if (!$p->dosen) {
                                return null;
                            }
                            return [
                                'id_dosen' => $p->dosen->id_dosen,
                                'dosen'    => [
                                    'id_dosen'   => $p->dosen->id_dosen,
                                    'nama_dosen' => $p->dosen->nama_dosen,
                                ],
                            ];
                        })
                        ->filter()
                        ->unique('id_dosen') // satu dosen tidak duplikat walau muncul di banyak baris pengampu
                        ->values(),
                ];
            })
            ->values();

        $kurikulums     = Kurikulum::orderBy('nama_kurikulum')->get();
        $tahunAkademiks = TahunAkademik::orderBy('tahun_ajaran', 'desc')->get();
        $tahunAkademik  = TahunAkademik::find($idTahun);

        // Calculate capacities
        $activePagiSlots = \App\Models\Slot_waktu::where('is_active', 1)->where('sesi', 'pagi')->count();
        $activeMalamSlots = \App\Models\Slot_waktu::where('is_active', 1)->where('sesi', 'malam')->where('id_slot', '!=', 14)->count();
        
        $hariCount = \App\Models\Hari::where('is_active', 1)->count() ?: 5; // Fallback to 5 if empty
        $slotsPerWeekPagi = ($hariCount * $activePagiSlots) - 1; // minus friday prayer slot
        $slotsPerWeekMalam = $hariCount * $activeMalamSlots;
        
        // 1. Ambil data ruang unik dari kelas yang dijadwalkan (sesuai logika Audit)
        $ruanganList = [];
        foreach ($kelas as $k) {
            $mk = $k->matakuliah ?? null;
            if (!$mk) continue;
            foreach ($mk->ruangans ?? [] as $r) {
                $ruanganList[$r->id_ruang] = $r;
            }
        }

        if (empty($ruanganList)) {
            $ruanganList = \App\Models\Ruangan::all()->keyBy('id_ruang')->all();
        }

        $totalRuangan = count($ruanganList);
        $totalLabRuangan = collect($ruanganList)->filter(fn($r) => strtolower(trim($r->tipe_ruangan ?? '')) === 'lab')->count();
        $totalTeoriRuangan = $totalRuangan - $totalLabRuangan;

        $kapasitasPagiTeori = $slotsPerWeekPagi * $totalTeoriRuangan;
        $kapasitasPagiLab = $slotsPerWeekPagi * $totalLabRuangan;
        $kapasitasMalamTeori = $slotsPerWeekMalam * $totalTeoriRuangan;
        $kapasitasMalamLab = $slotsPerWeekMalam * $totalLabRuangan;

        $sksPagiTeori = 0; $sksPagiLab = 0;
        $sksMalamTeori = 0; $sksMalamLab = 0;

        foreach ($kelas as $k) {
            // Abaikan kelas yang tidak memiliki dosen (Kelas Kosong), persis seperti di Audit
            if (empty($k->pengampus) || $k->pengampus->count() === 0) {
                continue;
            }

            $namaNorm = strtolower(trim($k->nama_kelas ?? ''));
            $isMalam = (bool) preg_match('/-\d*s[i\d]*[^\w]*$/i', $namaNorm)
                || (bool) preg_match('/\bsore\b|\bmalam\b/', $namaNorm);
            
            $mk = $k->matakuliah ?? null;
            $sks = $mk->sks ?? 0;
            
            $isLab = strtolower(trim($mk->jenis ?? '')) === 'praktikum';

            if ($isMalam) {
                if ($isLab) $sksMalamLab += $sks;
                else $sksMalamTeori += $sks;
            } else {
                if ($isLab) $sksPagiLab += $sks;
                else $sksPagiTeori += $sks;
            }
        }

        $stats = [
            'total_kelas'  => $kelas->count(),
            'total_matkul' => $kelas->unique('id_matakuliah')->count(),
            'total_sks'    => $sksPagiTeori + $sksPagiLab + $sksMalamTeori + $sksMalamLab,
            
            'sks_pagi_teori'     => $sksPagiTeori,
            'sks_pagi_lab'       => $sksPagiLab,
            'kapasitas_pagi_teori' => $kapasitasPagiTeori,
            'kapasitas_pagi_lab'   => $kapasitasPagiLab,

            'sks_malam_teori'    => $sksMalamTeori,
            'sks_malam_lab'      => $sksMalamLab,
            'kapasitas_malam_teori'=> $kapasitasMalamTeori,
            'kapasitas_malam_lab'  => $kapasitasMalamLab,
            
            'kelas_kosong' => $kelas->filter(fn($k) => $k->dosen === null)->count(),
        ];

        return view('management.kelas.index', compact(
            'kelas', 'kelasByProdi', 'searchTerm', 'prodis',
            'tahunAkademiks', 'tahunAkademik', 'mataKuliahs', 'kurikulums', 'stats'
        ));
    }

    /**
     * Form tambah kelas.
     */
    public function create()
    {
        $user = auth()->user();
        if ($user && !$user->isAdmin() && !$user->isDekan()) {
            $prodiId = $user->getProdiId();
            if ($prodiId) {
                $prodis = Prodi::where('id_prodi', $prodiId)->where('id_prodi', '!=', 99)->orderBy('nama_prodi', 'asc')->get();
            } else {
                $prodis = Prodi::where('id_prodi', '!=', 99)->orderBy('nama_prodi', 'asc')->get();
            }
        } else {
            $prodis = Prodi::where('id_prodi', '!=', 99)->orderBy('nama_prodi', 'asc')->get();
        }
        $tahunAkademiks = TahunAkademik::orderBy('tahun_ajaran', 'desc')->get();
        $kurikulums = Kurikulum::orderBy('nama_kurikulum')->get();

        return view('management.kelas.create', compact('prodis', 'tahunAkademiks', 'kurikulums'));
    }

    /**
     * Simpan kelas baru.
     */
    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionAccess('management_data', 'Kelas Paralel', 'edit'), 403, 'Unauthorized action.');

        $request->validate([
            'nama_kelas'       => 'required|string|max:50',
            'id_prodi'         => 'nullable|integer|exists:program_studi,id_prodi',
            'id_tahunakademik' => 'required|integer|exists:tahun_akademik,id_tahunakademik',
            'id_matakuliah'    => 'required|exists:mata_kuliah,id_matakuliah',
            'id_dosen'         => 'nullable|integer|exists:dosen,id_dosen',
            'kapasitas'        => 'required|integer|min:1',
            'semester'         => 'required|integer|min:1|max:14',
        ]);

        Kelas::create([
            'nama_kelas'       => $request->nama_kelas,
            'id_prodi'         => $request->id_prodi,
            'id_tahunakademik' => $request->id_tahunakademik,
            'id_matakuliah'    => $request->id_matakuliah,
            'id_dosen'         => $request->id_dosen,
            'kapasitas'        => $request->kapasitas,
            'semester'         => $request->semester,
        ]);

        return redirect()->route('kelas.index', [
            'tahun' => $request->id_tahunakademik
        ])->with('success', 'Data kelas berhasil ditambahkan.');
    }

    /**
     * Form edit kelas.
     */
    public function edit(Kelas $kela)
    {
        $user = auth()->user();
        if ($user && !$user->isAdmin() && !$user->isDekan()) {
            $prodiId = $user->getProdiId();
            if ($prodiId) {
                $prodis = Prodi::where('id_prodi', $prodiId)->where('id_prodi', '!=', 99)->orderBy('nama_prodi', 'asc')->get();
            } else {
                $prodis = Prodi::where('id_prodi', '!=', 99)->orderBy('nama_prodi', 'asc')->get();
            }
        } else {
            $prodis = Prodi::where('id_prodi', '!=', 99)->orderBy('nama_prodi', 'asc')->get();
        }
        $tahunAkademiks = TahunAkademik::orderBy('tahun_ajaran', 'desc')->get();
        $kurikulums = Kurikulum::orderBy('nama_kurikulum')->get();

        return view('management.kelas.edit', compact('kela', 'prodis', 'tahunAkademiks', 'kurikulums'));
    }

    /**
     * Update kelas.
     */
    public function update(Request $request, Kelas $kela)
    {
        abort_if(!auth()->user()->hasPermissionAccess('management_data', 'Kelas Paralel', 'edit'), 403, 'Unauthorized action.');

        $request->validate([
            'nama_kelas'       => 'required|string|max:50',
            'id_prodi'         => 'nullable|integer|exists:program_studi,id_prodi',
            'id_tahunakademik' => 'required|integer|exists:tahun_akademik,id_tahunakademik',
            'id_matakuliah'    => 'required|exists:mata_kuliah,id_matakuliah',
            'id_dosen'         => 'nullable|integer|exists:dosen,id_dosen',
            'kapasitas'        => 'required|integer|min:1',
            'semester'         => 'required|integer|min:1|max:14',
        ]);

        $kela->update([
            'nama_kelas'       => $request->nama_kelas,
            'id_prodi'         => $request->id_prodi,
            'id_tahunakademik' => $request->id_tahunakademik,
            'id_matakuliah'    => $request->id_matakuliah,
            'id_dosen'         => $request->id_dosen,
            'kapasitas'        => $request->kapasitas,
            'semester'         => $request->semester,
        ]);

        return redirect()->route('kelas.index', [
            'tahun' => $request->id_tahunakademik
        ])->with('success', 'Data kelas berhasil diperbarui.');
    }

    /**
     * Hapus kelas.
     */
    public function destroy(Request $request, Kelas $kela)
    {
        abort_if(!auth()->user()->hasPermissionAccess('management_data', 'Kelas Paralel', 'edit'), 403, 'Unauthorized action.');

        try {
            $tahun = $kela->id_tahunakademik;

            // Delete related schedule and pengampu records first to prevent FK constraint issues
            $kela->jadwals()->delete();
            $kela->pengampuKelas()->delete();

            $kela->delete();

            return redirect()->route('kelas.index', [
                'tahun' => $tahun
            ])->with('success', 'Data kelas berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus data kelas: ' . $e->getMessage());
        }
    }

    /**
     * Export Excel.
     */
    public function exportExcel(Request $request)
    {
        $user = auth()->user();
        $prodiId = ($user && !$user->isAdmin() && !$user->isDekan()) ? $user->getProdiId() : null;
        $tahunId = $request->tahun;
        return Excel::download(new KelasExport(false, $prodiId, $tahunId), 'daftar-kelas.xlsx');
    }

    /**
     * Export PDF.
     */
    public function exportPdf(Request $request)
    {
        $user = auth()->user();
        $prodiId = ($user && !$user->isAdmin() && !$user->isDekan()) ? $user->getProdiId() : null;
        $tahunId = $request->tahun;
        return Excel::download(
            new KelasExport(true, $prodiId, $tahunId),
            'daftar-kelas.pdf',
            \Maatwebsite\Excel\Excel::DOMPDF
        );
    }

    public function getMataKuliahByFilter(Request $request)
    {
        $request->validate([
            'id_prodi'     => 'required',
            'semester'     => 'required',
            'id_kurikulum' => 'required',
        ]);

        $mataKuliahs = MataKuliah::where('id_prodi', $request->id_prodi)
            ->where('semester', $request->semester)
            ->where('id_kurikulum', $request->id_kurikulum)
            ->orderBy('nama_matkul')
            ->get(['id_matakuliah', 'kode_matkul', 'nama_matkul', 'sks', 'sifat', 'konsentrasi']);

        return response()->json($mataKuliahs);
    }

    public function generate(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionAccess('management_data', 'Kelas Paralel', 'edit'), 403, 'Unauthorized action.');

        $request->validate([
            'id_prodi'         => 'required',
            'semester'         => 'required',
            'id_kurikulum'     => 'required|exists:kurikulum,id_kurikulum',
            'jumlah_mahasiswa' => 'required|integer|min:1',
            'id_tahunakademik' => 'required',
            'matkul_ids'       => 'required|array|min:1',
            'matkul_ids.*'     => 'exists:mata_kuliah,id_matakuliah',
            'tipe_kelas'       => 'nullable|in:pagi,malam',
        ], [
            'matkul_ids.required' => 'Pilih minimal satu mata kuliah untuk di-generate.',
        ]);

        $idProdi         = $request->id_prodi;
        $semester        = $request->semester;
        $jumlahMahasiswa = $request->jumlah_mahasiswa;
        $idTahun         = $request->id_tahunakademik;

        $jumlahKelas = (int) ceil($jumlahMahasiswa / 25);

        $prodi = Prodi::findOrFail($idProdi);

        $prefix = match (strtolower($prodi->nama_prodi)) {
            's1 sistem informasi' => 'A1',
            'sistem informasi'    => 'A1',

            's1 informatika' => 'A2',
            'informatika'    => 'A2',

            's1 rekayasa komputer' => 'A3',
            'rekayasa komputer'    => 'A3',

            default => 'AX'
        };

        $tipeKelas = $request->input('tipe_kelas', 'pagi');
        
        $mataKuliahs = MataKuliah::whereIn('id_matakuliah', $request->matkul_ids)->get();

        foreach ($mataKuliahs as $matkul) {
            $sisaMahasiswa = $jumlahMahasiswa;

            for ($i = 0; $i < $jumlahKelas; $i++) {
                if ($tipeKelas === 'malam') {
                    $huruf = ($jumlahKelas === 1) ? 'S' : 'S' . ($i + 1);
                } else {
                    $huruf = chr(65 + $i);
                }
                
                $namaKelas = $prefix . '-' . $semester . $huruf;
                $kapasitasKelas = min(25, $sisaMahasiswa);

                Kelas::create([
                    'nama_kelas'       => $namaKelas,
                    'semester'         => $semester,
                    'id_matakuliah'    => $matkul->id_matakuliah,
                    'id_prodi'         => $idProdi,
                    'id_tahunakademik' => $idTahun,
                    'kapasitas'        => $kapasitasKelas,
                    'jumlah_mahasiswa' => $kapasitasKelas,
                    'id_dosen'         => null,
                ]);

                $sisaMahasiswa -= $kapasitasKelas;
            }
        }

        return back()->with('success', 'Generate kelas berhasil.');
    }

    public function bulkDelete(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionAccess('management_data', 'Kelas Paralel', 'edit'), 403, 'Unauthorized action.');

        $ids = array_filter(explode(',', $request->ids));

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 kelas untuk dihapus.');
        }

        try {
            \App\Models\Jadwal::whereIn('id_kelas', $ids)->delete();
            \App\Models\PengampuKelas::whereIn('id_kelas', $ids)->delete();
            Kelas::whereIn('id_kelas', $ids)->delete();

            return redirect()->back()
                ->with('success', 'Data kelas berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus data kelas: ' . $e->getMessage());
        }
    }
}