<?php

namespace App\Http\Controllers;

use App\Models\Prodi;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProdiExport;

class ProdiController extends Controller
{
    public function index()
    {
        $prodis = Prodi::all();
        return view('management.prodi.index', [
            'prodis' => $prodis
        ]);
    }

    public function show($id)
    {
        $prodi = Prodi::with('kurikulums')->findOrFail($id);
        


        if (stripos($prodi->nama_prodi, 'Informatika') !== false) {
            $generalInfo = [
                'kode' => '55202',
                'akreditasi' => 'Baik',
                'rasio_dosen_mhs' => '1:8.88',
                'sk_selenggara' => '42/A/O/2025',
                'biaya_kuliah' => 'Rp300.000 - 6.250.000',
                'akreditasi_int' => '-',
                'rasio_terima_daftar' => '1:3',
                'tgl_sk' => '10 Januari 2025',
                'tgl_berdiri' => '10 Januari 2025',
                'telp' => '0271-716500',
            ];

            $dosenHomebase = [];
            $dosenRasio = [];
        } else {

            $generalInfo = [
                'kode' => $prodi->kode_prodi ?? '-',
                'akreditasi' => '-',
                'rasio_dosen_mhs' => '-',
                'sk_selenggara' => '-',
                'biaya_kuliah' => '-',
                'akreditasi_int' => '-',
                'rasio_terima_daftar' => '-',
                'tgl_sk' => '-',
                'tgl_berdiri' => '-',
                'telp' => '-',
            ];
            $dosenHomebase = [];
            $dosenRasio = [];
        }

        $matkuls = \App\Models\MataKuliah::all();
        $dosens = \App\Models\Dosen::all();

        return view('management.prodi.detail', [
            'prodi' => $prodi,
            'info' => $generalInfo,
            'matkuls' => $matkuls,
            'dosens' => $dosens
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

    public function storeKurikulum(Request $request, $id)
    {
        $prodi = Prodi::findOrFail($id);

        $request->validate([
            'nama_kurikulum' => 'required|string|max:50',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'kelas' => 'nullable|string|max:50',
            'matkul' => 'nullable|array',
            'matkul.*' => 'string|max:255',
            'dosen_pengampu' => 'nullable|array',
            'dosen_pengampu.*' => 'string|max:255',
        ]);

        Kurikulum::create([
            'id_prodi' => $prodi->id_prodi,
            'nama_kurikulum' => $request->nama_kurikulum,
            'status' => $request->status,
            'kelas' => $request->kelas,
            'matkul' => $request->matkul,
            'dosen_pengampu' => $request->dosen_pengampu,
        ]);

        return redirect()->route('prodi.show', $id)->with('success', 'Kurikulum berhasil ditambahkan.');
    }

    public function updateKurikulum(Request $request, $id, $id_kurikulum)
    {
        $prodi = Prodi::findOrFail($id);
        $kurikulum = Kurikulum::where('id_prodi', $prodi->id_prodi)->findOrFail($id_kurikulum);

        $request->validate([
            'nama_kurikulum' => 'required|string|max:50',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'kelas' => 'nullable|string|max:50',
            'matkul' => 'nullable|array',
            'matkul.*' => 'string|max:255',
            'dosen_pengampu' => 'nullable|array',
            'dosen_pengampu.*' => 'string|max:255',
        ]);

        $kurikulum->update([
            'nama_kurikulum' => $request->nama_kurikulum,
            'status' => $request->status,
            'kelas' => $request->kelas,
            'matkul' => $request->matkul,
            'dosen_pengampu' => $request->dosen_pengampu,
        ]);

        return redirect()->route('prodi.show', $id)->with('success', 'Kurikulum berhasil diperbarui.');
    }

    public function destroyKurikulum($id, $id_kurikulum)
    {
        $prodi = Prodi::findOrFail($id);
        $kurikulum = Kurikulum::where('id_prodi', $prodi->id_prodi)->findOrFail($id_kurikulum);
        $kurikulum->delete();

        return redirect()->route('prodi.show', $id)->with('success', 'Kurikulum berhasil dihapus.');
    }

    public function exportExcel()
    {
        return Excel::download(new ProdiExport, 'daftar-prodi.xlsx');
    }

    public function exportPdf()
    {
        $prodis = Prodi::all();
        $pdf = \PDF::loadView('exports.prodi', [
            'prodis' => $prodis,
            'isPdf' => true
        ])->setPaper('a4', 'portrait');
        return $pdf->download('daftar-prodi.pdf');
    }
}
