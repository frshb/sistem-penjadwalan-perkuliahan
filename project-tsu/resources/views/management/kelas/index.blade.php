<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Data | Data Kelas {{ $tahunAkademik->nama_tahunakademik }}</title>
    <meta name="description" content="Management Data Kelas Sistem Penjadwalan Perkuliahan">
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .prodi-tab.active {
            border-bottom-color: #0d9488;
            color: #0d9488;
            font-weight: 600;
        }
    </style>
</head>

<body x-data="{
    sidebarOpen: true,
    showAddModal: false,
    showEditModal: false,
    showExportMenu: false,
    searchTerm: localStorage.getItem('kelas_searchTerm') || '',
    selectedProdiFilter: localStorage.getItem('kelas_selectedProdiFilter') || 'all',
    selectedNamaKelasFilter: localStorage.getItem('kelas_selectedNamaKelasFilter') || 'all',
    selectedSemesterFilter: localStorage.getItem('kelas_selectedSemesterFilter') || 'all',

    init() {
        this.$watch('searchTerm', value => localStorage.setItem('kelas_searchTerm', value));
        this.$watch('selectedProdiFilter', value => localStorage.setItem('kelas_selectedProdiFilter', value));
        this.$watch('selectedNamaKelasFilter', value => localStorage.setItem('kelas_selectedNamaKelasFilter', value));
        this.$watch('selectedSemesterFilter', value => localStorage.setItem('kelas_selectedSemesterFilter', value));
    },

    filterKelasList() {
        if (typeof window.applyAllFilters === 'function') {
            window.applyAllFilters(this);
        }
    },
    currentEditMatkul: '',
    editData: {
        id_kelas: null,
        nama_kelas: '',
        id_prodi: '',
        id_tahunakademik: '',
        kapasitas: '',
        semester: '',
        id_matakuliah: '',
        id_kurikulum: '',
        sks: '',
    },
    selectedKelas: [],
    selectedProdi: '',
    selectedSemester: '',
    selectedKurikulum: '',
    selectedSks: '',
    mataKuliahs: @js($mataKuliahs),

    /**
     * Bandingkan dua nilai secara longgar sebagai string.
     * Mengatasi campur tipe data (int dari DB vs string dari <select> HTML)
     * dan nilai kosong ('' / null / undefined) yang dianggap 'tidak memfilter'.
     */
    eq(filterValue, dataValue) {
        if (filterValue === '' || filterValue === null || filterValue === undefined) {
            return true;
        }
        return String(filterValue) === String(dataValue);
    },

    selectMatkul(kode) {
        let matkul = this.mataKuliahs.find(item =>
            this.eq(kode, item.id_matakuliah) && this.eq(this.selectedProdi, item.id_prodi)
        );

        if (matkul) {
            this.selectedSks = matkul.sks ?? '';
        } else {
            this.selectedSks = '';
        }
    },

    selectEditMatkul(kode) {
        let matkul = this.mataKuliahs.find(item =>
            this.eq(kode, item.id_matakuliah) && this.eq(this.editData.id_prodi, item.id_prodi)
        );

        if (matkul) {
            this.editData.sks = matkul.sks ?? '';
            this.editData.id_kurikulum = matkul.id_kurikulum ?? '';
        } else {
            this.editData.sks = '';
        }
    },

    openEdit(kelas) {
        this.currentEditMatkul = kelas.id_matakuliah;

        this.editData = {
            ...kelas,
            id_kurikulum: kelas.matakuliah?.id_kurikulum ?? '',
            sks: kelas.matakuliah?.sks ?? '',
        };

        this.showEditModal = true;

        this.$nextTick(() => {
            this.selectEditMatkul(kelas.id_matakuliah);
        });
    },

    showGenerateModal: false,
    selectedGenerateProdi: '',
    selectedGenerateProdiName: '',
    semesterOptions: @js(
        str_contains(strtolower($tahunAkademik->nama_tahunakademik), 'genap')
            ? [2,4,6,8]
            : [1,3,5,7]
    ),

}" class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300">

@include('components.sidebar')

