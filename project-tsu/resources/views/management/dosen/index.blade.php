<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Management Data | Dosen</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<script>
    window.mataKuliahs = @json($mataKuliahs);
</script>
<body x-data="{
    sidebarOpen: true,
    showAddModal: false,
    showEditModal: false,
    showExportMenu: false,

    searchTerm: localStorage.getItem('dosen_searchTerm') || '',
    selectedProdi: localStorage.getItem('dosen_selectedProdi') || 'all',

    init() {
        this.$watch('searchTerm', value => localStorage.setItem('dosen_searchTerm', value));
        this.$watch('selectedProdi', value => localStorage.setItem('dosen_selectedProdi', value));
    },

    filterDosens() {
        if (typeof window.applyAllFilters === 'function') {
            window.applyAllFilters(this);
        }
    },

    // =========================
    // EDIT DOSEN
    // =========================

    editNama: '',
    editNuptk: '',
    editNidn: '',
    editProdi: '',

    editUrl: '',

    openEditModal(
        nidn,
        nuptk,
        nama,
        prodi
    ) {
        this.editNama = nama;
        this.editNuptk = nuptk;
        this.editNidn = nidn;
        this.editProdi = prodi;

        this.editUrl = '/management/dosen/' + nuptk;

        this.showEditModal = true;
    },

    confirmDelete(url) {
        if (confirm('Apakah Anda yakin ingin menghapus dosen ini?')) {

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;

            const tokenMeta = document.querySelector('meta[name=\'csrf-token\']');

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = tokenMeta.content;

            form.appendChild(csrfInput);

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';

            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }
    }
}" class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300 font-sans">

    @include('components.sidebar')

    <main id="main-content" :class="sidebarOpen ? 'lg:ml-64' : ''" class="flex-1 p-6 sm:p-10 transition-all duration-300 ease-in-out bg-gray-50">
        <div>
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <button @click="sidebarOpen = !sidebarOpen" class="flex flex-col hover:opacity-80 transition cursor-pointer" title="Toggle Sidebar">
                    <div class="w-2 h-5 bg-teal-800 rounded-tl-md"></div>
                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                </button>
                <h1 class="text-2xl font-bold text-gray-800 ml-3">Manajemen Dosen</h1>
            </div>
            @include('components.header-profile')
        </div>

        <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md mt-6 border border-transparent">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-700 mb-4 sm:mb-0">
                        Daftar Dosen
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
                                <a href="{{ route('dosen.export.excel') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 rounded-md hover:bg-teal-50 hover:text-teal-800 transition-colors">
                                    <svg class="w-4 h-4 mr-2.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Export Excel
                                </a>
                                <a href="{{ route('dosen.export.pdf') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 rounded-md hover:bg-teal-50 hover:text-teal-800 transition-colors">
                                    <svg class="w-4 h-4 mr-2.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    Export PDF
                                </a>
                            </div>
                        </div>
                        @if(Auth::user()->hasPermissionAccess('management_data', 'Dosen', 'edit'))
                        <button @click="showAddModal = true" class="px-5 py-2.5 bg-yellow-600 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-75">
                            Tambah Dosen
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Search & Filter -->
                <div class="mb-6 bg-gray-50/50 p-4 rounded-xl border border-gray-200">
                    <div class="flex flex-col md:flex-row items-center gap-3">

                        <!-- Search Bar -->
                        <div class="flex-1 w-full relative">
                            <input
                                type="text"
                                x-model="searchTerm"
                                @input="filterDosens()"
                                placeholder="Cari nama dosen, NUPTK, atau NIDN..."
                                class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm h-10"
                            >
                            <div class="absolute right-0 top-0 h-full px-3.5 text-gray-500 flex items-center justify-center pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Filter Prodi -->
                        <div class="w-full md:w-64">
                            <select 
                                x-model="selectedProdi" 
                                @change="filterDosens()" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm h-10 bg-white"
                            >
                                <option value="all">Semua Program Studi</option>
                                @foreach ($prodis as $prodi)
                                    <option value="{{ $prodi->nama_prodi }}">{{ $prodi->nama_prodi }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>

                @if ($searchTerm)

                    <h3 class="text-xl font-bold text-gray-700 mb-6">
                        Hasil Pencarian untuk: "{{ $searchTerm }}"
                    </h3>

                @endif

                <div class="overflow-hidden rounded-lg border border-[#DBDBDB]">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1100px] bg-white">
                            <thead class="bg-teal-800 text-white">
                                <tr>
                                    <th class="w-16 text-left py-2 px-4 uppercase font-semibold text-xs whitespace-nowrap">No</th>
                                    <th class="text-left py-2 px-4 uppercase font-semibold text-xs whitespace-nowrap cursor-pointer hover:bg-teal-700 select-none group relative" onclick="sortTable(this, 1, 'string')">
                                        <div class="flex items-center justify-between">
                                            <span>Prodi</span>
                                            <span class="sort-icon text-teal-300 group-hover:text-white transition-colors duration-200 ml-2">⇅</span>
                                        </div>
                                    </th>
                                    <th class="text-left py-2 px-4 uppercase font-semibold text-xs whitespace-nowrap cursor-pointer hover:bg-teal-700 select-none group" onclick="sortTable(this, 2, 'string')">
                                        <div class="flex items-center justify-between">
                                            <span>Nama Dosen</span>
                                            <span class="sort-icon text-teal-300 group-hover:text-white transition-colors duration-200 ml-2">⇅</span>
                                        </div>
                                    </th>
                                    <th class="text-left py-2 px-4 uppercase font-semibold text-xs whitespace-nowrap cursor-pointer hover:bg-teal-700 select-none group" onclick="sortTable(this, 3, 'string')">
                                        <div class="flex items-center justify-between">
                                            <span>NUPTK</span>
                                            <span class="sort-icon text-teal-300 group-hover:text-white transition-colors duration-200 ml-2">⇅</span>
                                        </div>
                                    </th>
                                    <th class="text-left py-2 px-4 uppercase font-semibold text-xs whitespace-nowrap cursor-pointer hover:bg-teal-700 select-none group" onclick="sortTable(this, 4, 'string')">
                                        <div class="flex items-center justify-between">
                                            <span>NIDN</span>
                                            <span class="sort-icon text-teal-300 group-hover:text-white transition-colors duration-200 ml-2">⇅</span>
                                        </div>
                                    </th>
                                    @if(Auth::user()->hasPermissionAccess('management_data', 'Dosen', 'edit'))
                                    <th class="w-48 text-left py-2 px-4 uppercase font-semibold text-xs whitespace-nowrap">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="text-gray-700" id="dosenTableBody">
                                 @forelse ($dosens as $dosen)
                                    <tr class="dosen-row border-b border-[#DBDBDB] hover:bg-gray-50" data-nama="{{ strtolower($dosen->nama_dosen) }}" data-nuptk="{{ strtolower($dosen->nuptk) }}" data-nidn="{{ strtolower($dosen->nidn) }}" data-prodi="{{ $dosen->prodi->nama_prodi ?? 'Belum Dipilih' }}">
                                        <td class="text-left py-3 px-4 text-sm whitespace-nowrap row-number">{{ $loop->iteration }}</td>
                                        <td class="text-left py-3 px-4 text-sm whitespace-nowrap">{{ $dosen->prodi->nama_prodi ?? 'Belum Dipilih' }}</td>
                                        <td class="text-left py-3 px-4 text-sm whitespace-nowrap font-medium text-gray-900">{{ $dosen->nama_dosen }}</td>
                                        <td class="text-left py-3 px-4 text-sm whitespace-nowrap">{{ $dosen->nuptk }}</td>
                                        <td class="text-left py-3 px-4 text-sm whitespace-nowrap">{{ $dosen->nidn }}</td>
                                        @if(Auth::user()->hasPermissionAccess('management_data', 'Dosen', 'edit'))
                                        <td class="text-left py-3 px-4 text-sm whitespace-nowrap">
                                            <div class="flex space-x-2">
                                                <button
                                                    @click="openEditModal(
                                                        '{{ $dosen->nidn }}',
                                                        '{{ $dosen->nuptk }}',
                                                        '{{ addslashes($dosen->nama_dosen) }}',
                                                        '{{ $dosen->id_prodi }}'
                                                    )"
                                                    class="flex items-center justify-center bg-yellow-400 text-gray-900 px-3 py-1 rounded-md hover:bg-yellow-500 text-xs font-medium transition-colors">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                    Edit
                                                </button>
 
                                                <button
                                                    @click="confirmDelete('{{ route('dosen.destroy',[
                                                        $dosen->nuptk
                                                    ]) }}')"
                                                    class="flex items-center justify-center bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-700 text-xs font-medium transition-colors">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-gray-500">
                                            Data dosen belum tersedia.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            </div>
    </main>
    </div>

    <!-- ===== AWAL MODAL TAMBAH DOSEN ===== -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
            <div x-show="showAddModal" @click="showAddModal = false" class="fixed inset-0 z-40 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    x-show="showAddModal"
                    class="relative z-50 inline-block align-bottom
                        bg-white rounded-xl text-left overflow-hidden
                        shadow-xl transform transition-all
                        sm:my-8 sm:align-middle
                        w-full max-w-lg"
                >
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                        <h3 class="text-xl font-bold text-teal-800" id="modal-title">Tambah Dosen</h3>
                        <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                        <form action="{{ route('dosen.store') }}" method="POST" class="mt-6 space-y-6">
                            @csrf

                            {{-- NAMA --}}
                            <div class="flex items-center space-x-4">
                                <label class="w-1/3 text-lg text-gray-700 font-medium">
                                    Nama Dosen :
                                </label>

                                <input
                                    type="text"
                                    name="nama_dosen"
                                    class="w-2/3 border border-gray-300 rounded-lg px-4 py-2"
                                    required
                                >
                            </div>

                            {{-- NUPTK --}}
                            <div class="flex items-center space-x-4">
                                <label class="w-1/3 text-lg text-gray-700 font-medium">
                                    NUPTK :
                                </label>

                                <input
                                    type="text"
                                    name="nuptk"
                                    class="w-2/3 border border-gray-300 rounded-lg px-4 py-2"
                                    required
                                >
                            </div>

                            {{-- NIDN --}}
                            <div class="flex items-center space-x-4">
                                <label class="w-1/3 text-lg text-gray-700 font-medium">
                                    NIDN :
                                </label>

                                <input
                                    type="text"
                                    name="nidn"
                                    class="w-2/3 border border-gray-300 rounded-lg px-4 py-2"
                                    required
                                >
                            </div>

                            {{-- PRODI --}}
                            <div class="flex items-center space-x-4">
                                <label class="w-1/3 text-lg text-gray-700 font-medium">
                                    Program Studi :
                                </label>

                                <select
                                    name="id_prodi"
                                    class="w-2/3 border border-gray-300 rounded-lg px-4 py-2"
                                    required
                                >
                                    <option value="">Pilih Prodi</option>

                                    @foreach($prodis as $prodi)
                                        <option value="{{ $prodi->id_prodi }}">
                                            {{ $prodi->nama_prodi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- BUTTON --}}
                            <div class="flex justify-end space-x-4 pt-6">

                                <button
                                    type="button"
                                    @click="showAddModal = false"
                                    class="px-5 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg"
                                >
                                    Batal
                                </button>

                                <button
                                    type="submit"
                                    class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg"
                                >
                                    Simpan
                                </button>

                            </div>

                        </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== AKHIR MODAL TAMBAH DOSEN ===== -->

