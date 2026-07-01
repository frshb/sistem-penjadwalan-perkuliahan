<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Manajemen Pengampu</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .dosen-dropzone.drag-over {
            background-color: #f0fdf4 !important;
            border: 2px dashed #22c55e !important;
        }
        .kelas-item { cursor: grab; user-select: none; }
        .kelas-item:active { cursor: grabbing; }
        .kelas-item.dragging { opacity: 0.4; }
        #filter-popup { display: none; }
        #filter-popup.open { display: block; }
        #filter-popup-dosen { display: none; }
        #filter-popup-dosen.open { display: block; }
    </style>
</head>

<body
    x-data="{ 
@php $mode = request('mode', 'workspace'); @endphp

        sidebarOpen: true, 
        kelasSidebarOpen: true, 
        detailSidebarOpen: false,
        selectedKelas: null,
        focusMode: false,
        searchKelas: '',
        filterProdi: [],
        filterSemester: [],
        filterMatkul: '',
        showToast: false,
        toastMessage: '',
        toastType: 'success'
    }"
    @set-selected-kelas.window="selectedKelas = $event.detail; detailSidebarOpen = true"
    class="bg-gray-100/50 min-h-screen overflow-x-hidden"
>

@include('components.sidebar')

<script>
    window.__MATKULS__   = @json($matkulsJs);
    window.__PENGAMPUS__ = @json($pengampusJs);
    var ID_TAHUNAKADEMIK = {{ $tahunAkademik->id_tahunakademik }};
</script>

