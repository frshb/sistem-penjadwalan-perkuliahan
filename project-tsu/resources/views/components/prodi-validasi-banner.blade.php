@props(['tahunAkademik'])

@php
    $user = Auth::user();
    $roleName = strtolower($user->role->nama_role ?? '');
    $isSekprodi = ($roleName === 'sekretaris prodi');
    $isKaprodi  = ($roleName === 'kaprodi');
    $isAdmin    = ($roleName === 'admin');

    $status = $tahunAkademik->status_validasi ?? 'draft';
    $catatanRevisi = $tahunAkademik->catatan_revisi;

    // Progres Penyusunan Jadwal
    $kelasCount = \App\Models\Kelas::where('id_tahunakademik', $tahunAkademik->id_tahunakademik)->count();
    $jadwalCount = \App\Models\Jadwal::where('id_tahunakademik', $tahunAkademik->id_tahunakademik)->count();
    $progressPercent = $kelasCount > 0 ? min(100, (int)round(($jadwalCount / $kelasCount) * 100)) : 0;
    $isFullProgress = ($progressPercent >= 100);

    // Ambil komentar-komentar terbaru HANYA DARI KAPRODI
    $recentComments = \App\Models\JadwalApprovalHistory::with('user.role')
        ->where('id_tahunakademik', $tahunAkademik->id_tahunakademik)
        ->whereNotNull('catatan')
        ->where('catatan', '!=', '')
        ->where(function($q) {
            $q->whereHas('user.role', function($r) {
                $r->where('nama_role', 'like', '%kaprodi%');
            })->orWhere('aksi', 'like', '%kaprodi%');
        })
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

    // Custom Label Status untuk Prodi
    $displayStatus = strtoupper(str_replace('_', ' ', $status));
    if ($status === 'draft') {
        $displayStatus = 'BELUM DIAJUKAN (PROSES PENYUSUNAN OLEH ADMIN)';
    } elseif ($status === 'review_sekprodi' && $isKaprodi) {
        $displayStatus = 'SEDANG DIREVIEW SEKRETARIS PRODI';
    } elseif ($status === 'revisi_sekprodi' && $isKaprodi) {
        $displayStatus = 'DIKEMBALIKAN (REVISI) OLEH SEKRETARIS PRODI';
    } elseif ($status === 'disetujui_sekprodi' && $isKaprodi) {
        $displayStatus = 'DISETUJUI SEKPRODI (MENUNGGU ADMIN MENGIRIM KE KAPRODI)';
    }
@endphp

