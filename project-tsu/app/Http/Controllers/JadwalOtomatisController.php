<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kurikulum;
use App\Models\Semester;
use App\Models\Gedung;
use App\Models\Ruangan;
use App\Models\MataKuliah;
use App\Models\Dosen;
use App\Models\Hari;
use App\Models\SlotWaktu;
use App\Models\Jadwal;
use App\Services\PenjadwalanDraftService;
use App\Services\GenerateJadwalService;


class JadwalOtomatisController extends Controller
{
    protected $draftService;
    protected $generateService;
    public function __construct(
        PenjadwalanDraftService $draftService,
        GenerateJadwalService $generateService
    ) {
        $this->draftService = $draftService;
        $this->generateService = $generateService;
    }
    public function step1()
    {
        return view('penjadwalan.otomatis.step1', [
            'semesters' => Semester::where('status_aktif', 1)->get(),
            'kurikulums'=> Kurikulum::where('status', 'aktif')->get()
        ]);
    }

    public function storeStep1(Request $r)
    {
        $r->validate([
        'id_semester' => 'required',
        'id_kurikulum'=> 'required'
    ]);

        $this->draftService->save(
            $r->id_semester,
            1,
            $r->only('id_semester', 'id_kurikulum')
        );

        return redirect()->route('jadwal.otomatis.step2');
    }

    public function step2()
    {
        $step1 = $this->draftService->get($request->id_semester, 1);

        return view('penjadwalan.otomatis.step2', [
            'gedungs' => Gedung::with('ruangs')->get(),
            'selected'=> $this->draftService->get($request->id_semester, 2)
        ]);
    }

    public function storeStep2(Request $r)
    {
        $r->validate([
            'id_ruang' => 'required|array'
        ]);

        $this->draftService->save(
            $r->id_semester,
            2,
            ['id_ruang' => $r->id_ruang]
        );

        return redirect()->route('jadwal.otomatis.step3');
    }

    public function step3()
    {
        return view('penjadwalan.otomatis.step3', [
        'matkuls' => MataKuliah::where(
            'id_kurikulum',
            $this->draftService->get($request->id_semester, 1)['id_kurikulum']
        )->get(),
        'dosens' => Dosen::all(),
        'draft'  => $this->draftService->get($request->id_semester, 3)
        ]);
    }

    public function storeStep3(Request $r)
    {
        $r->validate([
        'penugasan' => 'required|array'
    ]);

    $this->draftService->save(
        $r->id_semester,
        3,
        ['penugasan' => $r->penugasan]
    );

    return redirect()->route('jadwal.otomatis.step4');
    }

    public function step4()
    {
        $draft = $this->draftService->getAll($request->id_semester);

        $hasil = $this->generateService->generate($draft);

        return view('penjadwalan.otomatis.preview', compact('hasil'));
    }

    public function finalize(Request $r)
    {
        $this->generateService->save($r->hasil);

        $this->draftService->clear($r  ->id_semester);

        return redirect()->route('jadwal.manual.index')
            ->with('success', 'Jadwal otomatis berhasil digenerate');
    }
}