<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal Dosen Pengampu</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .dosen-dropzone.drag-over {
            background-color: #f0fdf4 !important;
            border: 2px dashed #22c55e !important;
        }
        .matkul-item { cursor: grab; user-select: none; }
        .matkul-item:active { cursor: grabbing; }
        .matkul-item.dragging { opacity: 0.4; }
        #filter-popup { display: none; }
        #filter-popup.open { display: block; }
        #filter-popup-dosen { display: none; }
        #filter-popup-dosen.open { display: block; }
    </style>
</head>

<body
    x-data="{ sidebarOpen: true, matkulSidebarOpen: true, focusMode: false }"
    class="bg-gray-100/50 min-h-screen overflow-x-hidden"
>

@include('components.sidebar')

<script>
    window.__MATKULS__   = @json($matkulsJs);
    window.__PENGAMPUS__ = @json($pengampusJs);
</script>

<main :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'" class="transition-all duration-300 p-6 sm:p-8">

    {{-- HEADER --}}
    <div class="flex items-center justify-between" x-show="!focusMode">
        <div>
            <div class="flex items-center gap-3">
                <div class="flex flex-col">
                    <div class="w-2 h-5 bg-teal-600 rounded-tl-md"></div>
                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Portal Dosen Pengampu</h1>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mt-2">
                <a href="{{ route('dosen-pengampu.pilih-tahun') }}" class="hover:text-teal-600 transition font-medium">Portal Dosen Pengampu</a>
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
        </div>
        <div class="flex items-center gap-2.5">
            <button
                @click="matkulSidebarOpen = !matkulSidebarOpen"
                :class="matkulSidebarOpen ? 'bg-teal-50 border-teal-200 text-teal-700' : 'bg-white border-gray-200 text-gray-700'"
                class="px-4 py-2.5 border rounded-xl font-semibold flex items-center gap-2 transition text-sm shadow-sm"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                <span x-text="matkulSidebarOpen ? 'Sembunyikan Mata Kuliah' : 'Tampilkan Mata Kuliah'"></span>
            </button>
            <button
                @click="focusMode = !focusMode; sidebarOpen = !focusMode; matkulSidebarOpen = !focusMode"
                :class="focusMode ? 'bg-gray-800 border-transparent text-white' : 'bg-white border-gray-200 text-gray-700'"
                class="px-4 py-2.5 border rounded-xl font-semibold flex items-center gap-2 transition text-sm shadow-sm"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="focusMode" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!focusMode"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l-5-5m11 5v-4m0 4h-4m4 0l-5-5"/></svg>
                <span x-text="focusMode ? 'Normal View' : 'Workspace Fokus'"></span>
            </button>
        </div>
    </div>

    {{-- CONTENT GRID --}}
    <div
        :class="focusMode ? 'h-[calc(100vh-90px)] mt-4 gap-4' : 'h-[calc(100vh-180px)] mt-6 gap-5'"
        class="grid grid-cols-12 overflow-hidden transition-all duration-300"
    >

        {{-- ════════════ SIDEBAR MATA KULIAH ════════════ --}}
        <div x-show="matkulSidebarOpen" class="col-span-12 xl:col-span-3 min-h-0 flex relative z-30">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 h-full flex flex-col w-full overflow-visible">

                {{-- Header --}}
                <div class="p-4 border-b border-gray-100 rounded-t-2xl bg-white relative z-30">
                    <h2 class="text-base font-bold text-gray-800 mb-3">Daftar Mata Kuliah</h2>

                    {{-- Search + Filter Button --}}
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <input
                                type="text"
                                id="search-matkul"
                                placeholder="Cari kelas / mata kuliah..."
                                class="w-full pl-4 pr-9 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-teal-500 outline-none text-sm"
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
                                    <span class="font-bold text-gray-800 text-sm">Filter Mata Kuliah</span>
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
                                        @foreach([1,2,3,4,5,6,7,8] as $sem)
                                        <label class="flex items-center gap-2 cursor-pointer group">
                                            <input type="checkbox" class="filter-semester w-4 h-4 rounded text-teal-600 border-gray-300 focus:ring-teal-500" value="{{ $sem }}" onchange="applyMatkulFilter()">
                                            <span class="text-sm text-gray-700 group-hover:text-teal-700">Semester {{ $sem }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Counter --}}
                                <p id="matkul-counter" class="text-xs text-gray-400 border-t pt-2"></p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- List --}}
                <div class="flex-1 overflow-y-auto min-h-0 px-2 py-2 rounded-b-2xl" id="matkul-list-container">
                    @foreach($matkuls as $item)
                    <div
                        id="matkul-{{ $item->kode_matkul }}"
                        class="matkul-item bg-white border border-gray-200 rounded-xl p-3 mx-2 my-2 hover:shadow-md hover:border-teal-300 transition-all duration-150 select-none"
                        draggable="true"
                        data-id="{{ $item->kode_matkul }}"
                        data-nama="{{ strtolower($item->nama_matkul) }}"
                        data-nama-display="{{ $item->nama_matkul }}"
                        data-sks="{{ $item->sks }}"
                        data-prodi="{{ $item->program_studi->nama_prodi ?? '-' }}"
                        data-id-prodi="{{ $item->id_prodi }}"
                        data-semester="{{ $item->semester }}"
                        data-id-kurikulum="{{ $item->id_kurikulum }}"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-gray-800 text-sm leading-tight">{{ $item->nama_matkul }}</h3>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $item->program_studi->nama_prodi ?? '-' }}</p>
                                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                    <span class="text-[10px] px-2 py-0.5 bg-teal-50 text-teal-700 rounded font-mono">{{ $item->kode_matkul }}</span>
                                    <span class="text-[10px] px-2 py-0.5 bg-blue-50 text-blue-700 rounded">{{ $item->sks }} SKS</span>
                                    <span class="text-[10px] px-2 py-0.5 bg-gray-100 text-gray-600 rounded">Smt {{ $item->semester }}</span>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ════════════ WORKSPACE DOSEN ════════════ --}}
        <div
            :class="matkulSidebarOpen ? 'xl:col-span-9' : 'xl:col-span-12'"
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
                        @if(!Auth::user() || !Auth::user()->isKaprodi())
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
                        @endif
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
    </div>
