<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use App\Models\Notification;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JadwalValidasiController extends Controller
{
    /**
     * Cek hak akses ke tahun akademik.
     */
    private function checkAccessTahunAkademik($idTahun)
    {
        $tahunAkademik = TahunAkademik::findOrFail($idTahun);

        if (!$tahunAkademik->status_aktif) {
            abort(403, 'Anda tidak memiliki akses ke tahun akademik yang dinonaktifkan.');
        }

        return $tahunAkademik;
    }

    /**
     * Helper untuk mencatat jejak audit riwayat persetujuan jadwal.
     */
    private function recordHistory($tahunId, $aksi, $statusSebelumnnya, $statusSesudah, $catatan = null)
    {
        try {
            $user = Auth::user();
            \App\Models\JadwalApprovalHistory::create([
                'id_tahunakademik'  => $tahunId,
                'id_prodi'          => \App\Helpers\ProdiFilter::getProdiId() ?: ($user->id_prodi ?? null),
                'user_id'           => $user?->id ?? null,
                'role_actor'        => strtolower($user?->role?->nama_role ?? 'sistem'),
                'aksi'              => $aksi,
                'status_sebelumnya' => $statusSebelumnnya,
                'status_sesudah'    => $statusSesudah,
                'catatan'           => $catatan,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mencatat JadwalApprovalHistory: ' . $e->getMessage());
        }
    }

    /**
     * Admin Mengajukan Jadwal ke Dekan untuk divalidasi
     */
    public function ajukan(Request $request, $idTahun)
    {
        $user = Auth::user();
        if (!$user || !in_array(strtolower($user->role->nama_role ?? ''), ['admin', 'kaprodi'])) {
            return back()->with('error', 'Hanya Admin / Pengelola Jadwal yang dapat mengajukan jadwal ke Dekan.');
        }

        $tahunAkademik = $this->checkAccessTahunAkademik($idTahun);

        $prodiId = \App\Helpers\ProdiFilter::getProdiId();

        $kelasQuery  = \App\Models\Kelas::where('id_tahunakademik', $idTahun);
        $jadwalQuery = \App\Models\Jadwal::where('id_tahunakademik', $idTahun);

        if ($prodiId) {
            $kelasQuery->where('id_prodi', $prodiId);
            $jadwalQuery->whereHas('kelas', fn($q) => $q->where('id_prodi', $prodiId));
        }

        $kCount   = $kelasQuery->count();
        $jCount   = $jadwalQuery->pluck('id_kelas')->unique()->count();
        $pPercent = $kCount > 0 ? min(100, (int)round(($jCount / $kCount) * 100)) : 0;

        if ($kCount == 0 || $pPercent < 100) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal belum 100% disusun (' . $pPercent . '%). Pengajuan ke Dekan hanya dapat dilakukan setelah seluruh kelas berhasil dijadwalkan.'
                ], 422);
            }
            return back()->with('error', 'Jadwal belum 100% disusun (' . $pPercent . '%). Pengajuan ke Dekan hanya dapat dilakukan setelah seluruh kelas berhasil dijadwalkan.');
        }



        $oldStatus = $tahunAkademik->status_validasi ?? 'draft';
        DB::transaction(function () use ($tahunAkademik, $user, $oldStatus) {
            $tahunAkademik->update([
                'status_validasi' => 'menunggu_persetujuan',
                'catatan_revisi'  => null,
                'validated_at'    => now(),
            ]);

            // Kirim notifikasi ke Dekan
            Notification::create([
                'user_id'     => null,
                'role_target' => 'dekan',
                'judul'       => '📋 Pengajuan Validasi Jadwal Perkuliahan',
                'pesan'       => "Admin ({$user->username}) telah menyelesaikan penyusunan jadwal periode {$tahunAkademik->nama_tahunakademik} dan mengajukannya untuk diperiksa & divalidasi oleh Dekan.",
                'tipe'        => 'info',
                'is_read'     => false,
            ]);

            $this->recordHistory($tahunAkademik->id_tahunakademik, 'submit_dekan', $oldStatus, 'menunggu_persetujuan');
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil diajukan ke Dekan untuk divalidasi!',
                'status'  => 'menunggu_persetujuan'
            ]);
        }

        return back()->with('success', 'Jadwal berhasil diajukan ke Dekan untuk divalidasi! Notifikasi otomatis telah dikirimkan ke akun Dekan.');
    }

    /**
     * Admin Membatalkan Pengajuan Jadwal ke Dekan (Kembali ke Draft)
     */
    public function batalkanPengajuan(Request $request, $idTahun)
    {
        $user = Auth::user();
        if (!$user || !in_array(strtolower($user->role->nama_role ?? ''), ['admin', 'kaprodi'])) {
            return back()->with('error', 'Hanya Admin / Pengelola Jadwal yang dapat membatalkan pengajuan.');
        }

        $tahunAkademik = $this->checkAccessTahunAkademik($idTahun);

        DB::transaction(function () use ($tahunAkademik, $user) {
            $tahunAkademik->update([
                'status_validasi' => 'disetujui_kaprodi',
                'catatan_revisi'  => null,
            ]);

            Notification::create([
                'user_id'     => null,
                'role_target' => 'dekan',
                'judul'       => 'ℹ️ Pengajuan Jadwal Dibatalkan Admin',
                'pesan'       => "Admin ({$user->username}) membatalkan pengajuan jadwal periode {$tahunAkademik->nama_tahunakademik} untuk melakukan penyesuaian.",
                'tipe'        => 'warning',
                'is_read'     => false,
            ]);

            $this->recordHistory($tahunAkademik->id_tahunakademik, 'batalkan_pengajuan', 'menunggu_persetujuan', 'disetujui_kaprodi');
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan jadwal ke Dekan berhasil dibatalkan. Status dikembalikan ke Disetujui Kaprodi.',
                'status'  => 'disetujui_kaprodi'
            ]);
        }

        return back()->with('success', 'Pengajuan jadwal ke Dekan berhasil dibatalkan. Anda dapat mengedit atau mengajukan ulang jadwal.');
    }

    /**
     * Setujui Jadwal dan Publikasikan secara Resmi
     */
    public function setujui(Request $request, $idTahun)
    {
        $user = Auth::user();
        if (!$user || strtolower($user->role->nama_role ?? '') !== 'dekan') {
            return back()->with('error', 'Hanya Dekan yang memiliki hak akses untuk melakukan aksi ini.');
        }

        $tahunAkademik = $this->checkAccessTahunAkademik($idTahun);

        DB::transaction(function () use ($tahunAkademik, $user) {
            $tahunAkademik->update([
                'status_validasi' => 'disetujui',
                'catatan_revisi'  => null,
                'validated_at'    => now(),
            ]);

            // 1. Notifikasi untuk Dosen
            Notification::create([
                'user_id'     => null,
                'role_target' => 'dosen',
                'judul'       => '📢 Jadwal Perkuliahan Dipublikasikan',
                'pesan'       => "Jadwal Perkuliahan untuk periode {$tahunAkademik->nama_tahunakademik} ({$tahunAkademik->tahun_ajaran}) telah disetujui oleh Dekan dan dipublikasikan secara resmi.",
                'tipe'        => 'success',
                'is_read'     => false,
            ]);

            // 2. Notifikasi untuk Kaprodi & Sekprodi
            Notification::create([
                'user_id'     => null,
                'role_target' => 'kaprodi',
                'judul'       => '📢 Jadwal Final Disetujui Dekan',
                'pesan'       => "Jadwal Perkuliahan periode {$tahunAkademik->nama_tahunakademik} telah disetujui oleh Dekan. Program Studi kini dapat mengunduh dan menyebarkan jadwal.",
                'tipe'        => 'success',
                'is_read'     => false,
            ]);

            // 3. Notifikasi untuk Admin
            Notification::create([
                'user_id'     => null,
                'role_target' => 'admin',
                'judul'       => '✅ Validation Approved by Dekan',
                'pesan'       => "Jadwal {$tahunAkademik->nama_tahunakademik} telah disetujui oleh {$user->username} (Dekan). Status jadwal saat ini telah AKTIF DIPUBLIKASIKAN.",
                'tipe'        => 'success',
                'is_read'     => false,
            ]);

            $this->recordHistory($tahunAkademik->id_tahunakademik, 'setujui_dekan', 'menunggu_persetujuan', 'disetujui');
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil disetujui & dipublikasikan secara resmi!',
                'status'  => 'disetujui'
            ]);
        }

        return back()->with('success', 'Jadwal berhasil disetujui & dipublikasikan secara resmi! Notifikasi otomatis telah dikirim ke Dosen & Prodi.');
    }

    /**
     * Kembalikan Jadwal ke Admin untuk Revisi
     */
    public function mintaRevisi(Request $request, $idTahun)
    {
        $user = Auth::user();
        if (!$user || strtolower($user->role->nama_role ?? '') !== 'dekan') {
            return back()->with('error', 'Hanya Dekan yang memiliki hak akses untuk meminta revisi jadwal.');
        }

        $request->validate([
            'catatan_revisi' => 'nullable|string|max:1000',
        ], [
            'catatan_revisi.max'      => 'Catatan revisi maksimal 1000 karakter.',
        ]);

        $tahunAkademik = $this->checkAccessTahunAkademik($idTahun);

        DB::transaction(function () use ($tahunAkademik, $request, $user) {
            $tahunAkademik->update([
                'status_validasi' => 'revisi',
                'catatan_revisi'  => $request->catatan_revisi,
                'validated_at'    => now(),
            ]);

            // Notifikasi untuk Admin
            $catatanText = $request->catatan_revisi ? "\"{$request->catatan_revisi}\"" : "(Tanpa catatan)";
            Notification::create([
                'user_id'     => null,
                'role_target' => 'admin',
                'judul'       => '⚠️ Permintaan Revisi Jadwal dari Dekan',
                'pesan'       => "Dekan ({$user->username}) mengembalikan Jadwal {$tahunAkademik->nama_tahunakademik} untuk dilakukan revisi. Catatan Revisi: {$catatanText}",
                'tipe'        => 'warning',
                'is_read'     => false,
            ]);

            // Notifikasi untuk Kaprodi (pemberitahuan jadwal sedang direvisi)
            Notification::create([
                'user_id'     => null,
                'role_target' => 'kaprodi',
                'judul'       => 'ℹ️ Penyesuaian Jadwal Kembali ke Admin',
                'pesan'       => "Jadwal periode {$tahunAkademik->nama_tahunakademik} saat ini dikembalikan ke Admin oleh Dekan untuk proses revisi.",
                'tipe'        => 'info',
                'is_read'     => false,
            ]);

            $this->recordHistory($tahunAkademik->id_tahunakademik, 'revisi_dekan', 'menunggu_persetujuan', 'revisi', $request->catatan_revisi);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success'        => true,
                'message'        => 'Jadwal telah dikembalikan ke Admin untuk dilakukan revisi.',
                'status'         => 'revisi',
                'catatan_revisi' => $request->catatan_revisi
            ]);
        }

        return back()->with('success', 'Jadwal telah dikembalikan kepada Admin untuk dilakukan revisi beserta catatan arahan Anda.');
    }

    /**
     * Membatalkan Persetujuan Jadwal (Kembali ke Draft / Revisi)
     */
    public function batalkanValidasi(Request $request, $idTahun)
    {
        $user = Auth::user();
        if (!$user || strtolower($user->role->nama_role ?? '') !== 'dekan') {
            return back()->with('error', 'Hanya Dekan yang dapat membatalkan persetujuan jadwal.');
        }

        $tahunAkademik = $this->checkAccessTahunAkademik($idTahun);

        DB::transaction(function () use ($tahunAkademik, $user) {
            $tahunAkademik->update([
                'status_validasi' => 'draft',
                'catatan_revisi'  => null,
                'validated_at'    => now(),
            ]);

            Notification::create([
                'user_id'     => null,
                'role_target' => 'admin',
                'judul'       => '⚠️ Persetujuan Jadwal Dibatalkan Dekan',
                'pesan'       => "Dekan ({$user->username}) membatalkan persetujuan/validasi jadwal periode {$tahunAkademik->nama_tahunakademik}. Status dikembalikan ke Draft.",
                'tipe'        => 'warning',
                'is_read'     => false,
            ]);

            $this->recordHistory($tahunAkademik->id_tahunakademik, 'batalkan_persetujuan', 'disetujui', 'draft');
        });

        return back()->with('success', 'Persetujuan jadwal berhasil dibatalkan. Status jadwal dikembalikan ke Draft.');
    }

    /**
     * Admin membatalkan pengajuan review ke Sekre Prodi
     */
    public function batalkanSekprodi($id)
    {
        $tahun = TahunAkademik::findOrFail($id);
        
        // Cek status saat ini
        if ($tahun->status_validasi !== 'review_sekprodi') {
            return redirect()->back()->with('error', 'Pembatalan gagal. Status saat ini tidak valid untuk dibatalkan.');
        }

        // Hapus data JadwalValidasiProdi
        \App\Models\JadwalValidasiProdi::where('id_tahunakademik', $id)->delete();

        // Kembalikan status menjadi draft
        $tahun->status_validasi = 'draft';
        $tahun->save();

        // Tambah history
        \App\Models\JadwalApprovalHistory::create([
            'id_tahunakademik' => $id,
            'actor_id' => Auth::id(),
            'role_actor' => Auth::user()->role->nama_role ?? 'Admin',
            'action' => 'batalkan_pengajuan_sekprodi',
            'notes' => 'Admin membatalkan pengajuan review ke Sekretaris Prodi'
        ]);

        return redirect()->back()->with('success', 'Pengajuan review ke Sekretaris Prodi berhasil dibatalkan. Status dikembalikan ke Draft.');
    }

    /**
     * Admin mengirim jadwal ke Sekre Prodi untuk direview
     */
    public function kirimSekprodi(Request $request, $idTahun)
    {
        $user = Auth::user();
        if (!$user || !in_array(strtolower($user->role->nama_role ?? ''), ['admin', 'kaprodi'])) {
            return back()->with('error', 'Hanya Admin / Pengelola Jadwal yang dapat mengirim jadwal untuk direview.');
        }

        $tahunAkademik = $this->checkAccessTahunAkademik($idTahun);
        $oldStatus = $tahunAkademik->status_validasi ?? 'draft';

        $prodiId = \App\Helpers\ProdiFilter::getProdiId();
        $kelasQuery  = \App\Models\Kelas::where('id_tahunakademik', $idTahun);
        $jadwalQuery = \App\Models\Jadwal::where('id_tahunakademik', $idTahun);

        if ($prodiId) {
            $kelasQuery->where('id_prodi', $prodiId);
            $jadwalQuery->whereHas('kelas', fn($q) => $q->where('id_prodi', $prodiId));
        }

        $kCount   = $kelasQuery->count();
        $jCount   = $jadwalQuery->pluck('id_kelas')->unique()->count();
        $pPercent = $kCount > 0 ? min(100, (int)round(($jCount / $kCount) * 100)) : 0;

        if ($kCount == 0 || $pPercent < 100) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal belum 100% disusun (' . $pPercent . '%). Review hanya dapat diajukan setelah seluruh kelas berhasil dijadwalkan.'
                ], 422);
            }
            return back()->with('error', 'Jadwal belum 100% disusun (' . $pPercent . '%). Review hanya dapat diajukan setelah seluruh kelas berhasil dijadwalkan.');
        }

        DB::transaction(function () use ($tahunAkademik, $user, $oldStatus, $idTahun) {
            $tahunAkademik->update([
                'status_validasi' => 'review_sekprodi',
                'catatan_revisi'  => null,
            ]);

            $prodis = \App\Models\Prodi::where('nama_prodi', 'NOT LIKE', '%Eksternal%')->get();
            foreach ($prodis as $prodi) {
                \App\Models\JadwalValidasiProdi::updateOrCreate(
                    ['id_tahunakademik' => $idTahun, 'id_prodi' => $prodi->id_prodi],
                    [
                        'status_sekprodi' => 'menunggu',
                        'status_kaprodi'  => 'menunggu',
                        'catatan_revisi_sekprodi' => null,
                        'catatan_revisi_kaprodi'  => null,
                    ]
                );
            }

            Notification::create([
                'user_id'     => null,
                'role_target' => 'sekretaris prodi',
                'judul'       => '📋 Review Jadwal Perkuliahan',
                'pesan'       => "Admin ({$user->username}) telah selesai menyusun jadwal periode {$tahunAkademik->nama_tahunakademik} dan mengirimkannya kepada Anda untuk direview.",
                'tipe'        => 'info',
                'is_read'     => false,
            ]);

            $this->recordHistory($tahunAkademik->id_tahunakademik, 'submit_sekprodi', $oldStatus, 'review_sekprodi');
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil dikirim ke Sekretaris Prodi untuk direview!',
                'status'  => 'review_sekprodi'
            ]);
        }

        return back()->with('success', 'Jadwal berhasil dikirim ke Sekretaris Prodi untuk direview! Notifikasi telah dikirimkan.');
    }

    /**
     * Sekretaris Prodi meminta revisi / memberikan komentar keluhan ke Admin
     */
    public function revisiSekprodi(Request $request, $idTahun)
    {
        $user = Auth::user();
        if (!$user || !in_array(strtolower($user->role->nama_role ?? ''), ['sekretaris prodi', 'admin', 'kaprodi'])) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk memberikan catatan revisi ini.');
        }

        $request->validate([
            'catatan_revisi' => 'nullable|string|max:1000',
        ], [
            'catatan_revisi.max' => 'Komentar maksimal 1000 karakter.',
        ]);

        $tahunAkademik = $this->checkAccessTahunAkademik($idTahun);
        $oldStatus = $tahunAkademik->status_validasi ?? 'review_sekprodi';

        DB::transaction(function () use ($tahunAkademik, $request, $user, $oldStatus, $idTahun) {
            $prodiId = \App\Helpers\ProdiFilter::getProdiId() ?: ($user->id_prodi ?? null);
            
            if ($prodiId) {
                \App\Models\JadwalValidasiProdi::where('id_tahunakademik', $idTahun)
                    ->where('id_prodi', $prodiId)
                    ->update([
                        'status_sekprodi' => 'revisi',
                        'catatan_revisi_sekprodi' => $request->catatan_revisi
                    ]);
            } else {
                $tahunAkademik->update([
                    'status_validasi' => 'revisi_sekprodi',
                    'catatan_revisi'  => $request->catatan_revisi,
                ]);
            }

            $catatanText = $request->catatan_revisi ? "\"{$request->catatan_revisi}\"" : "(Tanpa catatan)";
            $prodiName = $user->prodi->nama_prodi ?? '';
            Notification::create([
                'user_id'     => null,
                'role_target' => 'admin',
                'judul'       => "⚠️ Komentar / Keluhan Review dari Sekretaris Prodi $prodiName",
                'pesan'       => "Sekretaris Prodi $prodiName ({$user->username}) memberikan catatan evaluasi pada jadwal periode {$tahunAkademik->nama_tahunakademik}: {$catatanText}",
                'tipe'        => 'warning',
                'is_read'     => false,
            ]);

            $this->recordHistory($tahunAkademik->id_tahunakademik, 'revisi_sekprodi', $oldStatus, 'revisi_sekprodi', $request->catatan_revisi);
        });

        return back()->with('success', 'Komentar dan catatan keluhan berhasil dikirim kepada Admin.');
    }

    /**
     * Sekretaris Prodi menyetujui / meneruskan jadwal ke Kaprodi
     */
    public function setujuiSekprodi(Request $request, $idTahun)
    {
        $user = Auth::user();
        if (!$user || !in_array(strtolower($user->role->nama_role ?? ''), ['sekretaris prodi', 'admin', 'kaprodi'])) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk menyetujui jadwal ini.');
        }

        $tahunAkademik = $this->checkAccessTahunAkademik($idTahun);
        $oldStatus = $tahunAkademik->status_validasi ?? 'review_sekprodi';

        DB::transaction(function () use ($tahunAkademik, $user, $oldStatus, $idTahun) {
            $prodiId = \App\Helpers\ProdiFilter::getProdiId() ?: ($user->id_prodi ?? null);

            if ($prodiId) {
                \App\Models\JadwalValidasiProdi::where('id_tahunakademik', $idTahun)
                    ->where('id_prodi', $prodiId)
                    ->update([
                        'status_sekprodi' => 'disetujui'
                    ]);
            }

            // Check if all sekprodi approved
            $prodisValidasi = \App\Models\JadwalValidasiProdi::where('id_tahunakademik', $idTahun)->get();
            $allApproved = $prodisValidasi->every(function($vp) {
                return $vp->status_sekprodi === 'disetujui';
            });

            if ($allApproved && $tahunAkademik->status_validasi !== 'review_kaprodi') {
                $tahunAkademik->update([
                    'status_validasi' => 'review_kaprodi',
                    'catatan_revisi'  => null,
                ]);

                Notification::create([
                    'user_id'     => null,
                    'role_target' => 'kaprodi',
                    'judul'       => '📋 Pengajuan Review Jadwal dari Sekprodi',
                    'pesan'       => "Seluruh Sekretaris Prodi telah memeriksa jadwal periode {$tahunAkademik->nama_tahunakademik} dan mengajukannya kepada Anda untuk disetujui.",
                    'tipe'        => 'info',
                    'is_read'     => false,
                ]);
            }

            $prodiName = $user->prodi->nama_prodi ?? '';
            Notification::create([
                'user_id'     => null,
                'role_target' => 'admin',
                'judul'       => "✅ Review Sekprodi $prodiName Selesai",
                'pesan'       => "Sekretaris Prodi $prodiName telah memeriksa jadwal periode {$tahunAkademik->nama_tahunakademik}. Jadwal kini diteruskan kepada Kaprodi $prodiName.",
                'tipe'        => 'success',
                'is_read'     => false,
            ]);

            $this->recordHistory($tahunAkademik->id_tahunakademik, 'setujui_sekprodi', $oldStatus, $allApproved ? 'review_kaprodi' : $oldStatus);
        });

        return back()->with('success', 'Jadwal berhasil disetujui dan diteruskan kepada Kaprodi!');
    }

    /**
     * Kaprodi meminta revisi / memberikan komentar keluhan ke Admin
     */
    public function revisiKaprodi(Request $request, $idTahun)
    {
        $user = Auth::user();
        if (!$user || !in_array(strtolower($user->role->nama_role ?? ''), ['kaprodi', 'dekan', 'admin'])) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk memberikan catatan revisi ini.');
        }

        $request->validate([
            'catatan_revisi' => 'nullable|string|max:1000',
        ], [
            'catatan_revisi.max' => 'Catatan revisi maksimal 1000 karakter.',
        ]);

        $tahunAkademik = $this->checkAccessTahunAkademik($idTahun);
        $oldStatus = $tahunAkademik->status_validasi ?? 'review_kaprodi';

        DB::transaction(function () use ($tahunAkademik, $request, $user, $oldStatus, $idTahun) {
            $prodiId = \App\Helpers\ProdiFilter::getProdiId() ?: ($user->id_prodi ?? null);
            
            if ($prodiId) {
                \App\Models\JadwalValidasiProdi::where('id_tahunakademik', $idTahun)
                    ->where('id_prodi', $prodiId)
                    ->update([
                        'status_kaprodi' => 'revisi',
                        'catatan_revisi_kaprodi' => $request->catatan_revisi
                    ]);
            } else {
                $tahunAkademik->update([
                    'status_validasi' => 'revisi_kaprodi',
                    'catatan_revisi'  => $request->catatan_revisi,
                ]);
            }

            $catatanText = $request->catatan_revisi ? "\"{$request->catatan_revisi}\"" : "(Tanpa catatan)";
            $prodiName = $user->prodi->nama_prodi ?? '';
            Notification::create([
                'user_id'     => null,
                'role_target' => 'admin',
                'judul'       => "⚠️ Komentar / Revisi dari Kaprodi $prodiName",
                'pesan'       => "Kaprodi $prodiName ({$user->username}) memberikan catatan pada jadwal periode {$tahunAkademik->nama_tahunakademik}: {$catatanText}",
                'tipe'        => 'warning',
                'is_read'     => false,
            ]);

            $sekre = \App\Models\User::whereHas('role', fn($q) => $q->where('nama_role', 'sekretaris prodi'))->where('id_prodi', $prodiId)->first();
            if ($sekre) {
                Notification::create([
                    'user_id'     => $sekre->id_user ?? $sekre->id,
                    'role_target' => null,
                    'judul'       => 'ℹ️ Jadwal Dikembalikan Kaprodi ke Admin',
                    'pesan'       => "Kaprodi mengembalikan jadwal periode {$tahunAkademik->nama_tahunakademik} ke Admin untuk perbaikan.",
                    'tipe'        => 'info',
                    'is_read'     => false,
                ]);
            }

            $this->recordHistory($tahunAkademik->id_tahunakademik, 'revisi_kaprodi', $oldStatus, 'revisi_kaprodi', $request->catatan_revisi);
        });

        return back()->with('success', 'Catatan keluhan / revisi berhasil dikirim kepada Admin.');
    }

    /**
     * Kaprodi menyetujui jadwal perkuliahan
     */
    public function setujuiKaprodi(Request $request, $idTahun)
    {
        $user = Auth::user();
        if (!$user || !in_array(strtolower($user->role->nama_role ?? ''), ['kaprodi', 'dekan', 'admin'])) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk menyetujui jadwal ini.');
        }

        $tahunAkademik = $this->checkAccessTahunAkademik($idTahun);
        $oldStatus = $tahunAkademik->status_validasi ?? 'review_kaprodi';

        DB::transaction(function () use ($tahunAkademik, $user, $oldStatus, $idTahun) {
            $prodiId = \App\Helpers\ProdiFilter::getProdiId() ?: ($user->id_prodi ?? null);
            
            if ($prodiId) {
                \App\Models\JadwalValidasiProdi::where('id_tahunakademik', $idTahun)
                    ->where('id_prodi', $prodiId)
                    ->update([
                        'status_kaprodi' => 'disetujui'
                    ]);
            }

            // Check if all kaprodi approved
            $prodisValidasi = \App\Models\JadwalValidasiProdi::where('id_tahunakademik', $idTahun)->get();
            $allApproved = $prodisValidasi->every(function($vp) {
                return $vp->status_kaprodi === 'disetujui';
            });

            if ($allApproved && $tahunAkademik->status_validasi !== 'disetujui_kaprodi') {
                $tahunAkademik->update([
                    'status_validasi' => 'disetujui_kaprodi',
                    'catatan_revisi'  => null,
                ]);

                Notification::create([
                    'user_id'     => null,
                    'role_target' => 'admin',
                    'judul'       => '✅ Jadwal Disetujui Kaprodi (Seluruh Prodi)',
                    'pesan'       => "Seluruh Kaprodi telah menyetujui jadwal perkuliahan periode {$tahunAkademik->nama_tahunakademik}. Anda sekarang dapat mengajukan jadwal ke Dekan untuk validasi akhir.",
                    'tipe'        => 'success',
                    'is_read'     => false,
                ]);
            }

            $prodiName = $user->prodi->nama_prodi ?? '';
            $sekre = \App\Models\User::whereHas('role', fn($q) => $q->where('nama_role', 'sekretaris prodi'))->where('id_prodi', $prodiId)->first();
            
            if ($sekre) {
                Notification::create([
                    'user_id'     => $sekre->id_user ?? $sekre->id,
                    'role_target' => null,
                    'judul'       => '✅ Jadwal Disetujui Kaprodi',
                    'pesan'       => "Jadwal periode {$tahunAkademik->nama_tahunakademik} yang Anda teruskan telah disetujui oleh Kaprodi.",
                    'tipe'        => 'success',
                    'is_read'     => false,
                ]);
            }

            $this->recordHistory($tahunAkademik->id_tahunakademik, 'setujui_kaprodi', $oldStatus, $allApproved ? 'disetujui_kaprodi' : $oldStatus);
        });

        return back()->with('success', 'Jadwal berhasil disetujui! Admin kini dapat melanjutkan pengajuan ke Dekan.');
    }

    public function timeline(Request $request, $idTahun)
    {
        $this->checkAccessTahunAkademik($idTahun);

        $histories = \App\Models\JadwalApprovalHistory::with('user.role')
            ->where('id_tahunakademik', $idTahun)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id'            => $item->id,
                    'action'        => $item->aksi ?? $item->action ?? '-',
                    'old_status'    => $item->status_sebelumnya ?? $item->old_status ?? '-',
                    'new_status'    => $item->status_sesudah ?? $item->new_status ?? '-',
                    'notes'         => $item->catatan ?? $item->notes ?? '',
                    'user_name'     => $item->user->nama_user ?? ($item->user->username ?? 'System'),
                    'user_role'     => $item->user->role->nama_role ?? 'System',
                    'created_at'    => $item->created_at ? $item->created_at->translatedFormat('d F Y, H:i') : '-',
                    'time_ago'      => $item->created_at ? $item->created_at->diffForHumans() : '-',
                ];
            });

        return response()->json([
            'success'   => true,
            'histories' => $histories
        ]);
    }

    public function progress(Request $request, $idTahun)
    {
        $this->checkAccessTahunAkademik($idTahun);

        $validasiProdis = \App\Models\JadwalValidasiProdi::with('prodi')
            ->where('id_tahunakademik', $idTahun)
            ->whereHas('prodi', function($q) {
                $q->where('nama_prodi', 'NOT LIKE', '%Eksternal%');
            })
            ->get();

        $totalProdi = $validasiProdis->count();
        $approvedProdis = $validasiProdis->where('status_kaprodi', 'disetujui')->count();
        $progressApprovalPercent = $totalProdi > 0 ? (int)round(($approvedProdis / $totalProdi) * 100) : 0;

        $prodis = $validasiProdis->map(function($vp) {
            return [
                'nama_prodi' => $vp->prodi->nama_prodi ?? 'Prodi',
                'status_sekprodi' => $vp->status_sekprodi ?? 'menunggu_review',
                'status_kaprodi' => $vp->status_kaprodi ?? 'menunggu_review',
            ];
        });

        return response()->json([
            'success' => true,
            'prodis' => $prodis,
            'progress_percent' => $progressApprovalPercent
        ]);
    }

    /**
     * Tandai Notifikasi Sudah Dibaca
     */
    public function markNotificationRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => true]);

        $redirectUrl = $notification->url;

        if (!$redirectUrl) {
            $text = strtolower(($notification->judul ?? '') . ' ' . ($notification->pesan ?? ''));
            if (str_contains($text, 'pesan') || str_contains($text, 'obrolan')) {
                $redirectUrl = '#chat';
            } elseif (str_contains($text, 'jadwal') || str_contains($text, 'validasi') || str_contains($text, 'revisi') || str_contains($text, 'pengajuan') || str_contains($text, 'dekan') || str_contains($text, 'dosen')) {
                $redirectUrl = route('jadwal.index');
            } elseif (str_contains($text, 'kelas') || str_contains($text, 'prodi')) {
                $redirectUrl = route('kelas.index');
            } else {
                $redirectUrl = route('jadwal.index');
            }
        }

        return response()->json([
            'success'      => true,
            'redirect_url' => $redirectUrl
        ]);
    }

    /**
     * Tandai Semua Notifikasi Sudah Dibaca
     */
    public function markAllRead()
    {
        $userRole = strtolower(Auth::user()->role->nama_role ?? '');
        $userId   = Auth::id();

        Notification::where(function ($q) use ($userRole, $userId) {
            $q->where('user_id', $userId)
              ->orWhere('role_target', $userRole)
              ->orWhereNull('role_target');
        })->where('is_read', false)
          ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Hapus Catatan Revisi Aktif oleh Admin
     */
    public function hapusCatatanRevisi($idTahun)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return back()->with('error', 'Unauthenticated');
        }

        $tahunAkademik = \App\Models\TahunAkademik::findOrFail($idTahun);
        
        $tahunAkademik->update([
            'catatan_revisi' => null
        ]);

        return back()->with('success', 'Catatan revisi aktif berhasil dihapus/dibersihkan.');
    }

    /**
     * Hapus History Approval / Komentar
     */
    public function hapusHistory($id)
    {
        try {
            $history = \App\Models\JadwalApprovalHistory::findOrFail($id);
            $history->delete();
            return response()->json(['success' => true, 'message' => 'Komentar berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus komentar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Hapus Notifikasi Hanya Untuk User yang Mengeklik Hapus (Per-Role/User Isolated)
     */
    public function deleteNotification($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $notification = Notification::findOrFail($id);
        $deletedBy = $notification->deleted_by ?? [];

        if (!in_array((int) $user->id_user, array_map('intval', $deletedBy))) {
            $deletedBy[] = (int) $user->id_user;
            $notification->update(['deleted_by' => array_values(array_unique(array_map('intval', $deletedBy)))]);
        }

        return response()->json(['success' => true, 'message' => 'Notifikasi berhasil dihapus.']);
    }

    /**
     * Hapus Semua Notifikasi Hanya Untuk User yang Mengakses
     */
    public function deleteAllNotifications()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $userRole = strtolower($user->role->nama_role ?? '');
        $userId   = $user->id_user;

        $notifications = Notification::where(function ($q) use ($userRole, $userId) {
            $q->where('user_id', $userId)
              ->orWhere('role_target', $userRole)
              ->orWhereNull('role_target');
        })->get();

        foreach ($notifications as $notif) {
            $deletedBy = $notif->deleted_by ?? [];
            if (!in_array((int) $userId, array_map('intval', $deletedBy))) {
                $deletedBy[] = (int) $userId;
                $notif->update(['deleted_by' => array_values(array_unique(array_map('intval', $deletedBy)))]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Semua notifikasi berhasil dihapus dari akun Anda.']);
    }
}
