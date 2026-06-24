<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Tahun Akademik | Portal Dosen Pengampu</title>

    <meta name="description" content="Pilih Tahun Akademik untuk Portal Dosen Pengampu">

    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    x-data="{
        sidebarOpen: true,
        statusFilter: 'aktif',
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
                    Portal Dosen Pengampu
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
                    Silakan pilih tahun akademik untuk mengatur dosen pengampu.
                </p>
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

                @php
                    $isRestricted = !auth()->user()->isAdmin() && !$tahun->status_aktif;
                @endphp
                <div
                    x-show="statusFilter === 'semua' || (statusFilter === 'aktif' && {{ $tahun->status_aktif ? 1 : 0 }} == 1) || (statusFilter === 'nonaktif' && {{ $tahun->status_aktif ? 0 : 1 }} == 1)"
                    class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden transition-all duration-300 {{ $isRestricted ? 'opacity-50' : 'hover:shadow-xl hover:-translate-y-1' }}">

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
                        @if($isRestricted)
                            <button
                                disabled
                                class="w-full inline-flex items-center justify-center px-4 py-3 bg-gray-200 text-gray-400 font-semibold rounded-xl cursor-not-allowed">
                                Tidak Aktif (Akses Terbatas)
                            </button>
                        @else
                            <a href="{{ route('dosen-pengampu.index', ['tahun' => $tahun->id_tahunakademik]) }}"
                               class="w-full inline-flex items-center justify-center px-4 py-3 bg-teal-500 text-white font-semibold rounded-xl shadow-md hover:bg-teal-600 transition duration-200">

                                Atur Dosen Pengampu

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
                        @endif

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
                            Silakan hubungi Admin untuk menambahkan data tahun akademik pada menu Pengaturan Kurikulum & TA.
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

</body>
</html>
