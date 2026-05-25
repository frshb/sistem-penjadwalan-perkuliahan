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
        /* ── Modal Generate Config ── */
        #modal-generate-overlay {
            position: fixed; inset: 0; z-index: 9999;
            background: rgba(0,0,0,0.45);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none;
            transition: opacity 0.2s ease;
        }
        #modal-generate-overlay.show {
            opacity: 1; pointer-events: auto;
        }
        #modal-generate-box {
            background: #fff; border-radius: 20px;
            width: 100%; max-width: 480px;
            padding: 0; overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.18);
            transform: translateY(16px) scale(0.98);
            transition: transform 0.25s cubic-bezier(.34,1.56,.64,1), opacity 0.2s ease;
            opacity: 0;
        }
        #modal-generate-overlay.show #modal-generate-box {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
        .modal-header {
            background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
            padding: 22px 28px 20px;
        }
        .modal-header h2 {
            color: #fff; font-size: 18px; font-weight: 700; margin: 0 0 4px;
        }
        .modal-header p {
            color: rgba(255,255,255,0.75); font-size: 13px; margin: 0;
        }
        .modal-body { padding: 24px 28px; }
        .cfg-group { margin-bottom: 20px; }
        .cfg-group label {
            display: block; font-size: 12px; font-weight: 700;
            color: #6b7280; text-transform: uppercase; letter-spacing: .06em;
            margin-bottom: 8px;
        }
        .cfg-group .cfg-desc {
            font-size: 12px; color: #9ca3af; margin-top: 4px;
        }
        .cfg-row {
            display: flex; align-items: center; gap: 12px;
        }
        .cfg-row input[type="range"] {
            flex: 1; accent-color: #0d9488; height: 4px;
            cursor: pointer;
        }
        .cfg-val {
            min-width: 48px; text-align: center;
            background: #f0fdf9; border: 1.5px solid #5eead4;
            color: #0f766e; font-weight: 700; font-size: 15px;
            border-radius: 8px; padding: 4px 8px;
        }
        .cfg-divider { border: none; border-top: 1px solid #f3f4f6; margin: 8px 0 20px; }
        .randomize-toggle {
            display: flex; align-items: center; justify-content: space-between;
            background: #f9fafb; border-radius: 12px; padding: 14px 16px;
            border: 1.5px solid #e5e7eb; cursor: pointer;
            transition: border-color 0.15s, background 0.15s;
        }
        .randomize-toggle:hover { border-color: #5eead4; background: #f0fdf9; }
        .randomize-toggle.active { border-color: #14b8a6; background: #f0fdf9; }
        .toggle-info { flex: 1; }
        .toggle-info span { font-size: 14px; font-weight: 600; color: #111827; display: block; }
        .toggle-info small { font-size: 12px; color: #9ca3af; }
        .toggle-switch {
            width: 40px; height: 22px; border-radius: 11px;
            background: #d1d5db; position: relative; transition: background 0.2s; flex-shrink: 0;
        }
        .toggle-switch::after {
            content: ''; position: absolute; top: 3px; left: 3px;
            width: 16px; height: 16px; border-radius: 50%;
            background: #fff; transition: transform 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,.2);
        }
        .toggle-switch.on { background: #14b8a6; }
        .toggle-switch.on::after { transform: translateX(18px); }

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
        .btn-modal-run:hover { background: #0f766e; }
        .btn-modal-run svg { width: 18px; height: 18px; }

        /* seed badge */
        #seed-display {
            font-size: 11px; color: #9ca3af; text-align: center;
            margin-top: 6px; font-family: monospace;
            min-height: 16px;
        }
    </style>
</head>

<body
    x-data="{
        showTablePreview: false,
        sidebarOpen: true,
        selectedDay: 'senin',
        showFilter: false,
        filterProdi: [],
        filterSemester: [],
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

{{-- ============================================================ --}}
{{-- MODAL KONFIGURASI GENERATE                                    --}}
{{-- ============================================================ --}}
<div id="modal-generate-overlay" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div id="modal-generate-box">
        <div class="modal-header">
            <h2 id="modal-title">⚙️ Konfigurasi Generate Jadwal</h2>
            <p>Atur batasan sebelum menjalankan algoritma penjadwalan otomatis.</p>
        </div>

        <div class="modal-body">
            {{-- SKS Dosen per hari --}}
            <div class="cfg-group">
                <label>Maks. SKS Dosen per Hari</label>
                <div class="cfg-row">
                    <input type="range" id="cfg-sks-dosen" min="2" max="16" step="1" value="6"
                           oninput="document.getElementById('val-sks-dosen').innerText = this.value">
                    <span class="cfg-val" id="val-sks-dosen">6</span>
                </div>
                <div class="cfg-desc">Jumlah maksimal SKS yang bisa diajar satu dosen dalam satu hari.</div>
            </div>

            {{-- Kelas per slot --}}
            <div class="cfg-group">
                <label>Maks. Kelas per Slot Waktu</label>
                <div class="cfg-row">
                    <input type="range" id="cfg-kelas-slot" min="1" max="20" step="1" value="4"
                           oninput="document.getElementById('val-kelas-slot').innerText = this.value">
                    <span class="cfg-val" id="val-kelas-slot">4</span>
                </div>
                <div class="cfg-desc">Berapa banyak kelas boleh berjalan bersamaan di satu slot.</div>
            </div>

            {{-- MK per prodi per hari --}}
            <div class="cfg-group">
                <label>Maks. Mata Kuliah Unik per Prodi per Hari</label>
                <div class="cfg-row">
                    <input type="range" id="cfg-mk-prodi" min="1" max="16" step="1" value="4"
                           oninput="document.getElementById('val-mk-prodi').innerText = this.value">
                    <span class="cfg-val" id="val-mk-prodi">4</span>
                </div>
                <div class="cfg-desc">Batasi jumlah mata kuliah berbeda per program studi dalam satu hari.</div>
            </div>

            <hr class="cfg-divider">

            {{-- Randomize toggle --}}
            <div class="randomize-toggle" id="randomize-toggle" onclick="toggleRandomize()">
                <div class="toggle-info">
                    <span>🎲 Acak Hasil Generate</span>
                    <small>Setiap klik Generate menghasilkan jadwal yang berbeda.</small>
                </div>
                <div class="toggle-switch on" id="toggle-switch-el"></div>
            </div>
            <div id="seed-display">seed: —</div>
        </div>

        <div class="modal-footer">
            <button class="btn-modal-cancel" onclick="closeGenerateModal()">Batal</button>
            <button class="btn-modal-run" onclick="runGenerateFromModal()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Jalankan Generate
            </button>
        </div>
    </div>
</div>

<main :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'" class="transition-all duration-300 p-6 sm:p-8">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <div class="flex flex-col">
                    <div class="w-2 h-5 bg-teal-600 rounded-tl-md"></div>
                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                </div>
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
    <div class="mt-8 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button
                id="btn-generate"
                onclick="openGenerateModal()"
                class="px-5 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-semibold shadow flex items-center gap-2 transition"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Generate Jadwal
            </button>
            {{-- Ganti tombol Lihat Bentrok yang lama --}}
            <button
                id="btn-lihat-bentrok"
                onclick="lihatBentrok()"
                class="px-5 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl font-medium flex items-center gap-2 transition"
            >
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Lihat Bentrok
            </button>
            {{-- Setelah tombol "Lihat Bentrok" --}}
            <button
                onclick="resetWorkspace()"
                class="px-5 py-3 bg-red-50 hover:bg-red-100 border border-red-200 text-red-600 rounded-xl font-semibold flex items-center gap-2 transition"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Reset Jadwal
            </button>

            <button
                onclick="simpanSemuaJadwal()"
                id="btn-simpan-semua"
                class="px-5 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold shadow flex items-center gap-2 transition"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span id="btn-simpan-label">Simpan Jadwal</span>
            </button>
        </div>
        <div class="flex items-center gap-3">
            <button
                @click="showTablePreview = !showTablePreview"
                class="flex items-center gap-2 px-5 py-3 bg-teal-700 hover:bg-teal-800 text-white rounded-xl font-semibold shadow transition border-2 border-dashed border-teal-400"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/>
                </svg>
                <span x-show="!showTablePreview">Tampilan Tabel (Export)</span>
                <span x-show="showTablePreview">Tutup Tabel</span>
            </button>
        </div>
    </div>

    {{-- CONTENT --}}
    <div class="grid grid-cols-12 gap-6 mt-8 h-[calc(100vh-170px)] overflow-hidden">

        {{-- SIDEBAR KELAS --}}
        <div class="col-span-12 xl:col-span-3 min-h-0 flex">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 h-full flex flex-col w-full overflow-hidden">

                <div class="p-5 border-b border-gray-100">
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
                                class="absolute right-0 top-14 w-72 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden"
                                x-cloak
                            >
                                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                                    <h3 class="font-bold text-gray-800 text-sm">Filter Kelas</h3>
                                    <button @click="filterProdi = []; filterSemester = []; applyFilter()" class="text-xs text-red-500 hover:text-red-600 font-medium transition">Reset</button>
                                </div>
                                <div class="px-5 py-4 space-y-5 max-h-96 overflow-y-auto">
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
                                <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
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

                <div class="flex-1 overflow-y-auto min-h-0 px-2 pb-2">
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
                        data-ruangans="{{ json_encode($item->matakuliah->ruangans->map(fn($r) => ['id' => $r->id_ruang, 'nama' => $r->nama_ruang])) }}"
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
        <div class="col-span-12 xl:col-span-9 flex flex-col lg:flex-row gap-4 min-h-0">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 w-full flex flex-col overflow-hidden">

                <div class="p-5 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-800">Workspace Jadwal</h2>
                        <div class="flex items-center gap-2">
                            @foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat'] as $hari)
                            <button
                                @click="selectedDay = '{{ $hari }}'; filterCardsByDay('{{ $hari }}')"
                                :class="selectedDay === '{{ $hari }}' ? 'bg-teal-600 text-white' : 'border border-gray-200 hover:bg-gray-50 text-gray-700'"
                                class="px-6 py-2 rounded-xl font-semibold transition capitalize"
                            >{{ $hari }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="relative flex-1 overflow-auto">
                    @foreach ($slotWaktu as $slot)
                    <div class="grid grid-cols-12 border-b border-gray-200 h-[120px]
                    {{ $loop->iteration === 6 ? 'bg-amber-50 border-l-4 border-l-amber-400' : '' }}">
                        <div class="col-span-2 border-r border-gray-200 px-3 py-3 flex flex-col justify-start
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

                    <div id="jadwal-layer" class="absolute top-0 left-[16.666667%] right-0 bottom-0" style="min-width: 2000px; pointer-events: none;"></div>
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
                <div class="px-5 py-4 border-t border-gray-100 flex gap-2">
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
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium flex items-center gap-2 transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export Excel
                    </a>

                    <a href="{{ route('jadwal.export.pdf', $tahunAkademik->id_tahunakademik) }}"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium flex items-center gap-2 transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Export PDF
                    </a>
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
const SLOT_HEIGHT    = 120;
const CARD_WIDTH     = 185;
const CARD_GAP       = 12;
const CSRF_TOKEN     = document.querySelector('meta[name="csrf-token"]').content;
const TAHUN_AKADEMIK = document.querySelector('meta[name="tahun-akademik-id"]').content;

let ALL_RUANGAN = [];
let activeTab        = 'belum';
let activeDetailCard = null;
let draggedCard      = null;

let slotElMap = {};
let SLOT_VALID = [];

// ── Konfigurasi generate (bisa diubah dari modal) ──
let GEN_CONFIG = {
    maxSksDosen:   6,
    maxKelasSlot:  4,
    maxMkProdi:    4,
    randomize:     true,
    seed:          null,
};

// ============================================================
// MODAL GENERATE — OPEN / CLOSE / TOGGLE
// ============================================================
function openGenerateModal() {
    // Sinkronkan nilai slider dengan config saat ini
    document.getElementById('cfg-sks-dosen').value  = GEN_CONFIG.maxSksDosen;
    document.getElementById('val-sks-dosen').innerText = GEN_CONFIG.maxSksDosen;
    document.getElementById('cfg-kelas-slot').value = GEN_CONFIG.maxKelasSlot;
    document.getElementById('val-kelas-slot').innerText = GEN_CONFIG.maxKelasSlot;
    document.getElementById('cfg-mk-prodi').value   = GEN_CONFIG.maxMkProdi;
    document.getElementById('val-mk-prodi').innerText = GEN_CONFIG.maxMkProdi;

    // Sync toggle randomize
    const sw = document.getElementById('toggle-switch-el');
    const tg = document.getElementById('randomize-toggle');
    if (GEN_CONFIG.randomize) { sw.classList.add('on'); tg.classList.add('active'); }
    else { sw.classList.remove('on'); tg.classList.remove('active'); }

    updateSeedDisplay();
    document.getElementById('modal-generate-overlay').classList.add('show');
}

function closeGenerateModal() {
    document.getElementById('modal-generate-overlay').classList.remove('show');
}

// Tutup jika klik overlay (bukan box)
document.getElementById('modal-generate-overlay').addEventListener('click', function(e) {
    if (e.target === this) closeGenerateModal();
});

// Tekan Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeGenerateModal();
});

function toggleRandomize() {
    GEN_CONFIG.randomize = !GEN_CONFIG.randomize;
    const sw = document.getElementById('toggle-switch-el');
    const tg = document.getElementById('randomize-toggle');
    sw.classList.toggle('on', GEN_CONFIG.randomize);
    tg.classList.toggle('active', GEN_CONFIG.randomize);
    updateSeedDisplay();
}

function updateSeedDisplay() {
    const el = document.getElementById('seed-display');
    if (!GEN_CONFIG.randomize) {
        el.innerText = 'seed: tetap (deterministik)';
    } else {
        const preview = GEN_CONFIG.seed ?? '—';
        el.innerText = `seed akan dibuat baru setiap generate`;
    }
}

function runGenerateFromModal() {
    // Baca nilai terbaru dari slider
    GEN_CONFIG.maxSksDosen  = parseInt(document.getElementById('cfg-sks-dosen').value);
    GEN_CONFIG.maxKelasSlot = parseInt(document.getElementById('cfg-kelas-slot').value);
    GEN_CONFIG.maxMkProdi   = parseInt(document.getElementById('cfg-mk-prodi').value);

    // Buat seed baru jika randomize aktif
    if (GEN_CONFIG.randomize) {
        GEN_CONFIG.seed = Math.floor(Math.random() * 1_000_000);
    } else {
        GEN_CONFIG.seed = 42; // seed tetap → hasil deterministik
    }

    closeGenerateModal();
    setTimeout(generateJadwal, 220); // beri waktu modal menutup dulu
}

// ============================================================
// SEEDED RANDOM (Mulberry32 PRNG)
// Menghasilkan angka acak 0..1 yang bisa direproduksi dari seed
// ============================================================
function makePRNG(seed) {
    let s = seed >>> 0;
    return function() {
        s += 0x6D2B79F5;
        let t = s;
        t = Math.imul(t ^ t >>> 15, t | 1);
        t ^= t + Math.imul(t ^ t >>> 7, t | 61);
        return ((t ^ t >>> 14) >>> 0) / 4294967296;
    };
}

// Fisher-Yates shuffle dengan PRNG custom
function shuffleArray(arr, rng) {
    const a = [...arr];
    for (let i = a.length - 1; i > 0; i--) {
        const j = Math.floor(rng() * (i + 1));
        [a[i], a[j]] = [a[j], a[i]];
    }
    return a;
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
    ];
    let hash = 0;
    for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
    return colors[Math.abs(hash % colors.length)];
}

// ============================================================
// API — simpan & hapus jadwal
// ============================================================
async function simpanJadwal(card) {
    const kelasIdList = JSON.parse(card.dataset.kelasIdList || '[]');
    const hariMap = { senin: 1, selasa: 2, rabu: 3, kamis: 4, jumat: 5 };
    const hariId  = hariMap[card.dataset.day] ?? 1;

    try {
        const results = await Promise.all(kelasIdList.map(kelasId => {
            const sidebarEl = document.querySelector(`.kelas-item[data-id="${kelasId}"]`);
            return fetch('{{ route("jadwal.simpan-slot") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    kelas_id:          kelasId,
                    slot_id:           parseInt(card.dataset.start),
                    hari_id:           hariId,
                    ruang_id:          card.dataset.ruanganId || null,
                    durasi_sks:        parseInt(card.dataset.sks),
                    kode_matkul:       card.dataset.kodeMk || '',
                    dosen_id:          sidebarEl?.dataset.dosenId || null,
                    tahun_akademik_id: TAHUN_AKADEMIK,
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
    btn.disabled = true;
    label.innerText = 'Menyimpan...';

    let berhasil = 0, gagal = 0;
    for (const card of cards) {
        try { await simpanJadwal(card); berhasil++; }
        catch (e) { gagal++; console.error('Gagal simpan card:', card.dataset.kelas, e); }
    }

    btn.disabled = false;
    label.innerText = 'Simpan Jadwal';
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
function filterCardsByDay(day) {
    document.querySelectorAll('.jadwal-card').forEach(card => {
        const show = card.dataset.day === day;
        card.style.display       = show ? 'flex' : 'none';
        card.style.pointerEvents = show ? 'auto' : 'none';
    });
}

// ============================================================
// HAPUS CARD
// ============================================================
function removeCard(btn) {
    const card     = btn.closest('.jadwal-card');
    const kelasIds = JSON.parse(card.dataset.kelasIdList || '[]');
    hapusJadwal(card);
    card.remove();
    kelasIds.forEach(kid => {
        const remaining = document.querySelectorAll(`.jadwal-card[data-kelas-id="${kid}"]`);
        if (!remaining.length) setSidebarStatus(kid, 'belum');
    });
    updateCounter();
    applyFilter();
    renderTablePreview();
    updateBentrokButton();
    if (activeDetailCard === card) closeDetailPanel();
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

    const select   = document.getElementById('dp-ruangan-select');
    select.innerHTML = '<option value="">-- Pilih Ruangan --</option>';
    // ✅ BARU — semua ruangan, ruangan terkait MK ditandai
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

    const panel = document.getElementById('detail-panel');
    panel.classList.remove('hidden');
    panel.classList.add('flex');
}

function closeDetailPanel() {
    document.getElementById('detail-panel').classList.remove('flex');
    document.getElementById('detail-panel').classList.add('hidden');
    activeDetailCard = null;
}

function updateCardRuangan(ruanganId, ruanganNama) {
    if (!activeDetailCard) return;
    activeDetailCard.dataset.ruangan   = ruanganNama;
    activeDetailCard.dataset.ruanganId = ruanganId;
    const el = activeDetailCard.querySelector('.ruangan-text');
    if (el) el.innerText = ruanganNama || '-';
    renderTablePreview();
    updateBentrokButton();
}

// ============================================================
// TABLE PREVIEW
// ============================================================
function renderTablePreview() {
    const tbody = document.getElementById('tbody-preview');
    if (!tbody) return;
    const days  = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
    const cards = [...document.querySelectorAll('.jadwal-card')].sort((a, b) => {
        const dDiff = days.indexOf(a.dataset.day) - days.indexOf(b.dataset.day);
        return dDiff || parseInt(a.dataset.start) - parseInt(b.dataset.start);
    });

    if (!cards.length) {
        tbody.innerHTML = `<tr><td colspan="13" class="px-4 py-6 text-center text-gray-400">Belum ada jadwal yang disusun.</td></tr>`;
        return;
    }

    tbody.innerHTML = '';
    cards.forEach(card => {
        const kelasList = JSON.parse(card.dataset.kelasList || '[]');
        const slotId    = parseInt(card.dataset.start);
        const sesi = slotId <= 8 ? 'Pagi' : 'Malam';
        const hariLabel = card.dataset.day.charAt(0).toUpperCase() + card.dataset.day.slice(1);

        const kelasPertama = kelasList[0];
        const sidebarEl    = document.querySelector(`.kelas-item[data-kelas="${kelasPertama}"]`);
        const prodi        = sidebarEl?.querySelector('.bg-purple-100')?.innerText?.trim() || '-';
        const semester     = sidebarEl?.querySelector('.bg-gray-100')?.innerText?.replace('Semester ', '').trim() || '-';
        const namaKelas    = kelasList.length > 1
            ? kelasList.join(' + ') + ' <span class="text-xs px-1.5 py-0.5 bg-pink-100 text-pink-700 rounded font-semibold">Gabungan</span>'
            : kelasPertama;

        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50 border-b border-gray-100';
        tr.innerHTML = `
            <td class="px-4 py-3 text-sm">${hariLabel}</td>
            <td class="px-4 py-3 text-sm">${prodi}</td>
            <td class="px-4 py-3 text-sm">${semester}</td>
            <td class="px-4 py-3 text-sm">${sesi}</td>
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
}

// ============================================================
// BUAT CARD
// ============================================================
function createCard(data, skipSave = false) {
    const { sks, nama, kelas, kelasId, dosen, kodeMk, ruangan, ruanganId,
            slotId, day, jamMulai, jamSelesai, jadwalIds, prodi, jenis } = data;
    const color = getCourseColor(nama);

    const sameDay = [...document.querySelectorAll('.jadwal-card')].filter(c => c.dataset.day === day);
    let column = 0;
    while (true) {
        const collision = sameDay.some(ex => {
            const overlap = slotId < parseInt(ex.dataset.end) && (slotId + sks) > parseInt(ex.dataset.start);
            return overlap && parseInt(ex.dataset.column || 0) === column;
        });
        if (!collision) break;
        column++;
    }

    const left = 16 + column * (CARD_WIDTH + CARD_GAP);
    const top  = (slotId - 1) * SLOT_HEIGHT + 8;

    const card = document.createElement('div');
    card.className = `jadwal-card absolute ${color.bg} ${color.border} border border-l-[3px] rounded-2xl p-3 shadow-sm hover:shadow-md overflow-hidden z-10 cursor-move transition duration-200`;
    card.setAttribute('draggable', 'true');
    card.setAttribute('data-kelas-id', kelasId);

    const activeDay = Alpine.$data(document.body)?.selectedDay || 'senin';
    const isVisible = day === activeDay;

    Object.assign(card.dataset, {
        kelasList:   JSON.stringify([kelas]),
        kelasIdList: JSON.stringify([kelasId]),
        start:    slotId,
        end:      slotId + sks,
        column,   dosen, sks, nama, kelas, day, kodeMk, kelasId,
        prodi:      prodi || '',
        jamMulai:   jamMulai   || '-',
        jamSelesai: jamSelesai || '-',
        ruangan:    ruangan    || '',
        ruanganId:  ruanganId  || '',
        jadwalIds:  jadwalIds ? JSON.stringify(Array.isArray(jadwalIds) ? jadwalIds : [jadwalIds]) : '[]',
        jenis: jenis || 'Teori',
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

    return card;
}

// Load semua ruangan
try {
    const rawRuangan = document.getElementById('all-ruangan-data');
    if (rawRuangan) ALL_RUANGAN = JSON.parse(rawRuangan.textContent.trim());
} catch(e) {
    console.error('Gagal parse all-ruangan-data:', e);
}
// ============================================================
// LOAD JADWAL TERSIMPAN DARI DATABASE
// ============================================================
function loadExistingJadwals() {
    const raw = document.getElementById('existing-jadwal-data');
    if (!raw) return;

    let jadwals = [];
    try {
        jadwals = JSON.parse(raw.textContent.trim());
    } catch (e) {
        console.error('Gagal parse existing-jadwal-data:', e);
        return;
    }
    if (!jadwals.length) return;

    jadwals.forEach(j => {
        const slotIdInt = parseInt(j.slot_id);   // ← parseInt, bukan string
        const sksInt    = parseInt(j.sks);

        const infoStart = slotElMap[slotIdInt];
        const infoEnd   = slotElMap[slotIdInt + sksInt - 1];

        if (!infoStart) {
            console.warn('Slot tidak ditemukan:', slotIdInt, '| Tersedia:', Object.keys(slotElMap));
            return;
        }

        // Ambil prodi dari sidebar jika ada
        const sidebarEl = document.querySelector(`.kelas-item[data-id="${j.kelas_id}"]`);
        const prodi     = sidebarEl?.dataset.prodi || '-';
        const jenis     = sidebarEl?.dataset.jenis || 'Teori';

        createCard({
            sks:        sksInt,
            nama:       j.nama,
            kelas:      j.nama_kelas,
            kelasId:    parseInt(j.kelas_id),
            dosen:      j.dosen,
            kodeMk:     j.kode_mk,
            ruangan:    j.ruangan    || '',
            ruanganId:  j.ruangan_id || '',
            slotId:     slotIdInt,
            day:        j.hari,
            jamMulai:   infoStart.jamMulai,
            jamSelesai: infoEnd?.jamSelesai || '-',
            jadwalIds:  [j.jadwal_id],
            prodi:      prodi,
            jenis:      jenis,
        }, true);

        setSidebarStatus(j.kelas_id, 'sudah');
    });

    updateCounter();
    renderTablePreview();
}

function enableCardDrag(card) {
    card.addEventListener('dragstart', e => {
        draggedCard = card;
        e.dataTransfer.setData('from_workspace', '1');
        ['start','sks','nama','kelas','dosen','kodeMk','ruangan'].forEach(k =>
            e.dataTransfer.setData(k, card.dataset[k] || '')
        );
        e.dataTransfer.setData('kelas_id',     card.dataset.kelasId     || '');
        e.dataTransfer.setData('kelas_list',   card.dataset.kelasList   || '[]');
        e.dataTransfer.setData('kelas_id_list',card.dataset.kelasIdList || '[]');
        e.dataTransfer.setData('jadwal_ids',   card.dataset.jadwalIds   || '[]');
        e.dataTransfer.setData('jenis',        card.dataset.jenis       || 'Teori');
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
    });
}

// ============================================================
// GENERATE JADWAL
// Constraint yang aktif:
//   [C1] Dosen maks SKS per hari          (dari modal)
//   [C2] Ruangan tidak bentrok
//   [C3] Maks kelas per slot              (dari modal)
//   [C4] Kelas yang sama tidak bentrok di slot berbeda
//   [C5] MK yang sama tidak overlap waktu
//   [C6] Maks MK unik per prodi per hari  (dari modal)
//   [C7] Kelas A/B pagi/siang, kelas S malam
//
// Randomisasi:
//   - Urutan kelas diacak dengan seeded PRNG
//   - Urutan hari diacak per kelas
//   - Urutan slot diacak per kelas
//   - Pool ruangan diacak per kelas
// ============================================================
function generateJadwal() {
    const semua = [...document.querySelectorAll('.kelas-item')];
    const belum = semua.filter(el => el.dataset.status === 'belum');

    if (!belum.length) { showToast('Semua kelas sudah terjadwal!', 'green'); return; }

    const sudahAda = document.querySelectorAll('.jadwal-card').length;
    if (sudahAda > 0) {
        if (!confirm(`Sudah ada ${sudahAda} jadwal di workspace. Generate ulang akan menghapus semua. Lanjutkan?`)) return;
        document.querySelectorAll('.jadwal-card').forEach(c => c.remove());
        semua.forEach(el => setSidebarStatus(el.dataset.id, 'belum'));
        updateCounter();
    }

    // Tampilkan loading
    const btn = document.getElementById('btn-generate');
    btn.disabled  = true;
    btn.innerHTML = `<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Generating...`;

    // ── Baca konfigurasi dari GEN_CONFIG ────────────────────
    const MAX_SKS_DOSEN_PER_HARI    = GEN_CONFIG.maxSksDosen;
    const MAX_KELAS_PER_SLOT        = GEN_CONFIG.maxKelasSlot;
    const MAX_MK_PER_PRODI_PER_HARI = GEN_CONFIG.maxMkProdi;

    // ── Init PRNG dengan seed ────────────────────────────────
    const seed = GEN_CONFIG.seed ?? 42;
    const rng  = makePRNG(seed);

    // Tampilkan seed di badge/toast untuk reproducibility
    console.info(`[Generate] Seed: ${seed} | Config: SKS=${MAX_SKS_DOSEN_PER_HARI}, Slot=${MAX_KELAS_PER_SLOT}, MK=${MAX_MK_PER_PRODI_PER_HARI}`);

    const hariNama   = { 1:'senin', 2:'selasa', 3:'rabu', 4:'kamis', 5:'jumat' };
    const hariListBase = [1, 2, 3, 4, 5];

    // ── STATE C1 ─────────────────────────────────────────────
    const sksDosenPerHari = {};
    hariListBase.forEach(h => { sksDosenPerHari[h] = {}; });

    // ── STATE C2 ─────────────────────────────────────────────
    const ruanganTerpakai = {};
    hariListBase.forEach(h => {
        ruanganTerpakai[h] = {};
        SLOT_VALID.forEach(s => { ruanganTerpakai[h][s] = new Set(); });
    });

    // ── STATE C3 ─────────────────────────────────────────────
    const kelasPerSlot = {};
    hariListBase.forEach(h => {
        kelasPerSlot[h] = {};
        SLOT_VALID.forEach(s => { kelasPerSlot[h][s] = 0; });
    });

    // ── STATE C4 ─────────────────────────────────────────────
    const kelasNamaPerSlot = {};
    hariListBase.forEach(h => {
        kelasNamaPerSlot[h] = {};
        SLOT_VALID.forEach(s => { kelasNamaPerSlot[h][s] = new Map(); });
    });

    // ── STATE C5 ─────────────────────────────────────────────
    const mkPerSlot = {};
    hariListBase.forEach(h => {
        mkPerSlot[h] = {};
        SLOT_VALID.forEach(s => { mkPerSlot[h][s] = new Set(); });
    });

    // ── STATE C6 ─────────────────────────────────────────────
    const mkProdiPerHari = {};
    hariListBase.forEach(h => { mkProdiPerHari[h] = {}; });

    // ── STATE C9 ─────────────────────────────────────────────
    // mkDosenHari[namaMK|dosenId] = { hariId, slotAkhir }
    // Kelas MK+dosen sama WAJIB hari sama, slot tepat berdekatan
    const mkDosenHari = {};

    function getSaudaraInfo(namaMK, dosenId) {
        return mkDosenHari[namaMK + '|' + dosenId] || null;
    }

    function catatMkDosen(namaMK, dosenId, hariId, slotAkhir) {
        const key = namaMK + '|' + dosenId;
        if (!mkDosenHari[key]) {
            mkDosenHari[key] = { hariId, slotAkhir };
        } else {
            mkDosenHari[key].slotAkhir = Math.max(mkDosenHari[key].slotAkhir, slotAkhir);
        }
    }

    // ── HELPERS ───────────────────────────────────────────────
    function dosenBisaMengajar(hariId, dosenId, sks) {
        if (!dosenId) return true;
        return (sksDosenPerHari[hariId][dosenId] || 0) + sks <= MAX_SKS_DOSEN_PER_HARI;
    }

    function cariRuanganBebas(hariId, slotsDibutuhkan, poolRuangan) {
        for (const ruangan of poolRuangan) {
            const rid = String(ruangan.id);
            const bebas = slotsDibutuhkan.every(s => {
                const set = ruanganTerpakai[hariId][s];
                return set && !set.has(rid);
            });
            if (bebas) return ruangan;
        }
        return null;
    }

    function slotMasihBisa(hariId, slotsDibutuhkan) {
        return slotsDibutuhkan.every(s => (kelasPerSlot[hariId][s] || 0) < MAX_KELAS_PER_SLOT);
    }

    function kelasBentrok(hariId, slotsDibutuhkan, namaKelas, namaMK, namaDosenId) {
        return slotsDibutuhkan.some(s => {
            const map = kelasNamaPerSlot[hariId][s];
            if (!map || !map.has(namaKelas)) return false;
            const ex = map.get(namaKelas);
            return ex.namaMK !== namaMK || ex.dosenId !== namaDosenId;
        });
    }

    function mkOverlap(hariId, slotsDibutuhkan, namaMK) {
        return slotsDibutuhkan.some(s => {
            const set = mkPerSlot[hariId][s];
            return set && set.has(namaMK);
        });
    }

    function prodiSudahMaksimal(hariId, prodi, namaMK) {
        const set = mkProdiPerHari[hariId][prodi];
        if (!set) return false;
        if (set.has(namaMK)) return false;
        return set.size >= MAX_MK_PER_PRODI_PER_HARI;
    }

    const JAM_BATAS_MALAM = '16:30';
    function jamKeMenit(jamStr) {
        const [h, m] = jamStr.split(':').map(Number);
        return h * 60 + m;
    }
    const menitBatasMalam = jamKeMenit(JAM_BATAS_MALAM);

    // ── HELPER C8 ─────────────────────────────────────────────
    const SLOT_ISTIRAHAT = 6; // slot istirahat yang tidak boleh dilewati

    function melewatiIstirahat(slotId, sks) {
        const slotsDibutuhkan = Array.from({ length: sks }, (_, i) => slotId + i);
        return slotsDibutuhkan.includes(SLOT_ISTIRAHAT);
    }

    function jenisKelas(namaKelas) { return namaKelas.trim().slice(-1).toUpperCase(); }

    function slotSesuaiJenisKelas(slotId, namaKelas) {
        const info = slotElMap[slotId];
        if (!info || !info.jamMulai || info.jamMulai === '-') return true;
        const menitMulai = jamKeMenit(info.jamMulai);
        const jenis      = jenisKelas(namaKelas);
        if (jenis === 'S') return menitMulai >= menitBatasMalam;
        return menitMulai < menitBatasMalam;
    }

    // ── RANDOMISASI URUTAN KELAS ──────────────────────────────
    // Pertama urutkan SKS terbesar dulu, lalu kocok dengan PRNG
    // agar posisi slot awal berbeda-beda tiap generate
    const kelasUrut = shuffleArray(
        [...semua].sort((a, b) => parseInt(b.dataset.sks) - parseInt(a.dataset.sks)),
        rng
    );

    let berhasil = 0, gagal = 0;

    for (const el of kelasUrut) {
        const kelasId = el.dataset.id;
        const sks     = parseInt(el.dataset.sks) || 2;
        const nama    = el.dataset.nama    || '-';
        const kelas   = el.dataset.kelas   || '-';
        const dosenId = el.dataset.dosenId || '';
        const dosen   = el.dataset.dosen   || '-';
        const kodeMk  = el.dataset.kodeMk  || '-';
        const prodi   = el.dataset.prodi   || '-';
        const jenis   = el.dataset.jenis   || 'Teori';

        const poolRuanganBase = JSON.parse(el.dataset.ruangans || '[]');
        if (!poolRuanganBase.length) {
            console.warn(`[C2] Tidak ada ruangan: ${kelas} – ${nama}`);
            gagal++;
            continue;
        }

        const saudaraInfo  = getSaudaraInfo(nama, dosenId);
        const hariList     = saudaraInfo
            ? [saudaraInfo.hariId]           // wajib hari sama
            : shuffleArray(hariListBase, rng);
        const slotListBase = shuffleArray([...SLOT_VALID], rng);

        let ditempatkan = false;

        luarLoop:
        for (const hariId of hariList) {
            if (!dosenBisaMengajar(hariId, dosenId, sks)) continue;
            if (prodiSudahMaksimal(hariId, prodi, nama)) continue;

            // Jika ada saudara → coba slot tepat setelah saudara selesai
           const slotList = saudaraInfo
                ? [saudaraInfo.slotAkhir]
                : [
                    // Prioritas 1: slot yang TIDAK melewati istirahat
                    ...slotListBase.filter(s => !melewatiIstirahat(s, sks)),
                    // Prioritas 2: slot yang melewati istirahat (fallback, seminimal mungkin)
                    ...slotListBase.filter(s => melewatiIstirahat(s, sks)),
                ];

            for (const slotId of slotList) {
                const slotsDibutuhkan = Array.from({ length: sks }, (_, i) => slotId + i);
                if (slotsDibutuhkan.some(s => !SLOT_VALID.includes(s))) continue;
                if (!slotSesuaiJenisKelas(slotId, kelas)) continue;
                if (kelasBentrok(hariId, slotsDibutuhkan, kelas, nama, dosenId)) continue;
                if (mkOverlap(hariId, slotsDibutuhkan, nama)) continue;
                if (!slotMasihBisa(hariId, slotsDibutuhkan)) continue;

                // Kocok pool ruangan juga agar pilihan tidak monoton
                const poolRuangan = shuffleArray(poolRuanganBase, rng);
                const ruangDipilih = cariRuanganBebas(hariId, slotsDibutuhkan, poolRuangan);
                if (!ruangDipilih) continue;

                const hariNm      = hariNama[hariId];
                const infoMulai   = slotElMap[slotId];
                const infoSelesai = slotElMap[slotId + sks - 1];
                if (!infoMulai) continue;

                // ── TEMPATKAN ────────────────────────────────────
                createCard({
                    sks, nama, kelas,
                    kelasId:    parseInt(kelasId),
                    dosen, kodeMk, prodi,
                    jenis,
                    ruangan:    ruangDipilih.nama,
                    ruanganId:  ruangDipilih.id,
                    slotId,
                    day:        hariNm,
                    jamMulai:   infoMulai.jamMulai,
                    jamSelesai: infoSelesai?.jamSelesai || '-',
                    jadwalIds:  [],
                }, true);

                // Update state
                if (dosenId) sksDosenPerHari[hariId][dosenId] = (sksDosenPerHari[hariId][dosenId] || 0) + sks;
                slotsDibutuhkan.forEach(s => {
                    ruanganTerpakai[hariId][s].add(String(ruangDipilih.id));
                    kelasPerSlot[hariId][s] = (kelasPerSlot[hariId][s] || 0) + 1;
                    kelasNamaPerSlot[hariId][s].set(kelas, { namaMK: nama, dosenId });
                    mkPerSlot[hariId][s].add(nama);
                });
                if (!mkProdiPerHari[hariId][prodi]) mkProdiPerHari[hariId][prodi] = new Set();
                mkProdiPerHari[hariId][prodi].add(nama);
                catatMkDosen(nama, dosenId, hariId, slotId + sks);

                setSidebarStatus(kelasId, 'sudah');
                berhasil++;
                ditempatkan = true;
                break luarLoop;
            }
        }

        // C9 Fallback: jika slot tepat setelah penuh, coba slot lain di hari yang sama
        if (!ditempatkan && saudaraInfo) {
            const hariSama     = saudaraInfo.hariId;
            const slotFallbackBase = shuffleArray([...SLOT_VALID], rng);
            const slotFallback = [
                ...slotFallbackBase.filter(s => !melewatiIstirahat(s, sks)),
                ...slotFallbackBase.filter(s => melewatiIstirahat(s, sks)),
            ];


            for (const slotId of slotFallback) {
                const slotsDibutuhkan = Array.from({ length: sks }, (_, i) => slotId + i);
                if (slotsDibutuhkan.some(s => !SLOT_VALID.includes(s))) continue;
                if (!slotSesuaiJenisKelas(slotId, kelas)) continue;
                if (kelasBentrok(hariSama, slotsDibutuhkan, kelas, nama, dosenId)) continue;
                if (mkOverlap(hariSama, slotsDibutuhkan, nama)) continue;
                if (!slotMasihBisa(hariSama, slotsDibutuhkan)) continue;

                const poolRuangan  = shuffleArray(poolRuanganBase, rng);
                const ruangDipilih = cariRuanganBebas(hariSama, slotsDibutuhkan, poolRuangan);
                if (!ruangDipilih) continue;

                const infoMulai   = slotElMap[slotId];
                const infoSelesai = slotElMap[slotId + sks - 1];
                if (!infoMulai) continue;

                createCard({
                    sks, nama, kelas, kelasId: parseInt(kelasId),
                    dosen, kodeMk, prodi, jenis,
                    ruangan: ruangDipilih.nama, ruanganId: ruangDipilih.id,
                    slotId, day: hariNama[hariSama],
                    jamMulai: infoMulai.jamMulai,
                    jamSelesai: infoSelesai?.jamSelesai || '-',
                    jadwalIds: [],
                }, true);

                if (dosenId) sksDosenPerHari[hariSama][dosenId] = (sksDosenPerHari[hariSama][dosenId] || 0) + sks;
                slotsDibutuhkan.forEach(s => {
                    ruanganTerpakai[hariSama][s].add(String(ruangDipilih.id));
                    kelasPerSlot[hariSama][s] = (kelasPerSlot[hariSama][s] || 0) + 1;
                    kelasNamaPerSlot[hariSama][s].set(kelas, { namaMK: nama, dosenId });
                    mkPerSlot[hariSama][s].add(nama);
                });
                if (!mkProdiPerHari[hariSama][prodi]) mkProdiPerHari[hariSama][prodi] = new Set();
                mkProdiPerHari[hariSama][prodi].add(nama);
                catatMkDosen(nama, dosenId, hariSama, slotId + sks);

                setSidebarStatus(kelasId, 'sudah');
                berhasil++;
                ditempatkan = true;
                break;
            }
        }

        if (!ditempatkan) {
            gagal++;
            console.warn(`[Generate] Gagal: ${kelas} – ${nama} (${sks} SKS)`);
        }
    }

    // Selesai
    btn.disabled  = false;
    btn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Generate Jadwal`;

    updateCounter();
    applyFilter();
    filterCardsByDay(Alpine.$data(document.body).selectedDay || 'senin');
    renderTablePreview();
    updateBentrokButton();

    const seedInfo = GEN_CONFIG.randomize ? ` (seed: ${seed})` : '';
    showToast(
        gagal === 0
            ? `✓ ${berhasil} kelas berhasil dijadwalkan!${seedInfo}`
            : `${berhasil} berhasil, ${gagal} gagal (cek console)${seedInfo}`,
        gagal === 0 ? 'green' : 'red'
    );
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
        slotElMap[id] = {
            jamMulai:   slot.dataset.jamMulai,
            jamSelesai: slot.dataset.jamSelesai,
        };
        SLOT_VALID.push(id);
        slot.addEventListener('dragover', e => e.preventDefault());
        slot.addEventListener('drop', e => {
            e.preventDefault();
            const fromWorkspace = e.dataTransfer.getData('from_workspace');
            const sks     = parseInt(e.dataTransfer.getData('sks'));
            const nama    = e.dataTransfer.getData('nama');
            const kelas   = e.dataTransfer.getData('kelas');
            const kelasId = e.dataTransfer.getData('kelas_id');
            const dosen   = e.dataTransfer.getData('dosen');
            const ruangan = e.dataTransfer.getData('ruangan');
            const kodeMk  = e.dataTransfer.getData('kode_mk') || e.dataTransfer.getData('kodeMk') || '-';
            const prodi   = e.dataTransfer.getData('prodi') || '-';
            const jenis   = e.dataTransfer.getData('jenis') || 'Teori';
            const slotId  = parseInt(slot.dataset.slotLine);
            const day     = Alpine.$data(document.body).selectedDay;

            // Ambil data gabungan dari card asal jika dari workspace
            const kelasList   = fromWorkspace && draggedCard
                ? JSON.parse(draggedCard.dataset.kelasList   || '[]')
                : [kelas];
            const kelasIdList = fromWorkspace && draggedCard
                ? JSON.parse(draggedCard.dataset.kelasIdList || '[]')
                : [kelasId];
            const jadwalIds   = fromWorkspace && draggedCard
                ? JSON.parse(draggedCard.dataset.jadwalIds   || '[]')
                : [];

            if (!nama || !kelas || isNaN(sks) || sks < 1) return;

            // ✅ [C8] Cegah drop ke slot istirahat
            if (slot.dataset.isIstirahat === '1') {
                showToast('⚠ Perhatian: kelas ditempatkan di slot istirahat.', 'red');
                // Tidak return — tetap lanjut
            }

            if (fromWorkspace && draggedCard) {
                hapusJadwal(draggedCard);
                draggedCard.remove();
            }

            const jamMulai    = slot.dataset.jamMulai;
            const jamSelesaiEl = document.querySelector(`[data-slot-line="${slotId + sks - 1}"]`);
            const jamSelesai   = jamSelesaiEl?.dataset.jamSelesai || '-';

            const card = createCard({ sks, nama, kelas, kelasId, dosen, kodeMk, prodi, jenis, ruangan, slotId, day, jamMulai, jamSelesai, jadwalIds });

            // Restore data gabungan jika card berasal dari merge sebelumnya
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

            kelasIdList.forEach(kid => setSidebarStatus(kid, 'sudah'));
            updateCounter();
            applyFilter();
            renderTablePreview();
            updateBentrokButton();
        });
    });
    SLOT_VALID.sort((a, b) => a - b);

    setActiveTab('belum');
    updateCounter();
    filterCardsByDay('senin');
    loadExistingJadwals();
});

// ============================================================
// RESET WORKSPACE
// ============================================================
async function resetWorkspace() {
    const cards = [...document.querySelectorAll('.jadwal-card')];
    if (!cards.length) {
        showToast('Workspace sudah kosong.', 'green');
        return;
    }

    if (!confirm(`Hapus semua ${cards.length} jadwal dari workspace dan database? Tindakan ini tidak bisa dibatalkan.`)) return;

    const btn = document.querySelector('[onclick="resetWorkspace()"]');
    if (btn) { btn.disabled = true; btn.innerText = 'Mereset...'; }

    // Hapus dari database
    let gagal = 0;
    for (const card of cards) {
        try {
            await hapusJadwal(card);
        } catch(e) {
            gagal++;
            console.error('Gagal hapus jadwal:', e);
        }
    }

    // Hapus semua card dari workspace
    document.querySelectorAll('.jadwal-card').forEach(c => c.remove());

    // Reset semua status sidebar ke "belum"
    document.querySelectorAll('.kelas-item').forEach(el => {
        setSidebarStatus(el.dataset.id, 'belum');
    });

    updateCounter();
    applyFilter();
    renderTablePreview();
    updateBentrokButton();
    closeDetailPanel();

    if (btn) {
        btn.disabled = false;
        btn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Reset Jadwal`;
    }

    showToast(
        gagal === 0 ? '✓ Semua jadwal berhasil direset!' : `Reset selesai, ${gagal} gagal dihapus dari DB.`,
        gagal === 0 ? 'green' : 'red'
    );
}

// ============================================================
// DETEKSI BENTROK
// ============================================================
function detectBentrok() {
    const cards = [...document.querySelectorAll('.jadwal-card')];
    const bentrokSet = new Set();

    // Kelompokkan card per hari
    const byHari = {};
    cards.forEach(card => {
        const hari = card.dataset.day;
        if (!byHari[hari]) byHari[hari] = [];
        byHari[hari].push(card);
    });

    Object.values(byHari).forEach(hariCards => {
        for (let i = 0; i < hariCards.length; i++) {
            for (let j = i + 1; j < hariCards.length; j++) {
                const a = hariCards[i];
                const b = hariCards[j];

                const aStart = parseInt(a.dataset.start);
                const aEnd   = parseInt(a.dataset.end);
                const bStart = parseInt(b.dataset.start);
                const bEnd   = parseInt(b.dataset.end);

                // Cek overlap waktu
                const overlapWaktu = aStart < bEnd && aEnd > bStart;
                if (!overlapWaktu) continue;

                // [B1] Ruangan sama
                if (
                    a.dataset.ruanganId &&
                    b.dataset.ruanganId &&
                    a.dataset.ruanganId !== '' &&
                    b.dataset.ruanganId !== '' &&
                    String(a.dataset.ruanganId) === String(b.dataset.ruanganId)
                ) {
                    bentrokSet.add(a);
                    bentrokSet.add(b);
                }

                // [B2] Dosen sama
                if (
                    a.dataset.dosen &&
                    b.dataset.dosen &&
                    a.dataset.dosen !== '-' &&
                    a.dataset.dosen === b.dataset.dosen
                ) {
                    bentrokSet.add(a);
                    bentrokSet.add(b);
                }

                // [B3] Kelas yang sama bentrok di hari berbeda tidak perlu,
                // tapi kelas sama di slot overlap = bentrok
                const aKelas = JSON.parse(a.dataset.kelasIdList || '[]');
                const bKelas = JSON.parse(b.dataset.kelasIdList || '[]');
                const kelasOverlap = aKelas.some(k => bKelas.includes(k));
                if (kelasOverlap) {
                    bentrokSet.add(a);
                    bentrokSet.add(b);
                }
            }
        }
    });

    return [...bentrokSet];
}

// ============================================================
// UPDATE TOMBOL LIHAT BENTROK
// ============================================================
function updateBentrokButton() {
    const bentrokCards = detectBentrok();
    const btn = document.getElementById('btn-lihat-bentrok');
    if (!btn) return;

    if (bentrokCards.length > 0) {
        btn.className = 'px-5 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold flex items-center gap-2 transition animate-pulse border-2 border-red-300';
        btn.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            Bentrok! (${bentrokCards.length} card)`;
    } else {
        btn.className = 'px-5 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl font-medium flex items-center gap-2 transition';
        btn.innerHTML = `
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Lihat Bentrok`;
    }
}

// ============================================================
// MODAL DETAIL BENTROK
// ============================================================
function lihatBentrok() {
    const bentrokCards = detectBentrok();

    if (!bentrokCards.length) {
        showToast('✓ Tidak ada bentrok ditemukan!', 'green');
        return;
    }

    // Buat modal bentrok
    const existing = document.getElementById('modal-bentrok-overlay');
    if (existing) existing.remove();

    // Kelompokkan bentrok per pasangan
    const cards = [...document.querySelectorAll('.jadwal-card')];
    const pasangan = [];
    const byHari = {};
    cards.forEach(card => {
        const hari = card.dataset.day;
        if (!byHari[hari]) byHari[hari] = [];
        byHari[hari].push(card);
    });

    Object.values(byHari).forEach(hariCards => {
        for (let i = 0; i < hariCards.length; i++) {
            for (let j = i + 1; j < hariCards.length; j++) {
                const a = hariCards[i];
                const b = hariCards[j];
                const aStart = parseInt(a.dataset.start);
                const aEnd   = parseInt(a.dataset.end);
                const bStart = parseInt(b.dataset.start);
                const bEnd   = parseInt(b.dataset.end);
                if (!(aStart < bEnd && aEnd > bStart)) continue;

                const alasan = [];
                if (
                    a.dataset.ruanganId && b.dataset.ruanganId &&
                    a.dataset.ruanganId !== '' && b.dataset.ruanganId !== '' &&
                    String(a.dataset.ruanganId) === String(b.dataset.ruanganId)
                ) alasan.push(`Ruangan sama: <strong>${a.dataset.ruangan || '-'}</strong>`);

                if (a.dataset.dosen && a.dataset.dosen !== '-' && a.dataset.dosen === b.dataset.dosen)
                    alasan.push(`Dosen sama: <strong>${a.dataset.dosen}</strong>`);

                const aKelas = JSON.parse(a.dataset.kelasIdList || '[]');
                const bKelas = JSON.parse(b.dataset.kelasIdList || '[]');
                if (aKelas.some(k => bKelas.includes(k)))
                    alasan.push(`Kelas bentrok di slot yang sama`);

                if (alasan.length) pasangan.push({ a, b, alasan });
            }
        }
    });

    const hariLabel = h => h.charAt(0).toUpperCase() + h.slice(1);
    const rows = pasangan.map((p, idx) => `
        <div class="bentrok-row rounded-xl border border-red-100 bg-red-50 p-4 cursor-pointer hover:border-red-300 transition"
             onclick="highlightBentrokPair(${idx})" data-idx="${idx}">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 w-6 h-6 rounded-full bg-red-500 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">${idx + 1}</span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-bold text-gray-800">${p.a.dataset.kelas || p.a.dataset.nama}</span>
                        <span class="text-gray-400">↔</span>
                        <span class="text-sm font-bold text-gray-800">${p.b.dataset.kelas || p.b.dataset.nama}</span>
                    </div>
                    <div class="mt-1 text-xs text-gray-500">
                        ${hariLabel(p.a.dataset.day)} · Slot ${p.a.dataset.start}–${p.a.dataset.end} ↔ Slot ${p.b.dataset.start}–${p.b.dataset.end}
                    </div>
                    <div class="mt-2 space-y-1">
                        ${p.alasan.map(a => `<div class="text-xs text-red-600 flex items-center gap-1">⚠ ${a}</div>`).join('')}
                    </div>
                </div>
            </div>
        </div>
    `).join('');

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
            <div style="flex:1;overflow-y:auto;padding:20px 24px;display:flex;flex-direction:column;gap:12px;">
                ${rows}
            </div>
            <div style="padding:16px 24px;border-top:1px solid #f3f4f6;">
                <button onclick="document.getElementById('modal-bentrok-overlay').remove()"
                        style="width:100%;padding:12px;border-radius:12px;border:1.5px solid #e5e7eb;background:#fff;font-weight:600;font-size:14px;color:#6b7280;cursor:pointer;">
                    Tutup
                </button>
            </div>
        </div>`;

    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.remove(); });
    document.body.appendChild(overlay);

    // Simpan pasangan untuk highlight
    window._bentrokPasangan = pasangan;
}

// ============================================================
// HIGHLIGHT CARD BENTROK DI WORKSPACE
// ============================================================
function highlightBentrokPair(idx) {
    const pair = window._bentrokPasangan?.[idx];
    if (!pair) return;

    // Tutup modal
    document.getElementById('modal-bentrok-overlay')?.remove();

    // Pindah ke hari yang benar
    const targetDay = pair.a.dataset.day;
    Alpine.$data(document.body).selectedDay = targetDay;
    filterCardsByDay(targetDay);

    // Hapus highlight lama
    document.querySelectorAll('.jadwal-card').forEach(c => {
        c.style.outline = '';
        c.style.zIndex  = '10';
    });

    // Sorot kedua card
    [pair.a, pair.b].forEach(card => {
        card.style.outline = '3px solid #ef4444';
        card.style.zIndex  = '50';
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    // Hilangkan highlight setelah 3 detik
    setTimeout(() => {
        [pair.a, pair.b].forEach(card => {
            card.style.outline = '';
            card.style.zIndex  = '10';
        });
    }, 3000);
}
</script>

</body>
</html>
