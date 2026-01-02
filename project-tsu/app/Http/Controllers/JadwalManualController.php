<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JadwalManualController extends Controller
{
    public function index()
    {
        $jadwal = Jadwal::with(['matkul', 'dosen', 'ruang', 'hari', 'slot'])
            ->orderBy('id_hari')
            ->orderBy('id_slot')
            ->get();
        
        return view('jadwal.manual.index', compact('jadwal'));
    }

    public function edit($id)
    {
        return view('jadwal.manual.edit', [
            'jadwal' => Jadwal::findOrFail($id),
            'hari' => Hari::all(),
            'slot' => Slot::all(),
            'ruang' => Ruang::all(),
        ]);
    }

    public function update(Request $r, $id)
    {
        $jadwal = Jadwal::findOrFail($id);

        $bentrok = Jadwal::where('id_hari', $r->id_hari)
            ->where('id_slot', $r->id_slot)
            ->where('id_ruang', $r->id_ruang)
            ->where('id_jadwal', '!=', $id)
            ->where(function ($q) use ($jadwal, $r) {
                $q->where('id_dosen', $jadwal->id_dosen)
                  ->orWhere('kelas', $r->kelas)
                  ->orWhere('id_ruang', $r->id_ruang);
            })
            ->exists();
        
        if ($bentrok) {
            return back()->withErrors(['error' => 'Jadwal bentrok dengan jadwal lain. Silakan pilih hari, slot, atau ruang yang berbeda.'])->withInput();
        }
        
        $jadwal->update([
            'id_hari' => $r->id_hari,
            'id_slot' => $r->id_slot,
            'id_ruang' => $r->id_ruang,
            'kelas' => $r->kelas,
            'is_manual' => 1,
        ]);

        return redirect('jadwal/manual')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }
}
