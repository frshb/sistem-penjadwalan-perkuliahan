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
    activeTab: 'all',
    editData: {
        id_kelas: null,
        nama_kelas: '',
        id_prodi: '',
        id_tahunakademik: '',
        kapasitas: ''
    },
    isLoading: true,
    init() {
        setTimeout(() => this.isLoading = false, 1500)
    },
    selectedKelas: [],
    selectedProdi: '',
    selectedSemester: '',
    selectedKurikulum: '',
    selectedSks: '',
    mataKuliahs: @js($mataKuliahs),

    filteredDosens: [],

    selectMatkul(kode) {

        let matkul = this.mataKuliahs.find(
            item => item.kode_matkul == kode
        );

        if (matkul) {

            this.selectedSks = matkul.sks;

            this.filteredDosens = matkul.pengampus.map(
                item => item.dosen
            );

        } else {

            this.selectedSks = '';
            this.filteredDosens = [];
        }
    },

    filteredEditDosens: [],
    selectEditMatkul(kode) {

        let matkul = this.mataKuliahs.find(
            item => item.kode_matkul == kode
        );

        if (matkul) {

            this.editData.sks = matkul.sks;

            this.filteredEditDosens = matkul.pengampus.map(
                item => item.dosen
            );

        } else {

            this.editData.sks = '';
            this.filteredEditDosens = [];
        }
    },

    openEdit(kelas) {

        this.editData = {
            ...kelas,
            id_kurikulum: kelas.matakuliah?.id_kurikulum ?? '',
            sks: kelas.matakuliah?.sks ?? ''
        };

        this.selectEditMatkul(kelas.kode_matkul);

        this.showEditModal = true;
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
            <div class="flex flex-col">
                <div class="w-2 h-5 bg-teal-600 rounded-tl-md"></div>
                <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
            </div>

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

            <button @click="showAddModal = true"
                    class="px-5 py-2.5 bg-yellow-600 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-700">
                Tambah Kelas
            </button>
        </div>
    </div>

    <!-- Search -->
    <div class="mb-6">

        <form action="{{ route('kelas.index') }}" method="GET">

            <!-- SIMPAN TAHUN AKADEMIK -->
            <input type="hidden"
                name="tahun"
                value="{{ $tahunAkademik->id_tahunakademik }}">

            <div class="relative">

                <input type="text"
                    name="search"
                    value="{{ $searchTerm ?? '' }}"
                    placeholder="Cari kelas, kode MK, mata kuliah, atau dosen..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500">

                <button type="submit"
                        class="absolute right-0 top-0 h-full px-4 text-gray-600 hover:text-teal-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>

            </div>

        </form>

    </div>

    <!-- TAB PRODI -->
    <div class="flex space-x-2 mb-6 border-b border-gray-300 overflow-x-auto">
        <button
            @click="activeTab = 'all'"
            class="prodi-tab px-4 py-2 border-b-2 whitespace-nowrap transition-colors duration-200"
            :class="activeTab === 'all'
                ? 'border-teal-600 text-teal-700 font-bold'
                : 'border-transparent text-gray-600 hover:text-teal-700'">
            Semua Prodi
        </button>

        @foreach ($kelasByProdi as $namaProdi => $kelasGroup)
            <button
                @click="activeTab = '{{ Str::slug($namaProdi) }}'"
                class="prodi-tab px-4 py-2 border-b-2 whitespace-nowrap transition-colors duration-200"
                :class="activeTab === '{{ Str::slug($namaProdi) }}'
                    ? 'border-teal-600 text-teal-700 font-bold'
                    : 'border-transparent text-gray-600 hover:text-teal-700'">
                {{ $namaProdi }}
            </button>
        @endforeach
    </div>

    <!-- DATA -->
    @foreach ($prodis as $prodi)

    @php
        $kelasGroup = $kelasByProdi[$prodi->nama_prodi] ?? collect();
        $namaProdi = $prodi->nama_prodi;
    @endphp

        <div class="mb-8"
             x-show="activeTab === 'all' || activeTab === '{{ Str::slug($namaProdi) }}'">

            <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md">

                 {{-- GENERATE --}}
                <div class="flex justify-between items-center mb-6">

                    <h3 class="text-xl font-bold text-gray-700">
                        {{ $namaProdi }}
                    </h3>

                    <button
                        @click="
                            showGenerateModal = true;
                            selectedGenerateProdi = '{{ $prodi->id_prodi }}';
                            selectedGenerateProdiName = '{{ $namaProdi }}';
                        "
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">

                        Generate Kelas
                    </button>

                </div>

                <div class="overflow-hidden rounded-lg border border-[#DBDBDB]">

                    <div class="overflow-x-auto w-full">

                        <table class="min-w-full bg-white">

                            <thead class="bg-teal-700 text-white">
                                <tr>
                                    <th class="w-16 text-left py-2 px-3 uppercase font-semibold text-xs">
                                        No
                                    </th>

                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">
                                        Nama Kelas
                                    </th>

                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">
                                        Semester
                                    </th>

                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">
                                        Kode MK
                                    </th>

                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">
                                        Mata Kuliah
                                    </th>

                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">
                                        SKS
                                    </th>

                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">
                                        Kapasitas
                                    </th>

                                    <th class="w-48 text-left py-2 px-3 uppercase font-semibold text-xs">
                                        Aksi
                                    </th>

                                </tr>
                            </thead>

                            <tbody class="text-gray-700">

                                @php

                                    $kelasSorted = $kelasGroup->sortBy(function ($kelas) {

                                        $semester = $kelas->semester ?? 999;

                                        $namaMatkul = $kelas->matakuliah->nama_matkul ?? '';

                                        // Ambil suffix kelas (A/B/C)
                                        preg_match('/-([A-Z])$/', $kelas->nama_kelas, $match);

                                        $suffix = $match[1] ?? 'Z';

                                        return sprintf(
                                            '%02d-%s-%s',
                                            $semester,
                                            $namaMatkul,
                                            $suffix
                                        );

                                    });

                                @endphp

                                @forelse ($kelasSorted as $kelas)

                                <tr class="border-b border-[#DBDBDB] hover:bg-gray-50">
                                    <td class="text-left py-2 px-3 text-sm">

                                        <div class="flex items-center space-x-3">

                                            <input
                                                type="checkbox"
                                                value="{{ $kelas->id_kelas }}"
                                                class="checkbox-kelas-{{ $prodi->id_prodi }} rounded border-gray-300 text-red-600 focus:ring-red-500">

                                            <span>
                                                {{ $loop->iteration }}
                                            </span>

                                        </div>

                                    </td>

                                    <td class="text-left py-2 px-3 text-sm font-medium">
                                        {{ $kelas->nama_kelas }}
                                    </td>

                                    <td class="text-left py-2 px-3 text-sm">
                                        {{ $kelas->semester ?? '-' }}
                                    </td>

                                    <td class="text-left py-2 px-3 text-sm">
                                        {{ $kelas->kode_matkul ?? '-' }}
                                    </td>

                                    <td class="text-left py-2 px-3 text-sm">
                                        {{ $kelas->matakuliah->nama_matkul ?? '-' }}
                                    </td>

                                    <td class="text-left py-2 px-3 text-sm">
                                        {{ $kelas->matakuliah->sks ?? '-' }}
                                    </td>

                                    <td class="text-left py-2 px-3 text-sm">
                                        {{ $kelas->kapasitas }}
                                    </td>

                                    <td class="text-left py-2 px-3 text-sm">

                                        <div class="flex space-x-2">

                                            <button
                                                @click='openEdit(@json($kelas))'
                                                class="flex items-center justify-center bg-yellow-400 text-gray-900 px-3 py-1 rounded-md hover:bg-yellow-500 text-xs font-medium">

                                                Edit
                                            </button>

                                            <button
                                                onclick="confirmDelete('{{ route('kelas.destroy', $kelas->id_kelas) }}')"
                                                class="flex items-center justify-center bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-700 text-xs font-medium">

                                                Hapus
                                            </button>

                                        </div>

                                    </td>

                                </tr>

                                @empty

                                <tr>
                                    <td colspan="10"
                                        class="text-center py-4 text-gray-500">
                                        Data kelas belum tersedia.
                                    </td>
                                </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

                <!-- BULK ACTION -->
                <div class="flex items-center justify-between mt-4">

                    <div class="flex items-center space-x-2">

                        <!-- PILIH SEMUA -->
                        <button
                            type="button"
                            @click="
                                let checkboxes = document.querySelectorAll('.checkbox-kelas-{{ $prodi->id_prodi }}');

                                let allChecked = [...checkboxes].every(cb => cb.checked);

                                checkboxes.forEach(cb => {
                                    cb.checked = !allChecked;
                                });
                            "
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">

                            Pilih Semua
                        </button>

                        <!-- HAPUS SEMUA -->
                        <button
                            type="button"
                            onclick="deleteSelected('{{ $prodi->id_prodi }}')"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">

                            Hapus Semua
                        </button>

                    </div>

                    <span class="text-sm text-gray-500">
                        Centang data yang ingin dihapus
                    </span>

                </div>

            </div>

        </div>

    @endforeach

</main>

<!-- MODAL TAMBAH -->
<div x-show="showAddModal"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">

    <div class="flex items-center justify-center min-h-screen px-4">

        <div class="bg-white rounded-lg shadow-xl sm:max-w-lg sm:w-full p-6">

            <div class="flex justify-between items-center border-b pb-3">
                <h2 class="text-xl font-bold text-teal-800">
                    Tambah Kelas Baru
                </h2>

                <button @click="showAddModal = false">
                    ✕
                </button>
            </div>

                <form action="{{ route('kelas.store') }}"
                    method="POST"
                    class="mt-4 space-y-4">

                    @csrf

                    <!-- hidden tahun akademik -->
                    <input type="hidden"
                        name="id_tahunakademik"
                        value="{{ $tahunAkademik->id_tahunakademik }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- Nama Kelas -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nama Kelas
                            </label>

                            <input type="text"
                                name="nama_kelas"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                required>
                        </div>

                        <!-- Semester -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Semester
                            </label>

                            <select name="semester"
                                    x-model="selectedSemester"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    required>

                                <option value="">-- Pilih Semester --</option>

                                @for ($i = 1; $i <= 8; $i++)
                                    <option value="{{ $i }}">
                                        Semester {{ $i }}
                                    </option>
                                @endfor

                            </select>
                        </div>

                        <!-- Prodi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Program Studi
                            </label>

                            <select
                                name="id_prodi"
                                x-model="selectedProdi"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                required>

                                <option value="">-- Pilih Prodi --</option>

                                @foreach ($prodis as $prodi)
                                    <option value="{{ $prodi->id_prodi }}">
                                        {{ $prodi->nama_prodi }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        <!-- Kurikulum -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Kurikulum
                            </label>

                            <select
                                x-model="selectedKurikulum"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg">

                                <option value="">-- Pilih Kurikulum --</option>

                                @foreach ($kurikulums as $kurikulum)
                                    <option value="{{ $kurikulum->id_kurikulum }}">
                                        {{ $kurikulum->nama_kurikulum }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        <!-- Mata Kuliah -->
                        <div class="md:col-span-2">

                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Mata Kuliah
                            </label>

                            <select
                                name="kode_matkul"
                                @change="selectMatkul($event.target.value)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                required>

                                <option value="">-- Pilih Mata Kuliah --</option>

                                <template
                                    x-for="matkul in mataKuliahs.filter(m =>
                                        (!selectedProdi || m.id_prodi == selectedProdi) &&
                                        (!selectedSemester || m.semester == selectedSemester) &&
                                        (!selectedKurikulum || m.id_kurikulum == selectedKurikulum)
                                    )"
                                    :key="matkul.kode_matkul">

                                    <option
                                        :value="matkul.kode_matkul"
                                        x-text="`${matkul.kode_matkul} - ${matkul.nama_matkul}`">
                                    </option>

                                </template>

                            </select>

                        </div>

                        <!-- SKS otomatis -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                SKS
                            </label>

                            <input type="text"
                                x-model="selectedSks"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100"
                                readonly>
                        </div>

                        <!-- Kapasitas -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Kapasitas
                            </label>

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

                <h2 class="text-xl font-bold text-teal-800">
                    Edit Kelas
                </h2>

                <button @click="showEditModal = false">
                    ✕
                </button>

            </div>

            <form :action="`/management/kelas/${editData.id_kelas}`"
                  method="POST"
                  class="mt-4 space-y-4">

                @csrf
                @method('PUT')

                <!-- hidden tahun -->
                <input type="hidden"
                       name="id_tahunakademik"
                       :value="editData.id_tahunakademik">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- Nama Kelas -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Kelas
                        </label>

                        <input type="text"
                               name="nama_kelas"
                               x-model="editData.nama_kelas"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                               required>
                    </div>

                    <!-- Semester -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Semester
                        </label>

                        <select
                            name="semester"
                            x-model="editData.semester"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            required>

                            <option value="">-- Pilih Semester --</option>

                            @for ($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}">
                                    Semester {{ $i }}
                                </option>
                            @endfor

                        </select>
                    </div>

                    <!-- Prodi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Program Studi
                        </label>

                        <select
                            name="id_prodi"
                            x-model="editData.id_prodi"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            required>

                            <option value="">-- Pilih Prodi --</option>

                            @foreach ($prodis as $prodi)
                                <option value="{{ $prodi->id_prodi }}">
                                    {{ $prodi->nama_prodi }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <!-- Kurikulum -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Kurikulum
                        </label>

                        <select
                            x-model="editData.id_kurikulum"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">

                            <option value="">-- Pilih Kurikulum --</option>

                            @foreach ($kurikulums as $kurikulum)
                                <option value="{{ $kurikulum->id_kurikulum }}">
                                    {{ $kurikulum->nama_kurikulum }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <!-- Mata Kuliah -->
                    <div class="md:col-span-2">

                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Mata Kuliah
                        </label>

                        <select
                            name="kode_matkul"
                            x-model="editData.kode_matkul"
                            @change="selectEditMatkul($event.target.value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            required>

                            <option value="">-- Pilih Mata Kuliah --</option>

                            <template
                                x-for="matkul in mataKuliahs.filter(m =>
                                    (!editData.id_prodi || m.id_prodi == editData.id_prodi) &&
                                    (!editData.semester || m.semester == editData.semester) &&
                                    (!editData.id_kurikulum || m.id_kurikulum == editData.id_kurikulum)
                                )"
                                :key="matkul.kode_matkul">

                                <option
                                    :value="matkul.kode_matkul"
                                    x-text="`${matkul.kode_matkul} - ${matkul.nama_matkul}`">
                                </option>

                            </template>

                        </select>

                    </div>

                    <!-- SKS -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            SKS
                        </label>

                        <input type="text"
                               x-model="editData.sks"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100"
                               readonly>
                    </div>

                    <!-- Kapasitas -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Kapasitas
                        </label>

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

                    <h2 class="text-xl font-bold text-teal-800">
                        Generate Kelas
                    </h2>

                    <p class="text-sm text-gray-500 mt-1"
                       x-text="selectedGenerateProdiName">
                    </p>

                </div>

                <button @click="showGenerateModal = false">
                    ✕
                </button>

            </div>

            <form action="{{ route('kelas.generate') }}"
                  method="POST"
                  class="mt-6 space-y-4">

                @csrf

                <input type="hidden"
                       name="id_prodi"
                       :value="selectedGenerateProdi">

                <input type="hidden"
                       name="id_tahunakademik"
                       value="{{ $tahunAkademik->id_tahunakademik }}">

                <!-- KURIKULUM -->
                <div>

                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Kurikulum
                    </label>

                    <select name="id_kurikulum"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            required>

                        <option value="">
                            -- Pilih Kurikulum --
                        </option>

                        @foreach ($kurikulums as $kurikulum)

                            <option value="{{ $kurikulum->id_kurikulum }}">
                                {{ $kurikulum->nama_kurikulum }}
                            </option>

                        @endforeach

                    </select>

                </div>

                <!-- semester -->
                <div>

                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Semester
                    </label>

                    <select name="semester"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            required>

                        <option value="">
                            -- Pilih Semester --
                        </option>

                        <template x-for="semester in semesterOptions">

                            <option
                                :value="semester"
                                x-text="'Semester ' + semester">
                            </option>

                        </template>

                    </select>

                </div>

                <!-- jumlah mahasiswa -->
                <div>

                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Jumlah Mahasiswa
                    </label>

                    <input type="number"
                           name="jumlah_mahasiswa"
                           min="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                           required>

                </div>

                <div class="flex justify-end space-x-3 pt-4">

                    <button type="button"
                            @click="showGenerateModal = false"
                            class="px-4 py-2 bg-gray-200 rounded-lg">

                        Batal
                    </button>

                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">

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

function deleteSelected(prodiId)
{
    let checked = document.querySelectorAll(
        `.checkbox-kelas-${prodiId}:checked`
    );

    if (checked.length === 0) {
        alert('Pilih minimal 1 kelas.');
        return;
    }

    if (!confirm('Yakin ingin menghapus kelas terpilih?')) {
        return;
    }

    let ids = [];

    checked.forEach(item => {
        ids.push(item.value);
    });

    document.getElementById('bulkDeleteIds').value = ids.join(',');

    document.getElementById('bulkDeleteForm').submit();
}

</script>

<form id="bulkDeleteForm"
      action="{{ route('kelas.bulk-delete') }}"
      method="POST"
      style="display:none;">

    @csrf

    <input type="hidden"
           name="ids"
           id="bulkDeleteIds">

</form>

</body>
</html>
