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
        $hari = Hari::orderBy('id_hari', 'asc')->get();
        $slotWaktu = Slot_waktu::orderBy('jam_ke', 'asc')->get();

        return view('settings.waktu.index', compact('hari', 'slotWaktu'));
    }

    public function storeHari(Request $request)
    {
        $request->validate([
            'nama_hari' => 'required|string|max:20|unique:hari,nama_hari',
        ]);

        $maxId = Hari::max('id_hari') ?? 0;
        Hari::create([
            'id_hari' => $maxId + 1,
            'nama_hari' => $request->nama_hari,
        ]);

        return redirect()->back()->with('success', 'Hari berhasil ditambahkan.');
    }

    public function updateHari(Request $request, $id)
    {
        $request->validate([
            'nama_hari' => 'required|string|max:20|unique:hari,nama_hari,' . $id . ',id_hari',
        ]);

        $hari = Hari::findOrFail($id);
        $hari->update(['nama_hari' => $request->nama_hari]);

        return redirect()->back()->with('success', 'Hari berhasil diperbarui.');
    }

    public function destroyHari($id)
    {
        $inUse = Jadwal::where('id_hari', $id)->exists();
        if ($inUse) {
            return redirect()->back()->with('error', 'Hari tidak dapat dihapus karena sedang digunakan dalam jadwal.');
        }

        Hari::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Hari berhasil dihapus.');
    }

    // ============================================
    // SLOT WAKTU METHODS
    // ============================================

    public function storeSlot(Request $request)
    {
        $request->validate([
            'jam_ke' => 'required|integer|unique:slot_waktu,jam_ke',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required|after:waktu_mulai',
            'sesi' => 'required|in:pagi,malam',
        ]);

        $maxId = Slot_waktu::max('id_slot') ?? 0;
        Slot_waktu::create([
            'id_slot' => $maxId + 1,
            'jam_ke' => $request->jam_ke,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'sesi' => $request->sesi,
        ]);

        return redirect()->back()->with('success', 'Slot waktu berhasil ditambahkan.');
    }

    public function updateSlot(Request $request, $id)
    {
        $request->validate([
            'jam_ke' => 'required|integer|unique:slot_waktu,jam_ke,' . $id . ',id_slot',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required|after:waktu_mulai',
            'sesi' => 'required|in:pagi,malam',
        ]);

        $slot = Slot_waktu::findOrFail($id);
        $slot->update([
            'jam_ke' => $request->jam_ke,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'sesi' => $request->sesi,
        ]);

        return redirect()->back()->with('success', 'Slot waktu berhasil diperbarui.');
    }

    public function destroySlot($id)
    {
        $inUse = Jadwal::where('id_slot_mulai', $id)->exists();
        if ($inUse) {
            return redirect()->back()->with('error', 'Slot waktu tidak dapat dihapus karena sedang digunakan dalam jadwal.');
        }

        Slot_waktu::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Slot waktu berhasil dihapus.');
    }
}
