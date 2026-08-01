@props(['tahunAkademik'])

@php
    $user = Auth::user();
    $roleName = strtolower($user->role->nama_role ?? '');
    $isDekan = ($roleName === 'dekan');
    $isAdmin = in_array($roleName, ['admin', 'kaprodi']);

    $status = $tahunAkademik->status_validasi ?? 'draft';
    $catatanRevisi = $tahunAkademik->catatan_revisi;
    $validatedAt = $tahunAkademik->validated_at ? \Carbon\Carbon::parse($tahunAkademik->validated_at)->translatedFormat('d F Y, H:i') : null;

    // Progres Penyusunan Jadwal
    $kelasCount = \App\Models\Kelas::where('id_tahunakademik', $tahunAkademik->id_tahunakademik)->count();
    $jadwalCount = \App\Models\Jadwal::where('id_tahunakademik', $tahunAkademik->id_tahunakademik)->count();
    $progressPercent = $kelasCount > 0 ? min(100, (int)round(($jadwalCount / $kelasCount) * 100)) : 0;
    $isFullProgress = ($progressPercent >= 100);
@endphp

{{-- APABILA SUDAH DISETUJUI: PANEL VALIDASI HILANG, HANYA TAMPIL TOMBOL BATALKAN PERSETUJUAN --}}
@if($isDekan && $status === 'disetujui')
<div class="mb-6 bg-emerald-50 border-2 border-emerald-300 rounded-3xl p-5 shadow-md flex flex-col sm:flex-row items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-2xl bg-emerald-100 border-2 border-emerald-400 flex items-center justify-center text-emerald-700 shrink-0 shadow-sm">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <h4 class="text-base sm:text-lg font-black text-emerald-950">Jadwal Periode {{ $tahunAkademik->nama_tahunakademik }} Telah Resmi Disetujui</h4>
            <p class="text-xs sm:text-sm text-emerald-800 font-medium">Panel validasi disembunyikan. Jadwal telah aktif dan dipublikasikan kepada Dosen & Program Studi.</p>
        </div>
    </div>

    <form action="{{ route('jadwal.validasi.batalkan', $tahunAkademik->id_tahunakademik) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan persetujuan pada jadwal ini? Status akan dikembalikan ke Draft.')">
        @csrf
        <button type="submit" class="w-full sm:w-auto px-5 py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl font-extrabold text-xs sm:text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Batalkan Persetujuan Jadwal</span>
        </button>
    </form>
</div>
@endif

