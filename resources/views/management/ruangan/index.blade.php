<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Data | Data Ruangan</title>
    <meta name="description" content="Management Data Ruangan Sistem Penjadwalan Perkuliahan">
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .building-tab.active {
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
    activeTab: 'all', 
    editData: { id_ruang: null, nama_ruang: '', id_gedung: '', kapasitas: '', fasilitas: '' }, 
    isLoading: true, 
    init() { setTimeout(() => this.isLoading = false, 2000) },
    openEdit(ruangan) {
        this.editData = ruangan;
        this.showEditModal = true;
    } 
}" class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300">
        @include('components.sidebar')

        <main id="main-content" :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'" class="flex-1 min-w-0 p-6 sm:p-10 transition-all duration-300 ease-in-out">
            <!-- Skeleton Loader -->
            <div x-show="isLoading" class="animate-pulse space-y-6">
                {{-- <!-- Header Skeleton --> --}}
                <div class="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center space-x-3 w-1/3">
                        <div class="w-2 h-8 bg-gray-300 rounded-lg"></div>
                        <div class="w-48 h-6 bg-gray-300 rounded"></div>
                    </div>
                    <div class="w-32 h-10 bg-gray-300 rounded-full"></div>
                </div>
                
                {{-- <!-- Filter/Add Bar Skeleton --> --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 space-y-6">
                    <div class="flex flex-col sm:flex-row justify-between gap-4">
                        <div class="w-full sm:w-1/3 h-10 bg-gray-200 rounded-lg"></div>
                        <div class="w-32 h-10 bg-gray-300 rounded-lg"></div>
                    </div>
                    
                    {{-- <!-- Table Skeleton --> --}}
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

            <div x-show="!isLoading">
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

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-6 mb-4">
            <h2 class="text-xl font-bold text-gray-700 mb-4 sm:mb-0">
                Daftar Ruangan
            </h2>
            <div class="flex space-x-2">
                <div x-data="{ exportOpen: false }" class="relative">
                    <button @click="exportOpen = !exportOpen" @click.away="exportOpen = false" class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-opacity-75 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Export Data
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="exportOpen" x-transition class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 overflow-hidden border border-gray-100" style="display: none;">
                        <a href="{{ route('ruangan.export.excel') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-700 transition-colors">
                            <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Export Excel
                        </a>
                        <a href="{{ route('ruangan.export.pdf') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors">
                            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            Export PDF
                        </a>
                    </div>
                </div>
                <button @click="showAddModal = true" class="px-5 py-2 bg-yellow-600 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-75 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Ruang Baru
                </button>
            </div>
        </div>

        <div class="mb-6">
            <form action="{{ route('ruangan.index') }}" method="GET">
                <div class="relative">
                    <input type="text" name="search" value="{{ $searchTerm ?? '' }}" placeholder="Cari nama ruangan..." class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <button type="submit" class="absolute right-0 top-0 h-full px-4 text-gray-600 hover:text-teal-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </div>
            </form>
        </div>
        @if ($searchTerm)

            <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md border border-transparent">
                <h3 class="text-xl font-bold text-gray-700 mb-6">
                    Hasil Pencarian untuk: "{{ $searchTerm }}"
                </h3>

                <div class="overflow-hidden rounded-lg border border-[#DBDBDB]">
                    <div class="overflow-x-auto w-full">
                        <table class="min-w-full bg-white">
                            <thead class="bg-teal-700 text-white">
                                <tr>
                                <tr>
                                    <th class="w-16 text-left py-2 px-3 uppercase font-semibold text-xs">No</th>
                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Nama Ruangan</th>
                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Gedung</th>
                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Lokasi</th>
                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Fasilitas</th>
                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Kapasitas</th>
                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Status</th>
                                    <th class="w-48 text-left py-2 px-3 uppercase font-semibold text-xs">Aksi</th>
                                </tr>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                @forelse ($ruangans as $ruangan)
                                <tr class="border-b border-[#DBDBDB] hover:bg-gray-50">
                                    <td class="text-left py-2 px-3 text-sm">{{ $loop->iteration }}</td>
                                    <td class="text-left py-2 px-3 text-sm">{{ $ruangan->nama_ruang }}</td>
                                    <td class="text-left py-2 px-3 text-sm">{{ $ruangan->gedung->nama_gedung }}</td>
                                    <td class="text-left py-2 px-3 text-sm">{{ $ruangan->gedung->lokasi }}</td>
                                    <td class="text-left py-2 px-3 text-sm">{{ $ruangan->fasilitas }}</td>
                                    <td class="text-left py-2 px-3 text-sm">{{ $ruangan->kapasitas }}</td>
                                    <td class="text-left py-2 px-3 text-sm">
                                        @php $isAvailable = rand(0, 1); @endphp
                                        @if($isAvailable)
                                            <span class="px-2 py-1 text-xs font-semibold leading-tight text-green-700 bg-green-100 rounded-full">Tersedia</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold leading-tight text-red-700 bg-red-100 rounded-full">Tidak Tersedia</span>
                                        @endif
                                    </td>
                                    <td class="text-left py-2 px-3 text-sm">
                                        <div class="flex space-x-2">
                                            <button @click='openEdit(@json($ruangan))' 
                                                    class="flex items-center justify-center bg-yellow-400 text-gray-900 px-3 py-1 rounded-md hover:bg-yellow-500 text-xs font-medium">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                Edit
                                            </button>
                                            <button onclick="confirmDelete('{{ route('ruangan.destroy', $ruangan->id_ruang) }}')" 
                                                    class="flex items-center justify-center bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-700 text-xs font-medium">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-gray-500">
                                        Data ruangan tidak ditemukan.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        @else

            <div class="flex space-x-2 mb-6 border-b border-gray-300 overflow-x-auto">
                <button 
                    @click="activeTab = 'all'"
                    class="building-tab px-4 py-2 border-b-2 whitespace-nowrap transition-colors duration-200"
                    :class="activeTab === 'all' ? 'border-teal-600 text-teal-700 font-bold' : 'border-transparent text-gray-600 hover:text-teal-700'">
                    Semua Gedung
                </button>
                @foreach ($ruangansByGedung as $namaGedung => $ruangansInGedung)
                    <button 
                        @click="activeTab = '{{ Str::slug($namaGedung) }}'"
                        class="building-tab px-4 py-2 border-b-2 whitespace-nowrap transition-colors duration-200"
                        :class="activeTab === '{{ Str::slug($namaGedung) }}' ? 'border-teal-600 text-teal-700 font-bold' : 'border-transparent text-gray-600 hover:text-teal-700'">
                        {{ $namaGedung }}
                    </button>
                @endforeach
            </div>

            @forelse ($ruangansByGedung as $namaGedung => $ruangansInGedung)
                <div class="building-content mb-8" 
                     id="gedung-{{ Str::slug($namaGedung) }}" 
                     x-show="activeTab === 'all' || activeTab === '{{ Str::slug($namaGedung) }}'"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0">

                    <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md">
                        <h3 class="text-xl font-bold text-gray-700 mb-1">
                            {{ $namaGedung }}
                        </h3>
                        <p class="text-sm text-gray-500 mb-6">{{ $ruangansInGedung->first()->gedung->lokasi ?? 'Lokasi tidak diketahui' }}</p>

                        <div class="overflow-hidden rounded-lg border border-[#DBDBDB]">
                            <div class="overflow-x-auto w-full">
                                <table class="min-w-full bg-white">
                                    <thead class="bg-teal-700 text-white">
                                        <tr>
                                            <th class="w-16 text-left py-2 px-3 uppercase font-semibold text-xs">No</th>
                                            <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Nama Ruangan</th>
                                            <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Gedung</th>
                                            <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Lokasi</th>
                                            <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Fasilitas</th>
                                            <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Kapasitas</th>
                                            <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Status</th>
                                            <th class="w-48 text-left py-2 px-3 uppercase font-semibold text-xs">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-700">
                                        @forelse ($ruangansInGedung as $ruangan)
                                        <tr class="border-b border-[#DBDBDB] hover:bg-gray-50">
                                            <td class="text-left py-2 px-3 text-sm">{{ $loop->iteration }}</td>
                                            <td class="text-left py-2 px-3 text-sm">{{ $ruangan->nama_ruang }}</td>
                                            <td class="text-left py-2 px-3 text-sm">{{ $ruangan->gedung->nama_gedung }}</td>
                                            <td class="text-left py-2 px-3 text-sm">{{ $ruangan->gedung->lokasi }}</td>
                                            <td class="text-left py-2 px-3 text-sm">{{ $ruangan->fasilitas }}</td>
                                            <td class="text-left py-2 px-3 text-sm">{{ $ruangan->kapasitas }}</td>
                                            <td class="text-left py-2 px-3 text-sm">
                                                @php $isAvailable = rand(0, 1); @endphp
                                                @if($isAvailable)
                                                    <span class="px-2 py-1 text-xs font-semibold leading-tight text-green-700 bg-green-100 rounded-full">Tersedia</span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold leading-tight text-red-700 bg-red-100 rounded-full">Tidak Tersedia</span>
                                                @endif
                                            </td>
                                            <td class="text-left py-2 px-3 text-sm">
                                                <div class="flex space-x-2">
                                                    <button @click='openEdit(@json($ruangan))' class="flex items-center justify-center bg-yellow-400 text-gray-900 px-3 py-1 rounded-md hover:bg-yellow-500 text-xs font-medium">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                        Edit
                                                    </button>
                                                    <button onclick="confirmDelete('{{ route('ruangan.destroy', $ruangan->id_ruang) }}')" 
                                                            class="flex items-center justify-center bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-700 text-xs font-medium">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        Hapus
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-gray-500">
                                                Data ruangan belum tersedia untuk gedung ini.
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
                        Data ruangan belum tersedia.
                    </p>
                </div>
            @endforelse

        @endif
            </div>
        </main>

    <!-- ===== MODAL TAMBAH RUANGAN ===== -->
    <!-- ===== MODAL TAMBAH RUANGAN ===== -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
            <div x-show="showAddModal" @click="showAddModal = false" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showAddModal" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="p-6">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-teal-800">Tambah Ruangan Baru</h2>
                        <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                    </div>
                    
                    <form action="{{ route('ruangan.store') }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ruangan</label>
                            <input type="text" name="nama_ruang" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gedung</label>
                            <select name="id_gedung" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500" required>
                                <option value="">-- Pilih Gedung --</option>
                                @foreach ($gedungs as $gedung)
                                    <option value="{{ $gedung->id_gedung }}">{{ $gedung->nama_gedung }} ({{ $gedung->lokasi }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas</label>
                            <input type="number" name="kapasitas" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fasilitas</label>
                            <textarea name="fasilitas" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500" placeholder="Contoh: AC, Proyektor, Papan Tulis"></textarea>
                        </div>
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" @click="showAddModal = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 shadow-md">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MODAL EDIT RUANGAN ===== -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
            <div x-show="showEditModal" @click="showEditModal = false" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showEditModal" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="p-6">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-teal-800">Edit Ruangan</h2>
                        <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                    </div>
                    
                    <form :action="`/management/ruangan/${editData.id_ruang}`" method="POST" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ruangan</label>
                            <input type="text" name="nama_ruang" x-model="editData.nama_ruang" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gedung</label>
                            <select name="id_gedung" x-model="editData.id_gedung" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500" required>
                                <option value="">-- Pilih Gedung --</option>
                                @foreach ($gedungs as $gedung)
                                    <option value="{{ $gedung->id_gedung }}">{{ $gedung->nama_gedung }} ({{ $gedung->lokasi }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas</label>
                            <input type="number" name="kapasitas" x-model="editData.kapasitas" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fasilitas</label>
                            <textarea name="fasilitas" x-model="editData.fasilitas" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500" placeholder="Contoh: AC, Proyektor, Papan Tulis"></textarea>
                        </div>
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 shadow-md">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <x-delete-confirm-popup />
    </body>
</html>
