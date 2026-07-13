<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // Import Request
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\Kurikulum; // Import Kurikulum
use App\Models\Ruangan;
use PDF; // Untuk export PDF
use Maatwebsite\Excel\Facades\Excel; // Untuk export Excel
use App\Exports\MataKuliahExport; // Untuk export Excel
use Illuminate\Support\Facades\Auth;
use App\Helpers\ProdiFilter;

class MataKuliahController extends Controller
{
    /**
     * Menampilkan halaman daftar mata kuliah (dengan filter).
     */
    public function index(Request $request)
    {
        $user        = Auth::user();
        $prodiId     = ProdiFilter::getProdiId(); // ✅
        $searchTerm  = $request->input('search');

        $query = MataKuliah::with(['kurikulum', 'program_studi', 'ruangans']);

        if ($prodiId) {
            $query->where('id_prodi', $prodiId);
        }

        $matkuls   = $query->orderBy('semester')->orderBy('nama_matkul')->get();
        $kurikulums = Kurikulum::all();
        $ruangans   = Ruangan::all();
        $prodis     = $prodiId ? Prodi::where('id_prodi', $prodiId)->where('id_prodi', '!=', 99)->get()
                            : Prodi::where('id_prodi', '!=', 99)->get();

        $userProdiName = $prodiId ? ($prodis->first()->nama_prodi ?? '') : null;

        return view('management.matakuliah.index', compact(
            'matkuls', 'kurikulums', 'prodis', 'ruangans', 'userProdiName', 'searchTerm'
        ));
    }
    /**
     * Menyimpan mata kuliah baru ke database.
     */
    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionAccess('management_data', 'Mata Kuliah', 'edit'), 403, 'Unauthorized action.');

        // Validasi data (termasuk id_kurikulum)
        $request->validate([
            'nama_matkul' => 'required|string|max:100',
            'kode_matkul' => 'required|string|max:20',
            'jumlah_sks' => 'required|integer|min:1',
            'tipe' => 'required|string',
            'semester' => 'required|integer|min:1|max:8',
            'id_kurikulum' => 'required|integer|exists:kurikulum,id_kurikulum', // Validasi kurikulum
            'id_prodi' => 'required|integer|exists:program_studi,id_prodi', // Validasi program studi
            'ruangan_ids' => 'nullable|array',
            'ruangan_ids.*' => 'exists:ruang,id_ruang',
            'konsentrasi' => 'nullable|string|max:100',
            'sifat' => 'required|string|in:W,P',
        ]);

        // Simpan data (termasuk id_kurikulum)
        $matkul = MataKuliah::create([
            'nama_matkul' => $request->nama_matkul,
            'kode_matkul' => $request->kode_matkul,
            'sks' => $request->jumlah_sks,
            'jenis' => strtolower($request->tipe),
            'semester' => $request->semester,
            'id_kurikulum' => $request->id_kurikulum,
            'id_prodi' => $request->id_prodi,
            'konsentrasi' => $request->konsentrasi ?: null,
            'sifat' => $request->sifat,
        ]);
         // SIMPAN RELASI RUANGAN
        $matkul->ruangans()->sync($request->ruangan_ids ?? []);


        return redirect()->route('matakuliah.index')->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    /**
     * Memperbarui data mata kuliah.
     */
    public function update(Request $request, $id_matakuliah)
    {
        abort_if(!auth()->user()->hasPermissionAccess('management_data', 'Mata Kuliah', 'edit'), 403, 'Unauthorized action.');

        $matkul = MataKuliah::findOrFail($id_matakuliah);

        // Validasi
        $request->validate([
            'nama_matkul' => 'required|string|max:100',
            // Kode matkul bisa diubah, tapi harus unique kecuali punya sendiri
            'kode_matkul' => 'required|string|max:20',
            'jumlah_sks' => 'required|integer|min:1',
            'tipe' => 'required|string', // strtolower nanti
            'semester' => 'required|integer|min:1|max:8',
            'id_kurikulum' => 'required|integer|exists:kurikulum,id_kurikulum',
            'id_prodi' => 'required|integer|exists:program_studi,id_prodi',
            'ruangan_ids' => 'nullable|array',
            'ruangan_ids.*' => 'exists:ruang,id_ruang',
            'konsentrasi' => 'nullable|string|max:100',
            'sifat' => 'required|string|in:W,P',
        ]);

        // Update Data
        $matkul->update([
            'nama_matkul' => $request->nama_matkul,
            'kode_matkul' => $request->kode_matkul,
            'sks' => $request->jumlah_sks,
            'jenis' => strtolower($request->tipe),
            'semester' => $request->semester,
            'id_kurikulum' => $request->id_kurikulum,
            'id_prodi' => $request->id_prodi,
            'konsentrasi' => $request->konsentrasi ?: null,
            'sifat' => $request->sifat,
        ]);
        $matkul->ruangans()->sync($request->ruangan_ids ?? []);

        return response()->json(['message' => 'Mata kuliah berhasil diperbarui.']);
    }

    /**
     * Export data ke PDF.
     */
    public function exportPDF()
    {
        $user = Auth::user();
        $prodiId = ($user && !$user->isAdmin() && !$user->isDekan()) ? $user->getProdiId() : null;
        return Excel::download(new MataKuliahExport(true, $prodiId), 'daftar-mata-kuliah.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
    }

    /**
     * Menghapus mata kuliah dari database.
     */
    public function destroy($id_matakuliah)
    {
        abort_if(!auth()->user()->hasPermissionAccess('management_data', 'Mata Kuliah', 'edit'), 403, 'Unauthorized action.');

        $matkul = MataKuliah::findOrFail($id_matakuliah);

        $matkul->delete();

        return redirect()
            ->route('matakuliah.index')
            ->with('success', 'Mata kuliah berhasil dihapus.');
    }

    /**
     * Export data ke Excel.
     */
    public function exportExcel()
    {
        $user = Auth::user();
        $prodiId = ($user && !$user->isAdmin() && !$user->isDekan()) ? $user->getProdiId() : null;
        return Excel::download(new MataKuliahExport(false, $prodiId), 'daftar-mata-kuliah.xlsx');
    }
}