{{-- PANEL VALIDASI DEKAN (HANYA TAMPIL SAAT MENUNGGU PERSETUJUAN ATAU REVISI & JADWAL 100% TERUSUN) --}}
@if($isDekan && $isFullProgress && ($status === 'menunggu_persetujuan' || $status === 'revisi'))
<div x-data="{ 
    showModalSetujui: false, 
    showModalRevisi: false,
    catatanRevisiText: '{{ $status === "revisi" ? addslashes($catatanRevisi ?? "") : "" }}',
    isSubmitting: false
}" class="mb-8 bg-gradient-to-br from-amber-950 via-orange-900 to-amber-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-amber-500/30">
    
    <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-amber-500/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute right-20 top-0 w-32 h-32 bg-yellow-500/20 rounded-full blur-2xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div class="space-y-2 max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/30 border border-amber-400/40 text-amber-200 text-xs font-bold uppercase tracking-wider backdrop-blur-sm">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                <span>Tahap 4: Review & Persetujuan Dekan</span>
            </div>
            <h3 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                <span>🎓 Pengesahan Jadwal oleh Dekan</span>
            </h3>
            <p class="text-sm sm:text-base text-amber-100/90 leading-relaxed">
                Admin telah mengajukan jadwal perkuliahan periode <strong class="text-white">{{ $tahunAkademik->nama_tahunakademik }}</strong>. Silakan periksa kelayakan jadwal sebelum memberikan persetujuan final (pengesahan).
            </p>
            <div class="pt-2 flex items-center gap-4 text-xs font-medium text-amber-200/80">
                <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $jadwalCount }} dari {{ $kelasCount }} Kelas Dijadwalkan (100%)</span>
            </div>
            @if($status === 'revisi')
                <div class="mt-3 inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-rose-500/20 text-rose-200 border border-rose-500/30 text-xs font-bold backdrop-blur-md shadow-sm">
                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Sedang Dalam Proses Perbaikan oleh Admin (Menunggu Revisi Selesai)
                </div>
            @endif
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto shrink-0">
            @if($status === 'menunggu_persetujuan')
                {{-- Tombol Beri Komentar / Keluhan (Kembalikan ke Admin) --}}
                <button type="button" 
                    @click="showModalRevisi = true"
                    class="px-5 py-3.5 bg-rose-600/90 hover:bg-rose-600 text-white rounded-2xl font-bold text-sm shadow-lg shadow-rose-900/30 transition hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer border border-rose-400/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Kembalikan (Minta Revisi)</span>
                </button>

                {{-- Tombol Setujui Penjadwalan --}}
                <button type="button" 
                    @click="showModalSetujui = true"
                    class="w-full px-6 py-3.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 rounded-2xl font-black text-sm shadow-xl shadow-emerald-900/40 transition hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    <span>Setujui & Publikasikan</span>
                </button>
            @endif
        </div>
    </div>

    {{-- MODAL 1: KONFIRMASI SETUJUI DEKAN --}}
    <template x-teleport="body">
        <div x-show="showModalSetujui" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
                <div x-show="showModalSetujui" @click="showModalSetujui = false" class="fixed inset-0 transition-opacity bg-slate-900 opacity-60"></div>

                <div x-show="showModalSetujui" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     class="inline-block w-full max-w-xl p-6 sm:p-8 my-8 text-left bg-white rounded-3xl shadow-2xl transform transition-all relative z-10 border-2 border-emerald-500">
                    
                    <div class="flex items-center gap-4 mb-5 pb-4 border-b border-slate-100">
                        <div class="p-3.5 bg-emerald-100 text-emerald-600 rounded-2xl">
                            <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl sm:text-2xl font-black text-slate-900">Setujui & Publikasikan Jadwal</h3>
                            <p class="text-sm font-semibold text-slate-500 mt-0.5">Periode: {{ $tahunAkademik->nama_tahunakademik }}</p>
                        </div>
                    </div>

                    <div class="bg-emerald-50 border-2 border-emerald-300 rounded-2xl p-5 text-sm sm:text-base text-emerald-950 leading-relaxed mb-6 space-y-3">
                        <p class="font-extrabold text-emerald-900">Apakah Anda yakin ingin menyetujui dan mempublikasikan jadwal perkuliahan ini?</p>
                        <ul class="list-disc list-inside text-sm text-emerald-800 space-y-1.5 pt-1 font-medium">
                            <li>Jadwal akan dinyatakan <strong class="text-emerald-950 font-black">Final & Disetujui secara Resmi</strong>.</li>
                            <li>Sistem akan secara otomatis mengirimkan <strong class="text-emerald-950 font-black">Notifikasi Langsung</strong> kepada seluruh Dosen dan Program Studi.</li>
                        </ul>
                    </div>

                    <form action="{{ route('jadwal.validasi.setujui', $tahunAkademik->id_tahunakademik) }}" method="POST" @submit="isSubmitting = true">
                        @csrf
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="button" @click="showModalSetujui = false" class="px-6 py-3 border-2 border-slate-300 text-slate-700 rounded-2xl text-sm font-bold hover:bg-slate-100 transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" :disabled="isSubmitting" class="px-7 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-sm sm:text-base font-extrabold shadow-lg hover:shadow-xl transition flex items-center gap-2 cursor-pointer">
                                <svg x-show="isSubmitting" class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                                <span>Ya, Setujui & Publikasikan</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </template>

    {{-- MODAL 2: MINTA REVISI KE ADMIN --}}
    <template x-teleport="body">
        <div x-show="showModalRevisi" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
                <div x-show="showModalRevisi" @click="showModalRevisi = false" class="fixed inset-0 transition-opacity bg-slate-900 opacity-60"></div>

                <div x-show="showModalRevisi" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     class="inline-block w-full max-w-xl p-6 sm:p-8 my-8 text-left bg-white rounded-3xl shadow-2xl transform transition-all relative z-10 border-2 border-rose-500">
                    
                    <div class="flex items-center gap-4 mb-5 pb-4 border-b border-slate-100">
                        <div class="p-3.5 bg-rose-100 text-rose-600 rounded-2xl">
                            <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl sm:text-2xl font-black text-slate-900">Kembalikan ke Admin (Minta Revisi)</h3>
                            <p class="text-sm font-semibold text-slate-500 mt-0.5">Periode: {{ $tahunAkademik->nama_tahunakademik }}</p>
                        </div>
                    </div>

                    <form action="{{ route('jadwal.validasi.revisi', $tahunAkademik->id_tahunakademik) }}" method="POST" @submit="isSubmitting = true">
                        @csrf
                        
                        <div class="mb-6">
                            <label for="catatan_revisi" class="block text-base font-extrabold text-slate-800 mb-2">
                                Catatan / Arahan Revisi untuk Admin <span class="text-slate-400 text-sm font-normal">(Opsional)</span>
                            </label>
                            <textarea 
                                id="catatan_revisi"
                                name="catatan_revisi" 
                                x-model="catatanRevisiText"
                                rows="5" 
                                placeholder="Tuliskan petunjuk revisi secara rinci (opsional), contoh: Harap sesuaikan bentrok jadwal Dosen A di hari Selasa..."
                                class="w-full p-4 border-2 border-slate-300 rounded-2xl focus:ring-4 focus:ring-rose-200 focus:border-rose-500 text-base text-slate-900 shadow-inner placeholder-slate-400 font-medium leading-relaxed"
                            ></textarea>
                            <p class="text-xs sm:text-sm text-slate-500 mt-2 font-medium">Catatan ini akan dikirimkan sebagai <strong class="text-slate-700">notifikasi otomatis ke seluruh Admin</strong> agar segera diperbaiki.</p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                            <button type="button" @click="showModalRevisi = false" class="px-6 py-3 border-2 border-slate-300 text-slate-700 rounded-2xl text-sm font-bold hover:bg-slate-100 transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" :disabled="isSubmitting || !catatanRevisiText.trim()" class="px-7 py-3 bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white rounded-2xl text-sm sm:text-base font-extrabold shadow-lg hover:shadow-xl transition flex items-center gap-2 cursor-pointer">
                                <svg x-show="isSubmitting" class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                                <span>Kirim Catatan Revisi</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </template>

</div>
@endif
