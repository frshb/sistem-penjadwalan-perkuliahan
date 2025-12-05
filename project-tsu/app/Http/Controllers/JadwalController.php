<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prodi;
use App\Models\MataKuliah;
use App\Models\Dosen;
use App\Models\Ruangan;
use App\Models\Jadwal;

class JadwalController extends Controller
{
    /**
     * Menampilkan halaman modul penjadwalan.
     * Ini akan mengambil semua data yang diperlukan untuk dropdown manual.
     */
    public function index(Request $request)
    {
        // 1. Ambil data Prodi (Contoh: Ambil prodi S1 Informatika)
        // Anda bisa kembangkan ini dengan filter prodi nanti
        $prodi = Prodi::where('nama_prodi', 'S1 Teknik Informatika')->first();
        if (!$prodi) {
            // Fallback jika S1 TI tidak ditemukan
            $prodi = Prodi::first();
        }

        // 2. Ambil semua mata kuliah yang perlu dijadwalkan
        // Di-filter berdasarkan prodi dan semester (contoh: ganjil)
        $matakuliahs = MataKuliah::where('id_prodi', $prodi->id_prodi)
                            ->whereIn('semester', [1, 3, 5, 7]) // Asumsi semester ganjil
                            ->orderBy('semester')
                            ->get();

        // 3. Ambil semua sumber daya untuk dropdown
        $dosens = Dosen::all();
        $ruangans = Ruangan::all();

        // 4. Data statis untuk Hari & Jam
        $haris = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $jams = [
            '08:00 - 09:40',
            '09:40 - 11:20',
            '13:10 - 14:50',
            '14:50 - 16:30'
            // Tambahkan jam lain jika perlu
        ];

        // 5. Ambil jadwal yang sudah ada (jika ada)
        $jadwalDibuat = Jadwal::where('id_prodi', $prodi->id_prodi)
                            ->get()
                            // Gunakan kode_matkul sebagai key agar mudah dicari di view
                            ->keyBy(function ($item) {
                                // Pastikan relasi mataKuliah ada sebelum mengaksesnya
                                return $item->mataKuliah ? $item->mataKuliah->kode_matkul : null;
                            })->filter(); // Filter null keys

        return view('penjadwalan.index', [
            'prodi' => $prodi,
            'matakuliahs' => $matakuliahs,
            'dosens' => $dosens,
            'ruangans' => $ruangans,
            'haris' => $haris,
            'jams' => $jams,
            'jadwalDibuat' => $jadwalDibuat,
        ]);
    }

    /**
     * Tombol "Buat Jadwal" (Genetika) - INI HANYA PLACEHOLDER
     * Logika Algoritma Genetika sangat kompleks dan harus dibuat terpisah.
     */
    public function generateGA(Request $request)
    {
        // ... (LOGIKA ALGORITMA GENETIKA ANDA YANG KOMPLEKS ADA DI SINI) ...

        // Untuk saat ini, kita hanya kembali dengan pesan
        return redirect()->route('jadwal.index')
                         ->with('info', 'Fitur Algoritma Genetika sedang dalam pengembangan.');
    }

    /**
     * Menyimpan jadwal yang diisi manual dari tabel.
     */
    public function saveManual(Request $request)
    {
        // Loop data yang dikirim dari form
        foreach ($request->jadwal as $kode_matkul => $data) {

            // Cek data minimal (apakah semua dropdown diisi?)
            if (isset($data['id_dosen']) && isset($data['id_ruang']) && isset($data['hari']) && isset($data['jam'])) {

                // Simpan atau Update data di tabel 'jadwal'
                // Mencari berdasarkan kode_matkul, id_prodi, dan semester.
                Jadwal::updateOrCreate(
                    [
                        'kode_matkul' => $kode_matkul, // Asumsi id_matkul adalah kode_matkul
                        'id_prodi'  => $request->id_prodi,
                        'semester'  => $data['semester']
                    ],
                    [
                        'id_dosen'   => $data['id_dosen'],
                        'id_ruang'   => $data['id_ruang'],
                        'hari'       => $data['hari'],
                        'jam'        => $data['jam'],
                        'tipe'       => $data['tipe'],
                    ]
                );
            }
        }

        return redirect()->route('jadwal.index')->with('success', 'Jadwal manual berhasil disimpan.');
    }
}
