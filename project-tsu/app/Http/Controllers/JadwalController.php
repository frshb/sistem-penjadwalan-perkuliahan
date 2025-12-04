<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
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
    }

    public function handleStep1(Request $r)
    {
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
