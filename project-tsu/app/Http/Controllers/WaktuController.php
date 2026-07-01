<?php

namespace App\Http\Controllers;

use App\Models\Hari;
use App\Models\Slot_waktu;
use App\Models\Jadwal;
use Illuminate\Http\Request;

class WaktuController extends Controller
{
    public function index()
    {
        $hari = Hari::with('slotWaktus')->get()->sortBy(function ($h) {
            $urutan = ['senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4, 'jumat' => 5, 'sabtu' => 6, 'minggu' => 7, 'ahad' => 7];
            return $urutan[strtolower($h->nama_hari)] ?? 99;
        })->values();
        $slotWaktu = Slot_waktu::orderBy('jam_ke', 'asc')->get();

        return view('settings.waktu.index', compact('hari', 'slotWaktu'));
    }

    public function toggleHari(Request $request, $id)
    {
        $hari = Hari::findOrFail($id);
        $hari->update(['is_active' => !$hari->is_active]);
        return response()->json(['success' => true, 'is_active' => $hari->is_active, 'message' => 'Status hari diperbarui']);
    }

    public function toggleSlot(Request $request, $id)
    {
        $slot = Slot_waktu::findOrFail($id);
        $slot->update(['is_active' => !$slot->is_active]);
        return response()->json(['success' => true, 'is_active' => $slot->is_active, 'message' => 'Status slot diperbarui']);
    }

    public function updateMapping(Request $request)
    {
        $request->validate([
            'id_hari' => 'required|exists:hari,id_hari',
            'slot_ids' => 'array'
        ]);

        $hari = Hari::findOrFail($request->id_hari);
        $hari->slotWaktus()->sync($request->slot_ids ?? []);
        
        return response()->json(['success' => true, 'message' => 'Pemetaan slot berhasil diperbarui']);
    }
}
