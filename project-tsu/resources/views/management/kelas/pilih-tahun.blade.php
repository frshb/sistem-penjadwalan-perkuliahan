<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Tahun Akademik | Data Kelas</title>

    <meta name="description" content="Pilih Tahun Akademik Sistem Penjadwalan Perkuliahan">

    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    x-data="{
        sidebarOpen: true,
        showModalTambah: false,
        showModalHapus: false,
        showModalEdit: false,
        tahunHapus: null,
        namaHapus: '',
        statusFilter: 'aktif',
        activeCount: {{ $tahunAkademiks->where('status_aktif', 1)->count() }},
        inactiveCount: {{ $tahunAkademiks->where('status_aktif', 0)->count() }},
        totalCount: {{ $tahunAkademiks->count() }},
        editData: {
            id: null,
            nama_tahunakademik: '',
            tahun_ajaran: '',
            status_aktif: 1
        }
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

                <div class="flex flex-col">
                    <div class="w-2 h-5 bg-teal-600 rounded-tl-md"></div>
                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                </div>

                <h1 class="text-2xl font-bold text-gray-800 ml-3">
                    Management Kelas
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
                    Silakan pilih tahun akademik untuk melihat data kelas.
                </p>
            </div>

            <button
                @click="showModalTambah = true"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow-md transition duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Tahun Akademik
            </button>

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

                <div
                    x-show="statusFilter === 'semua' || (statusFilter === 'aktif' && {{ $tahun->status_aktif ? 1 : 0 }} == 1) || (statusFilter === 'nonaktif' && {{ $tahun->status_aktif ? 0 : 1 }} == 1)"
                    class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300">

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
                        <a href="{{ route('kelas.index', ['tahun' => $tahun->id_tahunakademik]) }}"
                           class="w-full inline-flex items-center justify-center px-4 py-3 bg-yellow-500 text-white font-semibold rounded-xl shadow-md hover:bg-yellow-600 transition duration-200">

                            Lihat Data Kelas

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

                        </a>
                        <div class="flex gap-2 mt-2">
                            <!-- Tombol Edit -->
                            <button
                                @click="
                                    showModalEdit = true;
                                    editData.id = {{ $tahun->id_tahunakademik }};
                                    editData.nama_tahunakademik = '{{ $tahun->nama_tahunakademik }}';
                                    editData.tahun_ajaran = '{{ $tahun->tahun_ajaran }}';
                                    editData.status_aktif = {{ $tahun->status_aktif }};
                                "
                                class="w-full mt-2 inline-flex items-center justify-center px-4 py-2.5 border border-teal-200 text-teal-600 font-semibold rounded-xl hover:bg-teal-50 transition duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </button>

                            <!-- Tombol Hapus -->
                            <button
                                @click="showModalHapus = true; tahunHapus = {{ $tahun->id_tahunakademik }}; namaHapus = '{{ $tahun->nama_tahunakademik }}'"
                                class="w-full mt-2 inline-flex items-center justify-center px-4 py-2.5 border border-red-200 text-red-600 font-semibold rounded-xl hover:bg-red-50 transition duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Hapus
                            </button>
                        </div>

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
                            Silakan tambahkan data tahun akademik terlebih dahulu.
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

<!-- ===================== MODAL TAMBAH ===================== -->
<div
    x-show="showModalTambah"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
    @keydown.escape.window="showModalTambah = false"
    @click.self="showModalTambah = false">

    <div
        x-show="showModalTambah"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="bg-white rounded-2xl shadow-2xl w-full max-w-md">

        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-teal-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Tambah Tahun Akademik</h3>
            </div>
            <button @click="showModalTambah = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <form action="{{ route('kelas.tahun-akademik.store') }}" method="POST" class="p-6 space-y-5">
            @csrf

            <!-- Nama Tahun Akademik -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Nama Tahun Akademik
                </label>
                <select
                    name="nama_tahunakademik"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition bg-white"
                    required>
                    <option value="" disabled selected>Pilih Semester</option>
                    <option value="Ganjil">Ganjil</option>
                    <option value="Genap">Genap</option>
                </select>
            </div>

            <!-- Tahun Ajaran -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Tahun Ajaran
                </label>
                <input
                    type="text"
                    name="tahun_ajaran"
                    placeholder="Contoh: 2024/2025"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                    required>
            </div>

            <!-- Status Aktif -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Status
                </label>
                <select
                    name="status_aktif"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition bg-white">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>

            <!-- Footer -->
            <div class="flex gap-3 pt-2">
                <button
                    type="button"
                    @click="showModalTambah = false"
                    class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-600 font-semibold rounded-xl hover:bg-gray-50 transition">
                    Batal
                </button>
                <button
                    type="submit"
                    class="flex-1 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow-md transition">
                    Simpan
                </button>
            </div>

        </form>

    </div>
</div>


<!-- ===================== MODAL HAPUS ===================== -->
<div
    x-show="showModalHapus"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
    @keydown.escape.window="showModalHapus = false"
    @click.self="showModalHapus = false">

    <div
        x-show="showModalHapus"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">

        <!-- Modal Header -->
        <div class="px-6 pt-6 pb-4 text-center">
            <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800">Hapus Tahun Akademik?</h3>
            <p class="text-sm text-gray-500 mt-2">
                Kamu akan menghapus <span class="font-semibold text-gray-700" x-text="namaHapus"></span>.
                Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data kelas terkait.
            </p>
        </div>

        <!-- Footer -->
        <div class="px-6 pb-6 flex gap-3">
            <button
                type="button"
                @click="showModalHapus = false"
                class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-600 font-semibold rounded-xl hover:bg-gray-50 transition">
                Batal
            </button>

            <form :action="`/kelas/tahun-akademik/${tahunHapus}`" method="POST" class="flex-1">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="w-full px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-xl shadow-md transition">
                    Ya, Hapus
                </button>
            </form>
        </div>

    </div>
</div>

<!-- ===================== MODAL EDIT ===================== -->
<div
    x-show="showModalEdit"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
    @keydown.escape.window="showModalEdit = false"
    @click.self="showModalEdit = false">

    <div
        x-show="showModalEdit"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="bg-white rounded-2xl shadow-2xl w-full max-w-md">

        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-teal-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Edit Tahun Akademik</h3>
            </div>
            <button @click="showModalEdit = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <form :action="`/kelas/tahun-akademik/${editData.id}`" method="POST" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <!-- Nama Tahun Akademik -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Nama Tahun Akademik
                </label>
                <select
                    name="nama_tahunakademik"
                    x-model="editData.nama_tahunakademik"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition bg-white"
                    required>
                    <option value="" disabled>Pilih Semester</option>
                    <option value="Ganjil">Ganjil</option>
                    <option value="Genap">Genap</option>
                </select>
            </div>

            <!-- Tahun Ajaran -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Tahun Ajaran
                </label>
                <input
                    type="text"
                    name="tahun_ajaran"
                    x-model="editData.tahun_ajaran"
                    placeholder="Contoh: 2024/2025"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                    required>
            </div>

            <!-- Status Aktif -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Status
                </label>
                <select
                    name="status_aktif"
                    x-model="editData.status_aktif"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition bg-white">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>

            <!-- Footer -->
            <div class="flex gap-3 pt-2">
                <button
                    type="button"
                    @click="showModalEdit = false"
                    class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-600 font-semibold rounded-xl hover:bg-gray-50 transition">
                    Batal
                </button>
                <button
                    type="submit"
                    class="flex-1 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow-md transition">
                    Simpan Perubahan
                </button>
            </div>

        </form>

    </div>
</div>

</body>
</html>
