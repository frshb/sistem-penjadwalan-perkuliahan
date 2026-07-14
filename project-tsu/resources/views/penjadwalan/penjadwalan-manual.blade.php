<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="tahun-akademik-id" content="{{ $tahunAkademik->id_tahunakademik }}">
    <title>Penjadwalan Manual</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>

    /* ── History Panel ── */
        #hist-panel {
            position: fixed; right: 24px; bottom: 24px; z-index: 9998;
            width: 320px; background: #fff; border-radius: 16px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.18); border: 1px solid #e5e7eb;
            overflow: hidden; display: none; flex-direction: column; max-height: 480px;
        }
        #hist-panel.open { display: flex; }
        .hist-panel-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 16px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; flex-shrink: 0;
        }
        .hist-panel-head span { font-size: 13px; font-weight: 700; color: #1f2937; display: flex; align-items: center; gap: 6px; }
        .hist-panel-btns { display: flex; gap: 6px; align-items: center; }
        .hist-clear-btn { font-size: 12px; color: #ef4444; border: none; background: none; cursor: pointer; padding: 3px 8px; border-radius: 6px; font-weight: 600; }
        .hist-clear-btn:hover { background: #fef2f2; }
        .hist-close-btn { width: 26px; height: 26px; border-radius: 6px; border: none; background: none; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 16px; line-height: 1; }
        .hist-close-btn:hover { background: #f3f4f6; color: #374151; }
        .hist-list { flex: 1; overflow-y: auto; min-height: 0; }
        .hist-item { display: flex; align-items: flex-start; gap: 10px; padding: 10px 14px; border-bottom: 1px solid #f3f4f6; cursor: pointer; transition: background .1s; position: relative; }
        .hist-item:last-child { border-bottom: none; }
        .hist-item:hover { background: #f9fafb; }
        .hist-item.hist-current { background: #f0fdf4; }
        .hist-item.hist-future { opacity: .4; }
        .hist-current-bar { position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: #059669; border-radius: 0 2px 2px 0; }
        .hist-icon { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; margin-top: 1px; }
        .hist-icon-future { background: #f3f4f6; color: #9ca3af; }
        .hist-icon-add    { background: #f0fdf4; color: #059669; }
        .hist-icon-move   { background: #eff6ff; color: #2563eb; }
        .hist-icon-del    { background: #fef2f2; color: #dc2626; }
        .hist-icon-edit   { background: #fffbeb; color: #d97706; }
        .hist-icon-merge  { background: #f5f3ff; color: #7c3aed; }
        .hist-icon-split  { background: #fdf2f8; color: #be185d; }
        .hist-icon-reset  { background: #f9fafb; color: #6b7280; }
        .hist-icon-ruang  { background: #fffbeb; color: #d97706; }
        .hist-body { flex: 1; min-width: 0; }
        .hist-title { font-size: 12px; font-weight: 600; color: #1f2937; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .hist-meta { font-size: 11px; color: #9ca3af; margin-top: 2px; }
        .hist-time { font-size: 11px; color: #d1d5db; flex-shrink: 0; padding-top: 2px; }
        .hist-now-badge { display: inline-block; font-size: 10px; padding: 1px 5px; border-radius: 8px; background: #d1fae5; color: #059669; font-weight: 700; margin-left: 4px; vertical-align: middle; }
        .hist-empty { padding: 2rem; text-align: center; color: #9ca3af; font-size: 13px; line-height: 1.7; }
        .hist-footer { display: flex; gap: 8px; padding: 10px 12px; border-top: 1px solid #e5e7eb; background: #f9fafb; flex-shrink: 0; }
        .hist-foot-btn { flex: 1; padding: 8px; font-size: 13px; font-weight: 600; border-radius: 10px; border: 1.5px solid #e5e7eb; background: #fff; cursor: pointer; transition: background .15s; color: #374151; }
        .hist-foot-btn:hover:not(:disabled) { background: #f3f4f6; }
        .hist-foot-btn:disabled { opacity: .35; cursor: not-allowed; }
        [x-cloak] { display: none !important; }

        /* ── Modal Optimasi ── */
         #modal-optimasi-overlay {
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(0,0,0,0.45);
        display: flex; align-items: center; justify-content: center;
        opacity: 0; pointer-events: none;
        transition: opacity 0.2s ease;
    }
    #modal-optimasi-overlay.show {
        opacity: 1; pointer-events: auto;
    }
    #modal-optimasi-box {
        background: #fff; border-radius: 20px;
        width: 100%; max-width: 520px;
        padding: 0; overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.18);
        transform: translateY(16px) scale(0.98);
        transition: transform 0.25s cubic-bezier(.34,1.56,.64,1), opacity 0.2s ease;
        opacity: 0;
    }
    #modal-optimasi-overlay.show #modal-optimasi-box {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
    .modal-header {
        background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
        padding: 22px 28px 20px;
    }
    .modal-header h2 { color: #fff; font-size: 18px; font-weight: 700; margin: 0 0 4px; }
    .modal-header p  { color: rgba(255,255,255,0.75); font-size: 13px; margin: 0; }
    .modal-body { padding: 24px 28px; }

    /* ── Stat box di dalam modal hasil ── */
    .stat-box {
        text-align: center; border: 1.5px solid #e5e7eb;
        border-radius: 12px; padding: 14px 10px;
    }
    .stat-box .stat-num { font-size: 26px; font-weight: 700; line-height: 1; }
    .stat-box .stat-label { font-size: 11px; color: #9ca3af; margin-top: 4px; }

    .modal-footer {
        padding: 16px 28px 24px;
        display: flex; gap: 10px;
    }
    .btn-modal-cancel {
        flex: 1; padding: 12px; border-radius: 12px;
        border: 1.5px solid #e5e7eb; background: #fff;
        font-weight: 600; font-size: 14px; color: #6b7280;
        cursor: pointer; transition: background 0.15s;
    }
    .btn-modal-cancel:hover { background: #f9fafb; }
    .btn-modal-run {
        flex: 2; padding: 12px; border-radius: 12px;
        border: none; background: #0d9488;
        font-weight: 700; font-size: 14px; color: #fff;
        cursor: pointer; transition: background 0.15s;
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-modal-run:hover   { background: #0f766e; }
    .btn-modal-run:disabled{ background: #9ca3af; cursor: not-allowed; }
    .btn-modal-run svg { width: 18px; height: 18px; }

    /* ── Daftar bentrok sisa ── */
    .bentrok-sisa-item {
        background: #fff7ed; border: 1px solid #fed7aa;
        border-radius: 10px; padding: 12px 14px; font-size: 12px;
    }
    .bentrok-sisa-item .kelas-nama { font-weight: 700; color: #9a3412; font-size: 13px; }
    .bentrok-sisa-item .alasan    { color: #c2410c; margin-top: 4px; }

    @keyframes spin { to { transform: rotate(360deg); } }
    .spin { display: inline-block; animation: spin 1s linear infinite; }
    </style>
</head>

<body
    x-data="{
        showTablePreview: false,
        sidebarOpen: true,
        selectedDay: '{{ count($hari) > 0 ? strtolower($hari[0]->nama_hari) : 'senin' }}',
        showFilter: false,
        filterProdi: [],
        filterSemester: [],
        classSidebarOpen: true,
        focusMode: false,
    }"
    class="bg-gray-100/50 min-h-screen overflow-x-hidden"
>

@include('components.sidebar')

{{-- ============================================================ --}}
{{-- DATA JADWAL TERSIMPAN — dibaca JS saat halaman load           --}}
{{-- ============================================================ --}}

<script id="existing-jadwal-data" type="application/json">
    {!! $jadwalJson !!}
</script>
<script id="all-ruangan-data" type="application/json">
    {!! $ruangan->map(fn($r) => ['id' => $r->id_ruang, 'nama' => $r->nama_ruang])->toJson() !!}
</script>
<script id="hari-slot-mapping" type="application/json">
    {!! $hari->mapWithKeys(fn($h) => [strtolower($h->nama_hari) => $h->slotWaktus->pluck('id_slot')])->toJson() !!}
</script>

{{-- ============================================================ --}}
{{-- MODAL KONFIGURASI GENERATE                                    --}}
{{-- ============================================================ --}}
<div id="modal-optimasi-overlay" role="dialog" aria-modal="true" aria-labelledby="modal-optimasi-title">
    <div id="modal-optimasi-box">

        {{-- ── State 1: Konfirmasi sebelum jalankan ── --}}
        <div id="modal-state-konfirmasi">
            <div class="modal-header">
                <h2 id="modal-optimasi-title">🔧 Optimasi Jadwal Otomatis</h2>
                <p>Sistem akan mendeteksi dan memperbaiki bentrok dari hasil Algoritma Genetika.</p>
            </div>
            <div class="modal-body">

                {{-- Info proses --}}
                <div class="space-y-3 mb-6">
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-teal-50 border border-teal-100">
                        <span class="text-teal-500 text-lg mt-0.5">①</span>
                        <div>
                            <p class="text-sm font-semibold text-teal-800">Deteksi Bentrok</p>
                            <p class="text-xs text-teal-600 mt-0.5">Sistem memeriksa semua konflik dosen, ruangan, dan kelas dari jadwal otomatis.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-teal-50 border border-teal-100">
                        <span class="text-teal-500 text-lg mt-0.5">②</span>
                        <div>
                            <p class="text-sm font-semibold text-teal-800">Perbaiki Otomatis</p>
                            <p class="text-xs text-teal-600 mt-0.5">Setiap bentrok dicoba dipindahkan ke slot atau ruangan alternatif yang kosong.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-amber-50 border border-amber-100">
                        <span class="text-amber-500 text-lg mt-0.5">③</span>
                        <div>
                            <p class="text-sm font-semibold text-amber-800">Tandai Sisa Bentrok</p>
                            <p class="text-xs text-amber-600 mt-0.5">Bentrok yang tidak bisa diperbaiki otomatis ditampilkan di workspace untuk diedit manual.</p>
                        </div>
                    </div>
                </div>

                @if(!$adaJadwalOtomatis)
                <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 font-medium">
                    ⚠ Belum ada jadwal dari penjadwalan otomatis. Jalankan Algoritma Genetika terlebih dahulu.
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button class="btn-modal-cancel" onclick="tutupModalOptimasi()">Batal</button>
                <button
                    class="btn-modal-run"
                    id="btn-run-optimasi"
                    onclick="jalankanOptimasi()"
                    {{ !$adaJadwalOtomatis ? 'disabled' : '' }}
                >
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Jalankan Optimasi
                </button>
            </div>
        </div>

        {{-- ── State 2: Loading ── --}}
        <div id="modal-state-loading" style="display:none;">
            <div class="modal-header">
                <h2>🔄 Sedang Mengoptimasi...</h2>
                <p>Harap tunggu, sistem sedang memproses jadwal.</p>
            </div>
            <div class="modal-body">
                <div class="flex flex-col items-center py-8 gap-4">
                    <svg class="w-12 h-12 text-teal-500 spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    <p class="text-sm text-gray-500" id="loading-pesan">Mendeteksi bentrok...</p>
                </div>
            </div>
        </div>

        {{-- ── State 3: Hasil ── --}}
        <div id="modal-state-hasil" style="display:none;">
            <div class="modal-header" id="hasil-header">
                <h2 id="hasil-judul">✅ Optimasi Selesai</h2>
                <p id="hasil-subjudul">Jadwal berhasil dioptimasi.</p>
            </div>
            <div class="modal-body">
                {{-- Stat boxes --}}
                <div class="grid grid-cols-4 gap-2 mb-5" id="hasil-stats">
                    <div class="stat-box">
                        <div class="stat-num text-gray-700" id="stat-total">0</div>
                        <div class="stat-label">Total Jadwal</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-num text-red-500"   id="stat-awal">0</div>
                        <div class="stat-label">Bentrok Awal</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-num text-green-500" id="stat-fix">0</div>
                        <div class="stat-label">Diperbaiki</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-num text-orange-500" id="stat-sisa">0</div>
                        <div class="stat-label">Sisa Manual</div>
                    </div>
                </div>

                {{-- Daftar sisa bentrok (kalau ada) --}}
                <div id="sisa-bentrok-section" style="display:none;">
                    <p class="text-xs font-bold text-orange-600 uppercase tracking-wider mb-2">
                        Perlu diselesaikan manual:
                    </p>
                    <div id="sisa-bentrok-list" class="space-y-2 max-h-48 overflow-y-auto"></div>
                </div>

                {{-- Semua beres --}}
                <div id="semua-beres-section" class="text-center py-3 text-green-600 font-semibold text-sm" style="display:none;">
                    ✓ Tidak ada sisa bentrok. Jadwal sudah optimal!
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal-cancel" onclick="tutupModalOptimasi()">Tutup</button>
                <button class="btn-modal-run" onclick="tutupModalOptimasiDanRefresh()">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh Workspace
                </button>
            </div>
        </div>

    </div>{{-- /modal-optimasi-box --}}
</div>

{{-- ============================================================ --}}
{{-- HISTORY PANEL                                                --}}
{{-- ============================================================ --}}
<div id="hist-panel" role="dialog" aria-label="Riwayat Perubahan">
    <div class="hist-panel-head">
        <span>
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Riwayat Perubahan
        </span>
        <div class="hist-panel-btns">
            <button class="hist-clear-btn" onclick="historyClear()">Hapus</button>
            <button class="hist-close-btn" onclick="tutupHistoryPanel()">✕</button>
        </div>
    </div>
    <div class="hist-list" id="hist-list">
        <div class="hist-empty" id="hist-empty">
            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto 8px;opacity:.3;display:block">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Belum ada perubahan.<br>Mulai menyusun jadwal<br>untuk merekam riwayat.
        </div>
    </div>
    <div class="hist-footer">
        <button class="hist-foot-btn" id="hist-undo-panel-btn" onclick="historyUndo()" disabled>↩ Undo</button>
        <button class="hist-foot-btn" id="hist-redo-panel-btn" onclick="historyRedo()" disabled>Redo ↪</button>
    </div>
</div>

<main :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'" class="transition-all duration-300 p-6 sm:p-8">

    {{-- HEADER --}}
    <div class="flex items-center justify-between" x-show="!focusMode" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2">
        <div>
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="flex flex-col hover:opacity-80 transition cursor-pointer" title="Toggle Sidebar">
                    <div class="w-2 h-5 bg-teal-600 rounded-tl-md"></div>
                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                </button>
                <h1 class="text-3xl font-bold text-gray-800">Penjadwalan Manual</h1>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mt-2">
                <a href="{{ route('jadwal.pilih-tahun') }}" class="hover:text-teal-600 transition font-medium">
                    Modul Penjadwalan
                </a>
                <span>/</span>
                <span class="text-teal-600 font-semibold">{{ $tahunAkademik->nama_tahunakademik }}</span>
            </div>
        </div>
        @include('components.header-profile')
    </div>

    {{-- ACTION BAR --}}
    <div class="mt-8" :class="focusMode ? 'mt-0 bg-white p-3 rounded-2xl border border-gray-150 shadow-sm' : ''">
        <div class="flex flex-wrap items-center justify-between gap-2">

            {{-- Grup Kiri --}}
            <div class="flex flex-wrap items-center gap-2">

                <!-- Focus Mode Badge -->
                <div x-show="focusMode" x-cloak
                    class="text-sm font-bold text-teal-800 bg-teal-50 px-3.5 py-2 rounded-xl border border-teal-200 flex items-center gap-2">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span>
                    </span>
                    <span class="hidden sm:inline">Periode:</span>
                    <span>{{ $tahunAkademik->nama_tahunakademik }}</span>
                </div>

                <!-- Optimasi -->
                <button
                    id="btn-optimasi"
                    onclick="bukaModalOptimasi()"
                    class="px-3 sm:px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-semibold shadow flex items-center gap-2 transition text-sm"
                >
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span class="hidden sm:inline">Optimasi Jadwal</span>
                    <span class="sm:hidden">Optimasi</span>
                </button>

                <!-- Lihat Bentrok -->
                <button
                    id="btn-lihat-bentrok"
                    onclick="lihatBentrok()"
                    class="px-3 sm:px-5 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl font-medium flex items-center gap-2 transition text-sm"
                >
                    <svg class="w-4 h-4 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="hidden sm:inline">Lihat Bentrok</span>
                    <span class="sm:hidden">Bentrok</span>
                </button>

                <!-- Reset -->
                <button
                    onclick="resetWorkspace()"
                    class="px-3 sm:px-5 py-2.5 bg-red-50 hover:bg-red-100 border border-red-200 text-red-600 rounded-xl font-semibold flex items-center gap-2 transition text-sm"
                >
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span class="hidden sm:inline">Reset Jadwal</span>
                    <span class="sm:hidden">Reset</span>
                </button>

                <!-- Simpan -->
                <button
                    onclick="simpanSemuaJadwal()"
                    id="btn-simpan-semua"
                    class="px-3 sm:px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold shadow flex items-center gap-2 transition text-sm"
                >
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span id="btn-simpan-label">
                        <span class="hidden sm:inline">Simpan Jadwal</span>
                        <span class="sm:hidden">Simpan</span>
                    </span>
                </button>

                <!-- Undo -->
                <button
                    id="hist-undo-btn"
                    onclick="historyUndo()"
                    disabled
                    title="Undo (Ctrl+Z)"
                    class="px-3 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl font-medium flex items-center gap-1.5 transition text-sm disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14L4 9m0 0l5-5M4 9h11a6 6 0 010 12h-1"/>
                    </svg>
                    <span class="hidden md:inline">Undo</span>
                </button>

                <!-- Redo -->
                <button
                    id="hist-redo-btn"
                    onclick="historyRedo()"
                    disabled
                    title="Redo (Ctrl+Y)"
                    class="px-3 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl font-medium flex items-center gap-1.5 transition text-sm disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 14l5-5m0 0l-5-5m5 5H9a6 6 0 000 12h1"/>
                    </svg>
                    <span class="hidden md:inline">Redo</span>
                </button>

                <!-- History -->
                <button
                    id="hist-toggle-btn"
                    onclick="bukaHistoryPanel()"
                    title="Riwayat perubahan"
                    class="px-3 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl font-medium flex items-center gap-1.5 transition text-sm"
                >
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span id="hist-stack-info" class="hidden md:inline">Riwayat</span>
                </button>

            </div>

            {{-- Grup Kanan --}}
            <div class="flex flex-wrap items-center gap-2">

                <!-- Toggle Sidebar Kelas -->
                <button
                    @click="classSidebarOpen = !classSidebarOpen"
                    :class="classSidebarOpen ? 'bg-teal-50 border-teal-200 text-teal-700 hover:bg-teal-100' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'"
                    class="px-3 sm:px-4 py-2.5 border rounded-xl font-semibold flex items-center gap-2 transition text-sm shadow-sm"
                >
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                    <span class="hidden lg:inline" x-text="classSidebarOpen ? 'Sembunyikan Kelas' : 'Tampilkan Kelas'"></span>
                    <span class="lg:hidden" x-text="classSidebarOpen ? 'Kelas ✕' : 'Kelas'"></span>
                </button>

                <!-- Mode Fokus -->
                <button
                    @click="focusMode = !focusMode; sidebarOpen = !focusMode; classSidebarOpen = !focusMode"
                    :class="focusMode ? 'bg-gray-800 border-transparent text-white hover:bg-gray-900' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'"
                    class="px-3 sm:px-4 py-2.5 border rounded-xl font-semibold flex items-center gap-2 transition text-sm shadow-sm"
                >
                    <svg class="w-4 h-4 flex-shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="focusMode" style="display:none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!focusMode">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"/>
                    </svg>
                    <span class="hidden lg:inline" x-text="focusMode ? 'Normal View' : 'Workspace Fokus'"></span>
                    <span class="lg:hidden" x-text="focusMode ? 'Normal' : 'Fokus'"></span>
                </button>

                <!-- Tampilan Tabel -->
                <button
                    @click="showTablePreview = !showTablePreview"
                    class="flex items-center gap-2 px-3 sm:px-4 py-2.5 bg-teal-700 hover:bg-teal-800 text-white rounded-xl font-semibold shadow transition border-2 border-dashed border-teal-400 text-sm"
                >
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/>
                    </svg>
                    <span class="hidden sm:inline" x-show="!showTablePreview">Tampilan Tabel</span>
                    <span class="hidden sm:inline" x-show="showTablePreview">Tutup Tabel</span>
                    <span class="sm:hidden">Tabel</span>
                </button>

            </div>
        </div>
    </div>

    {{-- CONTENT --}}
    <div
        :class="focusMode ? 'h-[calc(100vh-100px)] mt-4 gap-4' : 'h-[calc(100vh-170px)] mt-8 gap-6'"
        class="grid grid-cols-12 overflow-hidden transition-all duration-300"
    >

        {{-- SIDEBAR KELAS --}}
        <div
            x-show="classSidebarOpen"
            x-transition:enter="transition ease-out duration-350"
            x-transition:enter-start="opacity-0 -translate-x-10"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-250"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 -translate-x-10"
            class="col-span-12 xl:col-span-3 min-h-0 flex relative z-10"
        >
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 h-full flex flex-col w-full overflow-visible">

                <div class="p-5 border-b border-gray-100 rounded-t-2xl bg-white relative z-30">
                    <h2 class="text-lg font-bold text-gray-800">Daftar Kelas</h2>

                    <div class="mt-5 flex items-center gap-2">
                        <div class="relative flex-1">
                            <input
                                type="text"
                                id="search-kelas"
                                placeholder="Cari kelas / mata kuliah..."
                                class="w-full pl-4 pr-10 py-3 rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-teal-500 outline-none text-sm"
                                oninput="applyFilter()"
                            >
                            <svg class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
                            </svg>
                        </div>

                        <div class="relative">
                            <button
                                @click="showFilter = !showFilter"
                                :class="(filterProdi.length || filterSemester.length) ? 'border-teal-500 bg-teal-50' : 'border-gray-200 hover:bg-gray-50'"
                                class="w-12 h-12 rounded-xl border flex items-center justify-center transition relative"
                            >
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M6 12h12M10 20h4"/>
                                </svg>
                                <span x-show="filterProdi.length || filterSemester.length" class="absolute -top-1 -right-1 w-3 h-3 bg-teal-500 rounded-full border-2 border-white"></span>
                            </button>

                            <div
                                x-show="showFilter"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                                @click.outside="showFilter = false"
                                class="absolute right-0 top-14 w-72 max-w-[85vw] max-h-[70vh] overflow-y-auto bg-white rounded-2xl shadow-2xl border border-gray-100 z-[100]"
                                x-cloak
                            >
                                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white">
                                    <h3 class="font-bold text-gray-800 text-sm">Filter Kelas</h3>
                                    <button @click="filterProdi = []; filterSemester = []; applyFilter()" class="xs text-red-500 hover:text-red-600 font-medium transition">Reset</button>
                                </div>
                                <div class="px-5 py-4 space-y-5">
                                    <div>
                                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Program Studi</p>
                                        <div class="space-y-2">
                                            @foreach ($kelas->pluck('prodi.nama_prodi')->unique()->filter()->values() as $prodi)
                                            <label class="flex items-center gap-3 cursor-pointer group">
                                                <input type="checkbox" :value="'{{ $prodi }}'" x-model="filterProdi" @change="applyFilter()" class="w-4 h-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500 cursor-pointer">
                                                <span class="text-sm text-gray-700 group-hover:text-teal-600 transition">{{ $prodi }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="border-t border-gray-100"></div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Semester</p>
                                        <div class="grid grid-cols-2 gap-2">
                                            @foreach ($kelas->pluck('semester')->unique()->sort()->values() as $sem)
                                            <label class="flex items-center gap-3 cursor-pointer group">
                                                <input type="checkbox" :value="'{{ $sem }}'" x-model="filterSemester" @change="applyFilter()" class="w-4 h-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500 cursor-pointer">
                                                <span class="text-sm text-gray-700 group-hover:text-teal-600 transition">Semester {{ $sem }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 sticky bottom-0">
                                    <p class="text-xs text-gray-400 text-center" id="filter-count">0 kelas ditampilkan</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 border-b border-gray-100">
                        <div class="flex items-center gap-6 text-sm font-semibold">
                            <button id="tab-belum" onclick="setActiveTab('belum')" class="pb-3 border-b-2 border-teal-500 text-teal-600 flex items-center gap-2">
                                Belum Dijadwalkan
                                <span id="count-belum" class="px-2 py-0.5 rounded-full bg-teal-100 text-teal-700 text-xs">{{ $kelas->filter(fn($k) => $k->jadwals->count() == 0)->count() }}</span>
                            </button>
                            <button id="tab-sudah" onclick="setActiveTab('sudah')" class="pb-3 text-gray-500 hover:text-green-600 flex items-center gap-2">
                                Sudah
                                <span id="count-sudah" class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs">{{ $kelas->filter(fn($k) => $k->jadwals->count() > 0)->count() }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto min-h-0 px-2 pb-2 rounded-b-2xl">
                    @forelse ($kelas as $item)
                    <div
                        id="kelas-{{ $item->id_kelas }}"
                        class="kelas-item bg-white border border-gray-200 rounded-2xl p-4 mx-3 my-3 hover:shadow-md hover:border-teal-300 cursor-move transition-all duration-200"
                        draggable="true"
                        data-id="{{ $item->id_kelas }}"
                        data-prodi="{{ $item->prodi->nama_prodi ?? '-' }}"
                        data-dosen-id="{{ $item->dosen->id_dosen ?? '' }}"
                        data-status="{{ $item->jadwals->count() > 0 ? 'sudah' : 'belum' }}"
                        data-sks="{{ $item->matakuliah->sks ?? 1 }}"
                        data-nama="{{ $item->matakuliah->nama_matkul ?? '-' }}"
                        data-kode-mk="{{ $item->matakuliah->kode_matkul ?? '-' }}"
                        data-kelas="{{ $item->nama_kelas }}"
                        data-dosen="{{ $item->dosen->nama_dosen ?? '-' }}"
                        data-ruangans="{{ json_encode($item->matakuliah?->ruangans?->map(fn($r) => ['id' => $r->id_ruang, 'nama' => $r->nama_ruang]) ?? []) }}"
                        data-jenis="{{ $item->matakuliah->jenis ?? 'Teori' }}"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-bold text-gray-800">{{ $item->nama_kelas }}</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ $item->matakuliah->nama_matkul ?? '-' }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $item->dosen->nama_dosen ?? '-' }}</p>
                                <div class="flex flex-wrap items-center gap-2 mt-3">
                                    <span class="text-xs px-2 py-1 bg-teal-50 text-teal-700 rounded-lg font-mono">{{ $item->matakuliah->kode_matkul ?? '-' }}</span>
                                    <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded-lg">{{ $item->matakuliah->sks ?? 0 }} SKS</span>
                                    <span class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded-lg">Semester {{ $item->semester }}</span>
                                    <span class="text-xs px-2 py-1 bg-purple-100 text-purple-700 rounded-lg">{{ $item->prodi->nama_prodi ?? '-' }}</span>
                                </div>
                            </div>
                            <span class="status-badge px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1 {{ $item->jadwals->count() > 0 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }}">
                                <span class="w-2 h-2 rounded-full {{ $item->jadwals->count() > 0 ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                {{ $item->jadwals->count() > 0 ? 'Sudah' : 'Belum' }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-gray-500">Data kelas belum tersedia.</div>
                    @endforelse
                </div>

            </div>
        </div>

        {{-- WORKSPACE --}}
        <div
            :class="classSidebarOpen ? 'xl:col-span-9' : 'xl:col-span-12'"
            class="col-span-12 flex flex-col xl:flex-row gap-4 min-h-0 transition-all duration-300 relative z-10"
        >
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 w-full flex flex-col overflow-hidden">

                <div class="p-5 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-800">Workspace Jadwal</h2>
                        <div class="flex items-center gap-1 sm:gap-2 overflow-x-auto pb-1 sm:pb-0 hide-scrollbar">
                            @foreach ($hari as $h)
                            @php $namaHari = strtolower($h->nama_hari); @endphp
                            <button
                                @click="selectedDay = '{{ $namaHari }}'; filterCardsByDay('{{ $namaHari }}')"
                                :class="selectedDay === '{{ $namaHari }}' ? 'bg-teal-600 text-white' : 'border border-gray-200 hover:bg-gray-50 text-gray-700'"
                                class="px-3 sm:px-6 py-1.5 sm:py-2 rounded-xl font-semibold transition capitalize whitespace-nowrap text-xs sm:text-sm"
                            >{{ $h->nama_hari }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="relative flex-1 overflow-auto">
                    @foreach ($slotWaktu as $slot)
                    <div class="grid grid-cols-12 border-b border-gray-200 h-[140px]
                    {{ $loop->iteration === 6 ? 'bg-amber-50 border-l-4 border-l-amber-400' : '' }}">
                        <div class="col-span-2 sticky left-0 border-r border-gray-200 px-3 py-3 flex flex-col justify-start z-20 shadow-[2px_0_5px_rgba(0,0,0,0.03)]
                            {{ $loop->iteration === 6 ? 'bg-amber-50' : 'bg-white' }}">

                            {{-- Waktu mulai --}}
                            <div class="text-[14px] font-bold text-gray-900">
                                {{ \Carbon\Carbon::parse($slot->waktu_mulai)->format('H:i') }}
                            </div>

                            {{-- Slot label --}}
                            <div class="text-[13px] text-gray-800 mt-1">
                                {{ $loop->iteration === 6 ? '🕐 Istirahat' : 'Slot ' . $loop->iteration }}
                            </div>

                            {{-- Range waktu --}}
                            <div class="text-[12px] text-gray-700">
                                {{ \Carbon\Carbon::parse($slot->waktu_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->waktu_selesai)->format('H:i') }}
                            </div>

                        </div>
                        <div
                            class="col-span-10 border-r border-gray-100"
                            data-slot-line="{{ $slot->id_slot }}"
                            data-jam-mulai="{{ $slot->waktu_mulai }}"
                            data-jam-selesai="{{ $slot->waktu_selesai }}"
                            data-is-istirahat="{{ $loop->iteration === 6 ? '1' : '0' }}"
                        ></div>
                    </div>
                    @endforeach

                    <div id="jadwal-layer" class="absolute top-0 left-[16.666667%] right-0 bottom-0" style="pointer-events: none;"></div>
                </div>

            </div>

            {{-- DETAIL PANEL --}}
            <div id="detail-panel" class="hidden w-full lg:w-72 bg-white rounded-2xl shadow-sm border border-gray-100 flex-col overflow-hidden flex-shrink-0">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-teal-500 rounded-full"></span>
                        <h3 class="font-bold text-gray-800 text-sm">Detail Jadwal</h3>
                    </div>
                    <button onclick="closeDetailPanel()" class="text-gray-400 hover:text-gray-600 transition w-7 h-7 rounded-lg hover:bg-gray-100 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="px-5 pt-4">
                    <span class="px-3 py-1 rounded-full bg-teal-100 text-teal-700 text-xs font-semibold">Terjadwal</span>
                </div>
                <div class="px-5 py-4 space-y-4 flex-1 overflow-y-auto">
                    <div><p class="text-xs text-gray-400 font-medium mb-0.5">Mata Kuliah</p><p id="dp-nama" class="text-sm font-bold text-gray-800"></p></div>
                    <div><p class="text-xs text-gray-400 font-medium mb-0.5">Kode MK</p><p id="dp-kode" class="text-sm font-semibold text-teal-700 font-mono"></p></div>
                    <div><p class="text-xs text-gray-400 font-medium mb-0.5">Kelas</p><p id="dp-kelas" class="text-sm font-semibold text-gray-800"></p></div>
                    <div><p class="text-xs text-gray-400 font-medium mb-0.5">Dosen</p><p id="dp-dosen" class="text-sm font-semibold text-gray-800"></p></div>
                    <div><p class="text-xs text-gray-400 font-medium mb-0.5">Hari</p><p id="dp-hari" class="text-sm font-semibold text-gray-800"></p></div>
                    <div><p class="text-xs text-gray-400 font-medium mb-0.5">Waktu</p><p id="dp-waktu" class="text-sm font-semibold text-gray-800"></p></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><p class="text-xs text-gray-400 font-medium mb-0.5">Durasi</p><p id="dp-sks" class="text-sm font-semibold text-gray-800"></p></div>
                        <div><p class="text-xs text-gray-400 font-medium mb-0.5">Semester</p><p id="dp-semester" class="text-sm font-semibold text-gray-800"></p></div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium mb-0.5">Ruangan</p>
                        <select id="dp-ruangan-select" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-teal-500 outline-none" onchange="updateCardRuangan(this.value, this.options[this.selectedIndex].text)">
                            <option value="">-- Pilih Ruangan --</option>
                        </select>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-100 flex flex-col gap-2">
                    <button id="dp-btn-split" class="hidden flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-xl text-sm font-semibold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/>
                        </svg>
                        Pisahkan
                    </button>
                    <button
                        id="dp-btn-simpan"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-semibold transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan Perubahan
                    </button>
                    <button id="dp-btn-hapus" class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl text-sm font-semibold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Hapus
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- TABLE PREVIEW --}}
    <div
        x-show="showTablePreview"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-10"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="mt-10 bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden"
    >
        <div class="px-6 py-5 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Preview Tabel Jadwal</h2>
                    <p class="text-sm text-gray-500 mt-1">Konversi workspace menjadi format tabel export.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('jadwal.export.excel', $tahunAkademik->id_tahunakademik) }}"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium flex items-center gap-2 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export Excel
                    </a>
                    <a href="{{ route('jadwal.export.pdf', $tahunAkademik->id_tahunakademik) }}"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium flex items-center gap-2 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Export PDF
                    </a>
                </div>
            </div>

            {{-- ── FILTER & SEARCH BAR ── --}}
            <div class="mt-5 space-y-3">
                {{-- Search --}}
                <div class="relative">
                    <input
                        type="text"
                        id="preview-search"
                        placeholder="Cari dosen, mata kuliah, atau kelas..."
                        oninput="applyPreviewFilter()"
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 focus:ring-2 focus:ring-teal-500 outline-none text-sm"
                    >
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
                    </svg>
                </div>

                {{-- Filter chips --}}
                <div class="flex flex-wrap gap-2">
                    {{-- Hari --}}
                    <select id="pf-hari" onchange="applyPreviewFilter()" class="w-full bg-white border border-gray-200 text-gray-700 text-xs rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-teal-500 focus:border-teal-500 shadow-sm appearance-none cursor-pointer pr-7 bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%20fill%3D%22%236b7280%22%3E%3Cpath%20fill-rule%3D%22evenodd%22%20d%3D%22M5.293%207.293a1%201%200%20011.414%200L10%2010.586l3.293-3.293a1%201%200%20111.414%201.414l-4%204a1%201%200%2001-1.414%200l-4-4a1%201%200%20010-1.414z%22%20clip-rule%3D%22evenodd%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1rem] bg-[position:right_0.5rem_center] bg-no-repeat">
                        <option value="">Semua Hari</option>
                        @foreach ($hari as $h)
                        <option value="{{ ucfirst(strtolower($h->nama_hari)) }}">{{ ucfirst(strtolower($h->nama_hari)) }}</option>
                        @endforeach
                    </select>

                    {{-- Prodi --}}
                    <select id="pf-prodi" onchange="applyPreviewFilter()"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-teal-500 outline-none">
                        <option value="">Semua Prodi</option>
                    </select>

                    {{-- Semester --}}
                    <select id="pf-semester" onchange="applyPreviewFilter()"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-teal-500 outline-none">
                        <option value="">Semua Semester</option>
                    </select>

                    {{-- Sesi --}}
                    <select id="pf-sesi" onchange="applyPreviewFilter()"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-teal-500 outline-none">
                        <option value="">Semua Sesi</option>
                        <option value="Pagi">Pagi</option>
                        <option value="Malam">Malam</option>
                    </select>

                    {{-- Kurikulum --}}
                    <select id="pf-kurikulum" onchange="applyPreviewFilter()"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-teal-500 outline-none">
                        <option value="">Semua Kurikulum</option>
                    </select>

                    {{-- Reset --}}
                    <button onclick="resetPreviewFilter()"
                        class="px-4 py-2 text-sm rounded-xl border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 font-medium transition">
                        Reset Filter
                    </button>

                    {{-- Info hasil --}}
                    <span id="preview-filter-info" class="ml-auto self-center text-xs text-gray-400"></span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-teal-700 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left">Hari</th>
                        <th class="px-4 py-3 text-left">Prodi</th>
                        <th class="px-4 py-3 text-left">Semester</th>
                        <th class="px-4 py-3 text-left">Sesi</th>
                        <th class="px-4 py-3 text-left">Kelas</th>
                        <th class="px-4 py-3 text-left">Kode MK</th>
                        <th class="px-4 py-3 text-left">Mata Kuliah</th>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-left">Durasi</th>
                        <th class="px-4 py-3 text-left">Dosen</th>
                        <th class="px-4 py-3 text-left">Ruangan</th>
                        <th class="px-4 py-3 text-left">Jam Mulai</th>
                        <th class="px-4 py-3 text-left">Jam Selesai</th>
                    </tr>
                </thead>
                <tbody id="tbody-preview" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
    </div>

</main>

<script>
// ============================================================
// KONSTANTA & STATE GLOBAL
// ============================================================
const SLOT_HEIGHT    = 140;
const CARD_WIDTH     = 185;
const CARD_GAP       = 12;
const CSRF_TOKEN     = document.querySelector('meta[name="csrf-token"]').content;
const TAHUN_AKADEMIK = document.querySelector('meta[name="tahun-akademik-id"]').content;
const ROUTE_OPTIMASI       = "{{ route('jadwal.optimasi') }}";
const ROUTE_STATUS_BENTROK = "{{ route('jadwal.status-bentrok') }}";

let ALL_RUANGAN      = [];
let activeTab        = 'belum';
let activeDetailCard = null;
let draggedCard      = null;
let slotElMap        = {};
let SLOT_VALID       = [];
let _hasilOptimasiJadwal = null;

// ============================================================
// UNDO / REDO — STATE
// ============================================================
const MAX_HISTORY  = 80;
let _historyStack  = [];
let _historyIndex  = -1;
let _historyPaused = false;

const HIST_CFG = {
    add:   { label: 'Tambah jadwal',   iconSvg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',  cls: 'hist-icon-add'   },
    move:  { label: 'Pindah jadwal',   iconSvg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>',                                        cls: 'hist-icon-move'  },
    del:   { label: 'Hapus jadwal',    iconSvg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>',  cls: 'hist-icon-del'   },
    merge: { label: 'Gabung kelas',    iconSvg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',                                                          cls: 'hist-icon-merge' },
    split: { label: 'Pisah kelas',     iconSvg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/>',                                       cls: 'hist-icon-split' },
    reset: { label: 'Reset workspace', iconSvg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>',  cls: 'hist-icon-reset' },
    ruang: { label: 'Ubah ruangan',    iconSvg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',  cls: 'hist-icon-ruang' },
};

// ── Ambil snapshot seluruh workspace ──
function _snapshotWorkspace() {
    return [...document.querySelectorAll('.jadwal-card')].map(card => ({
        kelasList:   card.dataset.kelasList   || '[]',
        kelasIdList: card.dataset.kelasIdList || '[]',
        start:       card.dataset.start,
        end:         card.dataset.end,
        sks:         card.dataset.sks,
        nama:        card.dataset.nama,
        kelas:       card.dataset.kelas,
        kelasId:     card.dataset.kelasId,
        day:         card.dataset.day,
        dosen:       card.dataset.dosen,
        kodeMk:      card.dataset.kodeMk,
        ruangan:     card.dataset.ruangan,
        ruanganId:   card.dataset.ruanganId,
        prodi:       card.dataset.prodi,
        jenis:       card.dataset.jenis,
        jamMulai:    card.dataset.jamMulai,
        jamSelesai:  card.dataset.jamSelesai,
        jadwalIds:   card.dataset.jadwalIds   || '[]',
        column:      card.dataset.column,
    }));
}

function _updateWorkspaceMinWidth() {
    const layer = document.getElementById('jadwal-layer');
    if (!layer) return;

    let maxColumns = 0;
    document.querySelectorAll('.jadwal-card').forEach(c => {
        const col = parseInt(c.dataset.column || 0);
        if (col + 1 > maxColumns) maxColumns = col + 1;
    });

    const neededWidth = maxColumns > 1
        ? (maxColumns * (CARD_WIDTH + CARD_GAP)) + 32
        : 0;

    layer.style.minWidth = neededWidth > 0 ? `${neededWidth}px` : '';
}

// ── Push dengan snapshot yang sudah disiapkan (state SEBELUM aksi) ──
function _historyPushRaw(actionType, description, snapshot) {
    if (_historyPaused) return;
    if (_historyIndex < _historyStack.length - 1) {
        _historyStack = _historyStack.slice(0, _historyIndex + 1);
    }
    _historyStack.push({ type: actionType, description, snapshot, time: new Date() });
    if (_historyStack.length > MAX_HISTORY) _historyStack.shift();
    _historyIndex = _historyStack.length - 1;
    _renderHistoryPanel();
    _updateUndoRedoButtons();
}

// ── Restore workspace dari snapshot (tanpa sentuh DB) ──
function _restoreWorkspace(snapshot) {
    _historyPaused = true;
    document.querySelectorAll('.jadwal-card').forEach(c => c.remove());
    document.querySelectorAll('.kelas-item').forEach(el => setSidebarStatus(el.dataset.id, 'belum'));

    snapshot.forEach(s => {
        const c = createCard({
            sks:        parseInt(s.sks),
            nama:       s.nama,
            kelas:      s.kelas,
            kelasId:    parseInt(s.kelasId),
            dosen:      s.dosen,
            kodeMk:     s.kodeMk,
            ruangan:    s.ruangan,
            ruanganId:  s.ruanganId,
            slotId:     parseInt(s.start),
            day:        s.day,
            jamMulai:   s.jamMulai,
            jamSelesai: s.jamSelesai,
            jadwalIds:  JSON.parse(s.jadwalIds || '[]'),
            prodi:      s.prodi,
            jenis:      s.jenis,
            column:     s.column,
        }, true);

        const kelasList   = JSON.parse(s.kelasList   || '[]');
        const kelasIdList = JSON.parse(s.kelasIdList || '[]');
        if (kelasList.length > 1) {
            c.dataset.kelasList   = s.kelasList;
            c.dataset.kelasIdList = s.kelasIdList;
            const color = getCourseColor(s.kelas);
            c.innerHTML = `
                <div>
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-[13px] ${color.text}">${kelasList.join(' + ')}</span>
                            <span class="px-2 py-0.5 rounded-full ${color.badge} text-[10px] font-semibold">Gabungan</span>
                        </div>
                        <button class="text-gray-400 hover:text-red-500 transition ml-2" onclick="removeCard(this)">✕</button>
                    </div>
                    <div class="mt-1">
                        <span class="text-xs font-mono px-2 py-0.5 rounded-lg bg-white/60 ${color.text}">${s.kodeMk || '-'}</span>
                    </div>
                    <h3 class="mt-1 text-[13px] leading-snug font-bold text-gray-800">${s.nama || '-'}</h3>
                    <div class="mt-2 space-y-1">
                        <p class="text-sm text-gray-700 font-medium">${s.dosen}</p>
                        <p class="text-sm text-gray-700 font-semibold ruangan-text">${s.ruangan || '-'} <span class="mx-1 text-gray-300">|</span> ${s.sks} SKS</p>
                    </div>
                </div>`;
        }
        kelasIdList.forEach(kid => setSidebarStatus(kid, 'sudah'));
    });

    updateCounter();
    applyFilter();
    const days = {!! $hari->pluck('nama_hari')->map(fn($d) => strtolower($d))->toJson() !!};
    filterCardsByDay(Alpine.$data(document.body)?.selectedDay || (days.length ? days[0] : 'senin'));
    renderTablePreview();
    updateBentrokButton();
    closeDetailPanel();
    _historyPaused = false;
}

// ── Undo ──
function historyUndo() {
    if (_historyIndex <= 0) return;
    const desc = _historyStack[_historyIndex].description;
    _historyIndex--;
    _restoreWorkspace(_historyStack[_historyIndex].snapshot);
    _renderHistoryPanel();
    _updateUndoRedoButtons();
    showToast('↩ Undo: ' + desc, 'green');
}

// ── Redo ──
function historyRedo() {
    if (_historyIndex >= _historyStack.length - 1) return;
    _historyIndex++;
    _restoreWorkspace(_historyStack[_historyIndex].snapshot);
    _renderHistoryPanel();
    _updateUndoRedoButtons();
    showToast('↪ Redo: ' + _historyStack[_historyIndex].description, 'green');
}

// ── Lompat ke titik tertentu ──
function historyJumpTo(idx) {
    if (idx < 0 || idx >= _historyStack.length) return;
    _historyIndex = idx;
    _restoreWorkspace(_historyStack[idx].snapshot);
    _renderHistoryPanel();
    _updateUndoRedoButtons();
    showToast('⏱ Kembali ke: ' + _historyStack[idx].description, 'green');
}

// ── Hapus semua riwayat ──
function historyClear() {
    if (!_historyStack.length) return;
    if (!confirm('Hapus seluruh riwayat perubahan? Workspace tidak akan berubah.')) return;
    _historyStack = [];
    _historyIndex = -1;
    _renderHistoryPanel();
    _updateUndoRedoButtons();
}

// ── Update tombol undo/redo ──
function _updateUndoRedoButtons() {
    const canUndo = _historyIndex > 0;
    const canRedo = _historyIndex < _historyStack.length - 1;
    ['hist-undo-btn', 'hist-undo-panel-btn'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.disabled = !canUndo;
    });
    ['hist-redo-btn', 'hist-redo-panel-btn'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.disabled = !canRedo;
    });
    const infoEl = document.getElementById('hist-stack-info');
    if (infoEl) {
        if (!_historyStack.length) {
            infoEl.textContent = 'Riwayat';
        } else {
            const redoCnt = _historyStack.length - 1 - _historyIndex;
            infoEl.textContent = redoCnt > 0
                ? `Riwayat (${redoCnt} redo)`
                : `Riwayat (${_historyStack.length})`;
        }
    }
}

// ── Render isi panel ──
function _renderHistoryPanel() {
    const list = document.getElementById('hist-list');
    if (!list) return;
    if (!_historyStack.length) {
        list.innerHTML = `<div class="hist-empty">
            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto 8px;opacity:.3;display:block">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Belum ada perubahan.<br>Mulai menyusun jadwal<br>untuk merekam riwayat.</div>`;
        return;
    }
    list.innerHTML = '';
    for (let i = _historyStack.length - 1; i >= 0; i--) {
        const e   = _historyStack[i];
        const cfg = HIST_CFG[e.type] || HIST_CFG.add;
        const isCurrent = (i === _historyIndex);
        const isFuture  = (i > _historyIndex);
        const timeStr   = e.time.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const item = document.createElement('div');
        item.className = 'hist-item' + (isCurrent ? ' hist-current' : '') + (isFuture ? ' hist-future' : '');
        item.title = 'Klik untuk kembali ke titik ini';
        item.setAttribute('tabindex', '0');
        item.addEventListener('click', (function(idx){ return () => historyJumpTo(idx); })(i));
        item.addEventListener('keydown', (function(idx){ return ev => { if (ev.key === 'Enter' || ev.key === ' ') historyJumpTo(idx); }; })(i));
        item.innerHTML = `
            ${isCurrent ? '<div class="hist-current-bar"></div>' : ''}
            <div class="hist-icon ${isFuture ? 'hist-icon-future' : cfg.cls}">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">${cfg.iconSvg}</svg>
            </div>
            <div class="hist-body">
                <div class="hist-title">${e.description}${isCurrent ? '<span class="hist-now-badge">sekarang</span>' : ''}</div>
                <div class="hist-meta">${cfg.label}</div>
            </div>
            <div class="hist-time">${timeStr}</div>`;
        list.appendChild(item);
    }
}

// ── Buka / tutup panel ──
function bukaHistoryPanel() {
    const panel = document.getElementById('hist-panel');
    if (panel) panel.classList.toggle('open');
}
function tutupHistoryPanel() {
    const panel = document.getElementById('hist-panel');
    if (panel) panel.classList.remove('open');
}

// ============================================================
// KEYBOARD SHORTCUT (SATU listener saja)
// ============================================================
document.addEventListener('keydown', function(ev) {
    if (ev.key === 'Escape') {
        tutupModalOptimasi();
        tutupHistoryPanel();
        return;
    }
    const tag = document.activeElement ? document.activeElement.tagName : '';
    if (tag === 'INPUT' || tag === 'SELECT' || tag === 'TEXTAREA') return;
    const isMac = navigator.platform.toUpperCase().includes('MAC');
    const ctrl  = isMac ? ev.metaKey : ev.ctrlKey;
    if (ctrl && ev.key === 'z' && !ev.shiftKey) { ev.preventDefault(); historyUndo(); return; }
    if (ctrl && (ev.key === 'y' || (ev.key === 'z' && ev.shiftKey))) { ev.preventDefault(); historyRedo(); }
});

// ============================================================
// MODAL OPTIMASI — OPEN / CLOSE
// ============================================================
function bukaModalOptimasi() {
    tampilStateModal('konfirmasi');
    document.getElementById('modal-optimasi-overlay').classList.add('show');
}
function tutupModalOptimasi() {
    document.getElementById('modal-optimasi-overlay').classList.remove('show');
}
function tutupModalOptimasiDanRefresh() {
    tutupModalOptimasi();
    if (_hasilOptimasiJadwal && _hasilOptimasiJadwal.length) {
        refreshWorkspaceDariOptimasi(_hasilOptimasiJadwal);
    }
}
document.getElementById('modal-optimasi-overlay').addEventListener('click', function(e) {
    if (e.target === this) tutupModalOptimasi();
});
function tampilStateModal(state) {
    document.getElementById('modal-state-konfirmasi').style.display = state === 'konfirmasi' ? '' : 'none';
    document.getElementById('modal-state-loading').style.display    = state === 'loading'    ? '' : 'none';
    document.getElementById('modal-state-hasil').style.display      = state === 'hasil'      ? '' : 'none';
}

// ============================================================
// WARNA CARD
// ============================================================
function getCourseColor(name) {
    const colors = [
        { bg: 'bg-blue-100',   border: 'border-blue-400',   text: 'text-blue-800',   badge: 'bg-blue-200 text-blue-800' },
        { bg: 'bg-purple-100', border: 'border-purple-400', text: 'text-purple-800', badge: 'bg-purple-200 text-purple-800' },
        { bg: 'bg-green-100',  border: 'border-green-400',  text: 'text-green-800',  badge: 'bg-green-200 text-green-800' },
        { bg: 'bg-pink-100',   border: 'border-pink-400',   text: 'text-pink-800',   badge: 'bg-pink-200 text-pink-800' },
        { bg: 'bg-yellow-100', border: 'border-yellow-400', text: 'text-yellow-800', badge: 'bg-yellow-200 text-yellow-800' },
        { bg: 'bg-indigo-100', border: 'border-indigo-400', text: 'text-indigo-800', badge: 'bg-indigo-200 text-indigo-800' },
        { bg: 'bg-red-100',    border: 'border-red-400',    text: 'text-red-800',    badge: 'bg-red-200 text-red-800' },
        { bg: 'bg-orange-100', border: 'border-orange-400', text: 'text-orange-800', badge: 'bg-orange-200 text-orange-800' },
        { bg: 'bg-teal-100',   border: 'border-teal-400',   text: 'text-teal-800',   badge: 'bg-teal-200 text-teal-800' },
        { bg: 'bg-cyan-100',   border: 'border-cyan-400',   text: 'text-cyan-800',   badge: 'bg-cyan-200 text-cyan-800' },
        { bg: 'bg-emerald-100',border: 'border-emerald-400',text: 'text-emerald-800',badge: 'bg-emerald-200 text-emerald-800' },
        { bg: 'bg-rose-100',   border: 'border-rose-400',   text: 'text-rose-800',   badge: 'bg-rose-200 text-rose-800' },
        { bg: 'bg-fuchsia-100',border: 'border-fuchsia-400',text: 'text-fuchsia-800',badge: 'bg-fuchsia-200 text-fuchsia-800' },
    ];
    let hash = 0;
    if (!name) name = 'Default';
    for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
    return colors[Math.abs(hash % colors.length)];
}

// ============================================================
// API — simpan & hapus jadwal
// ============================================================
async function simpanJadwal(card) {
    const kelasIdList = JSON.parse(card.dataset.kelasIdList || '[]');
    const hariMap = {!! $hari->mapWithKeys(function($h) { return [strtolower($h->nama_hari) => $h->id_hari]; })->toJson() !!};
    const hariId  = hariMap[card.dataset.day] ?? 1;
    try {
        const results = await Promise.all(kelasIdList.map(kelasId => {
            const sidebarEl = document.querySelector(`.kelas-item[data-id="${kelasId}"]`);
            return fetch('{{ route("jadwal.simpan-slot", [], false) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: JSON.stringify({
                    kelas_id: kelasId, slot_id: parseInt(card.dataset.start),
                    hari_id: hariId, ruang_id: card.dataset.ruanganId || null,
                    durasi_sks: parseInt(card.dataset.sks), kode_matkul: card.dataset.kodeMk || '',
                    dosen_id: sidebarEl?.dataset.dosenId || null, tahun_akademik_id: TAHUN_AKADEMIK,
                }),
            }).then(async r => {
                if (!r.ok) { const err = await r.text(); throw new Error(err); }
                return r.json();
            });
        }));
        card.dataset.jadwalIds = JSON.stringify(results.map(r => r.jadwal_id));
    } catch (err) {
        console.error('Gagal simpan jadwal:', err);
        alert('Gagal menyimpan jadwal. Cek console untuk detail.');
    }
}

async function simpanSemuaJadwal() {
    const cards = [...document.querySelectorAll('.jadwal-card')];
    if (!cards.length) { alert('Belum ada jadwal yang disusun di workspace.'); return; }
    const btn   = document.getElementById('btn-simpan-semua');
    const label = document.getElementById('btn-simpan-label');
    btn.disabled = true; label.innerText = 'Menyimpan...';
    let berhasil = 0, gagal = 0;
    for (const card of cards) {
        try { await simpanJadwal(card); berhasil++; }
        catch (e) { gagal++; console.error('Gagal simpan card:', e); }
    }
    btn.disabled = false; label.innerText = 'Simpan Jadwal';
    showToast(gagal === 0 ? `✓ ${berhasil} jadwal berhasil disimpan!` : `${berhasil} berhasil, ${gagal} gagal. Cek console.`, gagal === 0 ? 'green' : 'red');
}

function showToast(message, color = 'green') {
    const existing = document.getElementById('toast-notif');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.id = 'toast-notif';
    toast.className = `fixed bottom-6 right-6 z-50 px-6 py-4 rounded-2xl shadow-xl text-white font-semibold text-sm transition-all duration-300 ${color === 'green' ? 'bg-green-600' : 'bg-red-500'}`;
    toast.innerText = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

async function hapusJadwal(card) {
    const ids = JSON.parse(card.dataset.jadwalIds || '[]');
    if (!ids.length) return;
    await Promise.all(ids.map(id =>
        fetch(`/jadwal/hapus-slot/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN } })
    ));
}

// ============================================================
// SIDEBAR — update status kelas
// ============================================================
function setSidebarStatus(kelasId, status) {
    const el = document.getElementById(`kelas-${kelasId}`);
    if (!el) return;
    const isSudah = status === 'sudah';
    el.dataset.status = status;
    el.setAttribute('draggable', isSudah ? 'false' : 'true');
    el.classList.toggle('bg-green-50', isSudah);
    el.classList.toggle('border-l-4', isSudah);
    el.classList.toggle('border-green-400', isSudah);
    el.classList.toggle('cursor-move', !isSudah);
    el.classList.toggle('cursor-not-allowed', isSudah);
    el.classList.toggle('opacity-70', isSudah);
    const badge = el.querySelector('.status-badge');
    if (badge) {
        badge.className = `status-badge px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1 ${isSudah ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'}`;
        badge.innerHTML = `<span class="w-2 h-2 ${isSudah ? 'bg-green-500' : 'bg-red-500'} rounded-full"></span> ${isSudah ? 'Sudah' : 'Belum'}`;
    }
}

function updateCounter() {
    document.getElementById('count-belum').innerText = document.querySelectorAll('.kelas-item[data-status="belum"]').length;
    document.getElementById('count-sudah').innerText = document.querySelectorAll('.kelas-item[data-status="sudah"]').length;
}

// ============================================================
// TABS & FILTER
// ============================================================
function setActiveTab(status) {
    activeTab = status;
    const belum = document.getElementById('tab-belum');
    const sudah = document.getElementById('tab-sudah');
    if (status === 'belum') {
        belum.className = 'pb-3 border-b-2 border-teal-500 text-teal-600 flex items-center gap-2';
        sudah.className = 'pb-3 text-gray-500 hover:text-green-600 flex items-center gap-2';
    } else {
        sudah.className = 'pb-3 border-b-2 border-green-500 text-green-600 flex items-center gap-2';
        belum.className = 'pb-3 text-gray-500 hover:text-teal-600 flex items-center gap-2';
    }
    applyFilter();
}

function applyFilter() {
    const query          = (document.getElementById('search-kelas')?.value || '').toLowerCase();
    const alpineData     = Alpine.$data(document.body);
    const filterProdi    = alpineData.filterProdi    || [];
    const filterSemester = alpineData.filterSemester || [];
    let visible = 0;
    document.querySelectorAll('.kelas-item').forEach(el => {
        if (el.dataset.status !== activeTab) { el.style.display = 'none'; return; }
        const matchSearch = !query || (el.dataset.kelas || '').toLowerCase().includes(query) || (el.dataset.nama || '').toLowerCase().includes(query);
        const prodiText   = el.querySelector('.bg-purple-100')?.innerText?.trim() || '';
        const semText     = el.querySelector('.bg-gray-100')?.innerText?.replace('Semester ', '').trim() || '';
        const matchProdi  = !filterProdi.length    || filterProdi.includes(prodiText);
        const matchSem    = !filterSemester.length || filterSemester.includes(semText);
        const show = matchSearch && matchProdi && matchSem;
        el.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const countEl = document.getElementById('filter-count');
    if (countEl) countEl.innerText = `${visible} kelas ditampilkan`;
}

// ============================================================
// FILTER CARD WORKSPACE BERDASARKAN HARI
// ============================================================
function filterCardsByDay(dayStr) {
    const days = {!! $hari->pluck('nama_hari')->map(fn($d) => strtolower($d))->toJson() !!};
    const activeDay = Alpine.$data(document.body)?.selectedDay || (days.length ? days[0] : 'senin');
    
    document.querySelectorAll('.jadwal-card').forEach(card => {
        const show = card.dataset.day === activeDay;
        card.style.display       = show ? 'flex' : 'none';
        card.style.pointerEvents = show ? 'auto' : 'none';
    });

    // Load pivot mapping
    const mappingEl = document.getElementById('hari-slot-mapping');
    let mapping = {};
    if (mappingEl) {
        mapping = JSON.parse(mappingEl.innerText || '{}');
    }
    const validSlots = mapping[activeDay] || [];

    // Gray out unavailable slots
    document.querySelectorAll('[data-slot-line]').forEach(slot => {
        const slotId = parseInt(slot.dataset.slotLine);
        const isIstirahat = slot.dataset.isIstirahat === '1';
        if (validSlots.includes(slotId) || isIstirahat) {
            slot.style.backgroundColor = '';
            slot.dataset.allowed = '1';
            // Also reset parent visual
            slot.parentElement.style.opacity = '1';
            slot.parentElement.classList.remove('bg-gray-100');
        } else {
            slot.style.backgroundColor = '#f9fafb';
            slot.dataset.allowed = '0';
            slot.parentElement.style.opacity = '0.6';
            slot.parentElement.classList.add('bg-gray-100');
        }
    });
}

// ============================================================
// HAPUS CARD
// ============================================================
function removeCard(btn) {
    const card     = btn.closest('.jadwal-card');
    const kelasIds = JSON.parse(card.dataset.kelasIdList || '[]');
    const kelas    = card.dataset.kelas || card.dataset.nama || 'jadwal';
    const day      = card.dataset.day || '';

    // Hapus snapshot sebelum:
    // _historyPushRaw('del', `Hapus: ${kelas} — ${day.charAt(0).toUpperCase() + day.slice(1)}`, _snapshotWorkspace());

    hapusJadwal(card);
    card.remove();
    _updateWorkspaceMinWidth();
    kelasIds.forEach(kid => {
        const remaining = document.querySelectorAll(`.jadwal-card[data-kelas-id="${kid}"]`);
        if (!remaining.length) setSidebarStatus(kid, 'belum');
    });
    updateCounter();
    applyFilter();
    renderTablePreview();
    updateBentrokButton();
    if (activeDetailCard === card) closeDetailPanel();

    _historyPushRaw('del', `Hapus: ${kelas} — ${day.charAt(0).toUpperCase() + day.slice(1)}`, _snapshotWorkspace());
}

// ============================================================
// DETAIL PANEL
// ============================================================
function openDetailPanel(card) {
    activeDetailCard = card;
    const kelasList  = JSON.parse(card.dataset.kelasList || '[]');
    const slotId     = parseInt(card.dataset.start);
    const endSlotId  = parseInt(card.dataset.end) - 1;
    const jamMulai   = card.dataset.jamMulai   || document.querySelector(`[data-slot-line="${slotId}"]`)?.dataset.jamMulai   || '-';
    const jamSelesai = card.dataset.jamSelesai || document.querySelector(`[data-slot-line="${endSlotId}"]`)?.dataset.jamSelesai || '-';
    const sidebarEl  = document.querySelector(`.kelas-item[data-kelas="${kelasList[0]}"]`);
    const semester   = sidebarEl?.querySelector('.bg-gray-100')?.innerText?.replace('Semester ', '').trim() || '-';
    const hari       = card.dataset.day;

    document.getElementById('dp-nama').innerText     = card.dataset.nama || '-';
    document.getElementById('dp-kelas').innerText    = kelasList.join(' + ');
    document.getElementById('dp-dosen').innerText    = card.dataset.dosen || '-';
    document.getElementById('dp-hari').innerText     = hari.charAt(0).toUpperCase() + hari.slice(1);
    document.getElementById('dp-waktu').innerText    = `${jamMulai} – ${jamSelesai} (${card.dataset.sks} SKS)`;
    document.getElementById('dp-sks').innerText      = `${card.dataset.sks} SKS`;
    document.getElementById('dp-kode').innerText     = card.dataset.kodeMk || '-';
    document.getElementById('dp-semester').innerText = `Semester ${semester}`;

    const select = document.getElementById('dp-ruangan-select');
    select.innerHTML = '<option value="">-- Pilih Ruangan --</option>';
    ALL_RUANGAN.forEach(r => {
        const opt    = document.createElement('option');
        opt.value    = r.id;
        opt.text     = r.nama;
        opt.selected = String(r.id) === String(card.dataset.ruanganId) || r.nama === card.dataset.ruangan;
        select.appendChild(opt);
    });

    document.getElementById('dp-btn-hapus').onclick = () => {
        const closeBtn = card.querySelector('button[onclick="removeCard(this)"]');
        if (closeBtn) closeBtn.click();
    };

    const btnSplit = document.getElementById('dp-btn-split');
    if (kelasList.length > 1) {
        btnSplit.classList.remove('hidden');
        btnSplit.onclick = () => splitCard(card);
    } else {
        btnSplit.classList.add('hidden');
        btnSplit.onclick = null;
    }

    document.getElementById('dp-btn-simpan').onclick = async () => {
        try { await simpanJadwal(card); showToast('✓ Perubahan jadwal berhasil disimpan', 'green'); }
        catch (e) { showToast('Gagal menyimpan perubahan', 'red'); }
    };

    const panel = document.getElementById('detail-panel');
    panel.classList.remove('hidden');
    panel.classList.add('flex');
}

function closeDetailPanel() {
    document.getElementById('detail-panel').classList.remove('flex');
    document.getElementById('detail-panel').classList.add('hidden');
    activeDetailCard = null;
}

// ============================================================
// SPLIT CARD
// ============================================================
function splitCard(card) {
    const kelasList   = JSON.parse(card.dataset.kelasList   || '[]');
    const kelasIdList = JSON.parse(card.dataset.kelasIdList || '[]');
    if (kelasList.length <= 1) { showToast('Kelas ini tidak dalam kondisi gabungan.', 'red'); return; }
    if (!confirm(`Pisahkan ${kelasList.length} kelas (${kelasList.join(' + ')}) menjadi card terpisah?`)) return;

    // Hapus snapshot sebelum:
    // _historyPushRaw('split', `Pisah kelas: ${kelasList.join(' + ')}`, _snapshotWorkspace());

    const slotId    = parseInt(card.dataset.start);
    const day       = card.dataset.day;
    const sks       = parseInt(card.dataset.sks);
    const nama      = card.dataset.nama;
    const dosen     = card.dataset.dosen;
    const kodeMk    = card.dataset.kodeMk;
    const ruangan   = card.dataset.ruangan;
    const ruanganId = card.dataset.ruanganId;
    const prodi     = card.dataset.prodi;
    const jenis     = card.dataset.jenis;
    const jamMulai  = card.dataset.jamMulai;
    const jamSelesai= card.dataset.jamSelesai;

    hapusJadwal(card);
    card.remove();
    closeDetailPanel();

    _historyPaused = true;
    kelasList.forEach((kelasNama, i) => {
        const kelasId    = kelasIdList[i];
        const sidebarEl  = document.getElementById(`kelas-${kelasId}`);
        const prodiKelas = sidebarEl?.dataset.prodi || prodi || '-';
        const jenisKelas = sidebarEl?.dataset.jenis || jenis || 'Teori';
        createCard({ sks, nama, dosen, kodeMk, ruangan, ruanganId, slotId, day, jamMulai, jamSelesai,
            kelas: kelasNama, kelasId: parseInt(kelasId), prodi: prodiKelas, jenis: jenisKelas, jadwalIds: [] });
        setSidebarStatus(kelasId, 'sudah');
    });
    _historyPaused = false;

    updateCounter();
    applyFilter();
    renderTablePreview();
    updateBentrokButton();
    showToast(`✓ ${kelasList.length} kelas berhasil dipisahkan!`, 'green');

    _historyPushRaw('split', `Pisah kelas: ${kelasList.join(' + ')}`, _snapshotWorkspace());
}

// ============================================================
// UPDATE RUANGAN DI CARD
// ============================================================
function updateCardRuangan(ruanganId, ruanganNama) {
    if (!activeDetailCard) return;
    activeDetailCard.dataset.ruangan   = ruanganNama;
    activeDetailCard.dataset.ruanganId = ruanganId;
    const el = activeDetailCard.querySelector('.ruangan-text');
    if (el) el.innerText = ruanganNama || '-';
    renderTablePreview();
    updateBentrokButton();
    activeDetailCard.classList.add('ring-2', 'ring-amber-400');
    setTimeout(() => activeDetailCard.classList.remove('ring-2', 'ring-amber-400'), 1500);

    _historyPushRaw('ruang', `Ruangan → ${ruanganNama || '-'} (${activeDetailCard.dataset.kelas || '-'})`, _snapshotWorkspace());
}

// ============================================================
// PREVIEW TABLE — FILTER & SEARCH
// ============================================================
function populatePreviewDropdowns() {
    const rows = [...document.querySelectorAll('#tbody-preview tr[data-preview]')];
    const prodis = new Set(), semesters = new Set(), kurikulums = new Set();
    rows.forEach(tr => {
        if (tr.dataset.prodi)     prodis.add(tr.dataset.prodi);
        if (tr.dataset.semester)  semesters.add(tr.dataset.semester);
        if (tr.dataset.kurikulum) kurikulums.add(tr.dataset.kurikulum);
    });
    const fillSelect = (id, values, label) => {
        const sel = document.getElementById(id);
        if (!sel) return;
        const cur = sel.value;
        sel.innerHTML = `<option value="">${label}</option>`;
        [...values].sort().forEach(v => {
            const opt = document.createElement('option');
            opt.value = opt.textContent = v;
            if (v === cur) opt.selected = true;
            sel.appendChild(opt);
        });
    };
    fillSelect('pf-prodi',     prodis,    'Semua Prodi');
    fillSelect('pf-semester',  semesters, 'Semua Semester');
    fillSelect('pf-kurikulum', kurikulums,'Semua Kurikulum');
}

function applyPreviewFilter() {
    const query     = (document.getElementById('preview-search')?.value || '').toLowerCase();
    const hari      = document.getElementById('pf-hari')?.value      || '';
    const prodi     = document.getElementById('pf-prodi')?.value     || '';
    const semester  = document.getElementById('pf-semester')?.value  || '';
    const sesi      = document.getElementById('pf-sesi')?.value      || '';
    const kurikulum = document.getElementById('pf-kurikulum')?.value || '';
    const rows = [...document.querySelectorAll('#tbody-preview tr[data-preview]')];
    let visible = 0;
    rows.forEach(tr => {
        const matchSearch = !query || (tr.dataset.dosen||'').toLowerCase().includes(query) || (tr.dataset.mk||'').toLowerCase().includes(query) || (tr.dataset.kelas||'').toLowerCase().includes(query);
        const show = matchSearch && (!hari||tr.dataset.hari===hari) && (!prodi||tr.dataset.prodi===prodi) && (!semester||tr.dataset.semester===semester) && (!sesi||tr.dataset.sesi===sesi) && (!kurikulum||tr.dataset.kurikulum===kurikulum);
        tr.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const info = document.getElementById('preview-filter-info');
    if (info) info.textContent = `${visible} dari ${rows.length} jadwal ditampilkan`;
}

function resetPreviewFilter() {
    const el = document.getElementById('preview-search');
    if (el) el.value = '';
    ['pf-hari','pf-prodi','pf-semester','pf-sesi','pf-kurikulum'].forEach(id => {
        const e = document.getElementById(id); if (e) e.value = '';
    });
    applyPreviewFilter();
}

function renderTablePreview() {
    const tbody = document.getElementById('tbody-preview');
    if (!tbody) return;
    const days = {!! $hari->pluck('nama_hari')->map(fn($d) => strtolower($d))->toJson() !!};
    const cards = [...document.querySelectorAll('.jadwal-card')].sort((a, b) => {
        const dDiff = days.indexOf(a.dataset.day) - days.indexOf(b.dataset.day);
        if (dDiff !== 0) return dDiff;

        const nameA = (a.dataset.nama || '').toLowerCase();
        const nameB = (b.dataset.nama || '').toLowerCase();
        const nDiff = nameA.localeCompare(nameB, 'id');
        if (nDiff !== 0) return nDiff;

        const dosenA = (a.dataset.dosen || '').toLowerCase();
        const dosenB = (b.dataset.dosen || '').toLowerCase();
        const doDiff = dosenA.localeCompare(dosenB, 'id');
        if (doDiff !== 0) return doDiff;

        const kelasA = (a.dataset.kelas || '').toLowerCase();
        const kelasB = (b.dataset.kelas || '').toLowerCase();
        const kDiff = kelasA.localeCompare(kelasB, 'id');
        if (kDiff !== 0) return kDiff;

        return parseInt(a.dataset.start) - parseInt(b.dataset.start);
    });
    if (!cards.length) {
        tbody.innerHTML = `<tr><td colspan="13" class="px-4 py-6 text-center text-gray-400">Belum ada jadwal yang disusun.</td></tr>`;
        const info = document.getElementById('preview-filter-info');
        if (info) info.textContent = '';
        return;
    }
    tbody.innerHTML = '';
    cards.forEach(card => {
        const kelasList    = JSON.parse(card.dataset.kelasList || '[]');
        const slotId       = parseInt(card.dataset.start);
        const sesi         = slotId <= 11 ? 'Pagi' : 'Malam';
        const hariLabel    = card.dataset.day.charAt(0).toUpperCase() + card.dataset.day.slice(1);
        const kelasPertama = kelasList[0];
        const sidebarEl    = document.querySelector(`.kelas-item[data-kelas="${kelasPertama}"]`);
        const prodi        = sidebarEl?.querySelector('.bg-purple-100')?.innerText?.trim() || '-';
        const semester     = sidebarEl?.querySelector('.bg-gray-100')?.innerText?.replace('Semester ', '').trim() || '-';
        const kurikulum    = kelasPertama ? '20' + kelasPertama.substring(0, 2) : '-';
        const namaKelas    = kelasList.length > 1
            ? kelasList.join(' + ') + ' <span class="text-xs px-1.5 py-0.5 bg-pink-100 text-pink-700 rounded font-semibold">Gabungan</span>'
            : kelasPertama;
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50 border-b border-gray-100';
        tr.setAttribute('data-preview', '1');
        tr.dataset.hari = hariLabel; tr.dataset.prodi = prodi; tr.dataset.semester = semester;
        tr.dataset.sesi = sesi; tr.dataset.kurikulum = kurikulum;
        tr.dataset.dosen = card.dataset.dosen || ''; tr.dataset.mk = card.dataset.nama || '';
        tr.dataset.kelas = kelasList.join(' ') || '';
        tr.innerHTML = `
            <td class="px-4 py-3 text-sm">${hariLabel}</td>
            <td class="px-4 py-3 text-sm">${prodi}</td>
            <td class="px-4 py-3 text-sm">${semester}</td>
            <td class="px-4 py-3 text-sm"><span class="px-2 py-0.5 rounded-full text-xs font-semibold ${sesi==='Pagi'?'bg-amber-100 text-amber-700':'bg-indigo-100 text-indigo-700'}">${sesi}</span></td>
            <td class="px-4 py-3 text-sm font-medium">${namaKelas}</td>
            <td class="px-4 py-3 text-sm font-mono text-teal-700">${card.dataset.kodeMk || '-'}</td>
            <td class="px-4 py-3 text-sm">${card.dataset.nama || '-'}</td>
            <td class="px-4 py-3 text-sm">${card.dataset.jenis || 'Teori'}</td>
            <td class="px-4 py-3 text-sm">${card.dataset.sks} SKS</td>
            <td class="px-4 py-3 text-sm">${card.dataset.dosen || '-'}</td>
            <td class="px-4 py-3 text-sm">${card.dataset.ruangan || '-'}</td>
            <td class="px-4 py-3 text-sm">${card.dataset.jamMulai || '-'}</td>
            <td class="px-4 py-3 text-sm">${card.dataset.jamSelesai || '-'}</td>`;
        tbody.appendChild(tr);
    });
    populatePreviewDropdowns();
    applyPreviewFilter();
}

// ============================================================
// BUAT CARD
// ============================================================


function createCard(data, skipSave = false) {
    const { sks, nama, kelas, kelasId, dosen, kodeMk, ruangan, ruanganId,
            slotId, day, jamMulai, jamSelesai, jadwalIds, prodi, jenis, column: forceColumn } = data;
    const color = getCourseColor(kelas);

    const sameDay = [...document.querySelectorAll('.jadwal-card')].filter(c => c.dataset.day === day);
    let column = 0;
    
    if (forceColumn !== undefined) {
        column = parseInt(forceColumn);
    } else {
        while (true) {
            const collision = sameDay.some(ex => {
                const overlap = slotId < parseInt(ex.dataset.end) && (slotId + sks) > parseInt(ex.dataset.start);
                return overlap && parseInt(ex.dataset.column || 0) === column;
            });
            if (!collision) break;
            column++;
        }
    }

    const left = 16 + column * (CARD_WIDTH + CARD_GAP);
    const top  = (slotId - 1) * SLOT_HEIGHT + 8;

    const card = document.createElement('div');
    card.className = `jadwal-card absolute ${color.bg} ${color.border} border border-l-[3px] rounded-2xl p-3 shadow-sm hover:shadow-md overflow-hidden z-10 cursor-move transition duration-200`;
    card.setAttribute('draggable', 'true');
    card.setAttribute('data-kelas-id', kelasId);

    const days = {!! $hari->pluck('nama_hari')->map(fn($d) => strtolower($d))->toJson() !!};
    const activeDay = Alpine.$data(document.body)?.selectedDay || (days.length ? days[0] : 'senin');
    const isVisible = day === activeDay;

    Object.assign(card.dataset, {
        kelasList:   JSON.stringify([kelas]),
        kelasIdList: JSON.stringify([kelasId]),
        start:    slotId, end: slotId + sks, column,
        dosen, sks, nama, kelas, day, kodeMk, kelasId,
        prodi:      prodi      || '',
        jamMulai:   jamMulai   || '-',
        jamSelesai: jamSelesai || '-',
        ruangan:    ruangan    || '',
        ruanganId:  ruanganId  || '',
        jadwalIds:  jadwalIds ? JSON.stringify(Array.isArray(jadwalIds) ? jadwalIds : [jadwalIds]) : '[]',
        jenis:      jenis      || 'Teori',
    });

    card.style.cssText = `width:170px; height:${sks * SLOT_HEIGHT - 18}px; left:${left}px; top:${top}px; pointer-events:${isVisible ? 'auto' : 'none'}; user-select:none; touch-action:none; display:${isVisible ? 'flex' : 'none'};`;
    card.innerHTML = `
        <div>
            <div class="flex items-start justify-between">
                <div class="text-sm font-bold ${color.text}">${kelas}</div>
                <button class="text-gray-400 hover:text-red-500 transition" onclick="removeCard(this)">✕</button>
            </div>
            <div class="mt-1">
                <span class="text-xs px-2 py-0.5 rounded-lg bg-white/60 text-gray-600">${prodi || '-'}</span>
                <span class="text-xs font-mono px-2 py-0.5 rounded-lg bg-white/60 ${color.text}">${kodeMk}</span>
            </div>
            <h3 class="mt-2 text-[14px] leading-snug font-bold text-gray-800">${nama}</h3>
            <div class="mt-3 space-y-1">
                <p class="text-sm text-gray-700 font-medium">${dosen}</p>
                <p class="text-sm text-gray-700 font-semibold ruangan-text">${ruangan || '-'} <span class="mx-1 text-gray-300">|</span> ${sks} SKS</p>
            </div>
        </div>`;

    document.getElementById('jadwal-layer').appendChild(card);
    enableCardDrag(card);
    enableMerge(card);
    updateBentrokButton();
    _updateWorkspaceMinWidth();
    return card;
}

// Load semua ruangan
try {
    const rawRuangan = document.getElementById('all-ruangan-data');
    if (rawRuangan) ALL_RUANGAN = JSON.parse(rawRuangan.textContent.trim());
} catch(e) { console.error('Gagal parse all-ruangan-data:', e); }

// ============================================================
// LOAD JADWAL TERSIMPAN DARI DATABASE
// ============================================================
function loadExistingJadwals() {
    const raw = document.getElementById('existing-jadwal-data');
    if (!raw) return;
    let jadwals = [];
    try { jadwals = JSON.parse(raw.textContent.trim()); }
    catch (e) { console.error('Gagal parse existing-jadwal-data:', e); return; }
    if (!jadwals.length) return;

    _historyPaused = true;
    jadwals.forEach(j => {
        const slotIdInt = parseInt(j.slot_id);
        const sksInt    = parseInt(j.sks);
        const infoStart = slotElMap[slotIdInt];
        const infoEnd   = slotElMap[slotIdInt + sksInt - 1];
        if (!infoStart) { console.warn('Slot tidak ditemukan:', slotIdInt); return; }
        const sidebarEl = document.querySelector(`.kelas-item[data-id="${j.kelas_id}"]`);
        const prodi     = sidebarEl?.dataset.prodi || '-';
        const jenis     = sidebarEl?.dataset.jenis || 'Teori';
        createCard({
            sks: sksInt, nama: j.nama, kelas: j.nama_kelas, kelasId: parseInt(j.kelas_id),
            dosen: j.dosen, kodeMk: j.kode_mk, ruangan: j.ruangan || '', ruanganId: j.ruangan_id || '',
            slotId: slotIdInt, day: j.hari,
            jamMulai: infoStart.jamMulai, jamSelesai: infoEnd?.jamSelesai || '-',
            jadwalIds: [j.jadwal_id], prodi, jenis,
        }, true);
        setSidebarStatus(j.kelas_id, 'sudah');
    });
    _historyPaused = false;

    // Simpan state awal sebagai titik pertama history
    _historyStack.push({
        type: 'add',
        description: 'State awal (dari database)',
        snapshot: _snapshotWorkspace(),
        time: new Date(),
    });
    _historyIndex = 0;

    updateCounter();
    renderTablePreview();
    _renderHistoryPanel();
    _updateUndoRedoButtons();
}

// ============================================================
// DRAG EVENT PADA CARD
// ============================================================
function enableCardDrag(card) {
    card.addEventListener('dragstart', e => {
        draggedCard = card;
        e.dataTransfer.setData('from_workspace', '1');
        ['start','sks','nama','kelas','dosen','kodeMk','ruangan','ruanganId'].forEach(k =>
            e.dataTransfer.setData(k, card.dataset[k] || '')
        );
        e.dataTransfer.setData('kelas_id',      card.dataset.kelasId     || '');
        e.dataTransfer.setData('kelas_list',    card.dataset.kelasList   || '[]');
        e.dataTransfer.setData('kelas_id_list', card.dataset.kelasIdList || '[]');
        e.dataTransfer.setData('jadwal_ids',    card.dataset.jadwalIds   || '[]');
        e.dataTransfer.setData('jenis',         card.dataset.jenis       || 'Teori');
        e.dataTransfer.setData('prodi',         card.dataset.prodi       || '-');
        e.dataTransfer.setData('column',        card.dataset.column      || '0');
    });
    card.addEventListener('click', e => {
        if (e.target.closest('button')) return;
        openDetailPanel(card);
    });
}

function enableMerge(card) {
    card.addEventListener('dragover', e => e.preventDefault());
    card.addEventListener('drop', e => {
        e.preventDefault();
        e.stopPropagation();
        const src = draggedCard;
        if (!src || src === card) return;
        if (src.dataset.nama !== card.dataset.nama || src.dataset.dosen !== card.dataset.dosen) {
            alert('Hanya kelas dengan mata kuliah dan dosen yang sama yang bisa digabung.');
            return;
        }

        const merged   = [...new Set([...JSON.parse(src.dataset.kelasList   || '[]'), ...JSON.parse(card.dataset.kelasList   || '[]')])];
        const mergedId = [...new Set([...JSON.parse(src.dataset.kelasIdList || '[]'), ...JSON.parse(card.dataset.kelasIdList || '[]')])];
        card.dataset.kelasList   = JSON.stringify(merged);
        card.dataset.kelasIdList = JSON.stringify(mergedId);

        const color = getCourseColor(card.dataset.nama);
        card.innerHTML = `
            <div>
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-bold text-[13px] ${color.text}">${merged.join(' + ')}</span>
                        <span class="px-2 py-0.5 rounded-full ${color.badge} text-[10px] font-semibold">Gabungan</span>
                    </div>
                    <button class="text-gray-400 hover:text-red-500 transition ml-2" onclick="removeCard(this)">✕</button>
                </div>
                <div class="mt-1">
                    <span class="text-xs font-mono px-2 py-0.5 rounded-lg bg-white/60 ${color.text}">${card.dataset.kodeMk || '-'}</span>
                </div>
                <h3 class="mt-1 text-[13px] leading-snug font-bold text-gray-800">${card.dataset.nama || '-'}</h3>
                <div class="mt-2 space-y-1">
                    <p class="text-sm text-gray-700 font-medium">${card.dataset.dosen}</p>
                    <p class="text-sm text-gray-700 font-semibold ruangan-text">${card.dataset.ruangan || '-'} <span class="mx-1 text-gray-300">|</span> ${card.dataset.sks} SKS</p>
                </div>
            </div>`;
        src.remove();

        renderTablePreview();
        updateBentrokButton();
        _historyPushRaw('merge', `Gabung kelas: ${merged.join(' + ')}`, _snapshotWorkspace());
    });
}

// ============================================================
// OPTIMASI
// ============================================================
async function jalankanOptimasi() {
    tampilStateModal('loading');
    const pesanEls = ['Mengambil jadwal otomatis...', 'Mendeteksi bentrok...', 'Memperbaiki slot & ruangan...'];
    let pi = 0;
    const pesanEl  = document.getElementById('loading-pesan');
    const interval = setInterval(() => { pesanEl.textContent = pesanEls[pi++ % pesanEls.length]; }, 900);
    try {
        const resp = await fetch(ROUTE_OPTIMASI, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: JSON.stringify({ tahun_akademik_id: TAHUN_AKADEMIK }),
        });
        clearInterval(interval);
        const data = await resp.json();
        if (!resp.ok || !data.success) { tutupModalOptimasi(); showToast(data.message ?? 'Terjadi kesalahan saat optimasi.', 'red'); return; }
        _hasilOptimasiJadwal = data.jadwal_terbaru ?? [];
        tampilkanHasilOptimasi(data.hasil);
    } catch (err) {
        clearInterval(interval);
        tutupModalOptimasi();
        showToast('Koneksi gagal: ' + err.message, 'red');
    }
}

function tampilkanHasilOptimasi(hasil) {
    const { total_jadwal, bentrok_awal, diperbaiki, gagal, detail_bentrok_sisa, pesan } = hasil;
    document.getElementById('stat-total').textContent = total_jadwal;
    document.getElementById('stat-awal').textContent  = bentrok_awal;
    document.getElementById('stat-fix').textContent   = diperbaiki;
    document.getElementById('stat-sisa').textContent  = gagal;
    const header = document.getElementById('hasil-header');
    document.getElementById('hasil-judul').textContent = gagal === 0 ? '✅ Optimasi Berhasil' : '⚠️ Optimasi Sebagian';
    header.style.background = gagal === 0 ? 'linear-gradient(135deg,#059669,#10b981)' : 'linear-gradient(135deg,#d97706,#f59e0b)';
    document.getElementById('hasil-subjudul').textContent = pesan;
    const sisaSection = document.getElementById('sisa-bentrok-section');
    const beresSect   = document.getElementById('semua-beres-section');
    const sisaList    = document.getElementById('sisa-bentrok-list');
    if (gagal > 0 && detail_bentrok_sisa.length) {
        sisaSection.style.display = '';
        beresSect.style.display   = 'none';
        sisaList.innerHTML = detail_bentrok_sisa.map(b => `
            <div class="bentrok-sisa-item" onclick="highlightBentrokDariOptimasi(${b.jadwal_id})" style="cursor:pointer;">
                <div class="kelas-nama">${b.kelas} — ${b.mata_kuliah}</div>
                <div class="alasan">⚠ ${b.alasan}</div>
                <div style="color:#9a3412;font-size:11px;margin-top:2px;">${b.hari}, Slot ${b.slot_asal} · ${b.ruangan} · ${b.dosen}<br><span style="color:#6b7280">Klik untuk sorot di workspace</span></div>
            </div>`).join('');
    } else {
        sisaSection.style.display = 'none';
        beresSect.style.display   = '';
    }
    tampilStateModal('hasil');
}

function refreshWorkspaceDariOptimasi(jadwalTerbaru) {
    document.querySelectorAll('.jadwal-card').forEach(c => c.remove());
    document.querySelectorAll('.kelas-item').forEach(el => setSidebarStatus(el.dataset.id, 'belum'));

    _historyPaused = true;
    jadwalTerbaru.forEach(j => {
        const slotIdInt = parseInt(j.slot_id), sksInt = parseInt(j.sks);
        const infoStart = slotElMap[slotIdInt], infoEnd = slotElMap[slotIdInt + sksInt - 1];
        if (!infoStart) return;
        const sidebarEl = document.querySelector(`.kelas-item[data-id="${j.kelas_id}"]`);
        createCard({
            sks: sksInt, nama: j.nama, kelas: j.nama_kelas, kelasId: parseInt(j.kelas_id),
            dosen: j.dosen, kodeMk: j.kode_mk, ruangan: j.ruangan || '', ruanganId: j.ruangan_id || '',
            slotId: slotIdInt, day: j.hari, jamMulai: infoStart.jamMulai, jamSelesai: infoEnd?.jamSelesai || '-',
            jadwalIds: [j.jadwal_id], prodi: sidebarEl?.dataset.prodi || '-', jenis: sidebarEl?.dataset.jenis || 'Teori',
        }, true);
        setSidebarStatus(j.kelas_id, 'sudah');
    });
    _historyPaused = false;

    // Simpan state sesudah optimasi
    _historyPushRaw('add', 'Hasil optimasi otomatis diterapkan', _snapshotWorkspace());

    updateCounter();
    updateBentrokButton();
    const days = {!! $hari->pluck('nama_hari')->map(fn($d) => strtolower($d))->toJson() !!};
    filterCardsByDay(Alpine.$data(document.body).selectedDay || (days.length ? days[0] : 'senin'));
    renderTablePreview();
    updateBentrokButton();
    showToast('✓ Workspace diperbarui sesuai hasil optimasi.', 'green');
}

function highlightBentrokDariOptimasi(jadwalId) {
    const target = [...document.querySelectorAll('.jadwal-card')].find(c => {
        const ids = JSON.parse(c.dataset.jadwalIds || '[]');
        return ids.includes(jadwalId) || ids.includes(String(jadwalId));
    });
    if (!target) { showToast('Card tidak ditemukan. Coba refresh workspace.', 'red'); return; }
    tutupModalOptimasi();
    const targetDay = target.dataset.day;
    Alpine.$data(document.body).selectedDay = targetDay;
    filterCardsByDay(targetDay);
    document.querySelectorAll('.jadwal-card').forEach(c => { c.style.outline = ''; c.style.zIndex = '10'; });
    target.style.outline = '3px solid #ef4444';
    target.style.zIndex  = '50';
    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(() => { target.style.outline = ''; target.style.zIndex = '10'; }, 3000);
}

// ============================================================
// DRAG & DROP DARI SIDEBAR KE SLOT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.kelas-item').forEach(el => {
        el.addEventListener('dragstart', e => {
            if (el.dataset.status === 'sudah') { e.preventDefault(); return; }
            ['id','sks','nama','kelas','dosen'].forEach(k =>
                e.dataTransfer.setData(k === 'id' ? 'kelas_id' : k, el.dataset[k] || '')
            );
            e.dataTransfer.setData('kode_mk', el.dataset.kodeMk || '-');
            e.dataTransfer.setData('prodi',   el.dataset.prodi  || '-');
        });
    });

    document.querySelectorAll('[data-slot-line]').forEach(slot => {
        const id = parseInt(slot.dataset.slotLine);
        slotElMap[id] = { jamMulai: slot.dataset.jamMulai, jamSelesai: slot.dataset.jamSelesai };
        SLOT_VALID.push(id);

        slot.addEventListener('dragover', e => e.preventDefault());
        slot.addEventListener('drop', e => {
            e.preventDefault();
            
            if (slot.dataset.allowed === '0') {
                showToast('Slot waktu ini tidak aktif untuk hari yang dipilih!', 'red');
                return;
            }

            const fromWorkspace = e.dataTransfer.getData('from_workspace');
            const sks       = parseInt(e.dataTransfer.getData('sks'));
            const nama      = e.dataTransfer.getData('nama');
            const kelas     = e.dataTransfer.getData('kelas');
            const kelasId   = e.dataTransfer.getData('kelas_id');
            const dosen     = e.dataTransfer.getData('dosen');
            const ruangan   = e.dataTransfer.getData('ruangan');
            const ruanganId = e.dataTransfer.getData('ruanganId');
            const kodeMk    = e.dataTransfer.getData('kode_mk') || e.dataTransfer.getData('kodeMk') || '-';
            const prodi     = e.dataTransfer.getData('prodi') || '-';
            const jenis     = e.dataTransfer.getData('jenis') || 'Teori';
            const slotId    = parseInt(slot.dataset.slotLine);
            const day       = Alpine.$data(document.body).selectedDay;

            const kelasList   = fromWorkspace && draggedCard ? JSON.parse(draggedCard.dataset.kelasList   || '[]') : [kelas];
            const kelasIdList = fromWorkspace && draggedCard ? JSON.parse(draggedCard.dataset.kelasIdList || '[]') : [kelasId];
            const jadwalIds   = fromWorkspace && draggedCard ? JSON.parse(draggedCard.dataset.jadwalIds   || '[]') : [];

            if (!nama || !kelas || isNaN(sks) || sks < 1) return;

            if (slot.dataset.isIstirahat === '1') {
                showToast('⚠ Perhatian: kelas ditempatkan di slot istirahat.', 'red');
            }

            const actionType = fromWorkspace ? 'move' : 'add';
            const actionDesc = fromWorkspace
                ? `Pindah: ${kelasList[0] || kelas} → ${day.charAt(0).toUpperCase() + day.slice(1)} Slot ${slotId}`
                : `Tambah: ${kelas} — ${day.charAt(0).toUpperCase() + day.slice(1)} Slot ${slotId}`;

            if (fromWorkspace && draggedCard) {
                hapusJadwal(draggedCard);
                draggedCard.remove();
            }

            const jamMulai     = slot.dataset.jamMulai;
            const jamSelesaiEl = document.querySelector(`[data-slot-line="${slotId + sks - 1}"]`);
            const jamSelesai   = jamSelesaiEl?.dataset.jamSelesai || '-';

            _historyPaused = true;
            const card = createCard({ sks, nama, kelas, kelasId, dosen, kodeMk, prodi, jenis, ruangan, ruanganId, slotId, day, jamMulai, jamSelesai, jadwalIds });
            _historyPaused = false;

            if (kelasList.length > 1) {
                card.dataset.kelasList   = JSON.stringify(kelasList);
                card.dataset.kelasIdList = JSON.stringify(kelasIdList);
                const color = getCourseColor(nama);
                card.innerHTML = `
                    <div>
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-[13px] text-gray-800">${kelasList.join(' + ')}</span>
                                <span class="px-2 py-0.5 rounded-full ${color.badge} text-[10px] font-semibold">Gabungan</span>
                            </div>
                            <button class="text-gray-400 hover:text-red-500 transition ml-2" onclick="removeCard(this)">✕</button>
                        </div>
                        <div class="mt-2">
                            <span class="text-xs px-2 py-0.5 rounded-lg bg-white/60 text-gray-600">${prodi || '-'}</span>
                            <span class="text-xs font-mono px-2 py-0.5 rounded-lg bg-white/60 ${color.text}">${kodeMk}</span>
                        </div>
                        <h3 class="mt-2 text-[14px] leading-snug font-bold text-gray-800">${nama}</h3>
                        <div class="mt-3 space-y-1">
                            <p class="text-sm text-gray-700 font-medium">${dosen}</p>
                            <p class="text-sm text-gray-700 font-semibold ruangan-text">${ruangan || '-'} <span class="mx-1 text-gray-300">|</span> ${sks} SKS</p>
                        </div>
                    </div>`;
            }

            _historyPushRaw(actionType, actionDesc, _snapshotWorkspace());

            simpanJadwal(card);
            kelasIdList.forEach(kid => setSidebarStatus(kid, 'sudah'));
            updateCounter();
            applyFilter();
            renderTablePreview();
            updateBentrokButton();
        });
    });

    const days = {!! $hari->pluck('nama_hari')->map(fn($d) => strtolower($d))->toJson() !!};
    SLOT_VALID.sort((a, b) => a - b);
    setActiveTab('belum');
    updateCounter();
    filterCardsByDay(days.length ? days[0] : 'senin');
    loadExistingJadwals();
});

// ============================================================
// RESET WORKSPACE
// ============================================================
function resetWorkspace() {
    const cards = [...document.querySelectorAll('.jadwal-card')];
    if (!cards.length) { showToast('Workspace sudah kosong.', 'green'); return; }
    if (!confirm(`Hapus semua ${cards.length} jadwal dari workspace dan database? Tindakan ini tidak bisa dibatalkan.`)) return;



    const btn = document.querySelector('[onclick="resetWorkspace()"]');
    if (btn) { btn.disabled = true; btn.innerText = 'Mereset...'; }

    // Langsung bersihkan DOM secara instan
    _historyPaused = true;
    document.querySelectorAll('.jadwal-card').forEach(c => c.remove());
    document.querySelectorAll('.kelas-item').forEach(el => setSidebarStatus(el.dataset.id, 'belum'));
    _historyPaused = false;

    updateCounter();
    applyFilter();
    renderTablePreview();
    updateBentrokButton();
    closeDetailPanel();
    
    _historyPushRaw('reset', `Reset workspace (${cards.length} jadwal)`, _snapshotWorkspace());

    showToast('Memproses penghapusan di latar belakang...', 'blue');

    // Proses penghapusan ke database secara bulk tanpa memblokir UI
    fetch('/jadwal/hapus-semua', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        body: JSON.stringify({ tahun_akademik_id: TAHUN_AKADEMIK })
    })
    .then(r => r.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Reset Jadwal`;
        }
        if (data.success) {
            showToast('✓ Semua jadwal berhasil direset dari database!', 'green');
        } else {
            showToast('Gagal mereset database.', 'red');
        }
    })
    .catch(err => {
        console.error('Reset bulk error:', err);
        showToast('Terjadi kesalahan jaringan saat reset.', 'red');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Reset Jadwal`;
        }
    });

    // Simpan state kosong sebagai titik redo
    _historyPushRaw('reset', 'Workspace kosong (setelah reset)', _snapshotWorkspace());
}

// ============================================================
// DETEKSI BENTROK
// ============================================================
function detectBentrok() {
    const cards = [...document.querySelectorAll('.jadwal-card')];
    const bentrokSet = new Set();
    const byHari = {};
    cards.forEach(card => {
        if (!byHari[card.dataset.day]) byHari[card.dataset.day] = [];
        byHari[card.dataset.day].push(card);
    });
    Object.values(byHari).forEach(hariCards => {
        for (let i = 0; i < hariCards.length; i++) {
            for (let j = i + 1; j < hariCards.length; j++) {
                const a = hariCards[i], b = hariCards[j];
                const aStart = parseInt(a.dataset.start), aEnd = parseInt(a.dataset.end);
                const bStart = parseInt(b.dataset.start), bEnd = parseInt(b.dataset.end);
                if (!(aStart < bEnd && aEnd > bStart)) continue;
                if (a.dataset.ruanganId && b.dataset.ruanganId && a.dataset.ruanganId !== '' && b.dataset.ruanganId !== '' && String(a.dataset.ruanganId) === String(b.dataset.ruanganId)) { bentrokSet.add(a); bentrokSet.add(b); }
                if (a.dataset.dosen && a.dataset.dosen !== '-' && a.dataset.dosen === b.dataset.dosen) { bentrokSet.add(a); bentrokSet.add(b); }
                const aKelas = JSON.parse(a.dataset.kelasIdList || '[]');
                const bKelas = JSON.parse(b.dataset.kelasIdList || '[]');
                if (aKelas.some(k => bKelas.includes(k))) { bentrokSet.add(a); bentrokSet.add(b); }
            }
        }
    });
    return [...bentrokSet];
}

function updateBentrokButton() {
    const bentrokCards = detectBentrok();
    const btn = document.getElementById('btn-lihat-bentrok');
    if (!btn) return;
    if (bentrokCards.length > 0) {
        btn.className = 'px-5 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold flex items-center gap-2 transition animate-pulse border-2 border-red-300';
        btn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>Bentrok! (${bentrokCards.length} card)`;
    } else {
        btn.className = 'px-5 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl font-medium flex items-center gap-2 transition';
        btn.innerHTML = `<svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Lihat Bentrok`;
    }
}

// ============================================================
// MODAL BENTROK
// ============================================================
function lihatBentrok() {
    const bentrokCards = detectBentrok();
    if (!bentrokCards.length) { showToast('✓ Tidak ada bentrok ditemukan!', 'green'); return; }
    const existing = document.getElementById('modal-bentrok-overlay');
    if (existing) existing.remove();
    const cards = [...document.querySelectorAll('.jadwal-card')];
    const pasangan = [];
    const byHari = {};
    cards.forEach(card => { if (!byHari[card.dataset.day]) byHari[card.dataset.day] = []; byHari[card.dataset.day].push(card); });
    Object.values(byHari).forEach(hariCards => {
        for (let i = 0; i < hariCards.length; i++) {
            for (let j = i + 1; j < hariCards.length; j++) {
                const a = hariCards[i], b = hariCards[j];
                const aStart = parseInt(a.dataset.start), aEnd = parseInt(a.dataset.end);
                const bStart = parseInt(b.dataset.start), bEnd = parseInt(b.dataset.end);
                if (!(aStart < bEnd && aEnd > bStart)) continue;
                const alasan = [];
                if (a.dataset.ruanganId && b.dataset.ruanganId && a.dataset.ruanganId !== '' && b.dataset.ruanganId !== '' && String(a.dataset.ruanganId) === String(b.dataset.ruanganId))
                    alasan.push(`Ruangan sama: <strong>${a.dataset.ruangan || '-'}</strong>`);
                if (a.dataset.dosen && a.dataset.dosen !== '-' && a.dataset.dosen === b.dataset.dosen)
                    alasan.push(`Dosen sama: <strong>${a.dataset.dosen}</strong>`);
                const aKelas = JSON.parse(a.dataset.kelasIdList || '[]'), bKelas = JSON.parse(b.dataset.kelasIdList || '[]');
                if (aKelas.some(k => bKelas.includes(k))) alasan.push('Kelas bentrok di slot yang sama');
                if (alasan.length) pasangan.push({ a, b, alasan });
            }
        }
    });
    const hariLabel = h => h.charAt(0).toUpperCase() + h.slice(1);
    const rows = pasangan.map((p, idx) => `
        <div class="bentrok-row rounded-xl border border-red-100 bg-red-50 p-4 cursor-pointer hover:border-red-300 transition" onclick="highlightBentrokPair(${idx})" data-idx="${idx}">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 w-6 h-6 rounded-full bg-red-500 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">${idx+1}</span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-bold text-gray-800">${p.a.dataset.kelas||p.a.dataset.nama}</span>
                        <span class="text-gray-400">↔</span>
                        <span class="text-sm font-bold text-gray-800">${p.b.dataset.kelas||p.b.dataset.nama}</span>
                    </div>
                    <div class="mt-1 text-xs text-gray-500">${hariLabel(p.a.dataset.day)} · Slot ${p.a.dataset.start}–${p.a.dataset.end} ↔ Slot ${p.b.dataset.start}–${p.b.dataset.end}</div>
                    <div class="mt-2 space-y-1">${p.alasan.map(a => `<div class="text-xs text-red-600 flex items-center gap-1">⚠ ${a}</div>`).join('')}</div>
                </div>
            </div>
        </div>`).join('');

    const overlay = document.createElement('div');
    overlay.id = 'modal-bentrok-overlay';
    overlay.style.cssText = 'position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.45);display:flex;align-items:center;justify-content:center;';
    overlay.innerHTML = `
        <div style="background:#fff;border-radius:20px;width:100%;max-width:520px;max-height:80vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <div style="background:linear-gradient(135deg,#dc2626,#ef4444);padding:22px 28px 20px;">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <h2 style="color:#fff;font-size:18px;font-weight:700;margin:0 0 4px;">⚠️ Daftar Bentrok</h2>
                        <p style="color:rgba(255,255,255,0.8);font-size:13px;margin:0;">${pasangan.length} konflik ditemukan · Klik baris untuk sorot di workspace</p>
                    </div>
                    <button onclick="document.getElementById('modal-bentrok-overlay').remove()"
                        style="color:#fff;background:rgba(255,255,255,0.2);border:none;border-radius:8px;width:32px;height:32px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;">✕</button>
                </div>
            </div>
            <div style="flex:1;overflow-y:auto;padding:20px 24px;display:flex;flex-direction:column;gap:12px;">${rows}</div>
            <div style="padding:16px 24px;border-top:1px solid #f3f4f6;">
                <button onclick="document.getElementById('modal-bentrok-overlay').remove()"
                    style="width:100%;padding:12px;border-radius:12px;border:1.5px solid #e5e7eb;background:#fff;font-weight:600;font-size:14px;color:#6b7280;cursor:pointer;">Tutup</button>
            </div>
        </div>`;
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.remove(); });
    document.body.appendChild(overlay);
    window._bentrokPasangan = pasangan;
}

function highlightBentrokPair(idx) {
    const pair = window._bentrokPasangan?.[idx];
    if (!pair) return;
    document.getElementById('modal-bentrok-overlay')?.remove();
    const targetDay = pair.a.dataset.day;
    Alpine.$data(document.body).selectedDay = targetDay;
    filterCardsByDay(targetDay);
    document.querySelectorAll('.jadwal-card').forEach(c => { c.style.outline = ''; c.style.zIndex = '10'; });
    [pair.a, pair.b].forEach(card => { card.style.outline = '3px solid #ef4444'; card.style.zIndex = '50'; card.scrollIntoView({ behavior: 'smooth', block: 'center' }); });
    setTimeout(() => { [pair.a, pair.b].forEach(card => { card.style.outline = ''; card.style.zIndex = '10'; }); }, 3000);
}

</script>

</body>
</html>
