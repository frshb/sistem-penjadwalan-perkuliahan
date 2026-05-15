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
        isLoading: true,
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
                    Management Data
                </h1>

            </div>

            @include('components.header-profile')

        </div>

        <!-- Title -->
        <div class="mt-6 mb-8">

            <h2 class="text-2xl font-bold text-gray-700">
                Pilih Tahun Akademik
            </h2>

            <p class="text-gray-500 mt-1">
                Silakan pilih tahun akademik untuk melihat data kelas.
            </p>

        </div>

        <!-- Card Tahun Akademik -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

            @forelse ($tahunAkademiks as $tahun)

                <div
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

    </div>

</main>

</body>
</html>