</main>

<script>
(function () {
    const CSRF      = document.querySelector('meta[name="csrf-token"]').content;
    const MATKULS   = window.__MATKULS__;
    const PENGAMPUS = window.__PENGAMPUS__;

    // ── Init: render assigned cards dari DB ──────────────────────
    function init() {
        PENGAMPUS.forEach(function(p) {
            var zone = document.querySelector('.dosen-dropzone[data-dosen-id="' + p.id_dosen + '"]');
            if (!zone) return;
            zone.appendChild(buildCard(p.kode_matkul, p.nama_matkul, p.sks, p.nama_prodi, String(p.id_dosen)));
        });
        document.querySelectorAll('.dosen-card').forEach(updateSks);
        updateCounter();
    }

    // ── Build assigned card ──────────────────────────────────────
    function buildCard(kodeMatkul, namaMatkul, sks, namaProdi, dosenId) {
        var div = document.createElement('div');
        div.className = 'assigned-card bg-white border border-gray-200 rounded-lg p-2.5 flex items-start justify-between gap-2 group shadow-sm hover:border-teal-300 transition-all';
        div.dataset.matkulId = kodeMatkul;
        div.dataset.sks      = sks;
        div.dataset.dosenId  = dosenId;
        div.innerHTML =
            '<div class="min-w-0 flex-1">' +
                '<p class="font-semibold text-gray-800 text-xs leading-tight truncate" title="' + namaMatkul + '">' + namaMatkul + '</p>' +
                '<p class="text-[10px] text-gray-500 truncate mt-0.5">' + namaProdi + '</p>' +
                '<span class="inline-block text-[10px] font-semibold px-1.5 py-0.5 bg-blue-50 text-blue-600 rounded mt-1">' + sks + ' SKS</span>' +
            '</div>' +
            '<button onclick="hapusPengampu(\'' + kodeMatkul + '\',\'' + dosenId + '\',this)" ' +
                'class="flex-shrink-0 text-gray-300 hover:text-red-500 hover:bg-red-50 p-1 rounded transition opacity-0 group-hover:opacity-100" ' +
                'title="Lepaskan">' +
                '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>' +
                '</svg>' +
            '</button>';
        return div;
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
    document.querySelectorAll('.matkul-item').forEach(function(item) {
        item.addEventListener('dragstart', function(e) {
            item.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'copy';
            e.dataTransfer.setData('text/plain', item.dataset.id);
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
            e.preventDefault();
            zone.classList.remove('drag-over');
            var kodeMatkul = e.dataTransfer.getData('text/plain');
            var dosenId    = zone.dataset.dosenId;
            if (!kodeMatkul || !dosenId) return;
            simpan(kodeMatkul, dosenId, zone);
        });
    });

    // ── Simpan AJAX ──────────────────────────────────────────────
    function simpan(kodeMatkul, dosenId, zone) {
        var mk = MATKULS.find(function(m) { return String(m.kode_matkul) === String(kodeMatkul); });
        if (!mk) return;

        fetch('{{ route("dosen-pengampu.simpan") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ kode_matkul: kodeMatkul, id_dosen: dosenId })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) { alert(data.message || 'Gagal menyimpan.'); return; }
            zone.appendChild(buildCard(mk.kode_matkul, mk.nama_matkul, mk.sks, mk.nama_prodi, String(dosenId)));
            updateSks(zone.closest('.dosen-card'));
        })
        .catch(function() { alert('Terjadi kesalahan jaringan.'); });
    }

    // ── Hapus pengampu ───────────────────────────────────────────
    window.hapusPengampu = function(kodeMatkul, dosenId, btn) {
        var card     = btn.closest('.assigned-card');
        var dosenCard = btn.closest('.dosen-card');
        card.style.opacity = '0.4';
        btn.disabled = true;

        fetch('{{ route("dosen-pengampu.hapus") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ kode_matkul: kodeMatkul, id_dosen: dosenId })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) { card.style.opacity = '1'; btn.disabled = false; alert(data.message || 'Gagal.'); return; }
            card.remove();
            updateSks(dosenCard);
        })
        .catch(function() { card.style.opacity = '1'; btn.disabled = false; alert('Terjadi kesalahan jaringan.'); });
    };

    // ── Filter Matkul (pure JS, no Alpine) ──────────────────────
    window.applyMatkulFilter = function() {
        var search    = (document.getElementById('search-matkul').value || '').toLowerCase();
        var kurikulums = Array.from(document.querySelectorAll('.filter-kurikulum:checked')).map(function(el) { return el.value; });
        var prodis     = Array.from(document.querySelectorAll('.filter-prodi-matkul:checked')).map(function(el) { return el.value; });
        var semesters  = Array.from(document.querySelectorAll('.filter-semester:checked')).map(function(el) { return parseInt(el.value); });

        var visible = 0;
        document.querySelectorAll('.matkul-item').forEach(function(item) {
            var show = true;
            if (search     && item.dataset.nama.indexOf(search) === -1) show = false;
            if (kurikulums.length && kurikulums.indexOf(item.dataset.idKurikulum) === -1) show = false;
            if (prodis.length     && prodis.indexOf(item.dataset.idProdi) === -1)         show = false;
            if (semesters.length  && semesters.indexOf(parseInt(item.dataset.semester)) === -1) show = false;

            item.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        var counter = document.getElementById('matkul-counter');
        if (counter) counter.textContent = visible + ' mata kuliah ditampilkan';

        // Update border filter button jika ada filter aktif
        var hasFilter = kurikulums.length || prodis.length || semesters.length;
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
        document.querySelectorAll('.filter-kurikulum, .filter-prodi-matkul, .filter-semester').forEach(function(el) {
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

</body>
</html>
