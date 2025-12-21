<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
<<<<<<< HEAD
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
=======
use App\Models\Kurikulum;
use App\Models\Ruangan;
use App\Models\Dosen;
use App\Models\Waktu;
use App\Models\Hari;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facade\Excel;
use App\Export\JadwalExport;
use PDF;

class JadwalController extends Controller
{
    
    public function index(){
        $step = session('penjadwalan.current_step', 1);
        
        return view('penjadwalan.penjadwalan', [
            'step' => $step,
            'kurikulums' => Kurikulum::all(),
            'ruangans' => Ruangan::all(),
            'dosens' => Dosen::all(),
            'waktus' => Waktu::all(),
            'haris' => Hari::all(),
            'matkuls' => MataKuliah::all(),
        ]);
    }

    public function proses(Request $r)
    {
        $step = $r->step;

        if ($step == 1) return $this->handleStep1($request);
        if ($step == 2) return $this->handleStep2($request);
        if ($step == 3) return $this->hanldeStep3($request);
        if ($step == 4) return $this->hanldeStep4();
>>>>>>> 4fb47b0488447a33505c294959653eed0a5466dd
    }

    public function handleStep1(Request $r)
    {
<<<<<<< HEAD
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
=======
        $r->validated([
            'id_kurikulum' => 'required',
            'semester' => 'required|array|min:1',
        ]);

        session([
            'penjadwalan.id_kurikulum' => $r->id_kurikulum,
            'penjadwalan.semester' => $r->semester,
            'penjadwalan.current_step' => 2,
        ]);
        
        return back();
    }

    public function handleStep2(Request $r)
    {
        $r->validate([
            'id_ruang' => 'required|array|min:1',
        ]);

        session([
            'penjadwalan.id_ruang' => $r->id_ruang,
            'penjadwalan.current_step' => 3,
        ]);

        return back();
    }

    public function handleStep3(Request $r)
    {
        $kurikulum = session('penjadwalan.id_kurikulum');
        $semester = session('penjadwalan.semester');

        //QUERY
        $matkul = MataKuliah::where(id_kurikulum, $kurikulum)
                    ->whereIn('semester', $semester)
                    ->get();
        
        session([
            'penjadwalan.matkul_list' => $matkul,
            'penjadwalan.current_step'=> 4,
        ]);

        return back();
    }

    public function handleStep4()
    {
        $matkuls = session('penjadwalan.matkul_list', []);
        $ruang = session('penjadwalan.id_ruang', []);

        if (empty($matkuls)) {
            return back()->with('error', "Tidak ada matakuliah yang dipilih");
        }

        $dataMK = MataKuliah::whereIn('id_matkul', $matkuls)
                    ->orderBy('sks', 'DESC')
                    ->get();
        $hari = Hari::all();
        $waktu = Waktu::all();
        $ruangan = Ruangan::whereIn('id_ruang', $ruang)->get();

        $gagal = [];

        foreach ($dataMK as $mk) {
            $dosen = $mk->id_dosen;
            $kelas = $mk->id_kelas;

            $sukses = false;

            foreach ($hari as $h) {
                foreach ($waktu as $w) {
                    $bentrok = Jadwal::where('id_hari', $h->id_hari)
                        ->where('id_slot', $w->id_slot)
                        ->where(function ($q) use ($dosen, $kelas) {
                            $q->where('id_dosen', $dosen)
                            ->orWhere('id_kelas', $kelas);
                        })->exists();

                    if ($bentrok) continue;
                    $ruangKosong = Ruangan::whereIn('id_ruang', $ruang)
                        ->whereDoesntHave('jadwal', function ($q) use ($h, $w) {
                            $q->where('id_hari', $h->id_hari)
                            ->where('id_slot', $w->id_slot);
                        })->first();
                    
                    if (!$ruangKosong) continue;

                    Jadwal::create([
                        'id_matkul' => $mk->id_matkul,
                        'id_dosen' => $dosen,
                        'id_kelas' => $kelas,
                        'id_ruang' => $ruangKosong->id_ruang,
                        'id_hari' => $h->id_hari,
                        'id_slot' => $w->id_slot,
                        'jenis_jadwal' => $mk->jenis,
                        'status_validasi' => 0,
                    ]);

                    $sukses = true;
                    break;
                }
                if ($sukses) break;
            }
            if (!$sukses) $gagal[] = $mk->nama_matkul;
        }
        return back()-with('gagal', $gagal);
>>>>>>> 4fb47b0488447a33505c294959653eed0a5466dd
    }
    
    //EXCEL
    //public function exportExcel()
    //{
    //    return Excel::donwload(new JadwalExport, 'jadwal.xlsx');
    //}

    //PDF
    //public function exportPDF()
    //{
    //    $jadwal = Jadwal::with(['matkul', 'dosen', 'kelas', 'ruang', 'hari', 'slot'])->get();
    //    $pdf = PDF::loadView('jadwal.export_pdf', compact('jadwal'));
    //    return $pdf->downloa('jadwal.pdf');
    //}
}
#belum selesai
