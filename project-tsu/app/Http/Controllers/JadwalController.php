<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prodi;
use App\Models\MataKuliah;
use App\Models\Dosen;
use App\Models\Ruangan;
use App\Models\Jadwal;
use Illuminate\Support\Facades\Auth;

class JadwalController extends Controller
{
    /**
     * Menampilkan halaman modul penjadwalan (Otomatis/Utama).
     */
    public function index(Request $request)
    {
        // ... (existing code for index) ...
        // 1. Ambil data Prodi berdasarkan User Login (Kaprodi)
        $user = Auth::user();
        $prodi = null;

        if ($user && $user->isKaprodi()) {
            if ($user->id_prodi) {
                $prodi = $user->prodi;
            } elseif ($user->dosen && $user->dosen->id_prodi) {
                $prodi = $user->dosen->prodi;
            }
        }

        // Fallback jika bukan Kaprodi atau data tdk lengkap (misal Admin lihat S1 Informatika default atau dari request)
        if (!$prodi) {
            // Bisa tambahkan logic request->id_prodi jika Admin ingin pilih prodi
            if ($request->has('id_prodi')) {
                 $prodi = Prodi::find($request->id_prodi);
            }
            if (!$prodi) {
                $prodi = Prodi::where('nama_prodi', 'like', '%Informatika%')->first() ?? Prodi::first();
            }
        }
        
        $userProdiName = $prodi ? $prodi->nama_prodi : '';

        // 2. Ambil semua mata kuliah yang perlu dijadwalkan
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
        ];

        // 5. Ambil jadwal yang sudah ada (jika ada)
        $jadwalDibuat = Jadwal::where('id_prodi', $prodi->id_prodi)
                            ->get()
                            ->keyBy(function ($item) {
                                return $item->mataKuliah ? $item->mataKuliah->kode_matkul : null;
                            })->filter(); 

        return view('penjadwalan.index', [
            'prodi' => $prodi,
            'matakuliahs' => $matakuliahs,
            'dosens' => $dosens,
            'ruangans' => $ruangans,
            'haris' => $haris,
            'jams' => $jams,
            'jadwalDibuat' => $jadwalDibuat,
            'userProdiName' => $userProdiName
        ]);
    }

    /**
     * Menampilkan halaman Penjadwalan Manual.
     */
    public function manual()
    {
        // dd('Manual Route Hit'); // Uncomment to debug
        return view('penjadwalan.penjadwalan-manual');
    }

    /**
     * Tombol "Buat Jadwal" (Genetika) - INI HANYA PLACEHOLDER
     */
    public function generateGA(Request $request)
    {
        // ... (LOGIKA ALGORITMA GENETIKA ANDA YANG KOMPLEKS ADA DI SINI) ...
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
            if (isset($data['id_dosen']) && isset($data['id_ruang']) && isset($data['hari']) && isset($data['jam'])) {
                Jadwal::updateOrCreate(
                    [
                        'kode_matkul' => $kode_matkul, 
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
