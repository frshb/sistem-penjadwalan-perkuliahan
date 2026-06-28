const fs = require('fs');
const path = 'resources/views/dosen-pengampu/index.blade.php';

const html = `<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Management Pengampu Mata Kuliah</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
        .kelas-card { cursor: grab; user-select: none; transition: all 0.2s ease; }
        .kelas-card:active { cursor: grabbing; }
        .kelas-card.dragging { opacity: 0.4; transform: scale(0.98); }
        .dosen-dropzone.drag-over { background-color: #f0fdf4 !important; border: 2px dashed #0d9488 !important; }
    </style>
</head>
<body x-data="{
    sidebarOpen: true,
    kelasSidebarOpen: true,
    mode: @js(request('mode', 'workspace')),
    searchKelas: '',
    filterProdi: [],
    filterSemester: [],
    filterMatkul: '',
    filterStatus: 'all',
    showFilters: false,
    showToast: false,
    toastMessage: '',
    toastType: 'success'
}" class="bg-gray-50/80 min-h-screen overflow-x-hidden font-sans antialiased">

@include('components.sidebar')

<script>
    window.__KELAS_LIST__ = @json($kelasList);
    window.__PENGAMPUS__ = @json($pengampusJs);
    window.__MATKULS__ = @json($matkulsJs);
    var ID_TAHUNAKADEMIK = {{ $tahunAkademik->id_tahunakademik }};
    var CSRF = '{{ csrf_token() }}';
</script>

<main :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'" class="transition-all duration-300 min-h-screen">

    <header class="bg-white border-b border-gray-200/80 sticky top-0 z-40">
        <div class="px-6 sm:px-8 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="flex flex-col">
                    <div class="w-2 h-5 bg-teal-600 rounded-tl-md"></div>
                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800 tracking-tight">Management Pengampu Mata Kuliah</h1>
                    <nav class="flex items-center gap-2 text-sm text-gray-500 mt-0.5">
                        <a href="{{ route('dosen-pengampu.pilih-tahun') }}" class="hover:text-teal-600 transition font-medium">Management Pengampu</a>
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        <span class="text-teal-700 font-semibold">{{ $tahunAkademik->nama_tahunakademik }}</span>
                    </nav>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex items-center bg-gray-100 rounded-xl p-1">
                    <button @click="mode = 'workspace'" :class="mode === 'workspace' ? 'bg-white text-teal-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/></svg>
                        Workspace
                    </button>
                    <button @click="mode = 'table'" :class="mode === 'table' ? 'bg-white text-teal-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Table
                    </button>
                </div>
                @include('components.header-profile')
            </div>
        </div>
    </header>

    <div x-show="mode === 'workspace'" x-transition.opacity class="flex h-[calc(100vh-73px)]">
