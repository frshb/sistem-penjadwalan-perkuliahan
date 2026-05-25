<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\Kurikulum;
use App\Models\MataKuliah;
use App\Models\PengampuMatkul;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DosenExport;
use Illuminate\Support\Facades\Auth;


class DosenController extends Controller
{
    /**
     * Menampilkan halaman daftar dosen.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userProdiName = null;
        $searchTerm = $request->search;
        $query = Dosen::with(['prodi', 'mataKuliahs']);
        if ($request->filled('search')) {

            $query->where(
                'nama_dosen',
                'like',
                '%' . $request->search . '%'
            );

        }

        if ($user && $user->isKaprodi()) {
            if ($user->id_prodi) {
                $query->where('id_prodi', $user->id_prodi);
                $userProdiName = $user->prodi->nama_prodi ?? '';
            } elseif ($user->dosen && $user->dosen->id_prodi) {
                $query->where('id_prodi', $user->dosen->id_prodi);
                $userProdiName = $user->dosen->prodi->nama_prodi ?? '';
            }
        }

        $dosens = $query
            ->paginate(10)
            ->appends($request->query())
            ->onEachSide(1);
        $prodis = Prodi::all();
        $kurikulums = Kurikulum::all();
        $mataKuliahs = MataKuliah::orderBy('semester')->get();

        return view('management.dosen.index', compact('dosens', 'userProdiName', 'prodis', 'kurikulums', 'mataKuliahs', 'searchTerm'));
    }

    /**
     * Menyimpan dosen baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_dosen' => 'required|string|max:100',
            'nidn' => [
                'required',
                'string',
                'max:20',
                Rule::unique('dosen', 'nidn'),
            ],
            'nuptk' => [
                'required',
                'string',
                'max:20',
                Rule::unique('dosen', 'nuptk'),
            ],
            'id_prodi' => 'required|exists:program_studi,id_prodi',

            'mata_kuliah' => 'nullable|array',
            'mata_kuliah.*' => 'exists:mata_kuliah,kode_matkul',
        ]);

        $dosen = Dosen::create([
            'nama_dosen' => $request->nama_dosen,
            'nidn' => $request->nidn,
            'nuptk' => $request->nuptk,
            'id_prodi' => $request->id_prodi,
        ]);

        if ($request->mata_kuliah) {

            foreach ($request->mata_kuliah as $kodeMatkul) {

                PengampuMatkul::create([
                    'id_dosen' => $dosen->id_dosen,
                    'kode_matkul' => $kodeMatkul,
                ]);
            }
        }

        return redirect()
            ->route('dosen.index')
            ->with('success', 'Data dosen berhasil ditambahkan.');
    }

    /**
     * Memperbarui data dosen.
     */
    public function update(Request $request, Dosen $dosen)
    {
        $request->validate([
            'nama_dosen' => 'required|string|max:100',
            'nidn' => [
                'required',
                'string',
                'max:20',
                Rule::unique('dosen', 'nidn')
                    ->ignore($dosen->id_dosen, 'id_dosen'),
            ],
            'nuptk' => [
                'required',
                'string',
                'max:20',
                Rule::unique('dosen', 'nuptk')
                    ->ignore($dosen->id_dosen, 'id_dosen'),
            ],

            'id_prodi' => 'required|exists:program_studi,id_prodi',

            'mata_kuliah' => 'nullable|array',
            'mata_kuliah.*' => 'exists:mata_kuliah,kode_matkul',
        ]);

        $dosen->update([
            'nama_dosen' => $request->nama_dosen,
            'nidn' => $request->nidn,
            'nuptk' => $request->nuptk,
            'id_prodi' => $request->id_prodi,
        ]);

        PengampuMatkul::where(
            'id_dosen',
            $dosen->id_dosen
        )->delete();

        if ($request->mata_kuliah) {

            foreach ($request->mata_kuliah as $kodeMatkul) {

                PengampuMatkul::create([
                    'id_dosen' => $dosen->id_dosen,
                    'kode_matkul' => $kodeMatkul,
                ]);
            }
        }

        return redirect()
            ->route('dosen.index', ['page' => $request->page])
            ->with('success', 'Data dosen berhasil diperbarui.');
    }

    /**
     * Menghapus data dosen.
     */
    public function destroy(Request $request, Dosen $dosen)
    {
        $dosen->delete();

        return redirect()
            ->route('dosen.index', [
                'page' => $request->page
            ])
            ->with('success', 'Data dosen berhasil dihapus.');
    }

    /**
     * Export Excel.
     */
    public function exportExcel()
    {
        return Excel::download(new DosenExport, 'daftar-dosen.xlsx');
    }

    /**
     * Export PDF.
     */
    public function exportPdf()
    {
        return Excel::download(
            new DosenExport(true),
            'daftar-dosen.pdf',
            \Maatwebsite\Excel\Excel::DOMPDF
        );
    }
}