<main :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'" class="transition-all duration-300 p-6 sm:p-8">

    {{-- HEADER --}}
    <div class="flex items-center justify-between" x-show="!focusMode">
        <div>
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="flex flex-col hover:opacity-80 transition cursor-pointer" title="Toggle Sidebar">
                    <div class="w-2 h-5 bg-teal-600 rounded-tl-md"></div>
                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                </button>
                <h1 class="text-3xl font-bold text-gray-800">Manajemen Pengampu</h1>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mt-2">
                <a href="{{ route('dosen-pengampu.pilih-tahun') }}" class="hover:text-teal-600 transition font-medium">Manajemen Pengampu</a>
                <span>/</span>
                <span class="text-teal-600 font-semibold">{{ $tahunAkademik->nama_tahunakademik }}</span>
            </div>
        </div>
        @include('components.header-profile')
    </div>

    {{-- ACTION BAR --}}
    <div class="mt-8 flex items-center justify-between" :class="focusMode ? 'mt-0 bg-white p-3 rounded-2xl border border-gray-150 shadow-sm' : ''">
        <div>
            <span x-show="focusMode" class="text-sm font-bold text-teal-800 bg-teal-50 px-3.5 py-2 rounded-xl border border-teal-200 inline-flex items-center gap-2">
                Periode: {{ $tahunAkademik->nama_tahunakademik }}
            </span>
            <div class="flex items-center gap-2" x-show="!focusMode">
                <a
                    href="?tahun={{ $tahunAkademik->id_tahunakademik }}&mode=workspace"
                    class="px-4 py-2.5 rounded-xl text-sm font-semibold border transition flex items-center gap-2 {{ $mode !== 'table' ? 'bg-teal-600 border-teal-600 text-white shadow-sm shadow-teal-100' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50' }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Workspace Mode
                </a>
                <a
                    href="?tahun={{ $tahunAkademik->id_tahunakademik }}&mode=table"
                    class="px-4 py-2.5 rounded-xl text-sm font-semibold border transition flex items-center gap-2 {{ $mode === 'table' ? 'bg-teal-600 border-teal-600 text-white shadow-sm shadow-teal-100' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50' }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Table Mode
                </a>
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            @if($mode !== 'table')
            <button
                @click="kelasSidebarOpen = !kelasSidebarOpen"
                :class="kelasSidebarOpen ? 'bg-teal-50 border-teal-200 text-teal-700' : 'bg-white border-gray-200 text-gray-700'"
                class="px-4 py-2.5 border rounded-xl font-semibold flex items-center gap-2 transition text-sm shadow-sm"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                <span x-text="kelasSidebarOpen ? 'Sembunyikan Kelas' : 'Tampilkan Kelas'"></span>
            </button>
            @endif
            <button
                @click="focusMode = !focusMode; sidebarOpen = !focusMode; kelasSidebarOpen = !focusMode"
                :class="focusMode ? 'bg-gray-800 border-transparent text-white' : 'bg-white border-gray-200 text-gray-700'"
                class="px-4 py-2.5 border rounded-xl font-semibold flex items-center gap-2 transition text-sm shadow-sm"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="focusMode" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!focusMode"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l-5-5m11 5v-4m0 4h-4m4 0l-5-5"/></svg>
                <span x-text="focusMode ? 'Normal View' : 'Workspace Fokus'"></span>
            </button>
        </div>
    </div>

    @if($mode !== 'table')
    {{-- CONTENT GRID --}}
    <div
        :class="focusMode ? 'h-[calc(100vh-90px)] mt-4 gap-4' : 'h-[calc(100vh-180px)] mt-6 gap-5'"
        class="grid grid-cols-12 overflow-hidden transition-all duration-300"
    >

        {{-- ════════════ SIDEBAR MATA KULIAH ════════════ --}}
        <div x-show="kelasSidebarOpen" class="col-span-12 xl:col-span-3 min-h-0 flex relative z-30">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 h-full flex flex-col w-full overflow-visible">

                {{-- Header --}}
                <div class="p-4 border-b border-gray-100 rounded-t-2xl bg-white relative z-30">
                    <h2 class="text-lg font-bold text-gray-900 mb-3">Daftar Kelas Paralel</h2>

                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <input
                                type="text"
                                id="search-matkul"
                                placeholder="Cari kelas / mata kuliah..."
                                class="w-full pl-4 pr-9 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-teal-500 outline-none text-base"
                                oninput="applyMatkulFilter()"
                            >
                            <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                        </div>
                        {{-- Filter Toggle Button --}}
                        <div class="relative">
                            <button
                                id="btn-filter-matkul"
                                onclick="toggleFilterMatkul(event)"
                                class="w-10 h-10 flex items-center justify-center border-2 border-gray-300 rounded-xl hover:border-teal-500 hover:bg-teal-50 transition text-gray-500 hover:text-teal-700"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M10 18h4"/></svg>
                            </button>
                            {{-- Filter Popup --}}
                            <div id="filter-popup" class="absolute right-0 top-12 w-72 max-w-[85vw] max-h-[70vh] overflow-y-auto bg-white border border-gray-200 rounded-2xl shadow-2xl z-[100] p-4 space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-gray-800 text-sm">Filter Kelas</span>
                                    <button onclick="resetMatkulFilter()" class="text-xs text-teal-600 hover:text-teal-800 font-semibold">Reset</button>
                                </div>

                                {{-- Kurikulum --}}
                                <div>
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Kurikulum</p>
                                    <div class="space-y-1.5">
                                        @foreach($kurikulums as $k)
                                        <label class="flex items-center gap-2 cursor-pointer group">
                                            <input type="checkbox" class="filter-kurikulum w-4 h-4 rounded text-teal-600 border-gray-300 focus:ring-teal-500" value="{{ $k->id_kurikulum }}" onchange="applyMatkulFilter()">
                                            <span class="text-sm text-gray-700 group-hover:text-teal-700">{{ $k->nama_kurikulum }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Program Studi --}}
                                @if(!Auth::user() || !Auth::user()->isKaprodi())
                                <div>
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Program Studi</p>
                                    <div class="space-y-1.5">
                                        @foreach($prodis as $p)
                                        <label class="flex items-center gap-2 cursor-pointer group">
                                            <input type="checkbox" class="filter-prodi-matkul w-4 h-4 rounded text-teal-600 border-gray-300 focus:ring-teal-500" value="{{ $p->id_prodi }}" onchange="applyMatkulFilter()">
                                            <span class="text-sm text-gray-700 group-hover:text-teal-700">{{ $p->nama_prodi }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                {{-- Semester --}}
                                <div>
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Semester</p>
                                    <div class="grid grid-cols-2 gap-1.5">
                                        @php
                                            $keterangan = strtolower($tahunAkademik->nama_tahunakademik ?? '');
                                            $isGenap = (strpos($keterangan, 'genap') !== false || strpos($keterangan, 'even') !== false);
                                            $semesters = $isGenap ? [2, 4, 6, 8] : [1, 3, 5, 7];
                                        @endphp
                                        @foreach($semesters as $sem)
                                        <label class="flex items-center gap-2 cursor-pointer group">
                                            <input type="checkbox" class="filter-semester w-4 h-4 rounded text-teal-600 border-gray-300 focus:ring-teal-500" value="{{ $sem }}" onchange="applyMatkulFilter()">
                                            <span class="text-sm text-gray-700 group-hover:text-teal-700">Semester {{ $sem }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Nama Kelas --}}
                                <div>
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Kelas</p>
                                    <div class="max-h-40 overflow-y-auto space-y-1.5 pr-1 border border-gray-100 p-2 rounded-lg bg-gray-50/50">
                                        @php
                                            $uniqueNamaKelas = $kelasList->pluck('nama_kelas')->unique()->sort();
                                        @endphp
                                        @foreach($uniqueNamaKelas as $nk)
                                        <label class="flex items-center gap-2 cursor-pointer group">
                                            <input type="checkbox" class="filter-nama-kelas w-4 h-4 rounded text-teal-600 border-gray-300 focus:ring-teal-500" value="{{ $nk }}" onchange="applyMatkulFilter()">
                                            <span class="text-sm text-gray-700 group-hover:text-teal-700">{{ $nk }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <p id="matkul-counter" class="text-xs text-gray-400 border-t pt-2"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab Selector -->
                    <div class="mt-4 flex items-center gap-4 text-xs font-bold uppercase tracking-wider border-t border-gray-100 pt-3">
                        <button id="tab-belum" onclick="setActivePlotTab('belum')" class="pb-2 border-b-2 border-teal-500 text-teal-600 flex items-center gap-1.5 transition cursor-pointer">
                            Belum Terplot
                        </button>
                        <button id="tab-terplot" onclick="setActivePlotTab('terplot')" class="pb-2 text-gray-500 hover:text-teal-600 flex items-center gap-1.5 transition cursor-pointer">
                            Terplot
                        </button>
                        <button id="tab-semua" onclick="setActivePlotTab('all')" class="pb-2 text-gray-500 hover:text-teal-600 flex items-center gap-1.5 transition cursor-pointer">
                            Semua
                        </button>
                    </div>
                </div>

                {{-- List --}}
                <div class="flex-1 overflow-y-auto min-h-0 px-3 py-3 rounded-b-2xl" id="matkul-list-container">
                    @foreach($kelasList as $item)
                    <div
                        id="kelas-{{ $item->id_kelas }}"
                        onclick="window.showKelasDetail('{{ $item->id_kelas }}')"
                        :class="selectedKelas && selectedKelas.id == '{{ $item->id_kelas }}' ? 'border-teal-500 bg-teal-50/30 shadow-md scale-[1.01]' : 'border-gray-200 bg-white hover:border-teal-400 hover:shadow-lg'"
                        class="kelas-item border-2 rounded-2xl p-5 mx-3 my-3 transition-all duration-200 select-none cursor-pointer"
                        draggable="true"
                        data-id="{{ $item->id_kelas }}"
                        data-nama="{{ strtolower($item->nama_kelas) }}"
                        data-nama-display="{{ $item->nama_kelas }}"
                        data-sks="{{ $item->matakuliah->sks ?? 0 }}"
                        data-prodi="{{ $item->prodi->nama_prodi ?? '-' }}"
                        data-id-prodi="{{ $item->id_prodi }}"
                        data-semester="{{ $item->semester }}"
                        data-kode-matkul="{{ $item->matakuliah->kode_matkul ?? '' }}"
                        data-nama-matkul="{{ $item->matakuliah->nama_matkul ?? '-' }}"
                        data-jumlah-mahasiswa="{{ $item->jumlah_mahasiswa }}"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h3 class="font-bold text-gray-900 text-xl leading-snug">{{ $item->nama_kelas }}</h3>
                                <p class="text-base text-gray-700 mt-2 font-semibold">{{ $item->matakuliah->nama_matkul ?? '-' }}</p>
                                <p class="text-sm text-gray-500 mt-1.5">{{ $item->prodi->nama_prodi ?? '-' }}</p>
                                <div class="flex flex-wrap items-center gap-2 mt-3">
                                    <span class="text-sm px-3 py-1.5 bg-teal-50 text-teal-700 rounded-lg font-mono font-bold">{{ $item->matakuliah->kode_matkul ?? '-' }}</span>
                                    <span class="text-sm px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg font-semibold">{{ $item->matakuliah->sks ?? 0 }} SKS</span>
                                    <span class="text-sm px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg font-semibold">Semester {{ $item->semester }}</span>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ════════════ WORKSPACE DOSEN ════════════ --}}
        <div
            :class="kelasSidebarOpen && detailSidebarOpen ? 'xl:col-span-6' : ((kelasSidebarOpen || detailSidebarOpen) ? 'xl:col-span-9' : 'xl:col-span-12')"
            class="col-span-12 flex flex-col min-h-0 transition-all duration-300 relative z-10"
        >
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 w-full flex flex-col overflow-hidden h-full">

                {{-- Workspace Header --}}
                <div class="p-4 border-b border-gray-100">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-base font-bold text-gray-800 mr-auto">Workspace Penugasan Dosen</h2>

                        {{-- Search Dosen --}}
                        <div class="relative">
                            <input
                                type="text"
                                id="search-dosen"
                                oninput="applyDosenFilter()"
                                placeholder="Cari dosen..."
                                class="pl-4 pr-9 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-teal-500 outline-none text-sm w-52"
                            >
                            <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                        </div>

                        {{-- Filter Dosen Button --}}
                        <div class="relative">
                            <button
                                id="btn-filter-dosen"
                                onclick="toggleFilterDosen(event)"
                                class="w-10 h-10 flex items-center justify-center border-2 border-gray-300 rounded-xl hover:border-teal-500 hover:bg-teal-50 transition text-gray-500 hover:text-teal-700"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M10 18h4"/></svg>
                            </button>
                            {{-- Filter Popup Dosen --}}
                            <div id="filter-popup-dosen" class="absolute right-0 top-12 w-64 max-w-[85vw] max-h-[70vh] overflow-y-auto bg-white border border-gray-200 rounded-2xl shadow-2xl z-[100] p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-gray-800 text-sm">Filter Dosen</span>
                                    <button onclick="resetDosenFilter()" class="text-xs text-teal-600 hover:text-teal-800 font-semibold">Reset</button>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Program Studi</p>
                                    <div class="space-y-1.5">
                                        @foreach($prodis as $p)
                                        <label class="flex items-center gap-2 cursor-pointer group">
                                            <input type="checkbox" class="filter-prodi-dosen w-4 h-4 rounded text-teal-600 border-gray-300 focus:ring-teal-500" value="{{ $p->id_prodi }}" onchange="applyDosenFilter()">
                                            <span class="text-sm text-gray-700 group-hover:text-teal-700">{{ $p->nama_prodi }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dosen Grid --}}
                <div class="flex-1 overflow-auto p-4 bg-gray-50/50">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4" id="dosen-grid">
                        @foreach($dosens as $dosen)
                        <div
                            class="dosen-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col h-[340px]"
                            data-dosen-id="{{ $dosen->id_dosen }}"
                            data-dosen-nama="{{ strtolower($dosen->nama_dosen) }}"
                            data-dosen-prodi="{{ $dosen->id_prodi }}"
                        >
                            <div class="bg-teal-700 px-4 py-3 flex items-center justify-between gap-2 flex-shrink-0">
                                <div class="min-w-0">
                                    <h3 class="font-bold text-white text-sm truncate" title="{{ $dosen->nama_dosen }}">{{ $dosen->nama_dosen }}</h3>
                                    <p class="text-teal-200 text-xs truncate">{{ $dosen->prodi->nama_prodi ?? '-' }}</p>
                                </div>
                                <span class="total-sks bg-white text-teal-800 text-xs font-bold px-2 py-1 rounded-md flex-shrink-0 whitespace-nowrap">0 SKS</span>
                            </div>
                            <div
                                class="dosen-dropzone flex-1 overflow-y-auto p-2 space-y-1.5 min-h-0"
                                data-dosen-id="{{ $dosen->id_dosen }}"
                            >
                                <div class="empty-hint h-full flex flex-col items-center justify-center text-gray-300 pointer-events-none select-none">
                                    <svg class="w-7 h-7 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                                    <p class="text-xs">Seret mata kuliah ke sini</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ════════════ SIDEBAR DETAIL KELAS ════════════ --}}
        <div
            x-show="detailSidebarOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            class="col-span-12 xl:col-span-3 min-h-0 flex relative z-30"
        >
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 h-full flex flex-col w-full overflow-hidden">
                {{-- Header --}}
                <div class="p-4 border-b border-gray-100 bg-white flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">Detail Kelas</h2>
                    <button
                        @click="detailSidebarOpen = false; selectedKelas = null"
                        class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition"
                        title="Tutup detail"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Detail Content --}}
                <div class="flex-1 overflow-y-auto p-5 space-y-6">
                    <template x-if="!selectedKelas">
                        <div class="h-full flex flex-col items-center justify-center text-center p-4">
                            <div class="w-16 h-16 bg-teal-50 rounded-2xl flex items-center justify-center text-teal-600 mb-4 shadow-inner">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h3 class="font-bold text-gray-700 text-sm">Belum Ada Kelas Terpilih</h3>
                            <p class="text-xs text-gray-400 mt-2 max-w-[200px] leading-relaxed">
                                Klik salah satu kelas di daftar sebelah kiri atau kelas yang sudah terplot untuk melihat informasi detail di sini.
                            </p>
                        </div>
                    </template>

                    <template x-if="selectedKelas">
                        <div class="space-y-6">
                            <!-- Header Info -->
                            <div class="bg-gradient-to-br from-teal-50 to-teal-100/50 p-4.5 rounded-2xl border border-teal-100">
                                <span class="text-[10px] font-bold tracking-wider uppercase bg-teal-200 text-teal-800 px-2 py-0.5 rounded">Kelas Paralel</span>
                                <h3 class="text-2xl font-black text-teal-950 mt-2 leading-none" x-text="selectedKelas.nama"></h3>
                                <p class="text-sm font-semibold text-teal-800 mt-2 leading-relaxed" x-text="selectedKelas.matkul"></p>
                            </div>

                            <!-- Detail List -->
                            <div class="space-y-4">
                                <div class="flex flex-col pb-3.5 border-b border-gray-100">
                                    <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Kode Mata Kuliah</span>
                                    <span class="text-sm font-mono font-bold text-gray-800 mt-1" x-text="selectedKelas.kode"></span>
                                </div>

                                <div class="grid grid-cols-2 gap-4 pb-3.5 border-b border-gray-100">
                                    <div class="flex flex-col">
                                        <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Bobot SKS</span>
                                        <span class="text-sm font-bold text-gray-800 mt-1" x-text="selectedKelas.sks + ' SKS'"></span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Semester</span>
                                        <span class="text-sm font-bold text-gray-800 mt-1" x-text="'Semester ' + selectedKelas.semester"></span>
                                    </div>
                                </div>

                                <div class="flex flex-col pb-3.5 border-b border-gray-100">
                                    <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Program Studi</span>
                                    <span class="text-sm font-semibold text-gray-800 mt-1" x-text="selectedKelas.prodi"></span>
                                </div>

                                <div class="flex flex-col pb-3.5 border-b border-gray-100">
                                    <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Jumlah Mahasiswa</span>
                                    <div class="flex items-center gap-2 mt-1">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <span class="text-sm font-bold text-gray-800" x-text="selectedKelas.jml_mahasiswa + ' Mahasiswa'"></span>
                                    </div>
                                </div>

                                <div class="flex flex-col pb-1">
                                    <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Dosen Pengampu</span>
                                    <div class="mt-2.5 flex items-center gap-2.5 p-3 rounded-xl border border-gray-150 bg-gray-50/50">
                                        <div class="w-8 h-8 rounded-full bg-teal-700 text-white flex items-center justify-center font-bold text-xs" x-text="(selectedKelas.dosen && selectedKelas.dosen !== '-') ? selectedKelas.dosen.charAt(0) : '?'"></div>
                                        <div class="min-w-0 flex-1">
                                            <span class="block text-sm font-bold text-gray-800 truncate" x-text="selectedKelas.dosen || 'Belum ditugaskan'"></span>
                                            <span class="block text-[10px] text-gray-400 mt-0.5 font-medium" x-text="(selectedKelas.dosen && selectedKelas.dosen !== '-') ? 'Sudah Terplot' : 'Belum Terplot'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($mode === 'table')
        <div class="mt-6">
            @include('dosen-pengampu.table')
        </div>
    @endif
