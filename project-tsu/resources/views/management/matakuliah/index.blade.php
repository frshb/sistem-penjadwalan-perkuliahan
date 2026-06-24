<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Data | Mata Kuliah</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">

    <!-- Memuat CSS dan JS dari Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body x-data="{
    allRooms: @js($ruangans),
    filteredRooms: @js($ruangans),

    selectedRooms: [],
    editSelectedRooms: [],

    sidebarOpen: true,
    showAddModal: false,
    showEditModal: false,
    showExportMenu: false,

    semesterType: 'ganjil',
    addSemester: '',

    editProdi: '',
    editNamaMatkul: '',
    editKodeMatkul: '',
    editSks: '',
    editSemester: '',
    editTipe: '',
    editKurikulum: '',
    editKonsentrasi: '',
    editSifat: 'W',
    editRuanganIds: [],

    editUrl: '',

    filterRooms(tipe) {

        if (tipe === 'Teori') {

            this.filteredRooms = this.allRooms.filter(room =>
                room.nama_ruang.startsWith('C')
            );

        } else if (tipe === 'Praktikum') {

            this.filteredRooms = this.allRooms.filter(room =>
                room.nama_ruang.toLowerCase().includes('lab')
            );

        } else if (tipe === 'Teori-Praktik') {

            // tampilkan semua ruangan
            this.filteredRooms = this.allRooms;

        } else {

            this.filteredRooms = [];

        }
    },

    openEditModal(kode, nama, sks, jenis, semester, kurikulum, prodi, ruanganIds, konsentrasi, sifat) {

        this.editProdi = prodi;
        this.editKodeMatkul = kode;
        this.editNamaMatkul = nama;
        this.editSks = sks;

        this.editTipe = jenis.charAt(0).toUpperCase() + jenis.slice(1);

        this.editSemester = semester;
        this.editKurikulum = kurikulum;
        this.editKonsentrasi = konsentrasi || '';
        this.editSifat = sifat || 'W';

        this.editRuanganIds = ruanganIds;
        this.editSelectedRooms = ruanganIds;
        this.editUrl = '{{ route('matakuliah.index') }}/' + kode;

        // FILTER RUANGAN BERDASARKAN TIPE
        this.filterRooms(this.editTipe);

        this.showEditModal = true;
    }
}" class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300 font-sans">

    <!-- Tombol untuk MEMBUKA sidebar (muncul saat sidebar tertutup) -->


    <div class="flex min-h-screen">
       @include('components.sidebar')

        <!-- Konten Utama -->
        <main id="main-content" :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'" class="flex-1 min-w-0 p-6 sm:p-10 transition-all duration-300 ease-in-out bg-gray-50">
            <div>
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <div class="flex flex-col">
                        <div class="w-2 h-5 bg-teal-800 rounded-tl-md"></div>
                        <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800 ml-3">Management Data{{ !empty($userProdiName) ? ' (' . $userProdiName . ')' : '' }}</h1>
                </div>
                @include('components.header-profile')
            </div>


            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-xl mt-6 border border-gray-100">

                <!-- Header Row: Title & Action Buttons -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-6 border-b border-gray-100 gap-4 mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-teal-800">
                            Mata Kuliah
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">
                            Kelola dan filter seluruh daftar mata kuliah program studi dan kurikulum.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Tombol Aksi Tambah Matkul -->
                        <button @click="showAddModal = true" class="inline-flex items-center px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl shadow-sm transition-all duration-200 transform hover:scale-[1.02] text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Tambah Matkul
                        </button>

                        <!-- Tombol Aksi Export -->
                        <div class="relative" @click.away="showExportMenu = false">
                            <button @click="showExportMenu = !showExportMenu" class="inline-flex items-center px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow-sm transition-all duration-200 text-sm">
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
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl z-20 border border-gray-200 p-1" 
                                 style="display: none;">
                                <a href="{{ route('matakuliah.export.excel') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 rounded-lg hover:bg-teal-50 hover:text-teal-800 transition-colors">
                                    <svg class="w-4 h-4 mr-2.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Export Excel
                                </a>
                                <a href="{{ route('matakuliah.export.pdf') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 rounded-lg hover:bg-teal-50 hover:text-teal-800 transition-colors">
                                    <svg class="w-4 h-4 mr-2.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    Export PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search bar (styled exactly like other pages) -->
                <div class="mb-6">
                    <form action="{{ route('matakuliah.index') }}" method="GET">
                        {{-- PERTAHANKAN FILTER YANG SUDAH DIPILIH --}}
                        <input type="hidden" name="semester" value="{{ request('semester') }}">
                        <input type="hidden" name="kurikulum" value="{{ request('kurikulum') }}">
                        <input type="hidden" name="prodi" value="{{ request('prodi') }}">

                        <div class="relative">
                            <input
                                type="text"
                                name="search"
                                value="{{ $searchTerm ?? '' }}"
                                placeholder="Cari mata kuliah, kode MK, atau jenis..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">

                            <button type="submit"
                                    class="absolute right-0 top-0 h-full px-4 text-gray-600 hover:text-teal-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Unified Toolbar: Filters Only -->
                <div class="bg-gray-50/75 p-5 rounded-2xl border border-gray-100 mb-6">
                    <form id="filter-form" action="{{ route('matakuliah.index') }}" method="GET" class="space-y-4">
                        {{-- PERTAHANKAN PENCARIAN YANG SUDAH DIPILIH --}}
                        <input type="hidden" name="search" value="{{ request('search') }}">

                        <div class="flex flex-wrap items-center gap-3">
                            
                            <!-- Semester Ganjil / Genap Segmented Control -->
                            <div class="inline-flex bg-white p-1 rounded-xl border border-gray-200 shadow-sm">
                                <button id="btn-ganjil" type="button" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200">
                                    Ganjil
                                </button>
                                <button id="btn-genap" type="button" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200">
                                    Genap
                                </button>
                            </div>

                            <!-- Select Semester Dropdown -->
                            <div class="relative min-w-[140px]">
                                <select id="select-semester" name="semester" onchange="this.form.submit()" class="w-full pl-3.5 pr-8 py-2.5 bg-white border border-gray-300 rounded-xl shadow-sm text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 appearance-none cursor-pointer">
                                    <!-- Opsi diisi JavaScript -->
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>

                            <!-- Kurikulum Dropdown -->
                            <div class="relative min-w-[160px]">
                                <select name="kurikulum" onchange="this.form.submit()" class="w-full pl-3.5 pr-8 py-2.5 bg-white border border-gray-300 rounded-xl shadow-sm text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 appearance-none cursor-pointer">
                                    <option value="">Semua Kurikulum</option>
                                    @foreach ($kurikulums as $kurikulum)
                                        <option value="{{ $kurikulum->id_kurikulum }}" {{ request('kurikulum') == $kurikulum->id_kurikulum ? 'selected' : '' }}>
                                            {{ $kurikulum->nama_kurikulum }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>

                            <!-- Prodi Dropdown -->
                            <div class="relative min-w-[160px]">
                                <select name="prodi" onchange="this.form.submit()" class="w-full pl-3.5 pr-8 py-2.5 bg-white border border-gray-300 rounded-xl shadow-sm text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 appearance-none cursor-pointer">
                                    <option value="">Semua Prodi</option>
                                    @foreach ($prodis as $prodi)
                                        <option value="{{ $prodi->id_prodi }}" {{ request('prodi') == $prodi->id_prodi ? 'selected' : '' }}>
                                            {{ $prodi->nama_prodi }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                            
                            <!-- Reset Button (Visible only when filters/search are active) -->
                            @if(request()->anyFilled(['search', 'semester', 'kurikulum', 'prodi']))
                                <a href="{{ route('matakuliah.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-xl transition-all duration-200 text-sm whitespace-nowrap">
                                    Reset
                                </a>
                            @endif

                        </div>
                    </form>
                </div>


                <div class="overflow-hidden rounded-lg border border-[#DBDBDB]">
                    <div class="overflow-x-auto w-full">
                        <table class="min-w-full bg-white">
                            <thead class="bg-teal-800 text-white">
                                <tr>
                                    <th class="w-16 text-left py-2 px-5 uppercase font-semibold text-xs tracking-wider">No</th>
                                    <th class="text-left py-2 px-5 uppercase font-semibold text-xs tracking-wider">Mata Kuliah</th>
                                    <th class="text-left py-2 px-5 uppercase font-semibold text-xs tracking-wider">Kode Matkul</th>
                                    <th class="text-left py-2 px-5 uppercase font-semibold text-xs tracking-wider">Jumlah SKS</th>
                                    <th class="text-left py-2 px-5 uppercase font-semibold text-xs tracking-wider">Tipe</th>
                                    <th class="text-left py-2 px-5 uppercase font-semibold text-xs tracking-wider">Semester</th>
                                    <th class="text-left py-2 px-5 uppercase font-semibold text-xs tracking-wider">Kurikulum</th>
                                    <th class="text-left py-2 px-5 uppercase font-semibold text-xs tracking-wider">Program Studi</th>
                                    <th class="text-left py-2 px-5 uppercase font-semibold text-xs tracking-wider">Sifat</th>
                                    <th class="text-left py-2 px-5 uppercase font-semibold text-xs tracking-wider">Konsentrasi</th>
                                    <th class="w-48 text-left py-2 px-5 uppercase font-semibold text-xs tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">

                                @forelse ($matkuls as $index => $matkul)
                                    <tr class="border-b border-[#DBDBDB] hover:bg-gray-50">
                                        <td class="text-left py-4 px-5 text-sm">{{ ($matkuls->currentPage() - 1) * $matkuls->perPage() + $index + 1 }}</td>
                                        <td class="text-left py-4 px-5 text-sm">{{ $matkul->nama_matkul }}</td>
                                        <td class="text-left py-4 px-5 text-sm">{{ $matkul->kode_matkul }}</td>
                                        <td class="text-left py-4 px-5 text-sm">{{ $matkul->sks }}</td>
                                        <td class="text-left py-4 px-5 text-sm">{{ $matkul->jenis }}</td>
                                        <td class="text-left py-4 px-5 text-sm">{{ $matkul->semester }}</td>
                                        <td class="text-left py-4 px-5 text-sm">{{ $matkul->kurikulum->nama_kurikulum ?? '-' }}</td>
                                        <td class="text-left py-4 px-5 text-sm">{{ $matkul->program_studi->nama_prodi ?? '-' }}</td>
                                        <td class="text-left py-4 px-5 text-sm font-semibold">{{ $matkul->sifat ?? 'W' }}</td>
                                        <td class="text-left py-4 px-5 text-sm">{{ in_array($matkul->semester, [5, 6]) ? ($matkul->konsentrasi ?? '-') : '-' }}</td>
                                        <td class="text-left py-4 px-5 text-sm">
                                            <div class="flex space-x-2">
                                                <button
                                                    @click="openEditModal(
                                                        '{{ $matkul->kode_matkul }}',
                                                        '{{ $matkul->nama_matkul }}',
                                                        '{{ $matkul->sks }}',
                                                        '{{ $matkul->jenis }}',
                                                        '{{ $matkul->semester }}',
                                                        '{{ $matkul->id_kurikulum }}',
                                                        '{{ $matkul->id_prodi }}',
                                                        {{ json_encode($matkul->ruangans->pluck('id_ruang')) }},
                                                        '{{ $matkul->konsentrasi }}',
                                                        '{{ $matkul->sifat }}'
                                                    )"
                                                    class="flex items-center justify-center bg-yellow-400 text-gray-900 px-3 py-1 rounded-md hover:bg-yellow-500 text-xs font-medium">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                    Edit
                                                </button>
                                                <button onclick="confirmDelete('{{ route('matakuliah.destroy', $matkul->kode_matkul) }}')" class="flex items-center justify-center bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-700 text-xs font-medium">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-4 text-gray-500">
                                            Data mata kuliah tidak ditemukan.
                                        </td>
                                    </tr>
                                @endForelse

                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ===== AKHIR TABEL ===== -->

                <!-- ===== AWAL PAGINATION LINKS ===== -->
                <div class="mt-6">
                    <!-- $matkuls->links() akan otomatis menyertakan query filter karena ->appends() di Controller -->
                    {{ $matkuls->links() }}
                </div>
                <!-- ===== AKHIR PAGINATION LINKS ===== -->

            </div>
            <!-- End Card Konten Utama -->

            </div>
        </main>
        <!-- ===== End Main Content ===== -->
    </div>

    <!-- Include Popup Komponen -->
    @include('components.success-popup')
    @include('components.delete-confirm-popup')

    <!-- ===== AWAL MODAL TAMBAH MATA KULIAH ===== -->
    <!-- ===== AWAL MODAL TAMBAH MATA KULIAH ===== -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
             <div x-show="showAddModal" @click="showAddModal = false" class="fixed inset-0 z-40 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showAddModal" class="relative z-50 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-teal-800">Tambah Mata Kuliah</h2>
                        <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form action="{{ route('matakuliah.store') }}" method="POST" class="mt-6 space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="id_prodi" class="block text-sm font-medium text-gray-700 mb-1">
                                    Program Studi
                                </label>

                                <select
                                    id="id_prodi"
                                    name="id_prodi"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                                    required
                                >
                                    <option value="">Pilih Prodi</option>

                                    @foreach ($prodis as $prodi)
                                        <option value="{{ $prodi->id_prodi }}">
                                            {{ $prodi->nama_prodi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="nama_matkul" class="block text-sm font-medium text-gray-700 mb-1">Nama Mata Kuliah</label>
                                <input type="text" id="nama_matkul" name="nama_matkul"
                                "class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                            </div>
                            <div>
                                <label for="kode_matkul" class="block text-sm font-medium text-gray-700 mb-1">Kode Matkul</label>
                                <input type="text" id="kode_matkul" name="kode_matkul" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                            </div>
                            <div>
                                <label for="jumlah_sks" class="block text-sm font-medium text-gray-700 mb-1">Jumlah SKS</label>
                                <input type="number" id="jumlah_sks" name="jumlah_sks" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                            </div>
                            <div>
                                <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                                <input type="number" id="semester" name="semester" x-model="addSemester" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                            </div>
                            <div>
                                <label for="tipe" class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                                <select id="tipe" name="tipe" @change="filterRooms( $event.target.value )
                                "class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                                    <option value="">Pilih Tipe</option>
                                    <option value="Teori">Teori</option>
                                    <option value="Praktikum">Praktikum</option>
                                    <option value="Teori-Praktik">Teori-Praktik</option>
                                </select>
                            </div>
                            <div>
                                <label for="id_kurikulum" class="block text-sm font-medium text-gray-700 mb-1">Kurikulum</label>
                                <select id="id_kurikulum" name="id_kurikulum" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                                    <option value="">Pilih Kurikulum</option>
                                    @foreach ($kurikulums as $kurikulum)
                                        <option value="{{ $kurikulum->id_kurikulum }}">{{ $kurikulum->nama_kurikulum }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="sifat" class="block text-sm font-medium text-gray-700 mb-1">Sifat Mata Kuliah</label>
                                <select id="sifat" name="sifat" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                                    <option value="W">Wajib (W)</option>
                                    <option value="P">Pilihan (P)</option>
                                </select>
                            </div>
                            <div x-show="addSemester == 5 || addSemester == 6" x-transition>
                                <label for="konsentrasi" class="block text-sm font-medium text-gray-700 mb-1">Konsentrasi</label>
                                <select id="konsentrasi" name="konsentrasi" :disabled="addSemester != 5 && addSemester != 6" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    <option value="">-- Pilih Konsentrasi (Opsional) --</option>
                                    <option value="AI">AI</option>
                                    <option value="Programming and Software Development">Programming and Software Development</option>
                                    <option value="IT Mobility and Security">IT Mobility and Security</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Ruangan Yang Bisa Digunakan
                                </label>

                                <div class="border border-gray-300 rounded-lg p-3">

                                    <!-- CHECKBOX PILIH SEMUA -->
                                    <div class="mb-3 border-b pb-2">
                                        <label class="flex items-center space-x-2 font-semibold text-teal-700">
                                            <input
                                                type="checkbox"

                                                @change="
                                                    if($event.target.checked){
                                                        selectedRooms = filteredRooms.map(r => r.id_ruang)
                                                    }else{
                                                        selectedRooms = []
                                                    }
                                                "
                                            >

                                            <span>Pilih Semua</span>
                                        </label>
                                    </div>

                                    <!-- LIST RUANGAN -->
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto">

                                        <template x-for="ruangan in filteredRooms" :key="ruangan.id_ruang">

                                            <label class="flex items-center space-x-2">

                                                <input
                                                    type="checkbox"
                                                    name="ruangan_ids[]"
                                                    :value="ruangan.id_ruang"
                                                    x-model="selectedRooms"
                                                    class="rounded border-gray-300 text-teal-600 focus:ring-teal-500"
                                                >

                                                <span
                                                    class="text-sm text-gray-700"
                                                    x-text="ruangan.nama_ruang"
                                                ></span>

                                            </label>

                                        </template>

                                    </div>

                                    <div
                                        x-show="filteredRooms.length === 0"
                                        class="text-sm text-red-500 mt-2"
                                    >
                                        Tidak ada ruangan tersedia
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4 pt-6">
                            <button type="button" @click="showAddModal = false" class="px-5 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- ===== AKHIR MODAL TAMBAH ===== -->

    <!-- ===== AWAL MODAL EDIT MATA KULIAH ===== -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
             <div x-show="showEditModal" @click="showEditModal = false" class="fixed inset-0 z-40 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showEditModal" class="relative z-50 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-teal-800">Edit Mata Kuliah</h2>
                        <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form id="form-edit-matkul" :action="editUrl" method="POST" class="mt-6 space-y-4">
                        @csrf
                        @method('PUT')
                        <!-- Error msg container -->
                        <div id="edit-errors" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm"></div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="edit_id_prodi" class="block text-sm font-medium text-gray-700 mb-1">
                                    Program Studi
                                </label>

                                <select
                                    id="edit_id_prodi"
                                    name="id_prodi"
                                    x-model="editProdi"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                                    required
                                >
                                    <option value="">Pilih Prodi</option>

                                    @foreach ($prodis as $prodi)
                                        <option value="{{ $prodi->id_prodi }}">
                                            {{ $prodi->nama_prodi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="edit_nama_matkul" class="block text-sm font-medium text-gray-700 mb-1">Nama Mata Kuliah</label>
                                <input type="text" id="edit_nama_matkul" name="nama_matkul" x-model="editNamaMatkul" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                            </div>
                            <div>
                                <label for="edit_kode_matkul" class="block text-sm font-medium text-gray-700 mb-1">Kode Matkul</label>
                                <input type="text" id="edit_kode_matkul" name="kode_matkul" x-model="editKodeMatkul" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                            </div>
                            <div>
                                <label for="edit_jumlah_sks" class="block text-sm font-medium text-gray-700 mb-1">Jumlah SKS</label>
                                <input type="number" id="edit_jumlah_sks" name="jumlah_sks" x-model="editSks" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                            </div>
                            <div>
                                <label for="edit_semester" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                                <input type="number" id="edit_semester" name="semester" x-model="editSemester" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                            </div>
                            <div>
                                <label for="edit_tipe" class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                                <select
                                    id="edit_tipe"
                                    name="tipe"
                                    x-model="editTipe"
                                    @change="filterRooms($event.target.value)"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                                    required
                                >
                                    <option value="">Pilih Tipe</option>
                                    <option value="Teori">Teori</option>
                                    <option value="Praktikum">Praktikum</option>
                                    <option value="Teori-Praktik">Teori-Praktik</option>
                                </select>
                            </div>
                            <div>
                                <label for="edit_id_kurikulum" class="block text-sm font-medium text-gray-700 mb-1">Kurikulum</label>
                                <select id="edit_id_kurikulum" name="id_kurikulum" x-model="editKurikulum" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                                    <option value="">Pilih Kurikulum</option>
                                    @foreach ($kurikulums as $kurikulum)
                                        <option value="{{ $kurikulum->id_kurikulum }}">{{ $kurikulum->nama_kurikulum }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="edit_sifat" class="block text-sm font-medium text-gray-700 mb-1">Sifat Mata Kuliah</label>
                                <select id="edit_sifat" name="sifat" x-model="editSifat" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                                    <option value="W">Wajib (W)</option>
                                    <option value="P">Pilihan (P)</option>
                                </select>
                            </div>
                            <div x-show="editSemester == 5 || editSemester == 6" x-transition>
                                <label for="edit_konsentrasi" class="block text-sm font-medium text-gray-700 mb-1">Konsentrasi</label>
                                <select id="edit_konsentrasi" name="konsentrasi" x-model="editKonsentrasi" :disabled="editSemester != 5 && editSemester != 6" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    <option value="">-- Pilih Konsentrasi (Opsional) --</option>
                                    <option value="AI">AI</option>
                                    <option value="Programming and Software Development">Programming and Software Development</option>
                                    <option value="IT Mobility and Security">IT Mobility and Security</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Ruangan Yang Bisa Digunakan
                                </label>

                                <div class="border border-gray-300 rounded-lg p-3">

                                    <!-- CHECKBOX PILIH SEMUA -->
                                    <div class="mb-3 border-b pb-2">
                                        <label class="flex items-center space-x-2 font-semibold text-teal-700">

                                            <input
                                                type="checkbox"

                                                @change="
                                                    if($event.target.checked){
                                                        editSelectedRooms = filteredRooms.map(r => r.id_ruang)
                                                    }else{
                                                        editSelectedRooms = []
                                                    }
                                                "
                                            >

                                            <span>Pilih Semua</span>

                                        </label>
                                    </div>

                                    <!-- LIST RUANGAN -->
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto">

                                        <template x-for="ruangan in filteredRooms" :key="ruangan.id_ruang">

                                            <label class="flex items-center space-x-2">

                                                <input
                                                    type="checkbox"
                                                    name="ruangan_ids[]"
                                                    :value="ruangan.id_ruang"
                                                    x-model="editSelectedRooms"
                                                    class="rounded border-gray-300 text-teal-600 focus:ring-teal-500"
                                                >

                                                <span
                                                    class="text-sm text-gray-700"
                                                    x-text="ruangan.nama_ruang"
                                                ></span>

                                            </label>

                                        </template>

                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4 pt-6">
                            <button type="button" @click="showEditModal = false" class="px-5 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- ===== AKHIR MODAL EDIT ===== -->

    <!-- ===== JavaScript untuk Sidebar & Modal ===== -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Logika Filter Semester (Keep as is for now as it involves DOM manipulation of select options)
            const btnGanjil = document.getElementById('btn-ganjil');
            const btnGenap = document.getElementById('btn-genap');
            const selectSemester = document.getElementById('select-semester');
            const ganjilOptions = [1, 3, 5, 7];
            const genapOptions = [2, 4, 6];

            function updateSemesterOptions(type) {
                selectSemester.innerHTML = '';
                const allOption = document.createElement('option');
                allOption.value = type;
                allOption.text = 'Semua Semester';
                if ( '{{ request('semester') }}' == type || !'{{ request('semester') }}' ) {
                    allOption.selected = true;
                }
                selectSemester.appendChild(allOption);

                const options = (type === 'ganjil') ? ganjilOptions : genapOptions;

                options.forEach(semester => {
                    const option = document.createElement('option');
                    option.value = semester;
                    option.text = 'Semester ' + semester;
                    if ( '{{ request('semester') }}' == semester ) {
                        option.selected = true;
                    }
                    selectSemester.appendChild(option);
                });

                if (type === 'ganjil') {
                    btnGanjil.className = 'px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200 bg-teal-600 text-white shadow-sm';
                    btnGenap.className = 'px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200 text-gray-500 hover:text-gray-800 bg-transparent';
                } else {
                    btnGenap.className = 'px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200 bg-teal-600 text-white shadow-sm';
                    btnGanjil.className = 'px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200 text-gray-500 hover:text-gray-800 bg-transparent';
                }
            }
            if(btnGanjil && btnGenap && selectSemester) {
                const currentSemester = '{{ request('semester') }}';
                const isGenap = genapOptions.includes(parseInt(currentSemester)) || currentSemester === 'genap';
                if (isGenap) {
                    updateSemesterOptions('genap');
                } else {
                    updateSemesterOptions('ganjil');
                }
                btnGanjil.addEventListener('click', () => {
                    updateSemesterOptions('ganjil');
                    selectSemester.form.submit();
                });
                btnGenap.addEventListener('click', () => {
                    updateSemesterOptions('genap');
                    selectSemester.form.submit();
                });
            }

            // Helper for Edit Form population (can be moved to Alpine but keeping simple function for now or refactor completely)
            // Form Submit for Edit (AJAX) - Keeping this as it handles specific error display logic
             function handleFormSubmit(formId, errorId, type) {
                const form = document.getElementById(formId);
                const errorBox = document.getElementById(errorId);
                if(!form) return;
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(form);
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                             // Assuming csrf token is handled by input
                        },
                        body: formData
                    })
                    .then(async response => {
                        const text = await response.text();
                         try {
                            const data = JSON.parse(text);
                            if (!response.ok) throw data;
                            return data;
                        } catch (e) {
                            console.error('Server Error:', text);
                            throw new Error('Terjadi kesalahan server.');
                        }
                    })
                    .then(data => {
                        window.location.reload();
                    })
                    .catch(error => {
                        let errorMessage = 'Terjadi kesalahan.';
                        if (error.errors) errorMessage = Object.values(error.errors).flat().join('<br>');
                        else if (error.message) errorMessage = error.message;
                        errorBox.innerHTML = errorMessage;
                        errorBox.classList.remove('hidden');
                    });
                });
            }
            handleFormSubmit('form-edit-matkul', 'edit-errors', 'edit');

            // ===== LOGIKA URUTKAN & FILTER TABEL (SORTING & FILTERING POPUPS) =====
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
                    function applyProdiFilter() {
                        const rows = Array.from(tbody.querySelectorAll('tr'));
                        rows.forEach(row => {
                            const prodiCell = row.cells[7]?.textContent.trim() || '';
                            const matches = activeProdiFilter === 'all' || prodiCell === activeProdiFilter;
                            row.style.display = matches ? '' : 'none';
                        });

                        // Re-index No column
                        let visibleIndex = 1;
                        const pageOffset = parseInt("{{ ($matkuls->currentPage() - 1) * $matkuls->perPage() }}") || 0;
                        rows.forEach(row => {
                            if (row.style.display !== 'none') {
                                const noCell = row.cells[0];
                                if (noCell) {
                                    noCell.textContent = pageOffset + visibleIndex;
                                    visibleIndex++;
                                }
                            }
                        });

                        // Update Prodi Header Filter Icon
                        const prodiHeader = headers[7];
                        const prodiIcon = prodiHeader.querySelector('.sort-icon');
                        if (prodiIcon) {
                            if (activeProdiFilter === 'all') {
                                prodiIcon.innerHTML = '⇅';
                                prodiIcon.classList.remove('text-amber-400');
                            } else {
                                prodiIcon.innerHTML = '✓';
                                prodiIcon.classList.add('text-amber-400');
                            }
                        }
                    }

                    // Helper to close all popups
                    function closeAllPopups() {
                        const popups = document.querySelectorAll('.header-popup-menu');
                        popups.forEach(p => p.remove());
                    }

                    // Sorting helper function
                    function sortTable(columnIndex, ascending) {
                        const rows = Array.from(tbody.querySelectorAll('tr'));
                        currentSortColumn = columnIndex;
                        isAscending = ascending;

                        // Update icons for all sortable headers
                        headers.forEach((h, idx) => {
                            const icon = h.querySelector('.sort-icon');
                            if (icon) {
                                if (idx === columnIndex) {
                                    icon.innerHTML = isAscending ? '▲' : '▼';
                                    icon.classList.remove('opacity-60', 'text-teal-300');
                                    icon.classList.add('opacity-100', 'text-amber-400');
                                } else {
                                    // Don't reset Prodi filter icon if it is active
                                    if (idx === 7 && activeProdiFilter !== 'all') {
                                        icon.innerHTML = '✓';
                                        icon.classList.add('text-amber-400');
                                    } else {
                                        icon.innerHTML = '⇅';
                                        icon.classList.remove('opacity-100', 'text-amber-400');
                                        icon.classList.add('opacity-60', 'text-teal-300');
                                    }
                                }
                            }
                        });

                        // Sort the actual rows array
                        rows.sort((rowA, rowB) => {
                            const cellA = rowA.cells[columnIndex]?.textContent.trim() || '';
                            const cellB = rowB.cells[columnIndex]?.textContent.trim() || '';

                            const isNumeric = columnIndex === 3 || columnIndex === 5;

                            if (isNumeric) {
                                const numA = parseFloat(cellA) || 0;
                                const numB = parseFloat(cellB) || 0;
                                return isAscending ? numA - numB : numB - numA;
                            } else {
                                return isAscending
                                    ? cellA.localeCompare(cellB, 'id', { sensitivity: 'base' })
                                    : cellB.localeCompare(cellA, 'id', { sensitivity: 'base' });
                            }
                        });

                        // Append sorted rows to tbody
                        rows.forEach(row => tbody.appendChild(row));

                        // Refresh numbers keeping display filter in mind
                        applyProdiFilter();
                    }

                    // Open Body-Level absolute popup
                    function openPopup(anchor, index, contentHtml, onSelect) {
                        closeAllPopups();

                        const popup = document.createElement('div');
                        popup.className = 'header-popup-menu fixed bg-white rounded-xl shadow-2xl border border-gray-200 p-1.5 z-[9999] min-w-[220px] text-sm text-gray-700';
                        popup.innerHTML = contentHtml;
                        document.body.appendChild(popup);

                        // Position popup correctly below the header cell
                        const rect = anchor.getBoundingClientRect();
                        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

                        popup.style.top = `${rect.bottom + scrollTop + 6}px`;
                        
                        if (rect.left + 220 > window.innerWidth) {
                            popup.style.left = `${rect.right + scrollLeft - 220}px`;
                        } else {
                            popup.style.left = `${rect.left + scrollLeft}px`;
                        }

                        // Outside click close handler
                        const outsideClick = (e) => {
                            if (!popup.contains(e.target) && !anchor.contains(e.target)) {
                                popup.remove();
                                document.removeEventListener('click', outsideClick);
                            }
                        };
                        
                        setTimeout(() => {
                            document.addEventListener('click', outsideClick);
                        }, 50);

                        // Bind selection click event
                        popup.querySelectorAll('[data-option]').forEach(btn => {
                            btn.addEventListener('click', () => {
                                onSelect(btn.getAttribute('data-option'));
                                popup.remove();
                                document.removeEventListener('click', outsideClick);
                            });
                        });
                    }

                    // Configure headers
                    headers.forEach((header, index) => {
                        if (index === 0 || index === 10) return;

                        header.classList.add('cursor-pointer', 'select-none', 'hover:bg-teal-900', 'transition-all', 'duration-200');
                        
                        const text = header.textContent.trim();
                        header.innerHTML = `
                            <div class="flex items-center justify-between gap-1.5 py-1.5 px-1">
                                <span>${text}</span>
                                <span class="sort-icon text-teal-300 opacity-60 text-xs transition-all duration-200 ml-1">⇅</span>
                            </div>
                        `;

                        header.addEventListener('click', (e) => {
                            // Prevent any default behavior
                            e.preventDefault();

                            // Semester (index 5) custom popup
                            if (index === 5) {
                                const popupContent = `
                                    <div class="flex flex-col p-1 space-y-1">
                                        <button data-option="asc" class="w-full text-left px-3 py-2 hover:bg-teal-50 hover:text-teal-800 rounded-lg transition-colors flex items-center gap-2.5 font-medium">
                                            <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path></svg>
                                            <span>Lowest to Highest (1 → 8)</span>
                                        </button>
                                        <button data-option="desc" class="w-full text-left px-3 py-2 hover:bg-teal-50 hover:text-teal-800 rounded-lg transition-colors flex items-center gap-2.5 font-medium">
                                            <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"></path></svg>
                                            <span>Highest to Lowest (8 → 1)</span>
                                        </button>
                                    </div>
                                `;
                                openPopup(header, index, popupContent, (option) => {
                                    sortTable(5, option === 'asc');
                                });
                            }
                            // Program Studi (index 7) custom popup
                            else if (index === 7) {
                                // Extract unique Prodi from current rows
                                const prodis = new Set();
                                originalRows.forEach(row => {
                                    const val = row.cells[7]?.textContent.trim() || '';
                                    if (val && val !== '-' && val !== 'Data mata kuliah tidak ditemukan.') {
                                        prodis.add(val);
                                    }
                                });

                                let popupContent = `
                                    <div class="flex flex-col p-1 max-h-[260px] overflow-y-auto space-y-1">
                                        <button data-option="all" class="w-full text-left px-3 py-2 hover:bg-teal-50 hover:text-teal-800 rounded-lg transition-colors flex items-center justify-between font-semibold">
                                            <span>Tampilkan Semua</span>
                                            ${activeProdiFilter === 'all' ? '<span class="text-teal-600 font-bold">✓</span>' : ''}
                                        </button>
                                        <div class="border-t border-gray-100 my-1"></div>
                                `;

                                prodis.forEach(prodi => {
                                    popupContent += `
                                        <button data-option="${prodi}" class="w-full text-left px-3 py-2 hover:bg-teal-50 hover:text-teal-800 rounded-lg transition-colors flex items-center justify-between gap-4 font-medium">
                                            <span>${prodi}</span>
                                            ${activeProdiFilter === prodi ? '<span class="text-teal-600 font-bold">✓</span>' : ''}
                                        </button>
                                    `;
                                });

                                popupContent += `</div>`;

                                openPopup(header, index, popupContent, (option) => {
                                    activeProdiFilter = option;
                                    applyProdiFilter();
                                });
                            }
                            // General columns sorting click direct toggle
                            else {
                                const ascending = currentSortColumn === index ? !isAscending : true;
                                sortTable(index, ascending);
                            }
                        });
                    });
                }
            }
        });
    </script>
</body>
</html>

