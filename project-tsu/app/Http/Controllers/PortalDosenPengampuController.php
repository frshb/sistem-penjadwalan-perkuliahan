<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dosen;
use App\Models\TahunAkademik;
use App\Models\MataKuliah;
use App\Models\PengampuMatkul;
use App\Models\Kurikulum;
use App\Models\Prodi;
use App\Helpers\ProdiFilter;

class PortalDosenPengampuController extends Controller
{
    public function pilihTahun()
    {
        $tahunAkademiks = TahunAkademik::orderBy('tahun_ajaran', 'desc')
            ->withCount(['kelas'])
            ->get();

        return view('dosen-pengampu.pilih-tahun', compact('tahunAkademiks'));
    }

    public function index(Request $request)
    {
        $idTahun = $request->tahun;
        if (!$idTahun) {
            return redirect()->route('dosen-pengampu.pilih-tahun');
        }

        $tahunAkademik = TahunAkademik::findOrFail($idTahun);
        $prodiId       = ProdiFilter::getProdiId(); // ✅ Ganti logika panjang jadi satu baris

        // Mata kuliah
        $matkulQuery = MataKuliah::with(['program_studi']);
        if ($prodiId) {
            $matkulQuery->where('id_prodi', $prodiId);
        }
        $matkuls = $matkulQuery->orderBy('semester')->orderBy('nama_matkul')->get();

        // Pengampu — filter by kode matkul yang sudah difilter prodi
        $pengampuQuery = PengampuMatkul::with(['mataKuliah.program_studi']);
        if ($prodiId) {
            $kodeMatkuls = $matkuls->pluck('kode_matkul');
            $pengampuQuery->whereIn('kode_matkul', $kodeMatkuls);
        }
        $pengampus = $pengampuQuery->get();

        // Dosen
        $dosenQuery = Dosen::with(['prodi'])->orderBy('nama_dosen');
        if ($prodiId) {
            $dosenQuery->where('id_prodi', $prodiId);
        }
        $dosens = $dosenQuery->get();

        // Kurikulum & Prodi untuk filter dropdown
        $kurikulums = \App\Models\Kurikulum::orderBy('nama_kurikulum')->get();
        $prodis     = $prodiId
            ? Prodi::where('id_prodi', $prodiId)->get()  // ✅ Kaprodi hanya lihat prodinya
            : Prodi::orderBy('nama_prodi')->get();

        // Pre-mapped untuk JS
        $matkulsJs = $matkuls->map(fn($m) => [
            'kode_matkul'  => $m->kode_matkul,
            'nama_matkul'  => $m->nama_matkul,
            'sks'          => $m->sks,
            'semester'     => $m->semester,
            'id_prodi'     => $m->id_prodi,
            'id_kurikulum' => $m->id_kurikulum,
            'nama_prodi'   => $m->program_studi->nama_prodi ?? '-',
        ])->values();

        $pengampusJs = $pengampus->map(fn($p) => [
            'id'          => $p->id,
            'id_dosen'    => $p->id_dosen,
            'kode_matkul' => $p->kode_matkul,
            'nama_matkul' => $p->mataKuliah->nama_matkul ?? '',
            'sks'         => $p->mataKuliah->sks ?? 0,
            'nama_prodi'  => $p->mataKuliah->program_studi->nama_prodi ?? '-',
        ])->values();

        return view('dosen-pengampu.index', compact(
            'tahunAkademik', 'matkuls', 'pengampus', 'dosens',
            'kurikulums', 'prodis', 'matkulsJs', 'pengampusJs'
        ));
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'kode_matkul' => 'required|string|exists:mata_kuliah,kode_matkul',
            'id_dosen'    => 'required|exists:dosen,id_dosen',
        ]);

        // Boleh dosen berbeda mengajar matkul yang sama — cek duplikat dosen+matkul
        $exists = PengampuMatkul::where('kode_matkul', $request->kode_matkul)
            ->where('id_dosen', $request->id_dosen)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Dosen ini sudah mengampu mata kuliah tersebut.']);
        }

        PengampuMatkul::create([
            'kode_matkul' => $request->kode_matkul,
            'id_dosen'    => $request->id_dosen,
        ]);

        return response()->json(['success' => true, 'message' => 'Dosen pengampu berhasil ditugaskan.']);
    }

    public function hapus(Request $request)
    {
        $request->validate([
            'kode_matkul' => 'required|string|exists:mata_kuliah,kode_matkul',
            'id_dosen'    => 'required|exists:dosen,id_dosen',
        ]);

        PengampuMatkul::where('kode_matkul', $request->kode_matkul)
            ->where('id_dosen', $request->id_dosen)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Dosen pengampu berhasil dilepaskan.']);
    }
}