<main id="main-content"
      :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'"
      class="flex-1 min-w-0 p-6 sm:p-10 transition-all duration-300 ease-in-out">

    <!-- Header -->
    <div class="flex justify-between items-center">
        <div class="flex items-center">
            <button @click="sidebarOpen = !sidebarOpen" class="flex flex-col hover:opacity-80 transition cursor-pointer" title="Toggle Sidebar">
                    <div class="w-2 h-5 bg-teal-600 rounded-tl-md"></div>
                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                </button>

            <div class="ml-3 flex items-center space-x-2 text-2xl font-bold">

                <a href="{{ route('kelas.pilih-tahun') }}"
                class="text-gray-800 hover:text-teal-600 transition">
                    Management Kelas
                </a>

                <svg class="w-5 h-5 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5l7 7-7 7"/>
                </svg>

                <span class="text-teal-700">
                    {{ $tahunAkademik->nama_tahunakademik }}
                </span>

            </div>
        </div>

        @include('components.header-profile')
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mt-4 p-4 text-sm text-green-800 rounded-xl bg-green-50 border border-green-200 flex items-center gap-3" role="alert">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <span class="font-medium">Berhasil!</span> {{ session('success') }}
            </div>
        </div>
    @endif
    @if(session('error') || $errors->any())
        <div class="mt-4 p-4 text-sm text-red-800 rounded-xl bg-red-50 border border-red-200 flex items-center gap-3" role="alert">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <span class="font-medium">Gagal!</span> {{ session('error') ?? 'Terdapat kesalahan.' }}
            </div>
        </div>
    @endif

    <!-- Title -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-6 mb-4">
        <h2 class="text-xl font-bold text-gray-700 mb-4 sm:mb-0">
            Daftar Kelas
        </h2>

        <div class="flex items-center space-x-2">
            <!-- Dropdown Menu Export -->
            <div class="relative" @click.away="showExportMenu = false">
                <button @click="showExportMenu = !showExportMenu" class="inline-flex items-center px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export
                    <svg class="w-3.5 h-3.5 ml-1.5 transition-transform duration-200" :class="showExportMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="showExportMenu"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl z-20 border border-gray-200 p-1"
                     style="display: none;">
                    <a href="{{ route('kelas.export.excel') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 rounded-md hover:bg-teal-50 hover:text-teal-800 transition-colors">
                        <svg class="w-4 h-4 mr-2.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Export Excel
                    </a>
                    <a href="{{ route('kelas.export.pdf') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 rounded-md hover:bg-teal-50 hover:text-teal-800 transition-colors">
                        <svg class="w-4 h-4 mr-2.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        Export PDF
                    </a>
                </div>
            </div>

            @if(Auth::user()->hasPermissionAccess('management_data', 'Kelas', 'edit'))
            <button @click="showAddModal = true"
                    class="px-5 py-2.5 bg-yellow-600 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-700">
                Tambah Kelas
            </button>
            @endif
        </div>
    </div>

    <!-- Statistics Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Card 1: TOTAL KELAS -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center">
            <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m-14 0V9a2 2 0 012-2h10a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Kelas</p>
                <h3 class="text-2xl font-black text-gray-800">{{ $stats['total_kelas'] ?? 0 }}</h3>
            </div>
        </div>
        
        <!-- Card 2: MATA KULIAH AKTIF -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center">
            <div class="w-12 h-12 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Mata Kuliah</p>
                <h3 class="text-2xl font-black text-gray-800">{{ $stats['total_matkul'] ?? 0 }}</h3>
            </div>
        </div>

        <!-- Card 3: TOTAL SKS -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center">
            <div class="w-12 h-12 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total SKS</p>
                <h3 class="text-2xl font-black text-gray-800">{{ $stats['total_sks'] ?? 0 }}</h3>
            </div>
        </div>

        <!-- Card 4: KELAS KOSONG -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center">
            <div class="w-12 h-12 rounded-lg bg-red-50 text-red-600 flex items-center justify-center mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Kelas Kosong (Dosen)</p>
                <h3 class="text-2xl font-black text-gray-800">{{ $stats['kelas_kosong'] ?? 0 }}</h3>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="mb-6 space-y-3">
        <!-- Search Bar -->
        <div class="flex items-center gap-3">
            <div class="flex-1 w-full">
                <div class="relative w-full">
                    <input type="text"
                        x-model="searchTerm"
                        @input="filterKelasList()"
                        placeholder="Cari nama kelas, kode MK, atau mata kuliah..."
                        class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
                    <div class="absolute right-0 top-0 h-full px-3.5 text-gray-500 flex items-center justify-center pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3">
            <!-- Filter Program Studi -->
            <div class="w-full sm:w-1/3">
                <div class="relative">
                    <select x-model="selectedProdiFilter" @change="filterKelasList()"
                        class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm appearance-none bg-white">
                        <option value="all">Semua Program Studi</option>
                        @foreach($prodis as $p)
                            <option value="{{ $p->nama_prodi }}">{{ $p->nama_prodi }}</option>
                        @endforeach
                    </select>
                    <div class="absolute left-0 top-0 h-full px-3 text-gray-500 flex items-center justify-center pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <div class="absolute right-0 top-0 h-full px-3 text-gray-500 flex items-center justify-center pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Filter Nama Kelas -->
            <div class="w-full sm:w-1/3">
                <div class="relative">
                    <select x-model="selectedNamaKelasFilter" @change="filterKelasList()"
                        class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm appearance-none bg-white">
                        <option value="all">Semua Nama Kelas</option>
                        @php
                            $namaKelasList = $kelas->pluck('nama_kelas')->unique()->sort()->values();
                        @endphp
                        @foreach($namaKelasList as $nk)
                            <option value="{{ $nk }}">{{ $nk }}</option>
                        @endforeach
                    </select>
                    <div class="absolute right-0 top-0 h-full px-3 text-gray-500 flex items-center justify-center pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Filter Semester -->
            <div class="w-full sm:w-1/3">
                <div class="relative">
                    <select x-model="selectedSemesterFilter" @change="filterKelasList()"
                        class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm appearance-none bg-white">
                        <option value="all">Semua Semester</option>
                        <template x-for="sem in semesterOptions" :key="sem">
                            <option :value="String(sem)" x-text="'Semester ' + sem"></option>
                        </template>
                    </select>
                    <div class="absolute right-0 top-0 h-full px-3 text-gray-500 flex items-center justify-center pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DATA -->
    <div class="mb-8">
        <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-700">Daftar Kelas</h3>
                @if(Auth::user()->hasPermissionAccess('management_data', 'Kelas', 'edit'))
                <button
                    @click="
                        showGenerateModal = true;
                        selectedGenerateProdi = '';
                        selectedGenerateProdiName = 'Semua Prodi';
                    "
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Generate Kelas
                </button>
                @endif
            </div>

            <div class="overflow-hidden rounded-lg border border-[#DBDBDB]">
                <div class="overflow-x-auto w-full">
                    <table class="min-w-full bg-white">
                         <thead class="bg-teal-700 text-white">
                            <tr>
                                <th class="w-16 text-left py-2 px-3 uppercase font-semibold text-xs">No</th>
                                <th class="text-left py-2 px-3 uppercase font-semibold text-xs cursor-pointer hover:bg-teal-800 select-none group relative" onclick="sortTable(this, 1, 'string')">
                                    <div class="flex items-center justify-between">
                                        <span>Prodi</span>
                                        <span class="sort-icon text-teal-300 group-hover:text-white transition-colors duration-200 ml-2">⇅</span>
                                    </div>
                                </th>
                                <th class="text-left py-2 px-3 uppercase font-semibold text-xs cursor-pointer hover:bg-teal-800 select-none group" onclick="sortTable(this, 2, 'string')">
                                    <div class="flex items-center justify-between">
                                        <span>Nama Kelas</span>
                                        <span class="sort-icon text-teal-300 group-hover:text-white transition-colors duration-200 ml-2">⇅</span>
                                    </div>
                                </th>
                                <th class="text-left py-2 px-3 uppercase font-semibold text-xs cursor-pointer hover:bg-teal-800 select-none group" onclick="sortTable(this, 3, 'number')">
                                    <div class="flex items-center justify-between">
                                        <span>Semester</span>
                                        <span class="sort-icon text-teal-300 group-hover:text-white transition-colors duration-200 ml-2">⇅</span>
                                    </div>
                                </th>
                                <th class="text-left py-2 px-3 uppercase font-semibold text-xs cursor-pointer hover:bg-teal-800 select-none group" onclick="sortTable(this, 4, 'string')">
                                    <div class="flex items-center justify-between">
                                        <span>Kode MK</span>
                                        <span class="sort-icon text-teal-300 group-hover:text-white transition-colors duration-200 ml-2">⇅</span>
                                    </div>
                                </th>
                                <th class="text-left py-2 px-3 uppercase font-semibold text-xs cursor-pointer hover:bg-teal-800 select-none group" onclick="sortTable(this, 5, 'string')">
                                    <div class="flex items-center justify-between">
                                        <span>Mata Kuliah</span>
                                        <span class="sort-icon text-teal-300 group-hover:text-white transition-colors duration-200 ml-2">⇅</span>
                                    </div>
                                </th>
                                <th class="text-left py-2 px-3 uppercase font-semibold text-xs cursor-pointer hover:bg-teal-800 select-none group" onclick="sortTable(this, 6, 'number')">
                                    <div class="flex items-center justify-between">
                                        <span>SKS</span>
                                        <span class="sort-icon text-teal-300 group-hover:text-white transition-colors duration-200 ml-2">⇅</span>
                                    </div>
                                </th>
                                <th class="text-left py-2 px-3 uppercase font-semibold text-xs cursor-pointer hover:bg-teal-800 select-none group" onclick="sortTable(this, 7, 'number')">
                                    <div class="flex items-center justify-between">
                                        <span>Kapasitas</span>
                                        <span class="sort-icon text-teal-300 group-hover:text-white transition-colors duration-200 ml-2">⇅</span>
                                    </div>
                                </th>
                                @if(Auth::user()->hasPermissionAccess('management_data', 'Kelas', 'edit'))
                                <th class="w-48 text-left py-2 px-3 uppercase font-semibold text-xs">Aksi</th>
                                @endif
                            </tr>
                        </thead>

                        <tbody class="text-gray-700" id="kelasTableBody">

                            @forelse ($kelas as $k)
                            <tr class="kelas-row border-b border-[#DBDBDB] hover:bg-gray-50" 
                                data-prodi="{{ $k->prodi->nama_prodi ?? 'Tanpa Prodi' }}" 
                                data-nama-kelas-asli="{{ $k->nama_kelas }}"
                                data-nama-kelas="{{ strtolower($k->nama_kelas) }}" 
                                data-semester="{{ $k->semester ?? '' }}"
                                data-kode-mk="{{ strtolower($k->matakuliah->kode_matkul ?? '') }}" 
                                data-nama-mk="{{ strtolower($k->matakuliah->nama_matkul ?? '') }}">
                                <td class="text-left py-2 px-3 text-sm">
                                    <div class="flex items-center space-x-3">
                                        <input
                                            type="checkbox"
                                            value="{{ $k->id_kelas }}"
                                            class="checkbox-kelas rounded border-gray-300 text-red-600 focus:ring-red-500">
                                        <span class="row-number-span">{{ $loop->iteration }}</span>
                                    </div>
                                </td>
                                <td class="text-left py-2 px-3 text-sm font-medium">
                                    {{ $k->prodi->nama_prodi ?? 'Tanpa Prodi' }}
                                </td>
                                <td class="text-left py-2 px-3 text-sm font-medium">
                                    {{ $k->nama_kelas }}
                                </td>
                                <td class="text-left py-2 px-3 text-sm">
                                    {{ $k->semester ?? '-' }}
                                </td>
                                <td class="text-left py-2 px-3 text-sm">
                                    {{ $k->matakuliah->kode_matkul ?? '-' }}
                                </td>
                                <td class="text-left py-2 px-3 text-sm">
                                    {{ $k->matakuliah->nama_matkul ?? '-' }}
                                </td>
                                <td class="text-left py-2 px-3 text-sm">
                                    {{ $k->matakuliah->sks ?? '-' }}
                                </td>
                                <td class="text-left py-2 px-3 text-sm">
                                    {{ $k->kapasitas }}
                                </td>
                                @if(Auth::user()->hasPermissionAccess('management_data', 'Kelas', 'edit'))
                                <td class="text-left py-2 px-3 text-sm">
                                    <div class="flex space-x-2">
                                        <button
                                            @click="openEdit(@js($k))"
                                            class="flex items-center justify-center bg-yellow-400 text-gray-900 px-3 py-1 rounded-md hover:bg-yellow-500 text-xs font-medium">
                                            Edit
                                        </button>
                                        <button
                                            onclick="confirmDelete('{{ route('kelas.destroy', $k->id_kelas) }}')"
                                            class="flex items-center justify-center bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-700 text-xs font-medium">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-gray-500">
                                    Data kelas belum tersedia.
                                </td>
                            </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>
            </div>

            <!-- BULK ACTION -->
            @if(Auth::user()->hasPermissionAccess('management_data', 'Kelas', 'edit'))
            <div class="flex items-center justify-between mt-4">
                <div class="flex items-center space-x-2">
                    <button
                        type="button"
                        @click="
                            let checkboxes = document.querySelectorAll('.checkbox-kelas');
                            let allChecked = [...checkboxes].every(cb => cb.checked);
                            checkboxes.forEach(cb => { cb.checked = !allChecked; });
                        "
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        Pilih Semua
                    </button>
                    <button
                        type="button"
                        onclick="deleteSelectedAll()"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Hapus Semua
                    </button>
                </div>
                <span class="text-sm text-gray-500">
                    Centang data yang ingin dihapus
                </span>
            </div>
            @endif
        </div>
    </div>

