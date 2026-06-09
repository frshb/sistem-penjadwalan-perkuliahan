<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Tahun Akademik | Penjadwalan</title>

    <meta name="description"
          content="Pilih Tahun Akademik Penjadwalan Perkuliahan">

    <link rel="icon"
          href="{{ asset('favicon_square.png') }}"
          type="image/png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    x-data="{
        sidebarOpen: true,
        isLoading: true,
        statusFilter: 'all',
        searchQuery: '',

        init() {
            setTimeout(() => this.isLoading = false, 1200)
        }
    }"
    class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300"
>

@include('components.sidebar')

<main id="main-content"
      :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'"
      class="flex-1 min-w-0 p-6 sm:p-10 transition-all duration-300 ease-in-out">

    <!-- Skeleton -->
    <div x-show="isLoading" class="animate-pulse space-y-6">

        <div class="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">

            <div class="flex items-center space-x-3 w-1/3">

                <div class="w-2 h-8 bg-gray-300 rounded-lg"></div>

                <div class="w-48 h-6 bg-gray-300 rounded"></div>

            </div>

            <div class="w-32 h-10 bg-gray-300 rounded-full"></div>

        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                @for ($i = 0; $i < 6; $i++)

                    <div class="border border-gray-200 rounded-2xl p-6 space-y-4">

                        <div class="w-32 h-5 bg-gray-300 rounded"></div>

                        <div class="w-48 h-4 bg-gray-200 rounded"></div>

                        <div class="w-full h-10 bg-gray-300 rounded-xl"></div>

                    </div>

                @endfor

            </div>

        </div>

    </div>

    <!-- CONTENT -->
    <div x-show="!isLoading">

        <!-- Header -->
        <div class="flex justify-between items-center">

            <div class="flex items-center">

                <div class="flex flex-col">

                    <div class="w-2 h-5 bg-teal-600 rounded-tl-md"></div>

                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>

                </div>

                <h1 class="text-2xl font-bold text-gray-800 ml-3">
                    Modul Penjadwalan
                </h1>

            </div>

            @include('components.header-profile')

        </div>

        <!-- Title & Info Alert -->
        <div class="mt-6 mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-800 tracking-tight">
                    Penyusunan Jadwal Kuliah
                </h2>
                <p class="text-gray-500 mt-1 text-sm">
                    Pilih Periode/Tahun Akademik untuk mengelola dan memantau penyusunan jadwal perkuliahan.
                </p>
            </div>
            <!-- Alert Deskripsi Peran (Dekan, Admin, Kaprodi) -->
            <div class="bg-teal-50 border-l-4 border-teal-500 p-4 rounded-r-xl max-w-md shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-semibold text-teal-800">Petunjuk Peran</p>
                        <p class="text-xs text-teal-700 mt-0.5 leading-relaxed">
                            <strong>Admin / Kaprodi:</strong> Klik "Susun Jadwal" untuk menyusun jadwal secara manual atau otomatis.<br/>
                            <strong>Dekan / Pimpinan:</strong> Pantau progress persentase keterisian kelas di setiap periode.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 1: TAHUN AKADEMIK AKTIF UTAMA -->
        @php
            $activeYear = $tahunAkademiks->firstWhere('status_aktif', 1);
        @endphp
        @if($activeYear)
            <div class="mb-10">
                <div class="flex items-center space-x-2 mb-4">
                    <span class="flex h-2.5 w-2.5 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">
                        Tahun Akademik Aktif Saat Ini (Rekomendasi Utama)
                    </h3>
                </div>
                
                <div class="bg-gradient-to-r from-teal-800 via-teal-900 to-emerald-950 rounded-3xl shadow-xl border border-teal-700 overflow-hidden relative group">
                    <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-teal-700 rounded-full opacity-20 blur-2xl group-hover:scale-110 transition-all duration-500"></div>
                    <div class="absolute -left-16 -top-16 w-64 h-64 bg-emerald-700 rounded-full opacity-10 blur-2xl"></div>
                    
                    <div class="p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                        <div class="space-y-4 max-w-xl">
                            <div>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-500 text-white shadow-sm">
                                    Periode Utama Berjalan
                                </span>
                                <h4 class="text-2xl md:text-3xl font-extrabold text-white mt-2">
                                    {{ $activeYear->nama_tahunakademik }}
                                </h4>
                                <p class="text-teal-200 text-sm mt-1">
                                    Tahun Ajaran {{ $activeYear->tahun_ajaran }} • Gunakan periode ini untuk menyusun atau mengedit jadwal perkuliahan aktif yang sedang berjalan saat ini.
                                </p>
                            </div>
                            
                            <!-- Progress stats -->
                            <div class="grid grid-cols-2 gap-4 bg-teal-950/40 p-4 rounded-2xl border border-teal-800/50">
                                <div>
                                    <p class="text-xs text-teal-300">Total Kelas Terdaftar</p>
                                    <p class="text-lg font-bold text-white mt-0.5 flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                        {{ $activeYear->kelas_count }} Kelas
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-teal-300">Kelas Sudah Terjadwal</p>
                                    <p class="text-lg font-bold text-white mt-0.5 flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ $activeYear->jadwals_count }} Terjadwal
                                    </p>
                                </div>
                                <div class="col-span-2 mt-1">
                                    <div class="flex justify-between text-xs text-teal-200 mb-1">
                                        <span>Progres Penyusunan Jadwal</span>
                                        <span class="font-bold text-white">{{ $activeYear->kelas_count > 0 ? round(($activeYear->jadwals_count / $activeYear->kelas_count) * 100) : 0 }}%</span>
                                    </div>
                                    <div class="w-full bg-teal-950/60 rounded-full h-2.5 overflow-hidden">
                                        <div class="bg-gradient-to-r from-yellow-400 to-emerald-400 h-2.5 rounded-full transition-all duration-500" style="width: {{ $activeYear->kelas_count > 0 ? min(100, round(($activeYear->jadwals_count / $activeYear->kelas_count) * 100)) : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row md:flex-col lg:flex-row gap-3 min-w-[200px]">
                            <a href="{{ route('jadwal.manual', ['tahun' => $activeYear->id_tahunakademik]) }}"
                               class="inline-flex items-center justify-center px-6 py-4 bg-yellow-500 hover:bg-yellow-400 text-teal-950 font-bold rounded-2xl shadow-lg hover:shadow-yellow-500/20 transform hover:-translate-y-0.5 transition-all duration-200 text-center">
                                Susun Jadwal
                                <svg class="w-5 h-5 ml-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- SECTION 2: SEMUA TAHUN AKADEMIK DENGAN FILTER & CARI -->
        <div class="border-t border-gray-200/80 pt-8 mt-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">
                        Cari & Filter Periode Akademik
                    </h3>
                    <p class="text-sm text-gray-500">
                        Gunakan kolom pencarian atau filter status untuk menemukan periode akademik secara cepat.
                    </p>
                </div>
                
                <!-- Controls: Search & Tabs -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <!-- Search Input -->
                    <div class="relative flex-1 sm:w-64">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input
                            type="text"
                            x-model="searchQuery"
                            placeholder="Cari semester atau tahun..."
                            class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-sm font-medium text-gray-700 h-11"
                        />
                    </div>
                    
                    <!-- Tabs Status -->
                    <div class="bg-gray-200/60 p-1 rounded-xl flex items-center border border-gray-200/30 self-start sm:self-auto">
                        <button
                            @click="statusFilter = 'all'"
                            :class="statusFilter === 'all' ? 'bg-white text-teal-800 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-800'"
                            class="px-4 py-2 rounded-lg text-xs font-semibold transition-all duration-200"
                        >
                            Semua ({{ $tahunAkademiks->count() }})
                        </button>
                        <button
                            @click="statusFilter = 'aktif'"
                            :class="statusFilter === 'aktif' ? 'bg-white text-emerald-800 shadow-sm font-bold' : 'text-gray-600 hover:text-emerald-700'"
                            class="px-4 py-2 rounded-lg text-xs font-semibold transition-all duration-200 flex items-center gap-1.5"
                        >
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                            Aktif ({{ $tahunAkademiks->where('status_aktif', 1)->count() }})
                        </button>
                        <button
                            @click="statusFilter = 'nonaktif'"
                            :class="statusFilter === 'nonaktif' ? 'bg-white text-gray-800 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-800'"
                            class="px-4 py-2 rounded-lg text-xs font-semibold transition-all duration-200 flex items-center gap-1.5"
                        >
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                            Nonaktif ({{ $tahunAkademiks->where('status_aktif', 0)->count() }})
                        </button>
                    </div>
                </div>
            </div>

            <!-- Grid Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse ($tahunAkademiks as $tahun)
                    @php
                        $progress = $tahun->kelas_count > 0 ? round(($tahun->jadwals_count / $tahun->kelas_count) * 100) : 0;
                    @endphp
                    <div
                        x-show="(statusFilter === 'all' || (statusFilter === 'aktif' && {{ $tahun->status_aktif ? 1 : 0 }} == 1) || (statusFilter === 'nonaktif' && {{ $tahun->status_aktif ? 0 : 1 }} == 1)) && ('{{ strtolower($tahun->nama_tahunakademik) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($tahun->tahun_ajaran) }}'.includes(searchQuery.toLowerCase()))"
                        :class="{{ $tahun->status_aktif }} ? 'ring-2 ring-emerald-500 ring-offset-1 border-emerald-100' : 'border-gray-200/60'"
                        class="bg-white rounded-2xl shadow-md border overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between"
                    >
                        <!-- Card Header & Body -->
                        <div class="px-6 py-5">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-lg font-bold text-gray-900 leading-tight">
                                            {{ $tahun->nama_tahunakademik }}
                                        </h4>
                                        @if($tahun->status_aktif)
                                            <span class="flex h-2 w-2 relative">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-gray-400 text-xs mt-1 font-medium">
                                        Tahun Ajaran {{ $tahun->tahun_ajaran }}
                                    </p>
                                </div>

                                <!-- Status Badge -->
                                @if($tahun->status_aktif)
                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-gray-50 text-gray-500 border border-gray-200">
                                        Nonaktif
                                    </span>
                                @endif
                            </div>

                            <!-- Progress & Stats Section -->
                            <div class="mt-6 space-y-4">
                                <div class="grid grid-cols-2 gap-4 bg-gray-50/70 p-3 rounded-xl border border-gray-100">
                                    <div>
                                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Total Kelas</p>
                                        <p class="text-sm font-bold text-gray-700 mt-0.5">{{ $tahun->kelas_count }} Kelas</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Terjadwal</p>
                                        <p class="text-sm font-bold text-gray-700 mt-0.5">{{ $tahun->jadwals_count }} Kelas</p>
                                    </div>
                                </div>

                                <!-- Progress Bar -->
                                <div>
                                    <div class="flex justify-between text-xs font-semibold text-gray-600 mb-1">
                                        <span>Progres Jadwal</span>
                                        <span class="{{ $progress == 100 ? 'text-green-600 font-bold' : ($progress > 0 ? 'text-yellow-600' : 'text-gray-400') }}">{{ $progress }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden border border-gray-200/50">
                                        <div class="h-2 rounded-full transition-all duration-500 {{ $progress == 100 ? 'bg-green-500' : ($progress > 0 ? 'bg-yellow-500' : 'bg-gray-300') }}" style="width: {{ $progress }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer / Actions -->
                        <div class="px-6 py-4 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between gap-3">
                            <!-- Detail Status Badge -->
                            <div>
                                @if($progress == 100)
                                    <span class="inline-flex items-center text-xs font-semibold text-green-700">
                                        <svg class="w-4 h-4 mr-1 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                        Selesai
                                    </span>
                                @elseif($progress > 0)
                                    <span class="inline-flex items-center text-xs font-semibold text-yellow-700">
                                        <svg class="w-4 h-4 mr-1 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Proses ({{ $progress }}%)
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-xs font-semibold text-gray-500">
                                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                        Belum Mulai
                                    </span>
                                @endif
                            </div>

                            <a href="{{ route('jadwal.manual', ['tahun' => $tahun->id_tahunakademik]) }}"
                               class="inline-flex items-center justify-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-bold rounded-xl shadow-md transition duration-200 text-sm">
                                Susun Jadwal
                                <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="bg-white p-10 rounded-2xl shadow-md text-center border border-gray-100">
                            <div class="flex justify-center mb-4">
                                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center text-3xl">
                                    📅
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
            
            <!-- Empty Search State -->
            <div
                x-show="searchQuery !== ''"
                class="bg-white p-12 rounded-2xl shadow-sm text-center border border-gray-150 mt-6"
                style="display: none;"
                x-cloak
            >
                <div class="flex justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-yellow-50 flex items-center justify-center text-yellow-600 text-3xl">
                        🔍
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-1">
                    Tidak Ada Periode yang Cocok
                </h3>
                <p class="text-gray-500 max-w-md mx-auto text-sm">
                    Pencarian dengan kata kunci "<span x-text="searchQuery" class="font-semibold text-gray-700"></span>" tidak menemukan hasil. Silakan gunakan kata kunci lain.
                </p>
            </div>
        </div>

    </div>

</main>

</body>
</html>
