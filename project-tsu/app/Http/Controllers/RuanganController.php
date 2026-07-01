<?php

namespace App\Http\Controllers;

use App\Models\Gedung;
use App\Models\Ruangan;
use Illuminate\Http\Request; 
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RuanganExport;

class RuanganController extends Controller
{
    /**
     * Menampilkan daftar semua ruangan, dengan filter pencarian dan pengelompokan.
     */
    public function index(Request $request) 
    {
        // Always retrieve all rooms for client-side search/filtering
        $ruangans = Ruangan::with('gedung')->orderBy('nama_ruang', 'asc')->get();
        $ruangansByGedung = $ruangans->groupBy('gedung.nama_gedung')->sortKeys();

        $gedungs = Gedung::orderBy('nama_gedung', 'asc')->get();
        $searchTerm = $request->input('search');

        return view('management.ruangan.index', [
            'ruangans' => $ruangans,
            'ruangansByGedung' => $ruangansByGedung,
            'gedungs' => $gedungs,
            'searchTerm' => $searchTerm
        ]);
    }

    // ... (Method create, store, edit, update, destroy Anda tidak berubah) ...
    // ... (Pastikan method lain tetap ada di sini) ...

    /**
     * Menampilkan form untuk menambah ruangan baru.
     */
    public function create()
    {
        abort_if(!auth()->user()->hasPermissionAccess('management_data', 'Ruangan', 'edit'), 403, 'Unauthorized action.');

        $gedungs = Gedung::orderBy('nama_gedung', 'asc')->get();
        return view('management.ruangan.create', compact('gedungs'));
    }

    /**
     * Menyimpan data ruangan baru ke database.
     */
    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionAccess('management_data', 'Ruangan', 'edit'), 403, 'Unauthorized action.');

        $request->validate([
            'nama_ruang' => 'required|string|max:50|unique:ruang,nama_ruang',
            'id_gedung' => 'required|integer|exists:gedung,id_gedung',
            'kapasitas' => 'required|integer|min:1',
            'fasilitas' => 'nullable|string',
        ]);
        Ruangan::create($request->all());
        return redirect()->route('ruangan.index')
                         ->with('success', 'Ruangan berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit ruangan.
     */
    public function edit(Ruangan $ruangan)
    {
        abort_if(!auth()->user()->hasPermissionAccess('management_data', 'Ruangan', 'edit'), 403, 'Unauthorized action.');

        $gedungs = Gedung::orderBy('nama_gedung', 'asc')->get();
        return view('management.ruangan.edit', compact('ruangan', 'gedungs'));
    }

    /**
     * Mengupdate data ruangan di database.
     */
    public function update(Request $request, Ruangan $ruangan)
    {
        abort_if(!auth()->user()->hasPermissionAccess('management_data', 'Ruangan', 'edit'), 403, 'Unauthorized action.');

        $request->validate([
            'nama_ruang' => 'required|string|max:50|unique:ruang,nama_ruang,' . $ruangan->id_ruang . ',id_ruang',
            'id_gedung' => 'required|integer|exists:gedung,id_gedung',
            'kapasitas' => 'required|integer|min:1',
            'fasilitas' => 'nullable|string',
        ]);
        $ruangan->update($request->all());
        return redirect()->route('ruangan.index')
                         ->with('success', 'Data ruangan berhasil diperbarui.');
    }

    /**
     * Menghapus data ruangan dari database.
     */
    public function destroy(Ruangan $ruangan)
    {
        abort_if(!auth()->user()->hasPermissionAccess('management_data', 'Ruangan', 'edit'), 403, 'Unauthorized action.');

        try {
            $ruangan->delete();
            return redirect()->route('ruangan.index')
                             ->with('success', 'Data ruangan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('ruangan.index')
                             ->with('error', 'Gagal menghapus data. Data mungkin still digunakan di jadwal.');
        }
    }

    public function exportExcel()
    {
        return Excel::download(new RuanganExport, 'daftar-ruangan.xlsx');
    }

    public function exportPdf()
    {
        return Excel::download(new RuanganExport(true), 'daftar-ruangan.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
    }
}