</main>

<!-- MODAL TAMBAH -->
<div x-show="showAddModal"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">

    <div class="flex items-center justify-center min-h-screen px-4">

        <div class="bg-white rounded-lg shadow-xl sm:max-w-lg sm:w-full p-6">

            <div class="flex justify-between items-center border-b pb-3">
                <h2 class="text-xl font-bold text-teal-800">Tambah Kelas Baru</h2>
                <button @click="showAddModal = false">✕</button>
            </div>

            <form action="{{ route('kelas.store') }}"
                method="POST"
                class="mt-4 space-y-4">

                @csrf

                <input type="hidden"
                    name="id_tahunakademik"
                    value="{{ $tahunAkademik->id_tahunakademik }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- Nama Kelas -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kelas</label>
                        <input type="text"
                            name="nama_kelas"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            required>
                    </div>

                    <!-- Semester -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                        <select name="semester"
                                x-model="selectedSemester"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                required>
                            <option value="">-- Pilih Semester --</option>
                            @for ($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Prodi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Program Studi</label>
                        <select
                            name="id_prodi"
                            x-model="selectedProdi"
                            @change="selectMatkul(document.querySelector('select[name=id_matakuliah]')?.value || '')"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="">Semua Program Studi (Umum)</option>
                            @foreach ($prodis as $prodi)
                                <option value="{{ $prodi->id_prodi }}">{{ $prodi->nama_prodi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kurikulum -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kurikulum</label>
                        <select
                            name="id_kurikulum"
                            x-model="selectedKurikulum"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="">-- Pilih Kurikulum --</option>
                            @foreach ($kurikulums as $kurikulum)
                                <option value="{{ $kurikulum->id_kurikulum }}">{{ $kurikulum->nama_kurikulum }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Mata Kuliah -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mata Kuliah</label>
                        <select
                            name="id_matakuliah"
                            @change="selectMatkul($event.target.value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            required>
                            <option value="">-- Pilih Mata Kuliah --</option>
                            <template
                                x-for="matkul in mataKuliahs.filter(m => eq(selectedProdi, m.id_prodi) && eq(selectedSemester, m.semester) && eq(selectedKurikulum, m.id_kurikulum))"
                                :key="matkul.id_matakuliah">
                                <option
                                    :value="matkul.id_matakuliah"
                                    x-text="`${matkul.kode_matkul} - ${matkul.nama_matkul}`">
                                </option>
                            </template>
                        </select>
                    </div>

                    <!-- SKS otomatis -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKS</label>
                        <input type="text"
                            x-model="selectedSks"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100"
                            readonly>
                    </div>

                    <!-- Kapasitas -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas</label>
                        <input type="number"
                            name="kapasitas"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            required>
                    </div>

                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button"
                            @click="showAddModal = false"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                        Simpan
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>
{{-- AKHIR MODAL TAMBAH --}}

<!-- MODAL EDIT -->
<div x-show="showEditModal"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">

    <div class="flex items-center justify-center min-h-screen px-4">

        <div class="bg-white rounded-lg shadow-xl sm:max-w-lg sm:w-full p-6">

            <div class="flex justify-between items-center border-b pb-3">
                <h2 class="text-xl font-bold text-teal-800">Edit Kelas</h2>
                <button @click="showEditModal = false">✕</button>
            </div>

            <form :action="`/management/kelas/${editData.id_kelas}`"
                  method="POST"
                  class="mt-4 space-y-4">

                @csrf
                @method('PUT')

                <input type="hidden"
                       name="id_tahunakademik"
                       :value="editData.id_tahunakademik">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- Nama Kelas -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kelas</label>
                        <input type="text"
                               name="nama_kelas"
                               x-model="editData.nama_kelas"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                               required>
                    </div>

                    <!-- Semester -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                        <select
                            name="semester"
                            x-model="editData.semester"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            required>
                            <option value="">-- Pilih Semester --</option>
                            @for ($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Prodi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Program Studi</label>
                        <select
                            name="id_prodi"
                            x-model="editData.id_prodi"
                            @change="selectEditMatkul(editData.id_matakuliah)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="">Semua Program Studi (Umum)</option>
                            @foreach ($prodis as $prodi)
                                <option value="{{ $prodi->id_prodi }}">{{ $prodi->nama_prodi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kurikulum -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kurikulum</label>
                        <select
                            x-model="editData.id_kurikulum"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="">-- Pilih Kurikulum --</option>
                            @foreach ($kurikulums as $kurikulum)
                                <option value="{{ $kurikulum->id_kurikulum }}">{{ $kurikulum->nama_kurikulum }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Mata Kuliah -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mata Kuliah</label>
                        <select
                            name="id_matakuliah"
                            x-model="editData.id_matakuliah"
                            @change="selectEditMatkul($event.target.value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            required>
                            <option value="">-- Pilih Mata Kuliah --</option>
                            <template
                                x-for="matkul in mataKuliahs.filter(m => eq(m.id_matakuliah, currentEditMatkul) || (eq(editData.id_prodi, m.id_prodi) && eq(editData.semester, m.semester) && eq(editData.id_kurikulum, m.id_kurikulum)))"
                                :key="matkul.id_matakuliah">
                                <option
                                    :value="matkul.id_matakuliah"
                                    x-text="`${matkul.kode_matkul} - ${matkul.nama_matkul}`">
                                </option>
                            </template>
                        </select>
                    </div>

                    <!-- SKS -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKS</label>
                        <input type="text"
                               x-model="editData.sks"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100"
                               readonly>
                    </div>

                    <!-- Kapasitas -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas</label>
                        <input type="number"
                               name="kapasitas"
                               x-model="editData.kapasitas"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                               required>
                    </div>

                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button"
                            @click="showEditModal = false"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                        Simpan Perubahan
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>
<!-- AKHIR MODAL EDIT -->

<!-- MODAL GENERATE -->
<div x-show="showGenerateModal"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display:none;">

    <div class="flex items-center justify-center min-h-screen px-4">

        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6">

            <div class="flex justify-between items-center border-b pb-3">
                <div>
                    <h2 class="text-xl font-bold text-teal-800">Generate Kelas</h2>
                    <p class="text-sm text-gray-500 mt-1" x-text="selectedGenerateProdiName"></p>
                </div>
                <button @click="showGenerateModal = false">✕</button>
            </div>

            <form action="{{ route('kelas.generate') }}"
                  method="POST"
                  class="mt-6 space-y-4">

                @csrf

                <input type="hidden" name="id_prodi" :value="selectedGenerateProdi">
                <input type="hidden" name="id_tahunakademik" value="{{ $tahunAkademik->id_tahunakademik }}">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kurikulum</label>
                    <select name="id_kurikulum"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            required>
                        <option value="">-- Pilih Kurikulum --</option>
                        @foreach ($kurikulums as $kurikulum)
                            <option value="{{ $kurikulum->id_kurikulum }}">{{ $kurikulum->nama_kurikulum }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                    <select name="semester"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            required>
                        <option value="">-- Pilih Semester --</option>
                        <template x-for="semester in semesterOptions">
                            <option :value="semester" x-text="'Semester ' + semester"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Mahasiswa</label>
                    <input type="number"
                           name="jumlah_mahasiswa"
                           min="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                           required>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" @click="showGenerateModal = false" class="px-4 py-2 bg-gray-200 rounded-lg">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Generate
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>
<!-- AKHIR MODAL GENERATE -->

<x-delete-confirm-popup />

<script>

function deleteSelectedAll()
{
    let checked = document.querySelectorAll(`.checkbox-kelas:checked`);

    if (checked.length === 0) {
        alert('Pilih minimal 1 kelas.');
        return;
    }

    if (!confirm('Yakin ingin menghapus kelas terpilih?')) {
        return;
    }

    let ids = [];
    checked.forEach(item => { ids.push(item.value); });

    document.getElementById('bulkDeleteIds').value = ids.join(',');
    document.getElementById('bulkDeleteForm').submit();
}

</script>

<form id="bulkDeleteForm"
      action="{{ route('kelas.bulk-delete') }}"
      method="POST"
      style="display:none;">
    @csrf
    <input type="hidden" name="ids" id="bulkDeleteIds">
</form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.querySelector('table');
            if (table) {
                const headers = table.querySelectorAll('thead th');
                const tbody = table.querySelector('tbody');
                const originalRows = Array.from(tbody.querySelectorAll('tr'));

                if (originalRows.length > 0 && originalRows[0].cells.length > 1) {
                    let currentSortColumn = -1;
                    let isAscending = true;
                    let activeFilters = {
                        prodi: 'all',
                        namaKelas: 'all',
                        semester: 'all'
                    };

                    // Function to apply filters
                    window.applyAllFilters = function(alpineState = null) {
                        const search = alpineState ? alpineState.searchTerm.toLowerCase() : (localStorage.getItem('kelas_searchTerm') || '').toLowerCase();
                        
                        // Sync filter states if called from Alpine
                        if (alpineState) {
                            if (alpineState.selectedProdiFilter) activeFilters.prodi = alpineState.selectedProdiFilter;
                            if (alpineState.selectedNamaKelasFilter) activeFilters.namaKelas = alpineState.selectedNamaKelasFilter;
                            if (alpineState.selectedSemesterFilter) activeFilters.semester = alpineState.selectedSemesterFilter;
                        } else {
                            activeFilters.prodi = localStorage.getItem('kelas_selectedProdiFilter') || 'all';
                            activeFilters.namaKelas = localStorage.getItem('kelas_selectedNamaKelasFilter') || 'all';
                            activeFilters.semester = localStorage.getItem('kelas_selectedSemesterFilter') || 'all';
                        }

                        const rows = Array.from(tbody.querySelectorAll('tr.kelas-row'));
                        let visibleIndex = 1;
                        const pageOffset = 0;
                        
                        rows.forEach(row => {
                            const rowProdi = row.dataset.prodi || '';
                            const matchesProdi = activeFilters.prodi === 'all' || rowProdi === activeFilters.prodi;
                            
                            const rowNamaKelas = row.dataset.namaKelasAsli || '';
                            const matchesNamaKelas = activeFilters.namaKelas === 'all' || rowNamaKelas === activeFilters.namaKelas;
                            
                            const rowSemester = row.dataset.semester || '';
                            const matchesSemester = activeFilters.semester === 'all' || rowSemester === activeFilters.semester;
                            
                            const namaKelas = row.dataset.namaKelas || '';
                            const kodeMk = row.dataset.kodeMk || '';
                            const namaMk = row.dataset.namaMk || '';
                            
                            const matchesSearch = search === '' || 
                                                  namaKelas.includes(search) || 
                                                  kodeMk.includes(search) ||
                                                  namaMk.includes(search);
                            
                            const matches = matchesProdi && matchesNamaKelas && matchesSemester && matchesSearch;
                            
                            row.style.display = matches ? '' : 'none';
                            
                            if (matches) {
                                const noCell = row.cells[0].querySelector('.row-number-span');
                                if (noCell) {
                                    noCell.textContent = pageOffset + visibleIndex;
                                    visibleIndex++;
                                }
                            }
                        });
                    };

                    // Helper to close all popups
                    function closeAllPopups() {
                        const popups = document.querySelectorAll('.header-popup-menu');
                        popups.forEach(p => p.remove());
                    }

                    // Reset sorting to original order
                    function resetTableSort() {
                        currentSortColumn = -1;
                        isAscending = true;
                        
                        // Remove all sort classes and text bolding from headers
                        headers.forEach((h, index) => {
                            if (index === 0 || index === headers.length - 1) return; // skip No, Aksi
                            const icon = h.querySelector('.sort-icon');
                            if (icon) {
                                icon.innerHTML = '⇅';
                                icon.classList.remove('text-amber-400');
                                icon.classList.add('text-teal-300');
                            }
                        });

                        // Empty tbody and append original rows
                        tbody.innerHTML = '';
                        originalRows.forEach(row => tbody.appendChild(row));
                        
                        // Re-apply filters to update numbering
                        window.applyAllFilters();
                    }

                    // Main Sort Function
                    window.sortTable = function(headerElement, columnIndex, type = 'string') {
                        // Close any open popups
                        closeAllPopups();
                        
                        // Determine if we are changing columns or just toggling direction
                        if (currentSortColumn === columnIndex) {
                            if (isAscending) {
                                isAscending = false; // Second click: Descending
                            } else {
                                // Third click: Reset
                                resetTableSort();
                                return;
                            }
                        } else {
                            currentSortColumn = columnIndex;
                            isAscending = true; // First click: Ascending
                        }

                        // Update styling for all headers
                        headers.forEach((h, index) => {
                            if (index === 0 || index === headers.length - 1) return; // skip No, Aksi
                            if (index === columnIndex) {
                                const icon = h.querySelector('.sort-icon');
                                if (icon) {
                                    icon.innerHTML = isAscending ? '▲' : '▼';
                                    icon.classList.remove('text-teal-300');
                                    icon.classList.add('text-amber-400');
                                }
                            } else {
                                const icon = h.querySelector('.sort-icon');
                                if (icon) {
                                    icon.innerHTML = '⇅';
                                    icon.classList.remove('text-amber-400');
                                    icon.classList.add('text-teal-300');
                                }
                            }
                        });

                        const rows = Array.from(tbody.querySelectorAll('tr.kelas-row'));

                        rows.sort((a, b) => {
                            let valA = a.cells[columnIndex].textContent.trim();
                            let valB = b.cells[columnIndex].textContent.trim();

                            if (type === 'number') {
                                valA = parseFloat(valA) || 0;
                                valB = parseFloat(valB) || 0;
                                return isAscending ? valA - valB : valB - valA;
                            }

                            // String sorting
                            return isAscending 
                                ? valA.localeCompare(valB, 'id', { sensitivity: 'base' })
                                : valB.localeCompare(valA, 'id', { sensitivity: 'base' });
                        });

                        // Reorder the DOM
                        tbody.innerHTML = '';
                        rows.forEach(row => tbody.appendChild(row));

                        // Re-apply filters to update row numbering based on current visibility
                        window.applyAllFilters();
                    };

                    // Close popups on click outside
                    document.addEventListener('click', function(event) {
                        const popups = document.querySelectorAll('.header-popup-menu');
                        popups.forEach(popup => {
                            if (!popup.contains(event.target)) {
                                popup.remove();
                            }
                        });
                    });

                    // Initial application of numbering and filters
                    window.applyAllFilters();
                }
            }
        });
    </script>
</body>
</html>