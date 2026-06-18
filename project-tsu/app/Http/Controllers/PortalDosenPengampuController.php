<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Dosen;
use App\Models\TahunAkademik;
use App\Models\Prodi;
use App\Models\MataKuliah;

class PortalDosenPengampuController extends Controller
{
    /**
     * Halaman pilih tahun akademik untuk portal dosen pengampu
     */
    public function pilihTahun()
    {
        $tahunAkademiks = TahunAkademik::orderBy('tahun_ajaran', 'desc')
            ->withCount(['kelas'])
            ->get();

        return view('dosen-pengampu.pilih-tahun', compact('tahunAkademiks'));
    }

    /**
     * Halaman utama Portal Dosen Pengampu (Drag & Drop)
     */
    public function index(Request $request)
    {
        $idTahun = $request->tahun;

        if (!$idTahun) {
            return redirect()->route('dosen-pengampu.pilih-tahun');
        }

        $tahunAkademik = TahunAkademik::findOrFail($idTahun);

        $user = auth()->user();
        $prodiId = ($user && !$user->isAdmin() && !$user->isDekan()) ? $user->getProdiId() : null;

        // Ambil data Mata Kuliah
        $matkulQuery = MataKuliah::with(['pengampus.dosen', 'program_studi']);

        if ($prodiId) {
            $matkulQuery->where('id_prodi', $prodiId);
        }

        $matkuls = $matkulQuery->get();

        // Ambil semua dosen yang relevan
        $dosenQuery = Dosen::orderBy('nama_dosen');
        if ($prodiId) {
            $dosenQuery->where('id_prodi', $prodiId);
        }
        $dosens = $dosenQuery->get();

        return view('dosen-pengampu.index', compact(
            'tahunAkademik',
            'matkuls',
            'dosens'
        ));
    }

    /**
     * Simpan penugasan dosen ke kelas (AJAX)
     */
    public function simpan(Request $request)
    {
        $request->validate([
            'kode_matkul' => 'required|string|exists:mata_kuliah,kode_matkul',
            'id_dosen' => 'required|exists:dosen,id_dosen',
        ]);

        // Karena UI memindahkan mata kuliah (hanya 1 dosen per matkul di UI ini),
        // kita hapus pengampu sebelumnya untuk matkul ini jika ada.
        \App\Models\PengampuMatkul::where('kode_matkul', $request->kode_matkul)->delete();

        \App\Models\PengampuMatkul::create([
            'kode_matkul' => $request->kode_matkul,
            'id_dosen' => $request->id_dosen,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dosen pengampu berhasil ditugaskan.'
        ]);
    }

    /**
     * Hapus penugasan dosen dari kelas (AJAX)
     */
    public function hapus(Request $request)
    {
        $request->validate([
            'kode_matkul' => 'required|string|exists:mata_kuliah,kode_matkul',
        ]);

        \App\Models\PengampuMatkul::where('kode_matkul', $request->kode_matkul)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dosen pengampu berhasil dilepaskan dari mata kuliah.'
        ]);
    }
}
