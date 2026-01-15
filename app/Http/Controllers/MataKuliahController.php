<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // Import Request
use App\Models\MataKuliah;
use App\Models\Kurikulum; // Import Kurikulum
use Illuminate\Validation\Rule;
use PDF; // Untuk export PDF
use Maatwebsite\Excel\Facades\Excel; // Untuk export Excel
use App\Exports\MataKuliahExport; // Untuk export Excel
use Illuminate\Support\Facades\Auth;

class MataKuliahController extends Controller
{
    /**
     * Menampilkan halaman daftar mata kuliah (dengan filter).
     */
    public function index(Request $request) // Tambahkan Request $request
    {
        // Mulai query
        $query = MataKuliah::query();

        $user = Auth::user();
        $userProdiName = null;

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

        // Terapkan filter jika ada
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        if ($request->filled('kurikulum')) {
            $query->where('id_kurikulum', $request->kurikulum);
        }

        if ($request->filled('prodi')) {
            $prodi = $request->prodi;
            if ($prodi == 'informatika') {
                $query->where('kode_matkul', 'like', '%INF%');
            } elseif ($prodi == 'sistem_informasi') {
                $query->where('kode_matkul', 'like', '%SIS%');
            } elseif ($prodi == 'rekayasa_komputer') {
                $query->where('kode_matkul', 'like', '%REK%');
            }
        }

// Ambil semua kurikulum untuk dropdown
        $kurikulums = Kurikulum::all(); // <-- PASTIKAN BARIS INI ADA

        // Paginate hasil query, dan tambahkan filter ke link pagination
        // USER REQUEST: Munculin semua data (limit diperbesar)
        $matkuls = $query->paginate(100)->appends($request->query());

        // Kirim data matkul DAN kurikulum ke view
        return view('management.matakuliah.index', [
            'matkuls' => $matkuls,
            'kurikulums' => $kurikulums, // <-- PASTIKAN $kurikulums DIKIRIM KE VIEW
            'userProdiName' => $userProdiName
        ]);
    }
    /**
     * Menyimpan mata kuliah baru ke database.
     */
    public function store(Request $request)
    {
        // Validasi data (termasuk id_kurikulum)
        $request->validate([
            'nama_matkul' => 'required|string|max:100',
            'kode_matkul' => [
                'required',
                'string',
                'max:20',
                Rule::unique('mata_kuliah', 'kode_matkul')
            ],
            'jumlah_sks' => 'required|integer|min:1',
            'tipe' => 'required|string|in:Teori,Praktikum',
            'semester' => 'required|integer|min:1|max:8',
            'id_kurikulum' => 'required|integer|exists:kurikulum,id_kurikulum' // Validasi kurikulum
        ]);

        // Simpan data (termasuk id_kurikulum)
        MataKuliah::create([
            'nama_matkul' => $request->nama_matkul,
            'kode_matkul' => $request->kode_matkul,
            'sks' => $request->jumlah_sks,
            'jenis' => strtolower($request->tipe),
            'semester' => $request->semester,
            'id_kurikulum' => $request->id_kurikulum, 
        ]);

        return redirect()->route('matakuliah.index')->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    /**
     * Memperbarui data mata kuliah.
     */
    public function update(Request $request, $kode_matkul)
    {
        $matkul = MataKuliah::where('kode_matkul', $kode_matkul)->firstOrFail();

        // Validasi
        $request->validate([
            'nama_matkul' => 'required|string|max:100',
            // Kode matkul bisa diubah, tapi harus unique kecuali punya sendiri
            'kode_matkul' => [
                'required',
                'string',
                'max:10', // Sesuai migration: string(10)
                Rule::unique('mata_kuliah', 'kode_matkul')->ignore($matkul->kode_matkul, 'kode_matkul')
            ],
            'jumlah_sks' => 'required|integer|min:1',
            'tipe' => 'required|string', // strtolower nanti
            'semester' => 'required|integer|min:1|max:8',
            'id_kurikulum' => 'required|integer|exists:kurikulum,id_kurikulum'
        ]);

        // Update Data
        $matkul->update([
            'nama_matkul' => $request->nama_matkul,
            'kode_matkul' => $request->kode_matkul,
            'sks' => $request->jumlah_sks,
            'jenis' => strtolower($request->tipe),
            'semester' => $request->semester,
            'id_kurikulum' => $request->id_kurikulum,
        ]);

        return response()->json(['message' => 'Mata kuliah berhasil diperbarui.']);
    }

    /**
     * Export data ke PDF.
     */
    public function exportPDF()
    {
        return Excel::download(new MataKuliahExport(true), 'daftar-mata-kuliah.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
    }

    /**
     * Menghapus mata kuliah dari database.
     */
    public function destroy($id)
    {
        $matkul = MataKuliah::findOrFail($id);
        $matkul->delete();

        return redirect()->route('matakuliah.index')->with('success', 'Mata kuliah berhasil dihapus.');
    }

    /**
     * Export data ke Excel.
     */
    public function exportExcel()
    {
        return Excel::download(new MataKuliahExport, 'daftar-mata-kuliah.xlsx');
    }
}
