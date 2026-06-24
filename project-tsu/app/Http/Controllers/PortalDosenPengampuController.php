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

        if (!auth()->user()->isAdmin() && !$tahunAkademik->status_aktif) {
            abort(403, 'Anda tidak memiliki akses ke tahun akademik yang dinonaktifkan.');
        }

        $prodiId       = ProdiFilter::getProdiId(); // ✅ Ganti logika panjang jadi satu baris

        // Mata kuliah
        $matkulQuery = MataKuliah::with(['program_studi']);
        if ($prodiId) {
            $matkulQuery->where('id_prodi', $prodiId);
        }
        $matkuls = $matkulQuery->orderBy('semester')->orderBy('nama_matkul')->get();

        // Pengampu — filter by kode matkul yang sudah difilter prodi & by tahun akademik
        $pengampuQuery = PengampuMatkul::with(['mataKuliah.program_studi'])
            ->where('id_tahunakademik', $idTahun);
        if ($prodiId) {
            $kodeMatkuls = $matkuls->pluck('kode_matkul');
            $pengampuQuery->whereIn('kode_matkul', $kodeMatkuls);
        }
        $pengampus = $pengampuQuery->get();

        // Dosen
        $dosenQuery = Dosen::with(['prodi'])->orderBy('nama_dosen');
        if ($prodiId) {
            $dosenQuery->whereIn('id_prodi', [$prodiId, 99]);
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

        $pengampusJs = $pengampus->map(function ($p) {
            $mk = \App\Models\MataKuliah::where('kode_matkul', $p->kode_matkul)
                ->where('id_prodi', $p->id_prodi)
                ->first() ?? $p->mataKuliah;

            return [
                'id'          => $p->id,
                'id_dosen'    => $p->id_dosen,
                'kode_matkul' => $p->kode_matkul,
                'id_prodi'    => $p->id_prodi,
                'nama_matkul' => $mk->nama_matkul ?? '',
                'sks'         => $mk->sks ?? 0,
                'semester'    => $mk->semester ?? 0,
                'nama_prodi'  => $mk->program_studi->nama_prodi ?? '-',
            ];
        })->values();

        return view('dosen-pengampu.index', compact(
            'tahunAkademik', 'matkuls', 'pengampus', 'dosens',
            'kurikulums', 'prodis', 'matkulsJs', 'pengampusJs'
        ));
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'kode_matkul'      => 'required|string|exists:mata_kuliah,kode_matkul',
            'id_dosen'         => 'required|exists:dosen,id_dosen',
            'id_prodi'         => 'nullable|integer|exists:program_studi,id_prodi',
            'id_tahunakademik' => 'required|integer|exists:tahun_akademik,id_tahunakademik',
        ]);

        // Boleh dosen berbeda mengajar matkul yang sama — cek duplikat dosen+matkul+prodi+tahunakademik
        $exists = PengampuMatkul::where('kode_matkul', $request->kode_matkul)
            ->where('id_dosen', $request->id_dosen)
            ->where('id_prodi', $request->id_prodi)
            ->where('id_tahunakademik', $request->id_tahunakademik)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Dosen ini sudah mengampu mata kuliah tersebut pada tahun akademik ini.']);
        }

        PengampuMatkul::create([
            'kode_matkul'      => $request->kode_matkul,
            'id_dosen'         => $request->id_dosen,
            'id_prodi'         => $request->id_prodi,
            'id_tahunakademik' => $request->id_tahunakademik,
        ]);

        return response()->json(['success' => true, 'message' => 'Dosen pengampu berhasil ditugaskan.']);
    }

    public function hapus(Request $request)
    {
        $request->validate([
            'kode_matkul'      => 'required|string|exists:mata_kuliah,kode_matkul',
            'id_dosen'         => 'required|exists:dosen,id_dosen',
            'id_prodi'         => 'nullable|integer',
            'id_tahunakademik' => 'required|integer|exists:tahun_akademik,id_tahunakademik',
        ]);

        PengampuMatkul::where('kode_matkul', $request->kode_matkul)
            ->where('id_dosen', $request->id_dosen)
            ->where('id_prodi', $request->id_prodi)
            ->where('id_tahunakademik', $request->id_tahunakademik)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Dosen pengampu berhasil dilepaskan.']);
    }
}
