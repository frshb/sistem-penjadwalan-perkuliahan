<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prodi; // Pastikan Model Prodi di-import

class ProdiController extends Controller
{
    /**
     * Menampilkan daftar semua program studi.
     */
    public function index()
    {
        $prodis = Prodi::all();
        return view('management.prodi.index', [
            'prodis' => $prodis
        ]);
    }

    /**
     * Menyimpan program studi baru ke database.
     * INI ADALAH METHOD YANG HILANG
     */
    public function store(Request $request)
    {
        // 1. Validasi data yang masuk dari form
        $validatedData = $request->validate([
            'nama_prodi' => 'required|string|max:255',
            'kode_prodi' => 'required|string|max:50|unique:program_studi,kode_prodi',
        ]);

        // 2. Buat record baru di database
        //    (Ini bisa berjalan karena Anda sudah mengatur $fillable di Model Prodi)
        Prodi::create($validatedData);

        // 3. Kembalikan ke halaman index dengan pesan sukses
        return redirect()->route('prodi.index')->with('success', 'Data program studi berhasil ditambahkan.');
    }

    // Nanti Anda juga akan butuh method update() dan destroy()
    // untuk tombol Edit dan Hapus
}
