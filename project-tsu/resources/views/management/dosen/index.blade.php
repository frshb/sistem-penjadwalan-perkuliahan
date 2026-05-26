<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Management Data | Dosen</title>
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
<script>
    window.mataKuliahs = @json($mataKuliahs);
</script>
<body x-data="{
    sidebarOpen: true,
    showAddModal: false,
    showEditModal: false,
    isLoading: true,

    // =========================
    // ADD DOSEN
    // =========================
    addProdi: '',
    addFilterProdi: '',
    addKurikulum: '',
    addSemester: '',
    addSelectedMatkuls: [],

    // =========================
    // EDIT DOSEN
    // =========================
    editNama: '',
    editNuptk: '',
    editNidn: '',
    editProdi: '',
    editFilterProdi: '',
    editKurikulum: '',
    editSemester: '',
    editSelectedMatkuls: [],
    editUrl: '',

    init() {
        setTimeout(() => this.isLoading = false, 500)
    },

    openEditModal(nidn, nuptk, nama, prodi, matkuls = []) {
        this.editNama = nama;
        this.editNuptk = nuptk;
        this.editNidn = nidn;
        this.editProdi = prodi;
        this.editSelectedMatkuls = Array.isArray(matkuls)
            ? matkuls.map(m => String(m).trim())
            : [];
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

    <main id="main-content"
    :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'"
    class="flex-1 min-w-0 p-6 sm:p-10 transition-all duration-300 ease-in-out">

        <!-- Skeleton Loader -->
        <div x-show="isLoading" class="animate-pulse space-y-6">
            <div class="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <div class="flex items-center space-x-3 w-1/3">
                    <div class="w-2 h-8 bg-gray-300 rounded-lg"></div>
                    <div class="w-48 h-6 bg-gray-300 rounded"></div>
                </div>
                <div class="w-32 h-10 bg-gray-300 rounded-full"></div>
            </div>
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 space-y-6">
                <div class="flex flex-col sm:flex-row justify-between gap-4">
                    <div class="w-full sm:w-1/3 h-10 bg-gray-200 rounded-lg"></div>
                    <div class="w-32 h-10 bg-gray-300 rounded-lg"></div>
                </div>
                <div class="border rounded-lg overflow-hidden">
                    <div class="bg-gray-50 h-12 flex items-center px-6 space-x-4 border-b">
                        <div class="w-10 h-4 bg-gray-300 rounded"></div>
                        <div class="w-1/4 h-4 bg-gray-300 rounded"></div>
                        <div class="w-1/4 h-4 bg-gray-300 rounded"></div>
                        <div class="w-1/4 h-4 bg-gray-300 rounded"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div x-show="!isLoading">

            <!-- Page Header -->
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <div class="flex flex-col">
                        <div class="w-2 h-5 bg-teal-700 rounded-tl-md"></div>
                        <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800 ml-3">
                        Management Data{{ !empty($userProdiName) ? ' (' . $userProdiName . ')' : '' }}
                    </h1>
                </div>
                @include('components.header-profile')
            </div>

            <!-- Action Bar -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-6 mb-4">
                <h2 class="text-xl font-bold text-gray-700 mb-4 sm:mb-0">
                    Daftar Dosen
                </h2>
                <div class="flex space-x-2">
                    <a href="{{ route('dosen.export.excel') }}"
                       class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-opacity-75">
                        Export Excel
                    </a>
                    <a href="{{ route('dosen.export.pdf') }}"
                       class="px-5 py-2 bg-red-600 text-white font-semibold rounded-lg shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-75">
                        Export PDF
                    </a>
                    <button @click="showAddModal = true"
                            class="px-5 py-2 bg-yellow-600 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-75">
                        Tambah Dosen
                    </button>
                </div>
            </div>

            <!-- Search -->
            <div class="mb-6">
                <form action="{{ route('dosen.index') }}" method="GET">
                    <div class="relative">
                        <input
                            type="text"
                            name="search"
                            value="{{ $searchTerm ?? '' }}"
                            placeholder="Cari nama dosen..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                        >
                        <button type="submit" class="absolute right-0 top-0 h-full px-4 text-gray-600 hover:text-teal-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            @if ($searchTerm)
                {{-- HASIL PENCARIAN --}}
                <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md border border-transparent">
                    <h3 class="text-xl font-bold text-gray-700 mb-6">
                        Hasil Pencarian untuk: "{{ $searchTerm }}"
                    </h3>

                    <div class="overflow-hidden rounded-lg border border-[#DBDBDB]">
                        <div class="overflow-x-auto w-full">
                            <table class="w-full min-w-[900px] bg-white">
                                <thead class="bg-teal-700 text-white">
                                    <tr>
                                        <th class="w-12 text-left py-2 px-3 uppercase font-semibold text-xs">No</th>
                                        <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Nama Dosen</th>
                                        <th class="text-left py-2 px-3 uppercase font-semibold text-xs">NUPTK</th>
                                        <th class="text-left py-2 px-3 uppercase font-semibold text-xs">NIDN</th>
                                        <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Program Studi</th>
                                        <th class="w-40 text-left py-2 px-3 uppercase font-semibold text-xs">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-700">
                                    @forelse($dosens->flatten() as $i => $dosen)
                                        <tr class="border-b border-[#DBDBDB] hover:bg-gray-50">
                                            <td class="py-2 px-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                                            <td class="py-2 px-3 text-sm font-semibold text-gray-800">{{ $dosen->nama_dosen }}</td>
                                            <td class="py-2 px-3 text-sm font-semibold text-gray-600">{{ $dosen->nuptk }}</td>
                                            <td class="py-2 px-3 text-sm font-semibold text-gray-600">{{ $dosen->nidn }}</td>
                                            <td class="py-2 px-3 text-sm text-gray-600">{{ $dosen->prodi->nama_prodi ?? '-' }}</td>
                                            <td class="py-2 px-3">
                                                <div class="flex space-x-2">
                                                    <button
                                                        @click="openEditModal(
                                                            '{{ $dosen->nidn }}',
                                                            '{{ $dosen->nuptk }}',
                                                            '{{ addslashes($dosen->nama_dosen) }}',
                                                            '{{ $dosen->id_prodi }}',
                                                            {{ json_encode($dosen->mataKuliahs->pluck('kode_matkul')->toArray()) }}
                                                        )"
                                                        class="flex items-center justify-center bg-yellow-400 text-gray-900 px-3 py-1 rounded-md hover:bg-yellow-500 text-xs font-medium transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                        </svg>
                                                        Edit
                                                    </button>
                                                    <button
                                                        @click="confirmDelete('{{ route('dosen.destroy', $dosen->nuptk) }}')"
                                                        class="flex items-center justify-center bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-700 text-xs font-medium transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Hapus
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-6 text-gray-400 italic text-sm">
                                                Dosen tidak ditemukan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            @else
                {{-- TAB PRODI + TABEL PER PRODI --}}
                <div x-data="{ activeTab: 'all' }">

                    {{-- TAB BUTTONS --}}
                    <div class="flex space-x-2 mb-6 border-b border-gray-300 overflow-x-auto">
                        <button
                            @click="activeTab = 'all'"
                            class="prodi-tab px-4 py-2 border-b-2 whitespace-nowrap transition-colors duration-200 text-sm font-medium"
                            :class="activeTab === 'all'
                                ? 'border-teal-600 text-teal-700 font-bold'
                                : 'border-transparent text-gray-600 hover:text-teal-700'">
                            Semua Prodi
                            <span class="ml-1 px-2 py-0.5 rounded-full bg-teal-100 text-teal-700 text-xs">
                                {{ $dosens->flatten()->count() }}
                            </span>
                        </button>

                        @foreach($dosens as $prodiId => $dosenGroup)
                            @php $namaProdi = $dosenGroup->first()->prodi->nama_prodi ?? 'Tanpa Prodi'; @endphp
                            <button
                                @click="activeTab = '{{ Str::slug($namaProdi) }}'"
                                class="prodi-tab px-4 py-2 border-b-2 whitespace-nowrap transition-colors duration-200 text-sm font-medium"
                                :class="activeTab === '{{ Str::slug($namaProdi) }}'
                                    ? 'border-teal-600 text-teal-700 font-bold'
                                    : 'border-transparent text-gray-600 hover:text-teal-700'">
                                {{ $namaProdi }}
                                <span class="ml-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs">
                                    {{ $dosenGroup->count() }}
                                </span>
                            </button>
                        @endforeach
                    </div>

                    {{-- TABEL PER PRODI --}}
                    @forelse($dosens as $prodiId => $dosenGroup)
                        @php $namaProdi = $dosenGroup->first()->prodi->nama_prodi ?? 'Tanpa Prodi'; @endphp

                        <div class="mb-8"
                             x-show="activeTab === 'all' || activeTab === '{{ Str::slug($namaProdi) }}'"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0">

                            <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md">

                                {{-- Prodi Header --}}
                                <div class="flex items-center gap-3 mb-1">
                                    <div class="flex flex-col">
                                        <div class="w-1.5 h-4 bg-teal-600 rounded-tl-sm"></div>
                                        <div class="w-1.5 h-2 bg-yellow-400 rounded-bl-sm"></div>
                                    </div>
                                    <h3 class="text-base font-bold text-gray-800">{{ $namaProdi }}</h3>
                                    <span class="px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 text-xs border border-teal-100">
                                        {{ $dosenGroup->count() }} dosen
                                    </span>
                                </div>
                                @php $firstDosen = $dosenGroup->first(); @endphp
                                @if(isset($firstDosen->prodi->kode_prodi))
                                    <p class="text-sm text-gray-500 mb-6 ml-5">{{ $firstDosen->prodi->kode_prodi }}</p>
                                @else
                                    <div class="mb-4"></div>
                                @endif

                                {{-- Tabel --}}
                                <div class="overflow-hidden rounded-lg border border-[#DBDBDB]">
                                    <div class="overflow-x-auto w-full">
                                        <table class="w-full min-w-[900px] bg-white">
                                            <thead class="bg-teal-700 text-white">
                                                <tr>
                                                    <th class="w-12 text-left py-2 px-3 uppercase font-semibold text-xs">No</th>
                                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Nama Dosen</th>
                                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">NUPTK</th>
                                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">NIDN</th>
                                                    <th class="w-40 text-left py-2 px-3 uppercase font-semibold text-xs">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-700">
                                                @forelse($dosenGroup as $i => $dosen)
                                                    <tr class="border-b border-[#DBDBDB] hover:bg-gray-50">
                                                        <td class="py-2 px-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                                                        <td class="py-2 px-3 text-sm font-semibold text-gray-800">{{ $dosen->nama_dosen }}</td>
                                                        <td class="py-2 px-3 text-sm font-semibold text-gray-600">{{ $dosen->nuptk }}</td>
                                                        <td class="py-2 px-3 text-sm font-semibold text-gray-600">{{ $dosen->nidn }}</td>
                                                        <td class="py-2 px-3">
                                                            <div class="flex space-x-2">
                                                                <button
                                                                    @click="openEditModal(
                                                                        '{{ $dosen->nidn }}',
                                                                        '{{ $dosen->nuptk }}',
                                                                        '{{ addslashes($dosen->nama_dosen) }}',
                                                                        '{{ $dosen->id_prodi }}',
                                                                        {{ json_encode($dosen->mataKuliahs->pluck('kode_matkul')->toArray()) }}
                                                                    )"
                                                                    class="flex items-center bg-yellow-400 text-gray-900 px-3 py-1 rounded-md hover:bg-yellow-500 text-xs font-medium transition-colors">
                                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                                    </svg>
                                                                    Edit
                                                                </button>
                                                                <button
                                                                    @click="confirmDelete('{{ route('dosen.destroy', $dosen->nuptk) }}')"
                                                                    class="flex items-center bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-700 text-xs font-medium transition-colors">
                                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                    </svg>
                                                                    Hapus
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center py-6 text-gray-400 italic text-sm">
                                                            Belum ada dosen di prodi ini.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>

                    @empty
                        <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md">
                            <p class="text-center text-gray-500">
                                Data dosen belum tersedia.
                            </p>
                        </div>
                    @endforelse

                </div>{{-- end x-data activeTab --}}

            @endif

        </div>{{-- end x-show !isLoading --}}

    </main>

    <!-- ===== AWAL MODAL TAMBAH DOSEN ===== -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
            <div x-show="showAddModal" @click="showAddModal = false" class="fixed inset-0 z-40 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                x-show="showAddModal"
                class="relative z-50 inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-auto sm:max-w-3xl sm:w-full"
            >
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                        <h3 class="text-xl font-bold text-teal-800">Tambah Dosen</h3>
                        <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form action="{{ route('dosen.store') }}" method="POST" class="mt-6 space-y-6">
                        @csrf

                        {{-- NAMA --}}
                        <div class="flex items-center space-x-4">
                            <label class="w-1/3 text-lg text-gray-700 font-medium">Nama Dosen :</label>
                            <input type="text" name="nama_dosen" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2" required>
                        </div>

                        {{-- NUPTK --}}
                        <div class="flex items-center space-x-4">
                            <label class="w-1/3 text-lg text-gray-700 font-medium">NUPTK :</label>
                            <input type="text" name="nuptk" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2" required>
                        </div>

                        {{-- NIDN --}}
                        <div class="flex items-center space-x-4">
                            <label class="w-1/3 text-lg text-gray-700 font-medium">NIDN :</label>
                            <input type="text" name="nidn" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2" required>
                        </div>

                        {{-- PRODI --}}
                        <div class="flex items-center space-x-4">
                            <label class="w-1/3 text-lg text-gray-700 font-medium">Program Studi :</label>
                            <select name="id_prodi" x-model="addProdi" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2" required>
                                <option value="">Pilih Prodi</option>
                                @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id_prodi }}">{{ $prodi->nama_prodi }}</option>
                                @endforeach
                            </select>
                        </div>

                        <label class="block text-lg font-medium text-gray-700 mb-2">Mata Kuliah Yang Diampu :</label>

                        {{-- MATKUL --}}
                        <div>
                            <div class="border rounded-lg max-h-64 overflow-y-auto">
                                {{-- FILTER --}}
                                <div class="sticky top-0 bg-white z-10 px-4 pt-4 pb-3 border-b border-gray-200 mb-4">
                                    <div class="flex flex-wrap gap-4 items-end">
                                        <div class="w-40">
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Kurikulum</label>
                                            <select x-model="addKurikulum" class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm">
                                                <option value="">Semua</option>
                                                @foreach($kurikulums as $kurikulum)
                                                    <option value="{{ $kurikulum->id_kurikulum }}">{{ $kurikulum->nama_kurikulum }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="w-32">
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Semester</label>
                                            <select x-model="addSemester" class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm">
                                                <option value="">Semua</option>
                                                @for($i = 1; $i <= 8; $i++)
                                                    <option value="{{ $i }}">Semester {{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="w-48">
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Prodi</label>
                                            <select x-model="addFilterProdi" class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm">
                                                <option value="">Semua Prodi</option>
                                                @foreach($prodis as $prodi)
                                                    <option value="{{ $prodi->id_prodi }}">{{ $prodi->nama_prodi }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- CHECKBOX MATKUL --}}
                                <div class="px-4 pb-4 space-y-2">
                                    <template
                                        x-for="matkul in window.mataKuliahs.filter(m =>
                                            (!addFilterProdi || m.id_prodi == addFilterProdi) &&
                                            (!addKurikulum || m.id_kurikulum == addKurikulum) &&
                                            (!addSemester || m.semester == addSemester)
                                        )"
                                        :key="matkul.kode_matkul"
                                    >
                                        <label class="flex items-start space-x-3 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                            <input
                                                type="checkbox"
                                                :value="String(matkul.kode_matkul).trim()"
                                                x-model="addSelectedMatkuls"
                                                class="mt-1"
                                            >
                                            <div>
                                                <div class="font-medium text-gray-800"><span x-text="matkul.nama_matkul"></span></div>
                                                <div class="text-sm text-gray-500">
                                                    <span x-text="matkul.kode_matkul"></span> • Semester <span x-text="matkul.semester"></span>
                                                </div>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- MATKUL TERPILIH --}}
                        <div class="mt-4" x-show="addSelectedMatkuls.length > 0">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Mata Kuliah Terpilih</label>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="kode in addSelectedMatkuls" :key="kode">
                                    <div>
                                        <input type="hidden" name="mata_kuliah[]" :value="kode">
                                        <div class="bg-teal-100 text-teal-800 px-3 py-2 rounded-lg text-sm flex items-center gap-2">
                                            <span x-text="(() => {
                                                let mk = window.mataKuliahs.find(m => m.kode_matkul == kode);
                                                return mk ? mk.nama_matkul + ' (' + mk.kode_matkul + ')' : kode;
                                            })()"></span>
                                            <button type="button" @click="addSelectedMatkuls = addSelectedMatkuls.filter(m => m != kode)" class="text-red-500 hover:text-red-700 font-bold">×</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- BUTTON --}}
                        <div class="flex justify-end space-x-4 pt-6">
                            <button type="button" @click="showAddModal = false" class="px-5 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg">Batal</button>
                            <button type="submit" class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg">Simpan</button>
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
            <div x-show="showEditModal" @click="showEditModal = false" class="fixed inset-0 z-40 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                x-show="showEditModal"
                class="relative z-50 inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-auto min-w-[650px] max-w-[800px]"
            >
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">

                    {{-- HEADER --}}
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                        <h3 class="text-xl font-bold text-teal-800">Edit Dosen</h3>
                        <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
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
                            <label class="w-1/3 text-lg text-gray-700 font-medium">Nama Dosen :</label>
                            <input type="text" name="nama_dosen" x-model="editNama" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                        </div>

                        {{-- NUPTK --}}
                        <div class="flex items-center space-x-4">
                            <label class="w-1/3 text-lg text-gray-700 font-medium">NUPTK :</label>
                            <input type="text" name="nuptk" x-model="editNuptk" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                        </div>

                        {{-- NIDN --}}
                        <div class="flex items-center space-x-4">
                            <label class="w-1/3 text-lg text-gray-700 font-medium">NIDN :</label>
                            <input type="text" name="nidn" x-model="editNidn" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                        </div>

                        {{-- PRODI --}}
                        <div class="flex items-center space-x-4">
                            <label class="w-1/3 text-lg text-gray-700 font-medium">Program Studi :</label>
                            <select name="id_prodi" x-model="editProdi" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2" required>
                                <option value="">Pilih Prodi</option>
                                @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id_prodi }}">{{ $prodi->nama_prodi }}</option>
                                @endforeach
                            </select>
                        </div>

                        <label class="block text-lg font-medium text-gray-700 mb-2">Mata Kuliah Yang Diampu :</label>

                        {{-- MATKUL --}}
                        <div>
                            <div class="border rounded-lg max-h-64 overflow-y-auto">
                                {{-- FILTER --}}
                                <div class="sticky top-0 bg-white z-10 px-4 pt-4 pb-3 border-b border-gray-200 mb-4">
                                    <div class="flex flex-wrap gap-4 items-end">
                                        <div class="w-40">
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Kurikulum</label>
                                            <select x-model="editKurikulum" class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm">
                                                <option value="">Semua</option>
                                                @foreach($kurikulums as $kurikulum)
                                                    <option value="{{ $kurikulum->id_kurikulum }}">{{ $kurikulum->nama_kurikulum }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="w-32">
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Semester</label>
                                            <select x-model="editSemester" class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm">
                                                <option value="">Semua</option>
                                                @for($i = 1; $i <= 8; $i++)
                                                    <option value="{{ $i }}">Semester {{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="w-48">
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Prodi</label>
                                            <select x-model="editFilterProdi" class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm">
                                                <option value="">Semua Prodi</option>
                                                @foreach($prodis as $prodi)
                                                    <option value="{{ $prodi->id_prodi }}">{{ $prodi->nama_prodi }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- LIST MATKUL --}}
                                <div class="px-4 pb-4 space-y-2">
                                    <template
                                        x-for="matkul in window.mataKuliahs.filter(m =>
                                            (!editFilterProdi || m.id_prodi == editFilterProdi) &&
                                            (!editKurikulum || m.id_kurikulum == editKurikulum) &&
                                            (!editSemester || m.semester == editSemester)
                                        )"
                                        :key="matkul.kode_matkul"
                                    >
                                        <label class="flex items-start space-x-3 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                            <input
                                                type="checkbox"
                                                :value="String(matkul.kode_matkul).trim()"
                                                x-model="editSelectedMatkuls"
                                                class="mt-1"
                                            >
                                            <div>
                                                <div class="font-medium text-gray-800"><span x-text="matkul.nama_matkul"></span></div>
                                                <div class="text-sm text-gray-500">
                                                    <span x-text="matkul.kode_matkul"></span> • Semester <span x-text="matkul.semester"></span>
                                                </div>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- MATKUL TERPILIH --}}
                        <div class="mt-4" x-show="editSelectedMatkuls.length > 0">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Mata Kuliah Terpilih</label>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="kode in editSelectedMatkuls" :key="kode">
                                    <div>
                                        <input type="hidden" name="mata_kuliah[]" :value="kode">
                                        <div class="bg-teal-100 text-teal-800 px-3 py-2 rounded-lg text-sm flex items-center gap-2">
                                            <span x-text="(() => {
                                                let mk = window.mataKuliahs.find(m => m.kode_matkul == kode);
                                                return mk ? mk.nama_matkul + ' (' + mk.kode_matkul + ')' : kode;
                                            })()"></span>
                                            <button type="button" @click="editSelectedMatkuls = editSelectedMatkuls.filter(m => m != kode)" class="text-red-500 hover:text-red-700 font-bold">×</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- BUTTON --}}
                        <div class="flex justify-end space-x-4 pt-6">
                            <button type="button" @click="showEditModal = false" class="px-5 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg">Batal</button>
                            <button type="submit" class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg">Update</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- ===== AKHIR MODAL EDIT DOSEN ===== -->

    @include('components.success-popup')
    @include('components.delete-confirm-popup')

</body>
</html>
