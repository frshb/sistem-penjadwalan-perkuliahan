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
                <div class="flex flex-col">
                    <div class="w-2 h-5 bg-teal-800 rounded-tl-md"></div>
                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 ml-3">Management Data{{ !empty($userProdiName) ? ' (' . $userProdiName . ')' : '' }}</h1>
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
                        <button @click="showAddModal = true" class="px-5 py-2.5 bg-yellow-600 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-75">
                            Tambah Dosen
                        </button>
                    </div>
                </div>

                <!-- Search & Filter -->
                <div class="mb-6 bg-gray-50/50 p-4 rounded-xl border border-gray-200">
                    <form action="{{ route('dosen.index') }}" method="GET" class="flex flex-col md:flex-row items-center gap-3">

                        <!-- Search Bar -->
                        <div class="flex-1 w-full relative">
                            <input
                                type="text"
                                name="search"
                                value="{{ $searchTerm ?? '' }}"
                                placeholder="Cari nama dosen..."
                                class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm h-10"
                            >
                            <button
                                type="submit"
                                class="absolute right-0 top-0 h-full px-3.5 text-gray-500 hover:text-teal-700 transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Filter Prodi -->
                        @if (!Auth::user() || !Auth::user()->isKaprodi())
                            <div class="relative w-full md:w-auto md:min-w-[240px]">
                                <select
                                    name="prodi"
                                    onchange="this.form.submit()"
                                    class="w-full pl-3.5 pr-8 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 appearance-none cursor-pointer h-10"
                                >
                                    <option value="">Semua Program Studi</option>
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
                        @endif

                        <!-- Reset Button -->
                        @if(request()->anyFilled(['search', 'prodi']))
                            <a href="{{ route('dosen.index') }}" class="w-full md:w-auto inline-flex items-center justify-center px-5 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition-colors text-sm h-10">
                                Reset
                            </a>
                        @endif

                    </form>
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
                                    <th class="w-16 text-left py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">No</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">Prodi</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">Nama Dosen</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">NUPTK</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">NIDN</th>
                                    <th class="w-48 text-left py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                @forelse ($dosens as $dosen)
                                    <tr class="border-b border-[#DBDBDB] hover:bg-gray-50">
                                        <td class="text-left py-3 px-4 text-sm whitespace-nowrap">{{ ($dosens->currentPage() - 1) * $dosens->perPage() + $loop->iteration }}</td>
                                        <td class="text-left py-3 px-4 text-sm whitespace-nowrap">{{ $dosen->prodi->nama_prodi ?? 'Belum Dipilih' }}</td>
                                        <td class="text-left py-3 px-4 text-sm whitespace-nowrap font-medium text-gray-900">{{ $dosen->nama_dosen }}</td>
                                        <td class="text-left py-3 px-4 text-sm whitespace-nowrap">{{ $dosen->nuptk }}</td>
                                        <td class="text-left py-3 px-4 text-sm whitespace-nowrap">{{ $dosen->nidn }}</td>
                                        <td class="text-left py-3 px-4 text-sm whitespace-nowrap">
                                            <div class="flex space-x-2">
                                                <button
                                                    @click="openEditModal(
                                                        '{{ $dosen->nidn }}',
                                                        '{{ $dosen->nuptk }}',
                                                        '{{ $dosen->nama_dosen }}',
                                                        '{{ $dosen->id_prodi }}'
                                                    )"
                                                    class="flex items-center justify-center bg-yellow-400 text-gray-900 px-3 py-1 rounded-md hover:bg-yellow-500 text-xs font-medium transition-colors">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                    Edit
                                                </button>

                                                <button
                                                    @click="confirmDelete('{{ route('dosen.destroy',[
                                                        $dosen->nuptk,
                                                        'page' => request('page', 1)
                                                    ]) }}')"
                                                    class="flex items-center justify-center bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-700 text-xs font-medium transition-colors">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
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

                <div class="mt-4 flex justify-center">
                    {{ $dosens->links() }}
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

</body>
</html>
