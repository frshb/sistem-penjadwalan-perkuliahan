<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\Kurikulum;
use App\Models\MataKuliah;
use App\Models\PengampuMatkul;
use App\Models\TahunAkademik;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DosenExport;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ProdiFilter;


class DosenController extends Controller
{
    public function index(Request $request)
    {
        $user        = Auth::user();
        $prodiId     = ProdiFilter::getProdiId();
        $searchTerm  = $request->search;

        $query = Dosen::with(['prodi']);

        if ($prodiId) {
            $query->whereIn('id_prodi', [$prodiId, 99]);
        }

        $dosens = $query->orderBy('nama_dosen')->get();

        // Dropdown data — dibatasi sesuai prodi jika kaprodi/dosen (tambahkan pilihan Dosen Eksternal)
        $prodis      = $prodiId ? Prodi::whereIn('id_prodi', [$prodiId, 99])->get()
                                : Prodi::all();
        $mataKuliahs = $prodiId ? MataKuliah::where('id_prodi', $prodiId)->get()
                                : MataKuliah::all();
        $kurikulums  = Kurikulum::all();

        // Nama prodi untuk ditampilkan di header (jika kaprodi/dosen)
        $userProdiName = $prodiId ? (Prodi::where('id_prodi', $prodiId)->first()->nama_prodi ?? '') : null;

        return view('management.dosen.index', compact(
            'dosens', 'userProdiName', 'prodis', 'kurikulums', 'mataKuliahs', 'searchTerm'
        ));
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionAccess('management_data', 'Dosen', 'edit'), 403, 'Unauthorized action.');

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
            $activeTahun = TahunAkademik::where('status_aktif', 1)->first();
            $idTahun = $activeTahun ? $activeTahun->id_tahunakademik : null;

            foreach ($request->mata_kuliah as $kodeMatkul) {
                $mk = MataKuliah::where('kode_matkul', $kodeMatkul)->first();

                PengampuMatkul::create([
                    'id_dosen' => $dosen->id_dosen,
                    'kode_matkul' => $kodeMatkul,
                    'id_prodi' => $mk->id_prodi ?? null,
                    'id_tahunakademik' => $idTahun,
                ]);
            }
        }

        return redirect()
            ->route('dosen.index')
            ->with('success', 'Data dosen berhasil ditambahkan.');
    }

    public function update(Request $request, $id_dosen)
    {
        abort_if(!auth()->user()->hasPermissionAccess('management_data', 'Dosen', 'edit'), 403, 'Unauthorized action.');

        $dosen = Dosen::findOrFail($id_dosen);

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

        $activeTahun = TahunAkademik::where('status_aktif', 1)->first();
        $idTahun = $activeTahun ? $activeTahun->id_tahunakademik : null;

        PengampuMatkul::where('id_dosen', $dosen->id_dosen)
            ->where('id_tahunakademik', $idTahun)
            ->delete();

        if ($request->mata_kuliah) {

            foreach ($request->mata_kuliah as $kodeMatkul) {
                $mk = MataKuliah::where('kode_matkul', $kodeMatkul)->first();

                PengampuMatkul::create([
                    'id_dosen' => $dosen->id_dosen,
                    'kode_matkul' => $kodeMatkul,
                    'id_prodi' => $mk->id_prodi ?? null,
                    'id_tahunakademik' => $idTahun,
                ]);
            }
        }

        return redirect()
            ->route('dosen.index', ['page' => $request->page])
            ->with('success', 'Data dosen berhasil diperbarui.');
    }

    public function destroy(Request $request, Dosen $dosen)
    {
        abort_if(!auth()->user()->hasPermissionAccess('management_data', 'Dosen', 'edit'), 403, 'Unauthorized action.');

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
        $user = Auth::user();
        $prodiId = ($user && !$user->isAdmin() && !$user->isDekan()) ? $user->getProdiId() : null;
        return Excel::download(new DosenExport(false, $prodiId), 'daftar-dosen.xlsx');
    }

    public function exportPdf()
    {
        $user = Auth::user();
        $prodiId = ($user && !$user->isAdmin() && !$user->isDekan()) ? $user->getProdiId() : null;
        return Excel::download(
            new DosenExport(true, $prodiId),
            'daftar-dosen.pdf',
            \Maatwebsite\Excel\Excel::DOMPDF
        );
    }
}