</main>



<script>
(function () {
    const CSRF      = document.querySelector('meta[name="csrf-token"]').content;
    const MATKULS   = window.__MATKULS__;
    const PENGAMPUS = window.__PENGAMPUS__;
    const HAS_EDIT_PERMISSION = @json(Auth::user()->hasPermissionAccess('management_data', 'Dosen Pengampu', 'edit'));

    // ── Init: render assigned cards dari DB ──────────────────────
    function init() {
        PENGAMPUS.forEach(function(p) {
            var zone = document.querySelector('.dosen-dropzone[data-dosen-id="' + p.id_dosen + '"]');
            if (!zone) return;
            zone.appendChild(buildCard(
                p.id_kelas,
                p.nama_kelas,
                p.nama_matkul,
                p.sks,
                p.nama_prodi,
                String(p.id_dosen),
                p.semester,
                p.kode_matkul,
                p.id
            ));
        });
        document.querySelectorAll('.dosen-card').forEach(updateSks);
        updateKelasStats();
    }

    // ── Build assigned card ──────────────────────────────────────
    function buildCard(idKelas, namaKelas, namaMatkul, sks, namaProdi, dosenId, semester, kodeMatkul, pengampuId) {
        var div = document.createElement('div');
        div.className = 'assigned-card bg-white border border-gray-200 rounded-lg p-2.5 flex items-start justify-between gap-2 group shadow-sm hover:border-teal-300 transition-all cursor-grab active:cursor-grabbing';
        div.dataset.kelasId  = idKelas;
        div.dataset.namaKelas  = namaKelas;
        div.dataset.namaMatkul = namaMatkul;
        div.dataset.sks      = sks;
        div.dataset.dosenId  = dosenId;
        div.dataset.pengampuId = pengampuId;
        div.dataset.namaProdi  = namaProdi;
        div.dataset.semester   = semester;
        div.dataset.kodeMatkul = kodeMatkul;

        if (HAS_EDIT_PERMISSION) {
            div.setAttribute('draggable', 'true');
        } else {
            div.setAttribute('draggable', 'false');
        }

        div.addEventListener('dragstart', function(e) {
            if (!HAS_EDIT_PERMISSION) {
                e.preventDefault();
                return;
            }
            div.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'copy';
            
            var dragData = {
                id_kelas: idKelas,
                nama_kelas: namaKelas,
                nama_matkul: namaMatkul,
                sks: sks,
                nama_prodi: namaProdi,
                semester: semester,
                kode_matkul: kodeMatkul,
                is_reassign: true,
                old_dosen_id: dosenId,
                pengampu_id: pengampuId
            };
            e.dataTransfer.setData('text/plain', JSON.stringify(dragData));
        });

        div.addEventListener('dragend', function() {
            div.classList.remove('dragging');
        });
        
        div.innerHTML =
            '<div class="min-w-0 flex-1 cursor-pointer" onclick="window.showKelasDetail(\'' + idKelas + '\')">' +
                '<p class="font-bold text-gray-800 text-xs leading-tight truncate" title="' + namaKelas + ' — ' + namaMatkul + '">' +
                    namaKelas + ' — ' + namaMatkul +
                '</p>' +
                '<p class="text-[10px] text-gray-500 truncate mt-0.5">' + namaProdi + '</p>' +
                '<div class="flex flex-wrap items-center gap-1.5 mt-1">' +
                    '<span class="text-[10px] font-mono px-1.5 py-0.5 bg-teal-50 text-teal-700 rounded">' + kodeMatkul + '</span>' +
                    '<span class="text-[10px] font-semibold px-1.5 py-0.5 bg-blue-50 text-blue-600 rounded">' + sks + ' SKS</span>' +
                    '<span class="text-[10px] px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded">Smt ' + (semester || '-') + '</span>' +
                '</div>' +
            '</div>' +
            (HAS_EDIT_PERMISSION ? 
            '<button onclick="hapusPengampu(\'' + pengampuId + '\',this)" ' +
                'class="flex-shrink-0 text-gray-300 hover:text-red-500 hover:bg-red-50 p-1 rounded transition opacity-0 group-hover:opacity-100" ' +
                'title="Lepaskan">' +
                '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>' +
                '</svg>' +
            '</button>' : '');
        return div;
    }

    function updateKelasStats() {
        var assignedIds = {};
        document.querySelectorAll('.assigned-card').forEach(function(card) {
            assignedIds[card.dataset.kelasId] = true;
        });

        var total = 0;
        var terplot = 0;
        var belum = 0;

        document.querySelectorAll('.kelas-item').forEach(function(item) {
            total++;
            var idKelas = item.dataset.id;
            var isAssigned = !!assignedIds[idKelas];
            
            item.dataset.assigned = isAssigned ? '1' : '0';
            
            // Mark class item visually
            if (isAssigned) {
                item.classList.add('is-assigned');
                item.setAttribute('draggable', 'false');
                terplot++;
                
                // Add checkmark badge if not already present
                var checkBadge = item.querySelector('.chip-terplot');
                if (!checkBadge) {
                    var titleEl = item.querySelector('h3');
                    if (titleEl) {
                        var badge = document.createElement('span');
                        badge.className = 'text-[10px] font-semibold px-1.5 py-0.5 rounded chip-terplot flex-shrink-0 ml-2 bg-teal-50 text-teal-700 border border-teal-200';
                        badge.textContent = '✓ Terplot';
                        titleEl.appendChild(badge);
                    }
                }
            } else {
                item.classList.remove('is-assigned');
                if (HAS_EDIT_PERMISSION) {
                    item.setAttribute('draggable', 'true');
                } else {
                    item.setAttribute('draggable', 'false');
                }
                belum++;
                
                // Remove checkmark badge
                var checkBadge = item.querySelector('.chip-terplot');
                if (checkBadge) checkBadge.remove();
            }
        });

        // Update stats counters
        var cntBelum = document.getElementById('cnt-belum');
        if (cntBelum) cntBelum.textContent = belum;
        
        var cntTerplot = document.getElementById('cnt-terplot');
        if (cntTerplot) cntTerplot.textContent = terplot;

        var cntTotal = document.getElementById('cnt-total');
        if (cntTotal) cntTotal.textContent = total;
        
        var counter = document.getElementById('matkul-counter');
        if (counter) counter.textContent = total + ' kelas paralel';

        // Trigger filter to update the tab display
        if (typeof applyMatkulFilter === 'function') {
            applyMatkulFilter();
        }

        if (window.currentSelectedKelasId) {
            window.showKelasDetail(window.currentSelectedKelasId);
        }
    }

    // ── Update SKS badge & empty hint ───────────────────────────
    function updateSks(dosenCard) {
        var total = 0;
        dosenCard.querySelectorAll('.assigned-card').forEach(function(c) {
            total += parseInt(c.dataset.sks) || 0;
        });
        var badge = dosenCard.querySelector('.total-sks');
        badge.textContent = total + ' SKS';
        if (total > 12) {
            badge.style.backgroundColor = '#fee2e2';
            badge.style.color = '#b91c1c';
        } else {
            badge.style.backgroundColor = '#ffffff';
            badge.style.color = '#134e4a';
        }
        var hint = dosenCard.querySelector('.empty-hint');
        if (hint) {
            hint.style.display = dosenCard.querySelectorAll('.assigned-card').length === 0 ? 'flex' : 'none';
        }
    }

    // ── Drag & Drop ──────────────────────────────────────────────
    document.querySelectorAll('.kelas-item').forEach(function(item) {
        item.addEventListener('dragstart', function(e) {
            if (!HAS_EDIT_PERMISSION) {
                e.preventDefault();
                return;
            }
            item.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'copy';
            
            var dragData = {
                id_kelas: item.dataset.id,
                nama_kelas: item.dataset.namaDisplay,
                nama_matkul: item.dataset.namaMatkul,
                sks: item.dataset.sks,
                nama_prodi: item.dataset.prodi,
                semester: item.dataset.semester,
                kode_matkul: item.dataset.kodeMatkul,
                jumlah_mahasiswa: item.dataset.jumlahMahasiswa || 0
            };
            e.dataTransfer.setData('text/plain', JSON.stringify(dragData));
        });
        item.addEventListener('dragend', function() {
            item.classList.remove('dragging');
        });
    });

    document.querySelectorAll('.dosen-dropzone').forEach(function(zone) {
        zone.addEventListener('dragover', function(e) {
            e.preventDefault();
            zone.classList.add('drag-over');
        });
        zone.addEventListener('dragleave', function(e) {
            if (!zone.contains(e.relatedTarget)) zone.classList.remove('drag-over');
        });
        zone.addEventListener('drop', function(e) {
            if (!HAS_EDIT_PERMISSION) return;
            e.preventDefault();
            zone.classList.remove('drag-over');
            var rawData = e.dataTransfer.getData('text/plain');
            var dosenId = zone.dataset.dosenId;
            if (!rawData || !dosenId) return;
            
            try {
                var dragData = JSON.parse(rawData);
                simpan(dragData, dosenId, zone);
            } catch (err) {
                console.error('Drop error:', err);
            }
        });
    });

    // ── Simpan AJAX ──────────────────────────────────────────────
    function simpan(dragData, dosenId, zone) {
        if (!dragData.id_kelas) return;

        fetch('{{ route("dosen-pengampu.simpan", [], false) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({
                id_kelas: dragData.id_kelas,
                id_dosen: dosenId,
                id_tahunakademik: ID_TAHUNAKADEMIK
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) { alert(data.message || 'Gagal menyimpan.'); return; }

            if (dragData.is_reassign) {
                document.querySelectorAll('.assigned-card[data-kelas-id="' + dragData.id_kelas + '"]').forEach(function(oldCard) {
                    var oldDosenCard = oldCard.closest('.dosen-card');
                    oldCard.remove();
                    if (oldDosenCard) {
                        updateSks(oldDosenCard);
                    }
                });
            }

            zone.appendChild(buildCard(
                dragData.id_kelas,
                dragData.nama_kelas,
                dragData.nama_matkul,
                dragData.sks,
                dragData.nama_prodi,
                String(dosenId),
                dragData.semester,
                dragData.kode_matkul,
                data.id
            ));
            updateSks(zone.closest('.dosen-card'));
            updateKelasStats();
        })
        .catch(function(err) {
            console.error('Simpan error:', err);
            alert('Terjadi kesalahan jaringan.');
        });
    }

    // ── Hapus pengampu ───────────────────────────────────────────
    window.hapusPengampu = function(pengampuId, btn) {
        var card     = btn.closest('.assigned-card');
        var dosenCard = btn.closest('.dosen-card');
        card.style.opacity = '0.4';
        btn.disabled = true;

        fetch('{{ route("dosen-pengampu.hapus", [], false) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ id: pengampuId })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) { card.style.opacity = '1'; btn.disabled = false; alert(data.message || 'Gagal.'); return; }
            card.remove();
            updateSks(dosenCard);
            updateKelasStats();
        })
        .catch(function(err) {
            card.style.opacity = '1';
            btn.disabled = false;
            console.error('Hapus error:', err);
            alert('Terjadi kesalahan jaringan.');
        });
    };

    // ── Detail Kelas ─────────────────────────────────────────────
    window.currentSelectedKelasId = null;
    window.showKelasDetail = function(kelasId) {
        var el = document.getElementById('kelas-' + kelasId);
        if (!el) return;
        
        window.currentSelectedKelasId = kelasId;
        
        // Find the current assigned dosen name if any
        var dosenNama = '-';
        var card = document.querySelector('.assigned-card[data-kelas-id="' + kelasId + '"]');
        if (card) {
            var dosenCard = card.closest('.dosen-card');
            if (dosenCard) {
                var h3El = dosenCard.querySelector('h3');
                if (h3El) dosenNama = h3El.textContent.trim();
            }
        }

        var payload = {
            id: kelasId,
            nama: el.dataset.namaDisplay || '',
            matkul: el.dataset.namaMatkul || '',
            kode: el.dataset.kodeMatkul || '',
            sks: el.dataset.sks || '0',
            prodi: el.dataset.prodi || '',
            semester: el.dataset.semester || '',
            dosen: dosenNama,
            jml_mahasiswa: el.dataset.jumlahMahasiswa || 0
        };

        // Dispatch event to update Alpine component data
        var event = new CustomEvent('set-selected-kelas', { detail: payload });
        document.body.dispatchEvent(event);
    };

    // ── Filter Kelas / Matkul (pure JS, no Alpine) ───────────────
    window.activePlotFilter = 'belum';

    window.setActivePlotTab = function(status) {
        window.activePlotFilter = status;
        
        var tabBelum = document.getElementById('tab-belum');
        var tabTerplot = document.getElementById('tab-terplot');
        var tabSemua = document.getElementById('tab-semua');
        
        if (tabBelum) tabBelum.className = 'pb-2 text-gray-500 hover:text-teal-600 flex items-center gap-1.5 transition cursor-pointer';
        if (tabTerplot) tabTerplot.className = 'pb-2 text-gray-500 hover:text-teal-600 flex items-center gap-1.5 transition cursor-pointer';
        if (tabSemua) tabSemua.className = 'pb-2 text-gray-500 hover:text-teal-600 flex items-center gap-1.5 transition cursor-pointer';
        
        if (status === 'belum') {
            if (tabBelum) tabBelum.className = 'pb-2 border-b-2 border-teal-500 text-teal-600 flex items-center gap-1.5 transition cursor-pointer';
        } else if (status === 'terplot') {
            if (tabTerplot) tabTerplot.className = 'pb-2 border-b-2 border-teal-500 text-teal-600 flex items-center gap-1.5 transition cursor-pointer';
        } else {
            if (tabSemua) tabSemua.className = 'pb-2 border-b-2 border-teal-500 text-teal-600 flex items-center gap-1.5 transition cursor-pointer';
        }
        
        applyMatkulFilter();
    };

    window.applyMatkulFilter = function() {
        var search    = (document.getElementById('search-matkul').value || '').toLowerCase();
        var kurikulums = Array.from(document.querySelectorAll('.filter-kurikulum:checked')).map(function(el) { return el.value; });
        var prodis     = Array.from(document.querySelectorAll('.filter-prodi-matkul:checked')).map(function(el) { return el.value; });
        var semesters  = Array.from(document.querySelectorAll('.filter-semester:checked')).map(function(el) { return parseInt(el.value); });
        var namaKelasList = Array.from(document.querySelectorAll('.filter-nama-kelas:checked')).map(function(el) { return el.value; });

        var visible = 0;
        document.querySelectorAll('.kelas-item').forEach(function(item) {
            var show = true;
            if (search) {
                var namaKelas = (item.dataset.namaDisplay || '').toLowerCase();
                var namaMatkul = (item.dataset.namaMatkul || '').toLowerCase();
                if (namaKelas.indexOf(search) === -1 && namaMatkul.indexOf(search) === -1) {
                    show = false;
                }
            }
            if (kurikulums.length && kurikulums.indexOf(item.dataset.idKurikulum) === -1) show = false;
            if (prodis.length     && prodis.indexOf(item.dataset.idProdi) === -1)         show = false;
            if (semesters.length  && semesters.indexOf(parseInt(item.dataset.semester)) === -1) show = false;
            if (namaKelasList.length && namaKelasList.indexOf(item.dataset.namaDisplay) === -1) show = false;

            // Plot status tab filter
            if (window.activePlotFilter === 'belum' && item.dataset.assigned === '1') {
                show = false;
            } else if (window.activePlotFilter === 'terplot' && item.dataset.assigned !== '1') {
                show = false;
            }

            item.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        var counter = document.getElementById('matkul-counter');
        if (counter) counter.textContent = visible + ' kelas paralel ditampilkan';

        // Update border filter button jika ada filter aktif
        var hasFilter = kurikulums.length || prodis.length || semesters.length || namaKelasList.length;
        var btn = document.getElementById('btn-filter-matkul');
        if (btn) {
            btn.classList.toggle('border-teal-500', !!hasFilter);
            btn.classList.toggle('bg-teal-50',      !!hasFilter);
            btn.classList.toggle('text-teal-700',   !!hasFilter);
        }
    };

    // ── Filter Dosen ─────────────────────────────────────────────
    window.applyDosenFilter = function() {
        var search = (document.getElementById('search-dosen').value || '').toLowerCase();
        var prodis  = Array.from(document.querySelectorAll('.filter-prodi-dosen:checked')).map(function(el) { return el.value; });

        document.querySelectorAll('.dosen-card').forEach(function(card) {
            var show = true;
            if (search && card.dataset.dosenNama.indexOf(search) === -1) show = false;
            if (prodis.length && prodis.indexOf(card.dataset.dosenProdi) === -1) show = false;
            card.style.display = show ? '' : 'none';
        });

        var hasFilter = prodis.length;
        var btn = document.getElementById('btn-filter-dosen');
        if (btn) {
            btn.classList.toggle('border-teal-500', !!hasFilter);
            btn.classList.toggle('bg-teal-50',      !!hasFilter);
            btn.classList.toggle('text-teal-700',   !!hasFilter);
        }
    };

    // ── Toggle filter popups ─────────────────────────────────────
    window.toggleFilterMatkul = function(e) {
        e.stopPropagation();
        var popup = document.getElementById('filter-popup');
        popup.classList.toggle('open');
        document.getElementById('filter-popup-dosen').classList.remove('open');
    };

    window.toggleFilterDosen = function(e) {
        e.stopPropagation();
        var popup = document.getElementById('filter-popup-dosen');
        popup.classList.toggle('open');
        document.getElementById('filter-popup').classList.remove('open');
    };

    // Close popup saat klik di luar
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#filter-popup') && !e.target.closest('#btn-filter-matkul')) {
            document.getElementById('filter-popup').classList.remove('open');
        }
        if (!e.target.closest('#filter-popup-dosen') && !e.target.closest('#btn-filter-dosen')) {
            document.getElementById('filter-popup-dosen').classList.remove('open');
        }
    });

    // ── Reset filters ─────────────────────────────────────────────
    window.resetMatkulFilter = function() {
        document.getElementById('search-matkul').value = '';
        document.querySelectorAll('.filter-kurikulum, .filter-prodi-matkul, .filter-semester, .filter-nama-kelas').forEach(function(el) {
            el.checked = false;
        });
        applyMatkulFilter();
    };

    window.resetDosenFilter = function() {
        document.getElementById('search-dosen').value = '';
        document.querySelectorAll('.filter-prodi-dosen').forEach(function(el) { el.checked = false; });
        applyDosenFilter();
    };

    function updateCounter() {
        var total = document.querySelectorAll('.matkul-item').length;
        var counter = document.getElementById('matkul-counter');
        if (counter) counter.textContent = total + ' mata kuliah ditampilkan';
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>



    {{-- Table mode initialization (only runs when in table mode) --}}
    <script>
        document.addEventListener('alpine:init', function() {
            @if($mode === 'table')
                Alpine.start();
                // Table filtering logic is now handled entirely within table.blade.php
            @endif
        });
    </script>

    <script>
        let currentChangeKelasId = null;

        window.selectDosen = function(dosenId) {
            const mkInput = document.getElementById('mk-' + currentChangeKelasId);
            const prodiInput = document.getElementById('prodi-' + currentChangeKelasId);
            if (!mkInput || !prodiInput) return;

            fetch('{{ route("dosen-pengampu.simpan", [], false) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    kode_matkul: mkInput.value,
                    id_dosen: dosenId,
                    id_kelas: currentChangeKelasId,
                    id_prodi: prodiInput.value,
                    id_tahunakademik: {{ $tahunAkademik->id_tahunakademik }}
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) window.location.reload();
                else alert(data.message || 'Gagal menyimpan');
            });
            closeDosenModal();
        };

        window.removeDosen = function(kelasId, pengampuId, event) {
            if (event) event.stopPropagation();
            if (!confirm('Lepaskan dosen dari kelas ini?')) return;
            const mkInput = document.getElementById('mk-' + kelasId);
            const prodiInput = document.getElementById('prodi-' + kelasId);
            const mk = mkInput ? mkInput.value : '';
            const prodi = prodiInput ? prodiInput.value : 0;

            fetch('{{ route("dosen-pengampu.hapus", [], false) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id: pengampuId,
                    kode_matkul: mk,
                    id_dosen: 0,
                    id_kelas: kelasId,
                    id_prodi: prodi,
                    id_tahunakademik: {{ $tahunAkademik->id_tahunakademik }}
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) window.location.reload();
                else alert(data.message || 'Gagal menghapus');
            });
        };

        document.addEventListener('DOMContentLoaded', function() {
            // The table mode filtering is handled within table.blade.php
        });
    </script>


</body>
</html>
