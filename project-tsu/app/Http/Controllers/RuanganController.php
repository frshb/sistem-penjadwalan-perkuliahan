<?php

namespace App\Http\Controllers;

use App\Models\Gedung;   // <-- Tambahkan ini
use App\Models\Ruangan;  // <-- Tambahkan ini
use Illuminate\Http\Request;

class RuanganController extends Controller
{
    /**
     * Menampilkan daftar semua ruangan.
     */
    public function index()
    {
        // Ambil data ruangan DAN data gedung terkait (Eager Loading)
        $ruangans = Ruangan::with('gedung')->orderBy('nama_ruang', 'asc')->get();

        // Kirim data ke view
        return view('management.ruangan.index', compact('ruangans'));
    }

    /**
     * Menampilkan form untuk menambah ruangan baru.
     */
    public function create()
    {
        // Ambil data gedung untuk dropdown/select
        $gedungs = Gedung::orderBy('nama_gedung', 'asc')->get();
        return view('management.ruangan.create', compact('gedungs'));
    }

    /**
     * Menyimpan data ruangan baru ke database.
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama_ruang' => 'required|string|max:50|unique:ruang,nama_ruang',
            'id_gedung' => 'required|integer|exists:gedung,id_gedung',
            'kapasitas' => 'required|integer|min:1',
            'fasilitas' => 'nullable|string',
        ]);

        // Simpan data baru
        Ruangan::create($request->all());

        // Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('ruangan.index')
                         ->with('success', 'Ruangan berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit ruangan.
     */
    public function edit(Ruangan $ruangan) // <-- Route Model Binding
    {
        // $ruangan otomatis terisi berdasarkan ID dari URL
        $gedungs = Gedung::orderBy('nama_gedung', 'asc')->get();
        return view('management.ruangan.edit', compact('ruangan', 'gedungs'));
    }

    /**
     * Mengupdate data ruangan di database.
     */
    public function update(Request $request, Ruangan $ruangan)
    {
        // Validasi input
        $request->validate([
            // 'unique' harus mengabaikan ID saat ini
            'nama_ruang' => 'required|string|max:50|unique:ruang,nama_ruang,' . $ruangan->id_ruang . ',id_ruang',
            'id_gedung' => 'required|integer|exists:gedung,id_gedung',
            'kapasitas' => 'required|integer|min:1',
            'fasilitas' => 'nullable|string',
        ]);

        // Update data
        $ruangan->update($request->all());

        // Redirect kembali ke halaman index dengan pesan sukses
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
            // Tangani jika ada error (misal: foreign key constraint)
            return redirect()->route('ruangan.index')
                             ->with('error', 'Gagal menghapus data. Data mungkin masih digunakan di jadwal.');
        }
    }
}