<!-- ===== AWAL MODAL EDIT DOSEN ===== -->
<div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

    <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">

        {{-- BACKDROP --}}
        <div
            x-show="showEditModal"
            @click="showEditModal = false"
            class="fixed inset-0 z-40 transition-opacity"
            aria-hidden="true"
        >
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">
            &#8203;
        </span>

        {{-- MODAL --}}
        <div
            x-show="showEditModal"
            class="relative z-50 inline-block align-bottom
                   bg-white rounded-xl text-left overflow-hidden
                   shadow-xl transform transition-all
                   sm:my-8 sm:align-middle
                   w-full max-w-lg"
        >

            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">

                {{-- HEADER --}}
                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-teal-800">
                        Edit Dosen
                    </h3>

                    <button
                        @click="showEditModal = false"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                {{-- FORM --}}
                <form :action="editUrl" method="POST" class="mt-6 space-y-6">

                    @csrf
                    @method('PUT')

                    <input type="hidden" name="page" value="{{ request('page', 1) }}">

                    {{-- NAMA --}}
                    <div class="flex items-center space-x-4">

                        <label class="w-1/3 text-lg text-gray-700 font-medium">
                            Nama Dosen :
                        </label>

                        <input
                            type="text"
                            name="nama_dosen"
                            x-model="editNama"
                            class="w-2/3 border border-gray-300 rounded-lg px-4 py-2
                                   focus:outline-none focus:ring-2 focus:ring-teal-500"
                            required
                        >
                    </div>

                    {{-- NUPTK --}}
                    <div class="flex items-center space-x-4">

                        <label class="w-1/3 text-lg text-gray-700 font-medium">
                            NUPTK :
                        </label>

                        <input
                            type="text"
                            name="nuptk"
                            x-model="editNuptk"
                            class="w-2/3 border border-gray-300 rounded-lg px-4 py-2
                                   focus:outline-none focus:ring-2 focus:ring-teal-500"
                            required
                        >
                    </div>

                    {{-- NIDN --}}
                    <div class="flex items-center space-x-4">

                        <label class="w-1/3 text-lg text-gray-700 font-medium">
                            NIDN :
                        </label>

                        <input
                            type="text"
                            name="nidn"
                            x-model="editNidn"
                            class="w-2/3 border border-gray-300 rounded-lg px-4 py-2
                                   focus:outline-none focus:ring-2 focus:ring-teal-500"
                            required
                        >
                    </div>

                    {{-- PRODI --}}
                    <div class="flex items-center space-x-4">

                        <label class="w-1/3 text-lg text-gray-700 font-medium">
                            Program Studi :
                        </label>

                        <select
                            name="id_prodi"
                            x-model="editProdi"
                            class="w-2/3 border border-gray-300 rounded-lg px-4 py-2"
                            required
                        >
                            <option value="">Pilih Prodi</option>

                            @foreach($prodis as $prodi)
                                <option value="{{ $prodi->id_prodi }}">
                                    {{ $prodi->nama_prodi }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    {{-- BUTTON --}}
                    <div class="flex justify-end space-x-4 pt-6">

                        <button
                            type="button"
                            @click="showEditModal = false"
                            class="px-5 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg"
                        >
                            Update
                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

<!-- ===== AKHIR MODAL EDIT ===== -->

        <!-- Include Popup Sukses -->
    @include('components.success-popup')
    <!-- Include Popup Delete Confirm -->
    @include('components.delete-confirm-popup')

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
                    let activeProdiFilter = 'all';

                    // Function to apply filters
                    window.applyAllFilters = function(alpineState = null) {
                        const search = alpineState ? alpineState.searchTerm.toLowerCase() : (localStorage.getItem('dosen_searchTerm') || '').toLowerCase();
                        const prodi = alpineState ? String(alpineState.selectedProdi) : (localStorage.getItem('dosen_selectedProdi') || 'all');
                        
                        // Sync prodi state with our local variable if called from Alpine
                        if (alpineState && alpineState.selectedProdi) {
                            activeProdiFilter = alpineState.selectedProdi;
                        } else if (!alpineState) {
                            activeProdiFilter = localStorage.getItem('dosen_selectedProdi') || 'all';
                        }

                        const rows = Array.from(tbody.querySelectorAll('tr.dosen-row'));
                        let visibleIndex = 1;
                        const pageOffset = 0;
                        
                        rows.forEach(row => {
                            const rowProdi = row.dataset.prodi || '';
                            let matchesProdi = false;
                            
                            if (activeProdiFilter === 'all') {
                                matchesProdi = true;
                            } else {
                                matchesProdi = rowProdi.toLowerCase().includes(activeProdiFilter.toLowerCase());
                            }
                            
                            const name = row.dataset.nama || '';
                            const nuptk = row.dataset.nuptk || '';
                            const nidn = row.dataset.nidn || '';
                            
                            const matchesSearch = search === '' || 
                                                  name.includes(search) || 
                                                  nuptk.includes(search) ||
                                                  nidn.includes(search);
                            
                            const matches = matchesProdi && matchesSearch;
                            
                            row.style.display = matches ? '' : 'none';
                            
                            if (matches) {
                                const noCell = row.cells[0];
                                if (noCell) {
                                    noCell.textContent = pageOffset + visibleIndex;
                                    visibleIndex++;
                                }
                            }
                        });
                    };
                    const applyProdiFilter = window.applyAllFilters;

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

                        const rows = Array.from(tbody.querySelectorAll('tr.dosen-row'));

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
