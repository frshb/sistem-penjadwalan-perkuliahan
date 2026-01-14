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
<body x-data="{ sidebarOpen: true, showAddModal: false, showEditModal: false, showExportMenu: false, semesterType: 'ganjil', editData: {}, isLoading: true, init() { setTimeout(() => this.isLoading = false, 2000) } }" class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300">

    <!-- Tombol untuk MEMBUKA sidebar (muncul saat sidebar tertutup) -->


    <div class="flex min-h-screen">
       @include('components.sidebar')

        <!-- Konten Utama -->
        <main id="main-content" :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'" class="flex-1 min-w-0 p-6 sm:p-10 transition-all duration-300 ease-in-out bg-gray-50">
            <!-- Skeleton Loader -->
            <div x-show="isLoading" class="animate-pulse space-y-6">
                <!-- Header Skeleton -->
                <div class="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center space-x-3 w-1/3">
                        <div class="w-2 h-8 bg-gray-300 rounded-lg"></div>
                        <div class="w-48 h-6 bg-gray-300 rounded"></div>
                    </div>
                    <div class="w-32 h-10 bg-gray-300 rounded-full"></div>
                </div>
                
                <!-- Filter/Add Bar Skeleton -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 space-y-6">
                    <div class="flex flex-col sm:flex-row justify-between gap-4">
                        <div class="w-full sm:w-1/3 h-10 bg-gray-200 rounded-lg"></div>
                        <div class="w-32 h-10 bg-gray-300 rounded-lg"></div>
                    </div>
                    
                    <!-- Table Skeleton -->
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
                        Mata Kuliah
                    </h2>
                    <div class="flex flex-wrap items-center gap-2">

                        <!-- ===== AWAL FORM FILTER ===== -->
                        <form action="{{ route('matakuliah.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                            <!-- Filter Semester (JS) -->
                            <button id="btn-ganjil" type="button" class="px-4 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md text-sm">
                                Ganjil
                            </button>
                            <button id="btn-genap" type="button" class="px-4 py-2 bg-white text-gray-700 font-semibold rounded-lg shadow-md border border-gray-300 hover:bg-gray-50 text-sm">
                                Genap
                            </button>
                            <select id="select-semester" name="semester" class="px-4 py-2 bg-white text-gray-700 font-semibold rounded-lg shadow-md border border-gray-300 hover:bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                                <!-- Opsi diisi JavaScript -->
                            </select>

                            <!-- Filter Kurikulum (Dinamis dari Controller) -->
                            <select name="kurikulum" class="px-4 py-2 bg-white text-gray-700 font-semibold rounded-lg shadow-md border border-gray-300 hover:bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                                <option value="">Semua Kurikulum</option>
                                @foreach ($kurikulums as $kurikulum)
                                    <option value="{{ $kurikulum->id_kurikulum }}" {{ request('kurikulum') == $kurikulum->id_kurikulum ? 'selected' : '' }}>
                                        {{ $kurikulum->nama_kurikulum }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Tombol Submit Filter -->
                            <button type="submit" class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700">
                                Filter
                            </button>
                        </form>
                        <!-- ===== AKHIR FORM FILTER ===== -->

                        <!-- Tombol Aksi -->
                        <div class="relative" @click.away="showExportMenu = false">
                            <button @click="showExportMenu = !showExportMenu" class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-opacity-75 flex items-center">
                                Export
                                <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div x-show="showExportMenu" x-transition class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-xl z-20 border border-gray-200" style="display: none;">

                                <a href="{{ route('matakuliah.export.excel') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Export Excel
                                </a>
                                <a href="{{ route('matakuliah.export.pdf') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Export PDF
                                </a>
                            </div>
                        </div>

                        <button @click="showAddModal = true" class="px-5 py-2 bg-yellow-600 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-75">
                            Tambah Matkul
                        </button>
                    </div>
                </div>


                <div class="overflow-hidden rounded-lg border border-[#DBDBDB]">
                    <div class="overflow-x-auto w-full">
                        <table class="min-w-full bg-white">
                            <thead class="bg-teal-800 text-white">
                                <tr>
                                    <th class="w-16 text-left py-2 px-3 uppercase font-semibold text-xs">No</th>
                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Mata Kuliah</th>
                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Jumlah SKS</th>
                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Tipe</th>
                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Semester</th>
                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Kurikulum</th>
                                    <th class="text-left py-2 px-3 uppercase font-semibold text-xs">Kode Matkul</th>
                                    <th class="w-48 text-left py-2 px-3 uppercase font-semibold text-xs">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">

                                @forelse ($matkuls as $index => $matkul)
                                    <tr class="border-b border-[#DBDBDB] hover:bg-gray-50">
                                        <td class="text-left py-2 px-3 text-sm">{{ ($matkuls->currentPage() - 1) * $matkuls->perPage() + $index + 1 }}</td>
                                        <td class="text-left py-2 px-3 text-sm">{{ $matkul->nama_matkul }}</td>
                                        <td class="text-left py-2 px-3 text-sm">{{ $matkul->sks }}</td>
                                        <td class="text-left py-2 px-3 text-sm">{{ $matkul->jenis }}</td>
                                        <td class="text-left py-2 px-3 text-sm">{{ $matkul->semester }}</td>
                                        <td class="text-left py-2 px-3 text-sm">{{ $matkul->kurikulum->nama_kurikulum ?? '-' }}</td>
                                        <td class="text-left py-2 px-3 text-sm">{{ $matkul->kode_matkul }}</td>
                                        <td class="text-left py-2 px-3 text-sm">
                                            <div class="flex space-x-2">
                                                <button 
                                                    onclick="openEditModal('{{ $matkul->kode_matkul }}', '{{ $matkul->nama_matkul }}', '{{ $matkul->sks }}', '{{ $matkul->jenis }}', '{{ $matkul->semester }}', '{{ $matkul->id_kurikulum }}')" 
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
                                        <td colspan="8" class="text-center py-4 text-gray-500">
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
             <div x-show="showAddModal" @click="showAddModal = false" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showAddModal" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
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
                                <label for="nama_matkul" class="block text-sm font-medium text-gray-700 mb-1">Nama Mata Kuliah</label>
                                <input type="text" id="nama_matkul" name="nama_matkul" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
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
                                <input type="number" id="semester" name="semester" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                            </div>
                            <div>
                                <label for="tipe" class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                                <select id="tipe" name="tipe" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                                    <option value="">Pilih Tipe</option>
                                    <option value="Teori">Teori</option>
                                    <option value="Praktikum">Praktikum</option>
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
             <div x-show="showEditModal" @click="showEditModal = false" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showEditModal" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-teal-800">Edit Mata Kuliah</h2>
                        <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form id="form-edit-matkul" method="POST" class="mt-6 space-y-4">
                        @csrf
                        @method('PUT')
                        <!-- Error msg container -->
                        <div id="edit-errors" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm"></div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="edit_nama_matkul" class="block text-sm font-medium text-gray-700 mb-1">Nama Mata Kuliah</label>
                                <input type="text" id="edit_nama_matkul" name="nama_matkul" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                            </div>
                            <div>
                                <label for="edit_kode_matkul" class="block text-sm font-medium text-gray-700 mb-1">Kode Matkul</label>
                                <input type="text" id="edit_kode_matkul" name="kode_matkul" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                            </div>
                            <div>
                                <label for="edit_jumlah_sks" class="block text-sm font-medium text-gray-700 mb-1">Jumlah SKS</label>
                                <input type="number" id="edit_jumlah_sks" name="jumlah_sks" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                            </div>
                            <div>
                                <label for="edit_semester" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                                <input type="number" id="edit_semester" name="semester" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                            </div>
                            <div>
                                <label for="edit_tipe" class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                                <select id="edit_tipe" name="tipe" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                                    <option value="">Pilih Tipe</option>
                                    <option value="Teori">Teori</option>
                                    <option value="Praktikum">Praktikum</option>
                                </select>
                            </div>
                            <div>
                                <label for="edit_id_kurikulum" class="block text-sm font-medium text-gray-700 mb-1">Kurikulum</label>
                                <select id="edit_id_kurikulum" name="id_kurikulum" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                                    <option value="">Pilih Kurikulum</option>
                                    @foreach ($kurikulums as $kurikulum)
                                        <option value="{{ $kurikulum->id_kurikulum }}">{{ $kurikulum->nama_kurikulum }}</option>
                                    @endforeach
                                </select>
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
                allOption.value = '';
                allOption.text = 'Semua Semester';
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
                    btnGanjil.classList.add('bg-teal-600', 'text-white');
                    btnGanjil.classList.remove('bg-white', 'text-gray-700', 'border', 'border-gray-300', 'hover:bg-gray-50');
                    btnGenap.classList.add('bg-white', 'text-gray-700', 'border', 'border-gray-300', 'hover:bg-gray-50');
                    btnGenap.classList.remove('bg-teal-600', 'text-white');
                } else {
                    btnGenap.classList.add('bg-teal-600', 'text-white');
                    btnGenap.classList.remove('bg-white', 'text-gray-700', 'border', 'border-gray-300', 'hover:bg-gray-50');
                    btnGanjil.classList.add('bg-white', 'text-gray-700', 'border', 'border-gray-300', 'hover:bg-gray-50');
                    btnGanjil.classList.remove('bg-teal-600', 'text-white');
                }
            }
            if(btnGanjil && btnGenap && selectSemester) {
                const currentSemester = '{{ request('semester') }}';
                if (genapOptions.includes(parseInt(currentSemester))) {
                    updateSemesterOptions('genap');
                } else {
                    updateSemesterOptions('ganjil');
                }
                btnGanjil.addEventListener('click', () => updateSemesterOptions('ganjil'));
                btnGenap.addEventListener('click', () => updateSemesterOptions('genap'));
            }

            // Helper for Edit Form population (can be moved to Alpine but keeping simple function for now or refactor completely)
            window.openEditModal = function(kode, nama, sks, jenis, semester, kurikulum_id) {
                // We can use Alpine store or events, but triggering Alpine state from here is also possible.
                // However, let's try to pass this data to Alpine state if possible, or just keep this helper to populate form
                // and then show modal via Alpine variable.
                const formEdit = document.getElementById('form-edit-matkul');
                document.getElementById('edit_nama_matkul').value = nama;
                document.getElementById('edit_kode_matkul').value = kode;
                document.getElementById('edit_jumlah_sks').value = sks;
                document.getElementById('edit_semester').value = semester;
                document.getElementById('edit_id_kurikulum').value = kurikulum_id;

                let jenisCapitalized = jenis.charAt(0).toUpperCase() + jenis.slice(1);
                document.getElementById('edit_tipe').value = jenisCapitalized;

                formEdit.action = `/management/matakuliah/${kode}`;
                
                // Trigger Alpine state change
                document.querySelector('[x-data]').__x.$data.showEditModal = true;
            };

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
                        document.querySelector('[x-data]').__x.$data.showEditModal = false;
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
        });
    </script>
</body>
</html>