{{-- CARD CATATAN & KOMENTAR REVIEW UNTUK ADMIN, SEKPRODI, & KAPRODI --}}
@if(($isAdmin || $isSekprodi || $isKaprodi) && ($catatanRevisi || $recentComments->count() > 0))
<div class="mb-6 bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-amber-500/5 border-2 border-amber-300/80 rounded-3xl p-5 text-slate-800 shadow-sm relative overflow-hidden">
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div class="flex items-start gap-3 grow">
            <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shrink-0 shadow-md shadow-amber-200">
                💬
            </div>
            <div class="grow">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h4 class="text-sm font-black text-amber-950 uppercase tracking-wider flex items-center gap-2">
                        <span>Catatan & Komentar Review Jadwal</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-200 text-amber-900 border border-amber-300">
                            {{ $recentComments->count() }} Catatan
                        </span>
                    </h4>
                    <button type="button" onclick="bukaModalTimelineApproval()" class="text-xs font-bold text-amber-800 hover:text-amber-950 underline transition flex items-center gap-1 cursor-pointer">
                        <span>Lihat Riwayat Lengkap (Audit Trail)</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                @if($catatanRevisi)
                    <div class="mt-2.5 p-3.5 bg-white/95 rounded-2xl border border-amber-300/80 shadow-xs">
                        <div class="flex items-center justify-between text-xs text-amber-950 font-bold mb-1">
                            <span class="flex items-center gap-1.5 text-amber-900">
                                <span>⚠️</span> Catatan Revisi Aktif:
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-bold">
                                    Status: {{ strtoupper(str_replace('_', ' ', $status)) }}
                                </span>
                                <form action="{{ route('jadwal.validasi.hapus-catatan-revisi', $tahunAkademik->id_tahunakademik) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus/membersihkan catatan revisi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-5 h-5 rounded-full bg-rose-100 text-rose-600 hover:bg-rose-200 hover:text-rose-800 flex items-center justify-center transition cursor-pointer" title="Hapus Catatan Revisi Aktif">
                                        ✕
                                    </button>
                                </form>
                            </div>
                        </div>
                        <p class="text-xs text-slate-800 font-medium italic leading-relaxed">"{{ $catatanRevisi }}"</p>
                    </div>
                @endif

                @if($recentComments->count() > 0)
                    <div class="mt-3 space-y-2">
                        <p class="text-[11px] font-extrabold text-amber-900 uppercase tracking-wider">Komentar & Evaluasi Terbaru:</p>
                        @foreach($recentComments as $comm)
                            <div class="p-3 bg-white/80 rounded-2xl border border-amber-200/80 flex items-start justify-between gap-3 text-xs shadow-2xs">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-slate-900">{{ $comm->user->nama_user ?? ($comm->user->username ?? 'Pengguna') }}</span>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $comm->user->role->nama_role ?? 'System' }}
                                        </span>
                                        @if($comm->aksi)
                                            <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-md {{ str_contains(strtolower($comm->aksi), 'revisi') || str_contains(strtolower($comm->aksi), 'kembalikan') ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-teal-100 text-teal-800 border border-teal-200' }}">
                                                {{ $comm->aksi }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-slate-800 font-medium italic">"{{ $comm->catatan }}"</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] text-slate-400 font-semibold shrink-0">
                                        {{ $comm->created_at ? $comm->created_at->diffForHumans() : '' }}
                                    </span>
                                    <button type="button" onclick="hapusKomentarJadwal({{ $comm->id }})" class="w-6 h-6 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-100 hover:text-rose-700 flex items-center justify-center transition" title="Hapus Komentar">
                                        ✕
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

{{-- 1. PANEL REVIEW SEKRETARIS PRODI --}}
@if($isSekprodi && $isFullProgress && $status === 'review_sekprodi')
<div class="mb-8 bg-gradient-to-br from-teal-900 via-teal-800 to-emerald-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-teal-500/30">
    <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute right-20 top-0 w-32 h-32 bg-emerald-400/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div class="space-y-2 max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-500/20 border border-teal-400/30 text-teal-200 text-xs font-bold uppercase tracking-wider backdrop-blur-sm">
                <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                <span>Tahap 2: Review Sekretaris Prodi</span>
            </div>
            <h3 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                <span>📋 Pemeriksaan & Review Jadwal Kuliah</span>
            </h3>
            <p class="text-sm sm:text-base text-teal-100/90 leading-relaxed">
                Admin telah menyelesaikan penyusunan jadwal untuk periode <strong class="text-white">{{ $tahunAkademik->nama_tahunakademik }}</strong>. Silakan periksa kesesuaian mata kuliah, jam, ruangan, dan bentrok dosen.
            </p>
            <div class="pt-2 flex items-center gap-4 text-xs font-medium text-teal-200/80">
                <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $jadwalCount }} dari {{ $kelasCount }} Kelas Dijadwalkan (100%)</span>
            </div>
        </div>

        @php
            $uProdiId = Auth::user()->id_prodi ?? null;
            $mySekprodiValidasi = \App\Models\JadwalValidasiProdi::where('id_tahunakademik', $tahunAkademik->id_tahunakademik)->where('id_prodi', $uProdiId)->first();
            $isMySekprodiDone = $mySekprodiValidasi && $mySekprodiValidasi->status_sekprodi === 'disetujui';
        @endphp
        
        @if($isMySekprodiDone)
            <div class="flex items-center gap-2 px-5 py-3.5 bg-emerald-500/20 border border-emerald-400/30 rounded-2xl text-emerald-100 font-bold text-sm shadow-lg mt-4 sm:mt-0">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                <span>Anda telah menyetujui jadwal ini. Menunggu Sekretaris Prodi lainnya.</span>
            </div>
        @else
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto shrink-0">
                {{-- Tombol Beri Komentar / Keluhan (Kembalikan ke Admin) --}}
                <button type="button" 
                    onclick="bukaModalDecisionApproval('{{ route('jadwal.validasi.revisi-sekprodi', $tahunAkademik->id_tahunakademik) }}', '⚠️ Kembalikan Jadwal ke Admin', 'Berikan catatan atau keluhan terkait jadwal yang perlu diperbaiki oleh Admin.', true, 'revisi')"
                    class="px-5 py-3.5 bg-rose-600/90 hover:bg-rose-600 text-white rounded-2xl font-bold text-sm shadow-lg shadow-rose-900/30 transition hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer border border-rose-400/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Beri Komentar / Keluhan</span>
                </button>

                {{-- Tombol Ajukan ke Kaprodi --}}
                <form action="{{ route('jadwal.validasi.setujui-sekprodi', $tahunAkademik->id_tahunakademik) }}" method="POST" onsubmit="if(confirm('Apakah Anda yakin jadwal perkuliahan sudah sesuai dan ingin meneruskannya kepada Kaprodi?')){ return handleFormSubmit(this, 'Mengajukan...'); } return false;">
                    @csrf
                    <button type="submit" class="w-full px-6 py-3.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 rounded-2xl font-black text-sm shadow-xl shadow-emerald-900/40 transition hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        <span>Ajukan ke Kaprodi</span>
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endif

{{-- 2. PANEL REVIEW KAPRODI --}}
@if($isKaprodi && $isFullProgress && $status === 'review_kaprodi')
<div class="mb-8 bg-gradient-to-br from-indigo-950 via-slate-900 to-blue-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-indigo-500/30">
    <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute right-20 top-0 w-32 h-32 bg-blue-400/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div class="space-y-2 max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-400/30 text-indigo-200 text-xs font-bold uppercase tracking-wider backdrop-blur-sm">
                <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span>
                <span>Tahap 3: Review & Persetujuan Kaprodi</span>
            </div>
            <h3 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                <span>📋 Persetujuan Jadwal oleh Kepala Program Studi</span>
            </h3>
            <p class="text-sm sm:text-base text-indigo-100/90 leading-relaxed">
                Sekretaris Prodi telah memeriksa dan meneruskan jadwal periode <strong class="text-white">{{ $tahunAkademik->nama_tahunakademik }}</strong>. Silakan periksa kembali sebelum menyetujui jadwal untuk pengajuan ke Dekan.
            </p>
            <div class="pt-2 flex items-center gap-4 text-xs font-medium text-indigo-200/80">
                <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $jadwalCount }} dari {{ $kelasCount }} Kelas Dijadwalkan (100%)</span>
            </div>
        </div>

        @php
            $uProdiId = Auth::user()->id_prodi ?? null;
            $myKaprodiValidasi = \App\Models\JadwalValidasiProdi::where('id_tahunakademik', $tahunAkademik->id_tahunakademik)->where('id_prodi', $uProdiId)->first();
            $isMyKaprodiDone = $myKaprodiValidasi && $myKaprodiValidasi->status_kaprodi === 'disetujui';
        @endphp

        @if($isMyKaprodiDone)
            <div class="flex items-center gap-2 px-5 py-3.5 bg-emerald-500/20 border border-emerald-400/30 rounded-2xl text-emerald-100 font-bold text-sm shadow-lg mt-4 sm:mt-0">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                <span>Anda telah menyetujui jadwal ini. Menunggu Kaprodi lainnya.</span>
            </div>
        @else
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto shrink-0">
                {{-- Tombol Beri Komentar / Keluhan (Kembalikan ke Admin) --}}
                <button type="button" 
                    onclick="bukaModalDecisionApproval('{{ route('jadwal.validasi.revisi-kaprodi', $tahunAkademik->id_tahunakademik) }}', '⚠️ Kembalikan Jadwal ke Admin', 'Berikan catatan keluhan atau alasan revisi jadwal perkuliahan.', true, 'revisi')"
                    class="px-5 py-3.5 bg-rose-600/90 hover:bg-rose-600 text-white rounded-2xl font-bold text-sm shadow-lg shadow-rose-900/30 transition hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer border border-rose-400/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Beri Catatan Revisi</span>
                </button>

                {{-- Tombol Setujui Jadwal --}}
                <form action="{{ route('jadwal.validasi.setujui-kaprodi', $tahunAkademik->id_tahunakademik) }}" method="POST" onsubmit="if(confirm('Apakah Anda yakin menyetujui jadwal ini? Jika semua Kaprodi setuju, jadwal akan diteruskan ke Dekan.')){ return handleFormSubmit(this, 'Menyetujui...'); } return false;">
                    @csrf
                    <button type="submit" class="w-full px-6 py-3.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 rounded-2xl font-black text-sm shadow-xl shadow-emerald-900/40 transition hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Setujui Jadwal</span>
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endif

