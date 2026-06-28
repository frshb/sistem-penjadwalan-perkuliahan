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
                ->orWhere('kode_matkul', 'like', '%' . $searchTerm . '%')
                ->orWhereHas('matakuliah', fn($m) => $m->where('nama_matkul', 'like', '%' . $searchTerm . '%'))
                ->orWhereHas('dosen', fn($d) => $d->where('nama_dosen', 'like', '%' . $searchTerm . '%'));
            });
        }

        $kelas = $query->orderBy('semester')->orderBy('nama_kelas')->get();

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
        // 3. dosen yang sudah terhapus / id_dosen orphan di pengampu_matkul
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

        return view('management.kelas.index', compact(
            'kelas', 'kelasByProdi', 'searchTerm', 'prodis',
            'tahunAkademiks', 'tahunAkademik', 'mataKuliahs', 'kurikulums'
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
        $request->validate([
            'nama_kelas'       => 'required|string|max:50',
            'id_prodi'         => 'nullable|integer|exists:program_studi,id_prodi',
            'id_tahunakademik' => 'required|integer|exists:tahun_akademik,id_tahunakademik',
            'kode_matkul'      => 'required|exists:mata_kuliah,kode_matkul',
            'id_dosen'         => 'nullable|integer|exists:dosen,id_dosen',
            'kapasitas'        => 'required|integer|min:1',
            'semester'         => 'required|integer|min:1|max:14',
        ]);

        Kelas::create([
            'nama_kelas'       => $request->nama_kelas,
            'id_prodi'         => $request->id_prodi,
            'id_tahunakademik' => $request->id_tahunakademik,
            'kode_matkul'      => $request->kode_matkul,
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
        $request->validate([
            'nama_kelas'       => 'required|string|max:50',
            'id_prodi'         => 'nullable|integer|exists:program_studi,id_prodi',
            'id_tahunakademik' => 'required|integer|exists:tahun_akademik,id_tahunakademik',
            'kode_matkul'      => 'required|exists:mata_kuliah,kode_matkul',
            'id_dosen'         => 'nullable|integer|exists:dosen,id_dosen',
            'kapasitas'        => 'required|integer|min:1',
            'semester'         => 'required|integer|min:1|max:14',
        ]);

        $kela->update([
            'nama_kelas'       => $request->nama_kelas,
            'id_prodi'         => $request->id_prodi,
            'id_tahunakademik' => $request->id_tahunakademik,
            'kode_matkul'      => $request->kode_matkul,
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
        try {
            $tahun = $kela->id_tahunakademik;

            $kela->delete();

            return redirect()->route('kelas.index', [
                'tahun' => $tahun
            ])->with('success', 'Data kelas berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus data. Kelas mungkin masih digunakan.');
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

    public function generate(Request $request)
    {
        $request->validate([
            'id_prodi'         => 'required',
            'semester'         => 'required',
            'id_kurikulum'     => 'required|exists:kurikulum,id_kurikulum',
            'jumlah_mahasiswa' => 'required|integer|min:1',
            'id_tahunakademik' => 'required',
        ]);

        $idProdi         = $request->id_prodi;
        $semester        = $request->semester;
        $jumlahMahasiswa = $request->jumlah_mahasiswa;
        $idTahun         = $request->id_tahunakademik;

        $jumlahKelas = ceil($jumlahMahasiswa / 20);

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

        $mataKuliahs = MataKuliah::where('id_prodi', $request->id_prodi)
            ->where('semester', $request->semester)
            ->where('id_kurikulum', $request->id_kurikulum)
            ->get();

        foreach ($mataKuliahs as $matkul) {
            $sisaMahasiswa = $jumlahMahasiswa;

            for ($i = 0; $i < $jumlahKelas; $i++) {
                $huruf = chr(65 + $i);
                $namaKelas = $prefix . '-' . $semester . $huruf;
                $kapasitasKelas = min(20, $sisaMahasiswa);

                Kelas::create([
                    'nama_kelas'       => $namaKelas,
                    'semester'         => $semester,
                    'kode_matkul'      => $matkul->kode_matkul,
                    'id_prodi'         => $idProdi,
                    'id_tahunakademik' => $idTahun,
                    'kapasitas'        => $kapasitasKelas,
                    'id_dosen'         => null,
                ]);

                $sisaMahasiswa -= $kapasitasKelas;
            }
        }

        return back()->with('success', 'Generate kelas berhasil.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = explode(',', $request->ids);

        Kelas::whereIn('id_kelas', $ids)->delete();

        return redirect()->back()
            ->with('success', 'Data kelas berhasil dihapus.');
    }
}