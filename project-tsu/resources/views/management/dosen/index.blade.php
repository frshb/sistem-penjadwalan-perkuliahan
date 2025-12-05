<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Data | Dosen</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="bg-gray-100/50 overflow-x-hidden min-h-screen">
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
                        Daftar Dosen
                    </h2>
                    <div class="flex space-x-2">
                        <!-- Tombol Export BARU -->
                        <a href="#" class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-opacity-75">
                            Export
                        </a>
                        <button id="open-modal-btn" class="px-5 py-2 bg-yellow-600 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-75">
                            Tambah Dosen
                        </button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-[#DBDBDB]">
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-teal-800 text-white">
                                <tr>
                                    <th class="w-16 text-left py-3 px-4 uppercase font-semibold text-sm">No</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Nama Dosen</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">NUPTK</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Mata Kuliah</th>
                                    <th class="w-48 text-left py-3 px-4 uppercase font-semibold text-sm">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">

                                @forelse ($dosens as $dosen)
                                    <tr class="border-b border-[#DBDBDB] hover:bg-gray-50">
                                        <td class="text-left py-3 px-4">{{ $loop->iteration }}</td>
                                        <td class="text-left py-3 px-4">{{ $dosen->nama_dosen }}</td>
                                        <td class="text-left py-3 px-4">{{ $dosen->nuptk }}</td>
                                        <td class="text-left py-3 px-4">{{ $dosen->mata_kuliah }}</td>
                                        <td class="text-left py-3 px-4">
                                            <div class="flex space-x-2">
                                                <a href="#" class="flex items-center justify-center bg-yellow-400 text-gray-900 px-4 py-1.5 rounded-md hover:bg-yellow-500 text-sm font-medium">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                    Edit
                                                </a>
                                                <form action="#" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dosen ini?');">
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
                                        <td colspan="5" class="text-center py-4 text-gray-500">
                                            Data dosen belum tersedia.
                                        </td>
                                    </tr>
                                @endForelse

                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- ===== AWAL MODAL TAMBAH DOSEN ===== -->
    <div id="modal-overlay" class="fixed inset-0 bg-[rgba(0,0,0,0.5)] z-20 hidden"></div>
    <div id="tambah-dosen-modal" class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl z-40 w-full max-w-md hidden">
        <div class="p-6">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <h2 class="text-xl font-bold text-teal-800">Tambah Dosen</h2>
                <button id="close-modal-btn" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('dosen.store') }}" method="POST" class="mt-6 space-y-6">
                @csrf
                <div class="flex items-center space-x-4">
                    <label for="nama_dosen" class="w-1/3 text-lg text-gray-700 font-medium">Nama Dosen :</label>
                    <input type="text" id="nama_dosen" name="nama_dosen" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                </div>

                <div class="flex items-center space-x-4">
                    <label for="nuptk" class="w-1/3 text-lg text-gray-700 font-medium">NUPTK :</label>
                    <input type="text" id="nuptk" name="nuptk" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                </div>

                <div class="flex items-center space-x-4">
                    <label for="mata_kuliah" class="w-1/3 text-lg text-gray-700 font-medium">Mata Kuliah :</label>
                    <input type="text" id="mata_kuliah" name="mata_kuliah" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Pisahkan dengan koma">
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
            const modal = document.getElementById('tambah-dosen-modal');
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

        });
    </script>
</body>
</html>
