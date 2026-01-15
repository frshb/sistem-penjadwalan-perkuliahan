<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dosen; // <-- Import model Dosen
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DosenExport;
use Illuminate\Support\Facades\Auth;

class DosenController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userProdiName = null;
        $query = Dosen::query();

        if ($user && $user->isKaprodi()) {
            if ($user->id_prodi) {
                 $query->where('id_prodi', $user->id_prodi);
                 $userProdiName = $user->prodi->nama_prodi ?? '';
            } 
            elseif ($user->dosen && $user->dosen->id_prodi) {
                $query->where('id_prodi', $user->dosen->id_prodi);
                $userProdiName = $user->dosen->prodi->nama_prodi ?? ''; 
            }
        }

        $dosens = $query->paginate(10)->onEachSide(1);
        return view('management.dosen.index', [
            'dosens' => $dosens,
            'userProdiName' => $userProdiName
        ]);
    }


    public function store(Request $request)
    {
        // Validasi data
        $request->validate([
            'nama_dosen' => 'required|string|max:100',
            'nidn' => [
                'required',
                'string',
                'max:20',
                Rule::unique('dosen', 'nidn') // Pastikan NIDN unik
            ],
            'mata_kuliah' => 'nullable|string',
            'ketersediaan_waktu' => 'nullable|string',
        ]);

        // Simpan data
        Dosen::create([
            'nama_dosen' => $request->nama_dosen,
            'nidn' => $request->nidn,
            'mata_kuliah' => $request->mata_kuliah,
            'ketersediaan_waktu' => $request->ketersediaan_waktu,
        ]);

        // Kembali ke halaman index
        return redirect()->route('dosen.index')->with('success', 'Data dosen berhasil ditambahkan.');
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
                Rule::unique('dosen', 'nidn')->ignore($dosen->id_dosen, 'id_dosen')
            ],
            'ketersediaan_waktu' => 'nullable|string',
        ]);

        $dosen->update([
            'nama_dosen' => $request->nama_dosen,
            'nidn' => $request->nidn,
            'ketersediaan_waktu' => $request->ketersediaan_waktu,
        ]);

        return redirect()->route('dosen.index')->with('success', 'Data dosen berhasil diperbarui.');
    }

    /**
     * Menghapus data dosen.
     */
    public function destroy(Dosen $dosen)
    {
        $dosen->delete();
        return redirect()->back()->with('success', 'Data dosen berhasil dihapus.');
    }

    /**
     * Export data ke Excel.
     */
    public function exportExcel()
    {
        return Excel::download(new DosenExport, 'daftar-dosen.xlsx');
    }

    public function exportPdf()
    {
        return Excel::download(new DosenExport(true), 'daftar-dosen.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
    }
}
