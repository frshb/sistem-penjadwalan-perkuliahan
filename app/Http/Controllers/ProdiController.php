<?php

namespace App\Http\Controllers;

use App\Models\Prodi; // Import Model Prodi
use Illuminate\Http\Request;


class ProdiController extends Controller
{

    public function index()
    {
        $prodis = Prodi::all();
        return view('management.prodi.index', [
            'prodis' => $prodis
        ]);
    }

    /**

     * Menyimpan program studi baru ke database. (CREATE)
     */
    public function store(Request $request)
    {
        // 1. Validasi data (id_prodi DIHAPUS dari sini)
        $request->validate([
            'nama_prodi' => 'required|string|max:100|unique:program_studi,nama_prodi',
            'kode_prodi' => 'nullable|string|max:20|unique:program_studi,kode_prodi',
        ], [
            'nama_prodi.required' => 'Nama Prodi wajib diisi.',
            'nama_prodi.unique' => 'Nama Prodi ini sudah ada.',
            'kode_prodi.unique' => 'Kode Prodi ini sudah digunakan.',
        ]);

        // 2. Buat data baru (id_prodi DIHAPUS dari sini)
        // Model 'creating' event akan mengisi id_prodi secara otomatis
        Prodi::create([
            'nama_prodi' => $request->nama_prodi,
            'kode_prodi' => $request->kode_prodi,
        ]);
        alert('Berhasil!', 'Program Studi berhasil ditambahkan.', 'success');
        // 3. Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('prodi.index');
    }
    /**
     * Menampilkan form untuk mengedit program studi. (UPDATE)
     * (Kita akan buat view baru untuk ini)
     */
    public function edit(Prodi $prodi)
    {
        // $prodi otomatis diambil oleh Laravel berdasarkan ID di URL
        return view('prodi.edit', compact('prodi'));
    }

    /**
     * Memperbarui data program studi di database. (UPDATE)
     */
    public function update(Request $request, Prodi $prodi)
    {
        // 1. Validasi data
        $request->validate([
            // Pastikan validasi unik mengabaikan data saat ini
            'nama_prodi' => 'required|string|max:100|unique:program_studi,nama_prodi,' . $prodi->id_prodi . ',id_prodi',
            'kode_prodi' => 'nullable|string|max:20|unique:program_studi,kode_prodi,' . $prodi->id_prodi . ',id_prodi',
        ], [
            'nama_prodi.required' => 'Nama Prodi wajib diisi.',
            'nama_prodi.unique' => 'Nama Prodi ini sudah ada.',
            'kode_prodi.unique' => 'Kode Prodi ini sudah digunakan.',
        ]);

        // 2. Update data
        $prodi->update([
            'nama_prodi' => $request->nama_prodi,
            'kode_prodi' => $request->kode_prodi,
        ]);

        alert('Berhasil!', 'Program Studi berhasil diperbarui.', 'success');
        // 3. Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('prodi.index');
    }

    /**
     * Menghapus program studi dari database. (DELETE)
     */
    public function destroy(Prodi $prodi)
    {
        try {
            $prodi->delete();

            alert('Berhasil!', 'Program Studi berhasil dihapus.', 'success');

            return redirect()->route('prodi.index');

        } catch (\Illuminate\Database\QueryException $e) {

            // Tangani error jika prodi tidak bisa dihapus (misal: karena terkait dengan tabel dosen)
            alert('Gagal!', 'Gagal menghapus Program Studi. Data ini mungkin terkait dengan data lain.', 'error');

            return redirect()->route('prodi.index');
        }
    }
}
