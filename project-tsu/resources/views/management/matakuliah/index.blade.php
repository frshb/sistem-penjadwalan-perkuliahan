<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Data | Mata Kuliah</title>

    <!-- Memuat CSS dan JS dari Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="bg-gray-100/50 overflow-x-hidden min-h-screen">

    <!-- Tombol untuk MEMBUKA sidebar (muncul saat sidebar tertutup) -->
    <button id="sidebar-open-btn" class="fixed top-6 left-6 z-20 text-gray-600 hover:text-gray-900 hidden transition-all duration-300 ease-in-out">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <div class="flex min-h-screen">
      @include('components.sidebar')
        <!-- Konten Utama -->
        <main id="main-content" class="flex-1 p-6 sm:p-10 transition-all duration-300 ease-in-out ml-64">

            <div class="flex items-center">
                <div class="flex flex-col">
                    <div class="w-2 h-5 bg-teal-800 rounded-tl-md"></div>
                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 ml-3">
                    Management Data
                </h1>
            </div>


            <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md mt-6">

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-700 mb-4 sm:mb-0">
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
                        <div class="relative" id="export-dropdown-container">
                            <button id="export-btn" class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-opacity-75 flex items-center">
                                Export
                                <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div id="export-menu" class="hidden absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-xl z-20 border border-gray-200">

                                <a href="{{ route('matakuliah.export.excel') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Export Excel
                                </a>
                            </div>
                        </div>

                        <button id="open-modal-btn" class="px-5 py-2 bg-yellow-600 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-75">
                            Tambah Matkul
                        </button>
                    </div>
                </div>


                <div class="overflow-hidden rounded-lg border border-[#DBDBDB]">
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-teal-800 text-white">
                                <tr>
                                    <th class="w-16 text-left py-3 px-4 uppercase font-semibold text-sm">No</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Mata Kuliah</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Jumlah SKS</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Tipe</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Semester</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Kode Matkul</th>
                                    <th class="w-48 text-left py-3 px-4 uppercase font-semibold text-sm">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">

                                @forelse ($matkuls as $index => $matkul)
                                    <tr class="border-b border-[#DBDBDB] hover:bg-gray-50">
                                        <td class="text-left py-3 px-4">{{ ($matkuls->currentPage() - 1) * $matkuls->perPage() + $index + 1 }}</td>
                                        <td class="text-left py-3 px-4">{{ $matkul->nama_matkucdl }}</td>
                                        <td class="text-left py-3 px-4">{{ $matkul->sks }}</td>
                                        <td class="text-left py-3 px-4">{{ $matkul->jenis }}</td>
                                        <td class="text-left py-3 px-4">{{ $matkul->semester }}</td>
                                        <td class="text-left py-3 px-4">{{ $matkul->kode_matkul }}</td>
                                        <td class="text-left py-3 px-4">
                                            <div class="flex space-x-2">
                                                <a href="#" class="flex items-center justify-center bg-yellow-400 text-gray-900 px-4 py-1.5 rounded-md hover:bg-yellow-500 text-sm font-medium">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                    Edit
                                                </a>
                                                <form action="#" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata kuliah ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="flex items-center justify-center bg-red-600 text-white px-4 py-1.5 rounded-md hover:bg-red-700 text-sm font-medium">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-gray-500">
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

        </main>
        <!-- ===== End Main Content ===== -->
    </div>

    <!-- ===== AWAL MODAL TAMBAH MATA KULIAH ===== -->
    <div id="modal-overlay" class="fixed inset-0 bg-[rgba(0,0,0,0.5)] z-20 hidden"></div>
    <div id="tambah-matkul-modal" class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl z-40 w-full max-w-lg hidden">
        <div class="p-6">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <h2 class="text-xl font-bold text-teal-800">Tambah Mata Kuliah</h2>
                <button id="close-modal-btn" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
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
                        <input type="number" id="semester" name="semester" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-Dteal-500" required>
                    </div>
                    <div>
                        <label for="tipe" class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                        <select id="tipe" name="tipe" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                            <option value="">Pilih Tipe</option>
                            <option value="Teori">Teori</option>
                            <option value="Praktikum">Praktikum</option>
                        </select>
                    </div>

                    <!-- PERUBAHAN: Dropdown Kurikulum di Modal -->
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
                    <button id="cancel-modal-btn" type="button" class="px-5 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- ===== AKHIR MODAL ===== -->

    <!-- ===== JavaScript untuk Sidebar & Modal ===== -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Logika Sidebar
            const closeBtn = document.getElementById('sidebar-toggle');
            const openBtn = document.getElementById('sidebar-open-btn');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');

            if (closeBtn && openBtn && sidebar && mainContent) {
                closeBtn.addEventListener('click', function() {
                    sidebar.classList.add('-translate-x-full');
                    mainContent.classList.remove('ml-64');
                    openBtn.classList.remove('hidden');
                });
                openBtn.addEventListener('click', function() {
                    sidebar.classList.remove('-translate-x-full');
                    mainContent.classList.add('ml-64');
                    openBtn.classList.add('hidden');
                });
            }

            // Logika Modal
            const modal = document.getElementById('tambah-matkul-modal');
            const overlay = document.getElementById('modal-overlay');
            const openModalBtn = document.getElementById('open-modal-btn');
            const closeModalBtn = document.getElementById('close-modal-btn');
            const cancelModalBtn = document.getElementById('cancel-modal-btn');

            function openModal() {
                if(modal && overlay) {
                    modal.classList.remove('hidden');
                    overlay.classList.remove('hidden');
                    if(mainContent) mainContent.classList.add('filter', 'blur-sm', 'pointer-events-none');
                }
            }
            function closeModal() {
                if(modal && overlay) {
                    modal.classList.add('hidden');
                    overlay.classList.add('hidden');
                    if(mainContent) mainContent.classList.remove('filter', 'blur-sm', 'pointer-events-none');
                }
            }

            if(openModalBtn) openModalBtn.addEventListener('click', openModal);
            if(closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
            if(cancelModalBtn) cancelModalBtn.addEventListener('click', closeModal);
            if(overlay) overlay.addEventListener('click', closeModal);


            // Logika Filter Semester
            const btnGanjil = document.getElementById('btn-ganjil');
            const btnGenap = document.getElementById('btn-genap');
            const selectSemester = document.getElementById('select-semester');
            const ganjilOptions = [1, 3, 5, 7];
            const genapOptions = [2, 4, 6];

            function updateSemesterOptions(type) {
                selectSemester.innerHTML = '';

                // Tambahkan opsi "Semua Semester"
                const allOption = document.createElement('option');
                allOption.value = '';
                allOption.text = 'Semua Semester';
                selectSemester.appendChild(allOption);

                const options = (type === 'ganjil') ? ganjilOptions : genapOptions;

                options.forEach(semester => {
                    const option = document.createElement('option');
                    option.value = semester;
                    option.text = 'Semester ' + semester;
                    // Pilih opsi yang sesuai dengan request
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
                // Set tombol Ganjil/Genap berdasarkan 'name' di form, BUKAN event listener klik
                // Ini penting agar filter tetap aktif setelah halaman di-load ulang
                const currentSemester = '{{ request('semester') }}';
                if (genapOptions.includes(parseInt(currentSemester))) {
                    updateSemesterOptions('genap');
                } else {
                    // Default ke ganjil (termasuk jika semester kosong atau ganjil)
                    updateSemesterOptions('ganjil');
                }

                // Event listener untuk mengubah UI, tapi TIDAK men-submit form
                btnGanjil.addEventListener('click', () => updateSemesterOptions('ganjil'));
                btnGenap.addEventListener('click', () => updateSemesterOptions('genap'));
            }

            // Logika Dropdown Export
            const exportBtn = document.getElementById('export-btn');
            const exportMenu = document.getElementById('export-menu');
            const exportContainer = document.getElementById('export-dropdown-container');

            if(exportBtn && exportMenu && exportContainer) {
                exportBtn.addEventListener('click', function() {
                    exportMenu.classList.toggle('hidden');
                });
                document.addEventListener('click', function(event) {
                    if (!exportContainer.contains(event.target)) {
                        exportMenu.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</body>
</html>

