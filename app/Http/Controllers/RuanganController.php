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
        // Kaprodi tidak boleh akses
        if (auth()->check() && auth()->user()->isKaprodi()) {
            abort(403, 'Unauthorized action.');
        }

        // 1. Ambil kata kunci pencarian
        $searchTerm = $request->input('search');

        // 2. Mulai query builder
        $query = Ruangan::with('gedung');

        // 3. Terapkan filter pencarian HANYA jika searchTerm ada
        if ($searchTerm) {
            $query->where('nama_ruang', 'like', '%' . $searchTerm . '%');
        }

        // 4. Ambil semua data (yang sudah difilter atau belum)
        $ruangans = $query->orderBy('nama_ruang', 'asc')->get();

        // 5. Buat koleksi untuk pengelompokan
        $ruangansByGedung = collect();

        // 6. HANYA lakukan pengelompokan jika TIDAK ADA PENCARIAN
        if (!$searchTerm) {
            $ruangansByGedung = $ruangans->groupBy('gedung.nama_gedung')->sortKeys();
        }

        // 6.b Ambil data gedung untuk dropdown filter/modal
        $gedungs = Gedung::orderBy('nama_gedung', 'asc')->get();

        // 7. Kirim SEMUA data ke view
        return view('management.ruangan.index', [
            'ruangans' => $ruangans, // Untuk hasil pencarian
            'ruangansByGedung' => $ruangansByGedung, // Untuk tampilan tab
            'searchTerm' => $searchTerm, // Untuk menampilkan value di input search
            'gedungs' => $gedungs, // Data gedung untuk modal
        ]);
    }

    // ... (Method create, store, edit, update, destroy Anda tidak berubah) ...
    // ... (Pastikan method lain tetap ada di sini) ...

    /**
     * Menampilkan form untuk menambah ruangan baru.
     */
    public function create()
    {
        $gedungs = Gedung::orderBy('nama_gedung', 'asc')->get();
        return view('management.ruangan.create', compact('gedungs'));
    }

    /**
     * Menyimpan data ruangan baru ke database.
     */
    public function store(Request $request)
    {
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
        $gedungs = Gedung::orderBy('nama_gedung', 'asc')->get();
        return view('management.ruangan.edit', compact('ruangan', 'gedungs'));
    }

    /**
     * Mengupdate data ruangan di database.
     */
    public function update(Request $request, Ruangan $ruangan)
    {
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
        $ruangans = Ruangan::with('gedung')->orderBy('id_gedung')->get();
        $pdf = \PDF::loadView('exports.ruangan', [
            'ruangans' => $ruangans,
            'isPdf' => true
        ])->setPaper('a4', 'landscape');
        return $pdf->download('daftar-ruangan.pdf');
    }
}
