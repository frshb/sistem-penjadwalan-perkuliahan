<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use Illuminate\Support\Facades\Auth;
use App\Models\Kurikulum;
use App\Models\Ruangan;
use App\Models\Dosen;
use App\Models\Waktu;
use App\Models\Hari;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JadwalExport;
use Barryvdh\DomPDF\Facade\Pdf;

class JadwalController extends Controller
{
    /**
     * Menampilkan halaman Penjadwalan Manual.
     * (Preserved from local branch)
     */
    public function manual()
    {
        // dd('Manual Route Hit'); // Uncomment to debug
        return view('penjadwalan.penjadwalan-manual');
    }

    public function index(){
        $step = session('penjadwalan.current_step', 1);
        
        return view('penjadwalan.penjadwalan', [
            'step' => $step,
            'kurikulums' => Kurikulum::all(),
            'ruangans' => Ruangan::with('gedung')->get(),
            'dosens' => Dosen::with('prodi')->get(),
            'waktus' => Waktu::all(),
            'haris' => Hari::all(),
            'matkuls' => MataKuliah::with(['kurikulum', 'prodi'])->get(),
        ]);
    }

    public function proses(Request $r)
    {
        $step = $r->step;

        if ($step == 1) return $this->handleStep1($r);
        if ($step == 2) return $this->handleStep2($r);
        if ($step == 3) return $this->handleStep3($r);
        if ($step == 4) return $this->handleStep4();
    }

    public function handleStep1(Request $r)
    {
        $r->validate([
            'semester' => 'required|array|min:1',
        ]);

        session([
            'penjadwalan.semester' => $r->semester,
            'penjadwalan.prodi' => \App\Models\Prodi::all(),
            'penjadwalan.current_step' => 2,
        ]);
        
        return back();
    }

    public function handleStep2(Request $r)
    {
        $r->validate([
            'id_ruang' => 'required|array',
        ]);

        session([
            'penjadwalan.id_ruang' => $r->id_ruang,
            'penjadwalan.current_step' => 3,
        ]);

        return back();
    }

    public function handleStep3(Request $r)
    {
        $r->validate([
            'kelas' => 'required|array',
            'matkul' => 'required|array',
            'dosen' => 'required|array'
        ]);

        session([
            'penjadwalan.step3_data' => $r->all(),
            'penjadwalan.current_step' => 4,
        ]);
        return back();
    }

    public function handleStep4()
    {
         $input = session('penjadwalan.step3_data', []);
        $ruanganDipilih = session('penjadwalan.id_ruang', []);

        $hari = Hari::all();
        $slot = Waktu::all();
        $ruangan = Ruangan::whereIn('id_ruang', $ruanganDipilih)->get();

        $gagal = [];

        foreach ($input['kelas'] as $index => $kelas) {

            $id_matkul = $input['matkul'][$index];
            $id_dosen = $input['dosen'][$index];

            $mk = MataKuliah::find($id_matkul);
            if (!$mk) continue;

            $sukses = false;

            foreach ($hari as $h) {
                foreach ($slot as $s) {
                    
                    $bentrok = Jadwal::where('id_hari', $h->id_hari)
                        ->where('id_slot', $s->id_slot)
                        ->where(function ($q) use ($id_dosen, $kelas) {
                            $q->where('id_dosen', $id_dosen)
                              ->orWhere('kelas', $kelas);
                        })
                        ->exists();

                    if ($bentrok) continue;

                    // CEK RUANGAN KOSONG
                    $ruangKosong = $ruangan->filter(function($r) use ($h, $s) {
                        return !Jadwal::where('id_ruang', $r->id_ruang)
                            ->where('id_hari', $h->id_hari)
                            ->where('id_slot', $s->id_slot)
                            ->exists();
                    })->first();

                    if (!$ruangKosong) continue;

                    // SIMPAN JADWAL
                    Jadwal::create([
                        'id_matkul' => $id_matkul,
                        'id_dosen' => $id_dosen,
                        'kelas' => $kelas,
                        'id_ruang' => $ruangKosong->id_ruang,
                        'id_hari' => $h->id_hari,
                        'id_slot' => $s->id_slot,
                        'status_validasi' => 0
                    ]);

                    $sukses = true;
                    break;
                }

                if ($sukses) break;
            }

            if (!$sukses) {
                $gagal[] = $mk->nama_matkul." - kelas ".$kelas;
            }
        }

        return back()->with('gagal', $gagal);
    }
    
    public function hasil()
    {
        $jadwals = Jadwal::with(['matkul.kurikulum', 'matkul.prodi', 'dosen.prodi', 'ruang.gedung', 'hari', 'slot'])->orderBy('id_hari')->orderBy('id_slot')->get();
        return view('penjadwalan.hasil', compact('jadwals'));
    }

    // EXCEL
    public function exportExcel()
    {
        return Excel::download(new JadwalExport, 'hasil_jadwal.xlsx');
    }

    // PDF
    public function exportPDF()
    {
        $jadwal = Jadwal::with(['matkul.kurikulum', 'matkul.prodi', 'dosen.prodi', 'ruang.gedung', 'hari', 'slot'])->orderBy('id_hari')->orderBy('id_slot')->get();
        $pdf = Pdf::loadView('penjadwalan.export_pdf', compact('jadwal'))->setPaper('a4', 'landscape');
        return $pdf->download('hasil_jadwal.pdf');
    }
}