{{-- 3. BANNER INFO STATUS UNTUK PRODI JIKA TIDAK SEDANG DALAM TAHAP REVIEW MEREKA --}}
@if(($isSekprodi && $status !== 'review_sekprodi') || ($isKaprodi && $status !== 'review_kaprodi'))
<div class="mb-6 bg-slate-800/80 border border-slate-700/60 rounded-2xl p-4 text-slate-300 flex items-center justify-between flex-wrap gap-3">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-slate-700/80 flex items-center justify-center text-teal-400 shrink-0">
            ℹ️
        </div>
        <div>
            <h4 class="text-xs font-bold text-white uppercase tracking-wider">Status Jadwal Saat Ini: <span class="text-teal-300">{{ $displayStatus }}</span></h4>
            <p class="text-xs text-slate-400 mt-0.5">Anda sedang memantau jadwal perkuliahan periode {{ $tahunAkademik->nama_tahunakademik }}.</p>
        </div>
    </div>
    @if($status === 'disetujui_kaprodi')
        <span class="px-3 py-1.5 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-bold flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            Telah Disetujui Kaprodi (Menunggu Admin Ajukan ke Dekan)
        </span>
    @endif
</div>
@endif
<script>
function hapusKomentarJadwal(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus komentar ini?')) return;
    
    fetch(`/penjadwalan/history/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Gagal: ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan saat menghapus komentar.');
    });
}
</script>
