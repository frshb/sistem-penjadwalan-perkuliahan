<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facade\Excel;
use App\Export\JadwalExport;
use PDF;

class JadwalController extends Controller
{
    //read dan filter
    public function index(Request $request)
    {
        $semester = $request->get('semester');

        $jadwal = Jadwal::query();

        if ($semester) {
            $jadwal->whereHas('matkul', function ($q) use ($semester){
                $q->where('semester', $semester);
            });
        }
        $jadwal = $jadwal->with(['matkul', 'dosen', 'kelas', 'ruang', 'hari', 'slot'])->get();
        return view();
    }

    //tambah - form
    public function store(Request $request)
    {
        $request->validate([
            'id_matkul' => 'required',
            'id_dosen' => 'required',
            'id_kelas' => 'required',
            'id_ruang'  => 'required',
            'id_hari' => 'required',
            'id_slot' => 'required',
            'jenis_jadwal' => 'required',
        ]);

        //bentrokan
        $bentrok = Jadwal::where('id_hari', $request->id_hari)
        ->where('id_slot', $request->id_slot)
        ->where(function ($q) use ($request) {
            $q->where('id_dosen', $request->id_dosen)
            ->orWhere('id_ruang', $request->id_ruang)
            ->orWhere('id_kelas', $request->id_kelas);
        })
        ->exist();

        if ($bentrok) {
            return redirect()->back()->with('error','Jadwal bentrok! Dosen, ruang, atau kelas sudah terpakai.');
        }
        
        Jadwal::create($request->all());
        return redirect()->route();
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
