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
        if (auth()->check() && auth()->user()->isKaprodi()) {
            abort(403, 'Unauthorized action.');
        }

        // ambil id tahun akademik dari URL
        $idTahun = $request->tahun;

        // jika tidak ada tahun dipilih
        if (!$idTahun) {
            return redirect()->route('kelas.pilih-tahun');
        }

        $searchTerm = $request->input('search');

        // query
        $query = Kelas::with([
            'prodi',
            'tahunAkademik',
            'matakuliah',
            'dosen'
        ])
        ->where('id_tahunakademik', $idTahun);

        // search
        if ($searchTerm) {
            $query->where('nama_kelas', 'like', '%' . $searchTerm . '%');
        }

        $kelas = $query
            ->orderBy('semester')
            ->orderBy('nama_kelas')
            ->get();

        // grouping prodi
        $kelasByProdi = $kelas
            ->groupBy(fn($item) => $item->prodi->nama_prodi ?? 'Tanpa Prodi')
            ->sortKeys();

        // data dropdown
        $prodis = Prodi::orderBy('nama_prodi')->get();

        $kurikulums = Kurikulum::orderBy(
            'nama_kurikulum'
        )->get();

        $tahunAkademiks = TahunAkademik::orderBy('tahun_ajaran', 'desc')->get();

        // tahun aktif dipilih
        $tahunAkademik = TahunAkademik::find($idTahun);

        $mataKuliahs = MataKuliah::with([
            'pengampus.dosen'
        ])->orderBy('nama_matkul')->get();

        return view('management.kelas.index', compact(
            'kelas',
            'kelasByProdi',
            'searchTerm',
            'prodis',
            'tahunAkademiks',
            'tahunAkademik',
            'mataKuliahs',
            'kurikulums'
        ));
    }

    /**
     * Form tambah kelas.
     */
    public function create()
    {
        $prodis = Prodi::orderBy('nama_prodi', 'asc')->get();
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
                'nama_kelas' => 'required|string|max:50',
                'id_prodi' => 'required|integer|exists:program_studi,id_prodi',
                'id_tahunakademik' => 'required|integer|exists:tahun_akademik,id_tahunakademik',
                'kode_matkul' => 'required|exists:mata_kuliah,kode_matkul',
                'id_dosen' => 'required|exists:dosen,id_dosen',
                'kapasitas' => 'required|integer|min:1',
                'semester' => 'required|integer|min:1|max:14',
            ]);

            Kelas::create([
                'nama_kelas' => $request->nama_kelas,
                'id_prodi' => $request->id_prodi,
                'id_tahunakademik' => $request->id_tahunakademik,
                'kode_matkul' => $request->kode_matkul,
                'id_dosen' => $request->id_dosen,
                'kapasitas' => $request->kapasitas,
                'semester' => $request->semester,
            ]);

            return redirect()->route('kelas.index', [
                'tahun' => $request->id_tahunakademik
            ])
            ->with(
                'success',
                'Data kelas berhasil ditambahkan.'
            );
        }

    /**
     * Form edit kelas.
     */
    public function edit(Kelas $kela)
    {
        $prodis = Prodi::orderBy('nama_prodi', 'asc')->get();
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
                'nama_kelas' => 'required|string|max:50',
                'id_prodi' => 'required|integer|exists:program_studi,id_prodi',
                'id_tahunakademik' => 'required|integer|exists:tahun_akademik,id_tahunakademik',
                'kode_matkul' => 'required|exists:mata_kuliah,kode_matkul',
                'id_dosen' => 'required|exists:dosen,id_dosen',
                'kapasitas' => 'required|integer|min:1',
                'semester' => 'required|integer|min:1|max:14',
            ]);

            $kela->update([
                'nama_kelas' => $request->nama_kelas,
                'id_prodi' => $request->id_prodi,
                'id_tahunakademik' => $request->id_tahunakademik,
                'kode_matkul' => $request->kode_matkul,
                'id_dosen' => $request->id_dosen,
                'kapasitas' => $request->kapasitas,
                'semester' => $request->semester,
            ]);

            return redirect()->route('kelas.index', [
                'tahun' => $request->id_tahunakademik
            ])
            ->with(
                'success',
                'Data kelas berhasil diperbarui.'
            );
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
                ])
                ->with(
                    'success',
                    'Data kelas berhasil dihapus.'
                );

            } catch (\Exception $e) {

                return redirect()->back()
                    ->with(
                        'error',
                        'Gagal menghapus data. Kelas mungkin masih digunakan.'
                    );
            }
        }

    /**
     * Export Excel.
     */
    public function exportExcel()
    {
        return Excel::download(new KelasExport, 'daftar-kelas.xlsx');
    }

    /**
     * Export PDF.
     */
    public function exportPdf()
    {
        return Excel::download(
            new KelasExport(true),
            'daftar-kelas.pdf',
            \Maatwebsite\Excel\Excel::DOMPDF
        );
    }

    public function generate(Request $request)
    {
        $request->validate([
            'id_prodi' => 'required',
            'semester' => 'required',
            'id_kurikulum' => 'required|exists:kurikulum,id_kurikulum',
            'jumlah_mahasiswa' => 'required|integer|min:1',
            'id_tahunakademik' => 'required',
        ]);

        $idProdi = $request->id_prodi;
        $semester = $request->semester;
        $jumlahMahasiswa = $request->jumlah_mahasiswa;
        $idTahun = $request->id_tahunakademik;

        // hitung jumlah kelas
        $jumlahKelas = ceil($jumlahMahasiswa / 20);

        // ambil prodi
        $prodi = Prodi::findOrFail($idProdi);

        $prefix = match(strtolower($prodi->nama_prodi)) {

            's1 sistem informasi' => 'A1',
            'sistem informasi' => 'A1',

            's1 informatika' => 'A2',
            'informatika' => 'A2',

            's1 rekayasa komputer' => 'A3',
            'rekayasa komputer' => 'A3',

            default => 'AX'
        };

        // ambil semua matkul semester tersebut
        $mataKuliahs = MataKuliah::where('id_prodi', $request->id_prodi)
            ->where('semester', $request->semester)
            ->where('id_kurikulum', $request->id_kurikulum)
            ->get();

        foreach ($mataKuliahs as $matkul) {

            for ($i = 0; $i < $jumlahKelas; $i++) {

                $huruf = chr(65 + $i);

                $namaKelas = $prefix . '-' . $semester . $huruf;

                Kelas::create([
                    'nama_kelas' => $namaKelas,
                    'semester' => $semester,
                    'kode_matkul' => $matkul->kode_matkul,
                    'id_prodi' => $idProdi,
                    'id_tahunakademik' => $idTahun,
                    'kapasitas' => 20,
                    'id_dosen' => null,
                ]);
            }
        }

        return back()->with(
            'success',
            'Generate kelas berhasil.'
        );
    }
}
