<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Tahun Akademik | Penugasan Dosen</title>

    <meta name="description" content="Pilih Tahun Akademik untuk Penugasan Dosen">

    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    x-data="{
        sidebarOpen: true,
        showModeModal: false,
        selectedTahunId: null,
        statusFilter: 'aktif',
        activeCount: {{ $tahunAkademiks->where('status_aktif', 1)->count() }},
        inactiveCount: {{ $tahunAkademiks->where('status_aktif', 0)->count() }},
        totalCount: {{ $tahunAkademiks->count() }}
    }"
    class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300"
>

@include('components.sidebar')

<main id="main-content"
      :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'"
      class="flex-1 min-w-0 p-6 sm:p-10 transition-all duration-300 ease-in-out">

    <!-- CONTENT -->
    <div>

        <!-- Header -->
        <div class="flex justify-between items-center">

            <div class="flex items-center">

                <button @click="sidebarOpen = !sidebarOpen" class="flex flex-col hover:opacity-80 transition cursor-pointer" title="Toggle Sidebar">
                    <div class="w-2 h-5 bg-teal-600 rounded-tl-md"></div>
                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                </button>

                <h1 class="text-2xl font-bold text-gray-800 ml-3">
                    Manajemen Pengampu
                </h1>

            </div>

            @include('components.header-profile')

        </div>

        <!-- Title -->
        <div class="mt-6 mb-8 flex items-center justify-between">

            <div>
                <h2 class="text-2xl font-bold text-gray-700">
                    Pilih Tahun Akademik
                </h2>
                <p class="text-gray-500 mt-1">
                    Silakan pilih tahun akademik untuk melakukan penugasan dosen pengampu.
                </p>
            </div>

        </div>

        <!-- Filter Tabs -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="bg-gray-200/60 p-1 rounded-xl flex items-center border border-gray-200/30 self-start">
                <button
                    @click="statusFilter = 'aktif'"
                    :class="statusFilter === 'aktif' ? 'bg-white text-teal-800 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-800'"
                    class="px-4 py-2 rounded-lg text-xs font-semibold transition-all duration-200 flex items-center gap-1.5"
                >
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                    Aktif (<span x-text="activeCount"></span>)
                </button>
                <button
                    @click="statusFilter = 'nonaktif'"
                    :class="statusFilter === 'nonaktif' ? 'bg-white text-gray-800 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-800'"
                    class="px-4 py-2 rounded-lg text-xs font-semibold transition-all duration-200 flex items-center gap-1.5"
                >
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                    Nonaktif (<span x-text="inactiveCount"></span>)
                </button>
                <button
                    @click="statusFilter = 'semua'"
                    :class="statusFilter === 'semua' ? 'bg-white text-teal-800 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-800'"
                    class="px-4 py-2 rounded-lg text-xs font-semibold transition-all duration-200"
                >
                    Semua (<span x-text="totalCount"></span>)
                </button>
            </div>
        </div>

        <!-- Card Tahun Akademik -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

            @forelse ($tahunAkademiks as $tahun)

                @php
                    $isRestricted = !$tahun->status_aktif;
                @endphp
                <div
                    x-show="statusFilter === 'semua' || (statusFilter === 'aktif' && {{ $tahun->status_aktif ? 1 : 0 }} == 1) || (statusFilter === 'nonaktif' && {{ $tahun->status_aktif ? 0 : 1 }} == 1)"
                    class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden transition-all duration-300 {{ $isRestricted ? 'opacity-50' : 'hover:shadow-xl hover:-translate-y-1' }}">

                    <!-- Header Card -->
                    <div class="bg-gradient-to-r from-teal-600 to-teal-700 px-6 py-5">

                        <div class="flex items-center justify-between">

                            <div>

                                <h3 class="text-xl font-bold text-white">
                                    {{ $tahun->nama_tahunakademik }}
                                </h3>

                                <p class="text-teal-100 text-sm mt-1">
                                    Tahun Ajaran {{ $tahun->tahun_ajaran }}
                                </p>

                            </div>

                            @if($tahun->status_aktif)

                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                    Aktif
                                </span>

                            @else

                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                                    Nonaktif
                                </span>

                            @endif

                        </div>

                    </div>

                    <!-- Body -->
                    <div class="p-6">

                        <div class="flex items-center justify-between mb-6">

                            <div>
                                <p class="text-sm text-gray-500">
                                    Semester
                                </p>

                                <p class="font-semibold text-gray-700">
                                    {{ $tahun->nama_tahunakademik }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">
                                    Tahun
                                </p>

                                <p class="font-semibold text-gray-700">
                                    {{ $tahun->tahun_ajaran }}
                                </p>
                            </div>

                        </div>

                        <!-- Button -->
                        @if($isRestricted)
                            <button
                                disabled
                                class="w-full inline-flex items-center justify-center px-4 py-3 bg-gray-200 text-gray-400 font-semibold rounded-xl cursor-not-allowed">
                                Tidak Aktif (Akses Terbatas)
                            </button>
                        @else
                            <button
                                @click="selectedTahunId = {{ $tahun->id_tahunakademik }}; showModeModal = true"
                                class="w-full inline-flex items-center justify-center px-4 py-3 bg-teal-600 text-white font-semibold rounded-xl shadow-sm hover:bg-teal-700 transition duration-200">

                                Atur Penugasan Dosen

                                <svg class="w-5 h-5 ml-2"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 5l7 7-7 7">
                                    </path>
                                </svg>

                            </button>
                        @endif

                    </div>

                </div>

            @empty

                <div class="col-span-full">
                    <div class="bg-white p-10 rounded-2xl shadow-md text-center">
                        <div class="flex justify-center mb-4">
                            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center text-3xl">
                                📚
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-700 mb-2">
                            Data Tahun Akademik Belum Ada
                        </h3>
                        <p class="text-gray-500">
                            Silakan hubungi Admin untuk menambahkan data tahun akademik pada menu Pengaturan Kurikulum & TA.
                        </p>
                    </div>
                </div>

            @endforelse

        </div>

        <!-- Empty State Filter -->
        <div
            x-show="(statusFilter === 'aktif' && activeCount === 0) || (statusFilter === 'nonaktif' && inactiveCount === 0) || (statusFilter === 'semua' && totalCount === 0)"
            class="bg-white p-12 rounded-2xl shadow-sm text-center border border-gray-100 mt-6"
            style="display: none;"
            x-cloak
        >
            <div class="flex justify-center mb-4">
                <div class="w-16 h-16 rounded-full bg-teal-50 flex items-center justify-center text-teal-600 text-3xl">
                    📅
                </div>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-1">
                Tidak Ada Tahun Akademik
            </h3>
            <p class="text-gray-500 max-w-md mx-auto text-sm" x-text="'Tidak ditemukan tahun akademik dengan status ' + (statusFilter === 'aktif' ? 'Aktif' : (statusFilter === 'nonaktif' ? 'Nonaktif' : '')) + '.'">
            </p>
        </div>

    </div>

</main>

{{-- ================================================ --}}
{{-- MODAL PILIH MODE PENUGASAN                         --}}
{{-- ================================================ --}}
<div
    x-show="showModeModal"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div x-show="showModeModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="showModeModal = false"
         class="absolute inset-0 bg-slate-900/60 backdrop-blur-md">
    </div>

    {{-- Modal Panel --}}
    <div x-show="showModeModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         @click.away="showModeModal = false"
         class="relative bg-white rounded-3xl shadow-[0_20px_60px_-15px_rgba(0,0,0,0.3)] w-full max-w-3xl overflow-hidden ring-1 ring-black/5">

        {{-- Header --}}
        <div class="px-8 pt-8 pb-6 border-b border-gray-100 text-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-teal-50/50 to-transparent pointer-events-none"></div>
            <div class="relative">
                <div class="w-16 h-16 bg-teal-100/50 text-teal-600 rounded-2xl flex items-center justify-center mx-auto mb-4 rotate-3">
                    <svg class="w-8 h-8 -rotate-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Pilih Mode Penugasan</h2>
                <p class="text-gray-500 mt-2 font-medium">
                    Pilih antarmuka kerja yang paling sesuai dengan kebutuhan Anda saat ini.
                </p>
            </div>
        </div>

        {{-- Body — 2 Pilihan --}}
        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50/50">

            {{-- Workspace Mode --}}
            <a @click.prevent="
                   const url = '{{ route('dosen-pengampu.index', ['tahun' => 'ID_PLACEHOLDER', 'mode' => 'workspace']) }}'.replace('ID_PLACEHOLDER', selectedTahunId);
                   window.location.href = url;
               "
               class="group relative flex flex-col p-8 rounded-3xl bg-white border border-gray-200 hover:border-teal-500 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] hover:-translate-y-1 transition-all duration-300 cursor-pointer overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-teal-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                
                <div class="relative flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center group-hover:scale-110 group-hover:bg-teal-100 transition-all duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-teal-700 transition-colors">Workspace</h3>
                        <p class="text-sm text-teal-600 font-medium">Visual & Interaktif</p>
                    </div>
                </div>
                
                <ul class="relative text-sm text-gray-500 space-y-3 mb-8 w-full flex-grow">
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-teal-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Sistem <strong>Drag & Drop</strong> intuitif</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-teal-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Visualisasi distribusi beban dosen</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-teal-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Sempurna untuk proses plotting awal</span>
                    </li>
                </ul>
                
                <div class="relative mt-auto">
                    <div class="flex items-center justify-center w-full py-3.5 bg-slate-50 border border-slate-100 text-teal-700 font-bold rounded-2xl group-hover:bg-teal-600 group-hover:border-teal-600 group-hover:text-white transition-all duration-300">
                        Masuk Workspace
                        <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </div>
                </div>
            </a>

            {{-- Table Mode --}}
            <a @click.prevent="
                   const url = '{{ route('dosen-pengampu.index', ['tahun' => 'ID_PLACEHOLDER2', 'mode' => 'table']) }}'.replace('ID_PLACEHOLDER2', selectedTahunId);
                   window.location.href = url;
               "
               class="group relative flex flex-col p-8 rounded-3xl bg-white border border-gray-200 hover:border-slate-800 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] hover:-translate-y-1 transition-all duration-300 cursor-pointer overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-slate-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                
                <div class="relative flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center group-hover:scale-110 group-hover:bg-slate-200 transition-all duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-slate-900 transition-colors">Tabel Data</h3>
                        <p class="text-sm text-slate-500 font-medium">Spreadsheet Klasik</p>
                    </div>
                </div>
                
                <ul class="relative text-sm text-gray-500 space-y-3 mb-8 w-full flex-grow">
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-slate-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Tampilan mirip <strong>Excel / Spreadsheet</strong></span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-slate-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Sangat cepat untuk edit massal</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-slate-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Efisien bagi operator administrasi</span>
                    </li>
                </ul>
                
                <div class="relative mt-auto">
                    <div class="flex items-center justify-center w-full py-3.5 bg-slate-50 border border-slate-100 text-slate-700 font-bold rounded-2xl group-hover:bg-slate-800 group-hover:border-slate-800 group-hover:text-white transition-all duration-300">
                        Masuk Tabel Data
                        <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </div>
                </div>
            </a>

        </div>

        {{-- Footer --}}
        <div class="px-8 py-5 bg-white border-t border-gray-100 flex justify-end items-center">
            <button @click="showModeModal = false"
                    class="px-6 py-2.5 text-sm text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-xl font-bold transition-colors">
                Batal
            </button>
        </div>

    </div>
</div>

</body>
</html>
