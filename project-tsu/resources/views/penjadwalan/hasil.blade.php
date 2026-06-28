<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Penjadwalan | TSU</title>
    <link rel="icon" href="{{ asset('tsuwhite.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300" x-data="{ sidebarOpen: true }">

    <div class="flex min-h-screen">
        @include('components.sidebar')

        <!-- Main Content pembaharuan--> 
        <main id="main-content" :class="sidebarOpen ? 'lg:ml-64' : ''" class="flex-1 min-w-0 p-6 sm:p-10 transition-all duration-300 ease-in-out ml-0 bg-gray-50 flex flex-col">
            
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div class="flex items-center">
                    <button @click="sidebarOpen = !sidebarOpen" class="flex flex-col hover:opacity-80 transition cursor-pointer" title="Toggle Sidebar">
                        <div class="w-2 h-5 bg-teal-800 rounded-tl-md"></div>
                        <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                    </button>
                    <h1 class="text-2xl font-bold text-gray-800 ml-3">Modul Penjadwalan</h1>
                </div>
                @include('components.header-profile')
            </div>

            <!-- Action Headers -->
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Hasil Penjadwalan</h2>
                    <p class="text-sm text-gray-500 mt-1">Daftar jadwal perkuliahan yang telah dibuat (otomatis & manual).</p>
                </div>
                <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto">
                    <div x-data="{ exportOpen: false }" class="relative w-full sm:w-auto">
                        <button @click="exportOpen = !exportOpen" @click.away="exportOpen = false" class="w-full sm:w-auto px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-opacity-75 flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Export Data
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="exportOpen" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 overflow-hidden border border-gray-100" style="display: none;">
                            <a href="{{ route('jadwal.export.excel') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-700 transition-colors">
                                <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Export Excel
                            </a>
                            <a href="{{ route('jadwal.export.pdf') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors">
                                <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                Export PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left text-gray-600">
                        <thead class="text-xs text-white uppercase bg-teal-800 border-b border-teal-700">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold w-12">No</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Hari</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Waktu</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Mata Kuliah</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Kelas</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Dosen</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Ruangan</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Jenis</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($jadwals as $index => $item)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $item->hari->nama_hari ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $item->slot->waktu_mulai ?? '-' }} - {{ $item->slot->waktu_selesai ?? '-' }}</td>
                                    <td class="px-6 py-4 font-medium text-teal-700">{{ $item->matkul->nama_matkul ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $item->kelas ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">{{ $item->dosen->nama_dosen ?? '-' }}</td>
                                    <td class="px-6 py-4 font-semibold text-teal-700">{{ $item->ruang->nama_ruang ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        @if($item->jenis_jadwal == 'Otomatis')
                                            <span class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">Otomatis</span>
                                        @else
                                            <span class="px-2.5 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">Manual</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                        <p class="text-base font-medium text-gray-900">Belum ada jadwal</p>
                                        <p class="mt-1">Silakan buat jadwal melalui menu Penjadwalan Otomatis atau Manual.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</body>
</html>
