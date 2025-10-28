<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dosen; // <-- Import model Dosen
use Illuminate\Validation\Rule;

class DosenController extends Controller
{
    /**
     * Menampilkan halaman daftar dosen.
     */
    public function index()
    {
        $dosens = Dosen::all();
        return view('management.dosen.index', [
            'dosens' => $dosens
        ]);
    }

    /**
     * Menyimpan dosen baru ke database.
     */
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
        ]);

        // Simpan data
        Dosen::create([
            'nama_dosen' => $request->nama_dosen,
            'nidn' => $request->nidn,
            'mata_kuliah' => $request->mata_kuliah,
        ]);

        // Kembali ke halaman index
        return redirect()->route('dosen.index')->with('success', 'Data dosen berhasil ditambahkan.');
    }
}
