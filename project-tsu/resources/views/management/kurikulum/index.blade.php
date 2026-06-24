<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Tahun Akademik | Setup Kurikulum</title>
    <meta name="description" content="Pilih Tahun Akademik untuk Setup Kurikulum">
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    x-data="{
        sidebarOpen: true,
        statusFilter: 'aktif',
        showModalTambahKurikulum: false,
        showModalTambahTahun: false,
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

    <div>
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <div class="flex flex-col">
                    <div class="w-2 h-5 bg-teal-600 rounded-tl-md"></div>
                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 ml-3">
                    Setup Kurikulum & TA
                </h1>
            </div>
            @include('components.header-profile')
        </div>

        @if(session('success'))
            <div class="mt-4 p-4 mb-4 text-sm text-green-800 rounded-xl bg-green-50 border border-green-200" role="alert">
                <span class="font-medium">Berhasil!</span> {{ session('success') }}
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="mt-4 p-4 mb-4 text-sm text-red-800 rounded-xl bg-red-50 border border-red-200" role="alert">
                <span class="font-medium">Gagal!</span> {{ session('error') ?? 'Terdapat kesalahan pada input Anda. Silakan coba lagi.' }}
                @if($errors->any())
                    <ul class="mt-1.5 list-disc list-inside">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        <div class="mt-6 mb-8 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-700">
                    Pilih Tahun Akademik
                </h2>
                <p class="text-gray-500 mt-1">
                    Silakan pilih tahun akademik untuk melakukan setup kurikulum dan mapping Dosen pengampu.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button
                    @click="showModalTambahTahun = true"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-xl shadow-md transition duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Tahun Akademik
                </button>
                <button
                    @click="showModalTambahKurikulum = true"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow-md transition duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Master Kurikulum
                </button>
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
                <div
                    x-show="statusFilter === 'semua' || (statusFilter === 'aktif' && {{ $tahun->status_aktif ? 1 : 0 }} == 1) || (statusFilter === 'nonaktif' && {{ $tahun->status_aktif ? 0 : 1 }} == 1)"
                    class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300">

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
                            <form action="{{ route('kurikulum.toggleTahun', $tahun->id_tahunakademik) }}" method="POST" class="flex items-center gap-2 bg-white/15 hover:bg-white/25 transition-colors duration-200 rounded-full px-3 py-1.5 backdrop-blur-sm border border-white/20">
                                @csrf
                                <span class="text-xs font-bold text-white uppercase tracking-wider">
                                    {{ $tahun->status_aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                <button type="submit" 
                                        class="{{ $tahun->status_aktif ? 'bg-green-400' : 'bg-gray-400/80' }} relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-teal-600 shadow-inner" 
                                        role="switch" 
                                        title="Klik untuk mengubah status">
                                    <span aria-hidden="true" 
                                          class="{{ $tahun->status_aktif ? 'translate-x-4' : 'translate-x-0' }} pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"></span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <p class="text-sm text-gray-500">Semester</p>
                                <p class="font-semibold text-gray-700">{{ $tahun->nama_tahunakademik }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Tahun</p>
                                <p class="font-semibold text-gray-700">{{ $tahun->tahun_ajaran }}</p>
                            </div>
                        </div>

                        <form action="{{ route('kurikulum.setup') }}" method="GET">
                            <input type="hidden" name="tahun" value="{{ $tahun->id_tahunakademik }}">
                            <button type="submit"
                               class="w-full inline-flex items-center justify-center px-4 py-3 bg-yellow-500 text-white font-semibold rounded-xl shadow-md hover:bg-yellow-600 transition duration-200">
                                Setup Kurikulum
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-white p-10 rounded-2xl shadow-md text-center">
                        <div class="flex justify-center mb-4">
                            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center text-3xl">📚</div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-700 mb-2">Data Tahun Akademik Belum Ada</h3>
                        <p class="text-gray-500">Silakan tambahkan data tahun akademik terlebih dahulu di Management Kelas Paralel.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</main>

<!-- ===================== MODAL TAMBAH KURIKULUM ===================== -->
<div
    x-show="showModalTambahKurikulum"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
    @keydown.escape.window="showModalTambahKurikulum = false"
    @click.self="showModalTambahKurikulum = false"
    style="display: none;">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">

        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">Tambah Master Kurikulum</h3>
            <button @click="showModalTambahKurikulum = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <form action="{{ route('kurikulum.storeKurikulum') }}" method="POST" class="p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Kode Kurikulum
                </label>
                <input
                    type="text"
                    name="kode_kurikulum"
                    placeholder="Contoh: KUR2024"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500"
                    required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Nama Kurikulum
                </label>
                <input
                    type="text"
                    name="nama_kurikulum"
                    placeholder="Contoh: Kurikulum Merdeka 2024"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500"
                    required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Tahun Berlaku
                </label>
                <input
                    type="number"
                    name="tahun_berlaku"
                    placeholder="Contoh: 2024"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500"
                    required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Status
                </label>
                <select
                    name="status"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
                    <option value="Aktif">Aktif</option>
                    <option value="Tidak Aktif">Tidak Aktif</option>
                </select>
            </div>

            <!-- Footer -->
            <div class="flex gap-3 pt-2">
                <button
                    type="button"
                    @click="showModalTambahKurikulum = false"
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

<!-- ===================== MODAL TAMBAH TAHUN AKADEMIK ===================== -->
<div
    x-show="showModalTambahTahun"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
    @keydown.escape.window="showModalTambahTahun = false"
    @click.self="showModalTambahTahun = false"
    style="display: none;">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">Tambah Tahun Akademik</h3>
            <button @click="showModalTambahTahun = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form action="{{ route('kurikulum.storeTahunAkademik') }}" method="POST" class="p-6 space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Tahun Akademik (Semester)</label>
                <select name="nama_tahunakademik" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white" required>
                    <option value="" disabled selected>Pilih Semester</option>
                    <option value="Ganjil">Ganjil</option>
                    <option value="Genap">Genap</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tahun Ajaran</label>
                <input type="text" name="tahun_ajaran" placeholder="Contoh: 2024/2025" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                <select name="status_aktif" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" @click="showModalTambahTahun = false" class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-600 font-semibold rounded-xl hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-xl shadow-md transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
