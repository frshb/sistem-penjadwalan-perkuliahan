<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use App\Models\Prodi;
use App\Models\MataKuliah;
use App\Models\Dosen;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;

class PortalKurikulumController extends Controller
{
    public function index()
    {
        $tahunAkademiks = TahunAkademik::orderBy('tahun_ajaran', 'desc')->get();
        $kurikulums = \App\Models\Kurikulum::orderBy('nama_kurikulum')->get();
        return view('management.kurikulum.index', compact('tahunAkademiks', 'kurikulums'));
    }

    public function storeKurikulum(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'kode_kurikulum' => 'required|string|max:20|unique:kurikulum,kode_kurikulum',
            'nama_kurikulum' => 'required|string|max:50',
            'tahun_berlaku' => 'required|digits:4',
            'status' => 'required|in:Aktif,Tidak Aktif',
        ]);

        if ($validator->fails()) {
            return redirect()->route('kurikulum.index')
                        ->withErrors($validator)
                        ->withInput()
                        ->with('error', 'Gagal menambahkan kurikulum. Periksa kembali isian Anda.');
        }

        \App\Models\Kurikulum::create([
            'kode_kurikulum' => $request->kode_kurikulum,
            'nama_kurikulum' => $request->nama_kurikulum,
            'tahun_berlaku' => $request->tahun_berlaku,
            'status' => $request->status,
        ]);

        return redirect()->route('kurikulum.index')->with('success', 'Kurikulum baru berhasil ditambahkan.');
    }

    public function storeTahunAkademik(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'nama_tahunakademik' => 'required|string|max:20',
            'tahun_ajaran'       => 'required|string|max:20',
            'status_aktif'       => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->route('kurikulum.index')
                        ->withErrors($validator)
                        ->withInput()
                        ->with('error', 'Gagal menambahkan Tahun Akademik. Periksa kembali isian Anda.');
        }

        $lastId = TahunAkademik::max('id_tahunakademik') ?? 0;

        TahunAkademik::create([
            'id_tahunakademik'   => $lastId + 1,
            'nama_tahunakademik' => $request->nama_tahunakademik,
            'tahun_ajaran'       => $request->tahun_ajaran,
            'status_aktif'       => $request->status_aktif,
        ]);

        return redirect()->route('kurikulum.index')->with('success', 'Tahun akademik berhasil ditambahkan.');
    }

    public function toggleTahunAkademik($id)
    {
        $tahun = \App\Models\TahunAkademik::findOrFail($id);
        $tahun->update([
            'status_aktif' => !$tahun->status_aktif
        ]);

        return redirect()->route('kurikulum.index')->with('success', 'Status Tahun Akademik berhasil diubah.');
    }

    public function setup(Request $request)
    {
        $idTahun = $request->tahun;
        if (!$idTahun) {
            return redirect()->route('kurikulum.index');
        }

        $tahunAkademik = TahunAkademik::find($idTahun);
        
        $prodis = Prodi::orderBy('id_prodi')->get();
        
        $mataKuliahs = MataKuliah::orderBy('nama_matkul')->get();
        $dosens = Dosen::orderBy('nama_dosen')->get();

        return view('management.kurikulum.setup', compact('tahunAkademik', 'prodis', 'mataKuliahs', 'dosens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_tahunakademik' => 'required|exists:tahun_akademik,id_tahunakademik',
            'kurikulum_data' => 'required|array',
        ]);

        $idTahun = $request->id_tahunakademik;
        $tahunAkademik = TahunAkademik::find($idTahun);
        $semesterType = str_contains(strtolower($tahunAkademik->nama_tahunakademik), 'genap') ? 'genap' : 'ganjil';

        DB::beginTransaction();
        try {
            foreach ($request->kurikulum_data as $data) {
                if (empty($data['kode_matkul']) || empty($data['id_dosen']) || empty($data['jumlah_kelas'])) {
                    continue;
                }

                $matkul = MataKuliah::find($data['kode_matkul']);
                if (!$matkul) continue;

                $semester = $matkul->semester ?? ($semesterType == 'genap' ? 2 : 1);

                $jumlahKelas = (int) $data['jumlah_kelas'];
                for ($i = 0; $i < $jumlahKelas; $i++) {
                    $namaKelas = chr(65 + $i); // A, B, C...
                    Kelas::create([
                        'nama_kelas' => $namaKelas,
                        'id_prodi' => $data['id_prodi'],
                        'id_tahunakademik' => $idTahun,
                        'kapasitas' => 40,
                        'semester' => $semester,
                        'kode_matkul' => $data['kode_matkul'],
                        'id_dosen' => $data['id_dosen'],
                        'jumlah_mahasiswa' => 0,
                    ]);
                }
            }
            DB::commit();
            return redirect()->route('kelas.index', ['tahun' => $idTahun])->with('success', 'Berhasil setup kurikulum dan meng-generate Kelas.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function updateTahunAkademik(Request $request, $id)
    {
        $request->validate([
            'nama_tahunakademik' => 'required|string|max:20',
            'tahun_ajaran'       => 'required|string|max:20',
            'status_aktif'       => 'required|boolean',
        ]);

        $tahun = TahunAkademik::findOrFail($id);
        $tahun->update([
            'nama_tahunakademik' => $request->nama_tahunakademik,
            'tahun_ajaran'       => $request->tahun_ajaran,
            'status_aktif'       => $request->status_aktif,
        ]);

        return redirect()->route('kurikulum.index')->with('success', 'Tahun akademik berhasil diperbarui.');
    }

    public function destroyTahunAkademik($id)
    {
        try {
            $tahun = TahunAkademik::findOrFail($id);
            $tahun->delete();
            return redirect()->route('kurikulum.index')->with('success', 'Tahun akademik berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus. Tahun akademik mungkin masih digunakan.');
        }
    }

    public function updateKurikulum(Request $request, $id)
    {
        $request->validate([
            'kode_kurikulum' => 'required|string|max:20|unique:kurikulum,kode_kurikulum,' . $id . ',id_kurikulum',
            'nama_kurikulum' => 'required|string|max:50',
            'tahun_berlaku' => 'required|digits:4',
            'status' => 'required|in:Aktif,Tidak Aktif',
        ]);

        $kurikulum = \App\Models\Kurikulum::findOrFail($id);
        $kurikulum->update([
            'kode_kurikulum' => $request->kode_kurikulum,
            'nama_kurikulum' => $request->nama_kurikulum,
            'tahun_berlaku' => $request->tahun_berlaku,
            'status' => $request->status,
        ]);

        return redirect()->route('kurikulum.index')->with('success', 'Kurikulum berhasil diperbarui.');
    }

    public function destroyKurikulum($id)
    {
        try {
            $kurikulum = \App\Models\Kurikulum::findOrFail($id);
            $kurikulum->delete();
            return redirect()->route('kurikulum.index')->with('success', 'Kurikulum berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus. Kurikulum mungkin masih digunakan.');
        }
    }
}
