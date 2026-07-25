<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dosen;
use App\Models\TahunAkademik;
use App\Models\MataKuliah;
use App\Models\PengampuKelas;
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

        $prodiId = ProdiFilter::getProdiId();
        $selectedProdi = $request->input('prodi');
        if (!$prodiId && $selectedProdi) {
            $prodiId = $selectedProdi;
        }

        // Mata kuliah lookup (for filters / JS, restricted by prodi if kaprodi)
        $matkulQuery = MataKuliah::with(['program_studi']);
        if (ProdiFilter::getProdiId()) {
            $matkulQuery->where('id_prodi', ProdiFilter::getProdiId());
        }
        $matkuls = $matkulQuery->orderBy('semester')->orderBy('nama_matkul')->get();

        // Request filters
        $semester = $request->input('semester');
        $kurikulumId = $request->input('kurikulum');
        $search = $request->input('search');

        // Pengampu Query
        $pengampuQuery = PengampuKelas::with([
            'kelas.matakuliah.program_studi', 
            'dosen.pengampus' => function($q) use ($idTahun) {
                $q->where('id_tahunakademik', $idTahun)->with('kelas.matakuliah');
            }
        ])->where('id_tahunakademik', $idTahun);
        
        if ($prodiId) {
            $pengampuQuery->whereHas('kelas', function($q) use ($prodiId) {
                $q->where('id_prodi', $prodiId);
            });
        }
        if ($semester) {
            if ($semester === 'ganjil') {
                $pengampuQuery->whereHas('kelas.matakuliah', function($q) {
                    $q->whereIn('semester', [1, 3, 5, 7]);
                });
            } elseif ($semester === 'genap') {
                $pengampuQuery->whereHas('kelas.matakuliah', function($q) {
                    $q->whereIn('semester', [2, 4, 6, 8]);
                });
            } else {
                $pengampuQuery->whereHas('kelas.matakuliah', function($q) use ($semester) {
                    $q->where('semester', $semester);
                });
            }
        }
        if ($kurikulumId) {
            $pengampuQuery->whereHas('kelas.matakuliah', function($q) use ($kurikulumId) {
                $q->where('id_kurikulum', $kurikulumId);
            });
        }
        if ($search) {
            $pengampuQuery->whereHas('kelas', function($q) use ($search) {
                $q->where(function($sq) use ($search) {
                    $sq->where('nama_kelas', 'like', "%{$search}%")
                      ->orWhereHas('matakuliah', function($mq) use ($search) {
                          $mq->where('nama_matkul', 'like', "%{$search}%")
                             ->orWhere('kode_matkul', 'like', "%{$search}%");
                      });
                });
            });
        }
        $pengampus = $pengampuQuery->get();

        $dosenQuery = Dosen::with(['prodi', 'pengampus' => function($q) use ($idTahun) {
            $q->where('id_tahunakademik', $idTahun)->with('kelas.matakuliah');
        }])
            ->where(function($q) {
                // Tampilkan dosen eksternal fakultas (99) atau null
                $q->where('id_prodi', '!=', 0) // Dummy condition untuk jaga struktur orWhereNull
                  ->orWhereNull('id_prodi');
            })
            ->orderBy('nama_dosen');
        // No prodi-specific filtering – show all dosen across program studi
        $dosens = $dosenQuery->get();

        // Kelas Paralel Query
        $kelasQuery = \App\Models\Kelas::with(['matakuliah', 'prodi'])
            ->where('id_tahunakademik', $idTahun);
        if ($prodiId) {
            $kelasQuery->where('id_prodi', $prodiId);
        }
        if ($semester) {
            if ($semester === 'ganjil') {
                $kelasQuery->whereHas('matakuliah', function($q) {
                    $q->whereIn('semester', [1, 3, 5, 7]);
                });
            } elseif ($semester === 'genap') {
                $kelasQuery->whereHas('matakuliah', function($q) {
                    $q->whereIn('semester', [2, 4, 6, 8]);
                });
            } else {
                $kelasQuery->whereHas('matakuliah', function($q) use ($semester) {
                    $q->where('semester', $semester);
                });
            }
        }
        if ($kurikulumId) {
            $kelasQuery->whereHas('matakuliah', function($q) use ($kurikulumId) {
                $q->where('id_kurikulum', $kurikulumId);
            });
        }
        if ($search) {
            $kelasQuery->where(function($q) use ($search) {
                $q->where('nama_kelas', 'like', "%{$search}%")
                  ->orWhereHas('matakuliah', function($mq) use ($search) {
                      $mq->where('nama_matkul', 'like', "%{$search}%")
                         ->orWhere('kode_matkul', 'like', "%{$search}%");
                  });
            });
        }
        $kelasList = $kelasQuery->get()->sortBy(function($k) {
            return ($k->matakuliah->kode_matkul ?? '') . '-' . $k->nama_kelas;
        })->values();

        // Kurikulum & Prodi lookup
        $kurikulums = \App\Models\Kurikulum::orderBy('nama_kurikulum')->get();
        $prodis     = ProdiFilter::getProdiId()
            ? Prodi::whereIn('id_prodi', [ProdiFilter::getProdiId(), 99])->get()
            : Prodi::whereIn('id_prodi', [1, 2, 3, 99])->orderBy('nama_prodi')->get();

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

        $kelasJs = $kelasList->map(fn($k) => [
            'id_kelas'    => $k->id_kelas,
            'nama_kelas'  => $k->nama_kelas,
            'id_matakuliah' => $k->id_matakuliah,
            'kode_matkul' => $k->matakuliah->kode_matkul ?? '',
            'id_prodi'    => $k->id_prodi,
            'nama_prodi'  => $k->prodi->nama_prodi ?? '-',
        ])->values();

        $pengampusJs = $pengampus->map(function ($p) {
            $mk = $p->kelas?->matakuliah;

            return [
                'id'          => $p->id,
                'id_dosen'    => $p->id_dosen,
                'id_kelas'    => $p->id_kelas,
                'kode_matkul' => $mk->kode_matkul ?? '',
                'id_prodi'    => $p->kelas->id_prodi ?? null,
                'nama_matkul' => $mk->nama_matkul ?? '',
                'nama_kelas'  => $p->kelas?->nama_kelas ?? '-',
                'sks'         => $mk->sks ?? 0,
                'semester'    => $mk->semester ?? 0,
                'nama_prodi'  => $p->kelas?->prodi?->nama_prodi ?? '-',
                'jumlah_mahasiswa' => $p->kelas?->jumlah_mahasiswa ?? 0,
            ];
        })->values();

        return view('dosen-pengampu.index', compact(
            'tahunAkademik', 'matkuls', 'pengampus', 'dosens',
            'kelasList', 'kelasJs',
            'kurikulums', 'prodis', 'matkulsJs', 'pengampusJs'
        ));
    }

    public function simpan(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionAccess('management_data', 'Pengampu Kelas', 'edit'), 403, 'Unauthorized action.');
        $request->validate([
            'id_dosen'         => 'required|exists:dosen,id_dosen',
            'id_kelas'         => 'required|integer|exists:kelas,id_kelas',
            'id_tahunakademik' => 'required|integer|exists:tahun_akademik,id_tahunakademik',
        ]);

        $exists = PengampuKelas::where('id_kelas', $request->id_kelas)
            ->where('id_tahunakademik', $request->id_tahunakademik)
            ->exists();

        if ($exists) {
            // Update dosen jika sudah ada
            $pk = PengampuKelas::where('id_kelas', $request->id_kelas)
                ->where('id_tahunakademik', $request->id_tahunakademik)
                ->first();
            $pk->update(['id_dosen' => $request->id_dosen]);
            
            return response()->json(['success' => true, 'message' => 'Dosen pengampu berhasil diupdate.', 'id' => $pk->id]);
        }

        $pk = PengampuKelas::create([
            'id_dosen'         => $request->id_dosen,
            'id_kelas'         => $request->id_kelas,
            'id_tahunakademik' => $request->id_tahunakademik,
        ]);

        return response()->json(['success' => true, 'message' => 'Dosen pengampu berhasil ditugaskan ke kelas.', 'id' => $pk->id]);
    }

    public function hapus(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionAccess('management_data', 'Pengampu Kelas', 'edit'), 403, 'Unauthorized action.');
        $request->validate([
            'id' => 'required|integer|exists:pengampu_kelas,id'
        ]);

        PengampuKelas::where('id', $request->id)->delete();

        return response()->json(['success' => true, 'message' => 'Dosen pengampu berhasil dilepaskan dari kelas.']);
    }
}
