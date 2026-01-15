<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jadwal;
use App\Models\Hari;
use App\Models\Slotwaktu;
use App\Models\Ruangan;
use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\Prodi;


class JadwalManualController extends Controller
{
    protected $cekBentrok;
        public function __construct(CekBentrokService $cekBentrok)
        {
            $this->cekBentrok = $cekBentrok;
        }
    public function index()
    {
        

        return view('penjadwalan-manual.index', [
            'jadwals' => Jadwal::where('is_manual', 1)->get(),
            'matkuls' => MataKuliah::all(),
            'dosens'  => Dosen::all(),
            'ruangs'  => Ruangan::with('gedung')->get(),
            'haris'   => Hari::all(),
            'slots'   => SlotWaktu::all()
        ]);
    }

    public function update(Request $r, $id)
    {
        $jadwal = Jadwal::findOrFail($id);

        if ($this->cekBentrok->bentrok(
            $r->id_hari, 
            $r->id_slot, 
            $r->id_ruang, 
            $jadwal->id_dosen, 
            $id
            )) {
            return back()
                ->withErrors(['error' => 'Jadwal bentrok dengan jadwal lain. Silakan pilih hari, slot, atau ruang yang berbeda.'])
                ->withInput();
        }
        
        $jadwal->update([
            'id_hari' => $r->id_hari,
            'id_slot' => $r->id_slot,
            'id_ruang' => $r->id_ruang,
            'is_manual' => 1,
        ]);

        return redirect('jadwal.manual.index')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }
}
