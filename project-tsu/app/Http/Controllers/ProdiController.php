<?php

namespace App\Http\Controllers;

use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProdiController extends Controller
{
    public function index()
    {
        $prodis = Prodi::all();
        return view('management.prodi.index', [
            'prodis' => $prodis
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi
        $validated = $request->validate([
            'nama_prodi' => 'required|string|max:100|unique:program_studi,nama_prodi',
            'kode_prodi' => 'nullable|string|max:20|unique:program_studi,kode_prodi',
        ], [
            'nama_prodi.required' => 'Nama Prodi wajib diisi.',
            'nama_prodi.unique' => 'Nama Prodi ini sudah ada.',
            'kode_prodi.unique' => 'Kode Prodi ini sudah digunakan.',
        ]);

        // 2. Simpan
        Prodi::create([
            'nama_prodi' => $request->nama_prodi,
            'kode_prodi' => $request->kode_prodi,
        ]);

        // 3. Cek apakah request datang dari AJAX (JavaScript)
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Program Studi berhasil ditambahkan.'
            ]);
        }

        // Fallback untuk request biasa
        return redirect()->route('prodi.index')->with('success', 'Program Studi berhasil ditambahkan.');
    }

    // ... (method update, destroy, dll tetap sama) ...
public function update(Request $request, $id)
    {
        $prodi = Prodi::findOrFail($id);

        $request->validate([
            // Perhatikan penggunaan ignore() agar tidak error "Nama Prodi sudah ada" saat tidak diganti
            'nama_prodi' => [
                'required',
                'string',
                'max:100',
                Rule::unique('program_studi', 'nama_prodi')->ignore($prodi->id_prodi, 'id_prodi')
            ],
            'kode_prodi' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('program_studi', 'kode_prodi')->ignore($prodi->id_prodi, 'id_prodi')
            ],
        ]);

        $prodi->update($request->only('nama_prodi', 'kode_prodi'));

        // Pastikan return JSON jika request AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Program Studi berhasil diperbarui.'
            ]);
        }

        return redirect()->route('prodi.index')->with('success', 'Program Studi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            $prodi = Prodi::findOrFail($id);
            $prodi->delete();
            return redirect()->route('prodi.index')->with('success', 'Program Studi berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('prodi.index')->with('error', 'Gagal menghapus data.');
        }
    }
}
