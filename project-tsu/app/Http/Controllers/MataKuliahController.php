<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // Import Request
use App\Models\MataKuliah;
use App\Models\Kurikulum; // Import Kurikulum
use Illuminate\Validation\Rule;
use PDF; // Untuk export PDF
use Maatwebsite\Excel\Facades\Excel; // Untuk export Excel
use App\Exports\MataKuliahExport; // Untuk export Excel

class MataKuliahController extends Controller
{
    /**
     * Menampilkan halaman daftar mata kuliah (dengan filter).
     */
    public function index(Request $request) // Tambahkan Request $request
    {
        // Mulai query
        $query = MataKuliah::query();

        // Terapkan filter jika ada
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        if ($request->filled('kurikulum')) {
            $query->where('id_kurikulum', $request->kurikulum);
        }

// Ambil semua kurikulum untuk dropdown
        $kurikulums = Kurikulum::all(); // <-- PASTIKAN BARIS INI ADA

        // Paginate hasil query, dan tambahkan filter ke link pagination
        $matkuls = $query->paginate(30)->appends($request->query());

        // Kirim data matkul DAN kurikulum ke view
        return view('management.matakuliah.index', [
            'matkuls' => $matkuls,
            'kurikulums' => $kurikulums // <-- PASTIKAN $kurikulums DIKIRIM KE VIEW
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
            'jumlah_sks' => $request->jumlah_sks,
            'tipe' => $request->tipe,
            'semester' => $request->semester,
            'id_kurikulum' => $request->id_kurikulum, // Simpan kurikulum
        ]);

        return redirect()->route('matakuliah.index')->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    /**
     * Export data ke PDF.
     */
    public function exportPDF()
    {
        // Note: Export ini belum menerapkan filter, Anda bisa tambahkan logika filter di sini jika perlu
        $matkuls = MataKuliah::all();
        $pdf = PDF::loadView('management.matakuliah.export_pdf', [
            'matkuls' => $matkuls
        ]);
        return $pdf->download('daftar-mata-kuliah.pdf');
    }

    /**
     * Export data ke Excel.
     */
    public function exportExcel()
    {
        // Note: Export ini juga belum menerapkan filter
        return Excel::download(new MataKuliahExport, 'daftar-mata-kuliah.xlsx');
    }
}
