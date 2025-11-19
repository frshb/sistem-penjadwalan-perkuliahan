<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Data | Data Ruangan</title>

@vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="bg-gray-100/50 overflow-x-hidden">

<button id="sidebar-open-btn" class="fixed top-6 left-6 z-20 text-gray-600 hover:text-gray-900 hidden transition-all duration-300 ease-in-out">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <div class="flex min-h-screen">

    <aside id="sidebar" class="w-64 flex-shrink-0 bg-white shadow-md flex flex-col transition-all duration-300 ease-in-out fixed inset-y-0 left-0 z-10">
    <div class="h-20 flex items-center justify-between px-6 border-b border-gray-200">
                <img src="{{ asset('1151.jpg') }}" alt="TSU Logo" class="h-10">

                <button id="sidebar-toggle" class="text-gray-500 hover:text-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Navigasi -->
            <nav class="px-4 py-4 space-y-1">
                <a href="#" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6-4h.01M12 12h.01M15 12h.01M12 15h.01M15 15h.01M9 15h.01"></path></svg>
                    Dashboard
                </a>

                <p class="px-4 pt-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Management Data</p>

                <a href="{{ route('prodi.index') }}" class="flex items-center py-2 pl-12 pr-4 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-100 hover:text-gray-900">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Management Prodi
                </a>

                <!-- Link Ruangan (Aktif) -->
                <a href="{{ route('ruangan.index') }}" class="flex items-center px-4 py-2 text-white bg-teal-600 rounded-lg shadow-lg">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                    Management Data Ruangan
                </a>

                <a href="{{ route('matakuliah.index') }}" class="flex items-center py-2 pl-12 pr-4 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-100 hover:text-gray-900">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    Management Mata Kuliah
                </a>
                <a href="{{ route('dosen.index') }}" class="flex items-center py-2 pl-12 pr-4 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-100 hover:text-gray-900">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Management Data Dosen
                </a>
                <a href="#" class="flex items-center py-2 pl-12 pr-4 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-100 hover:text-gray-900">
                   <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-2.39l-1.107-1.106a4 4 0 10-5.074 0L4.356 15.61A3 3 0 000 18v2h5m12 0v-2a3 3 0 01-3-3m-4.243-3.343A4.001 4.001 0 0012 6a4 4 0 00-4.243 5.657"></path></svg>
                    Management Data Mahasiswa
                </a>
                <a href="#" class="flex items-center py-2 pl-12 pr-4 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-100 hover:text-gray-900">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Management KP & Skripsi
                </a>
            </nav>

            <!-- Menu Bawah --><div class="px-4 py-4 border-t border-gray-200">
                <a href="#" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Modul Penjadwalan
                </a>
            </div>
        </aside>
        <!-- ===== End Sidebar ===== --><!-- ===== Main Content ===== --><main id="main-content" class="flex-1 p-6 sm:p-10 transition-all duration-300 ease-in-out ml-64">
            <!-- Header Konten --><div class="flex items-center">
                <div class="flex flex-col">
                    <div class="w-2 h-5 bg-teal-600 rounded-tl-md"></div>
                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 ml-3">
                    Management Data
                </h1>
            </div>

            <!-- Card Konten Utama --><div class="bg-white p-6 sm:p-8 rounded-lg shadow-md mt-6">

                <!-- Judul Card dan Tombol --><div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-700 mb-4 sm:mb-0">
                        Daftar Ruangan
                    </h2>

                    <a href="#" class="px-5 py-2 bg-yellow-600 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-75">
                        Tambah Ruang
                    </a>
                </div>

                <!-- Tabel Data --><div class="overflow-hidden rounded-lg border border-[#DBDBDB]">
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-teal-700 text-white">
                                <tr>
                                    <th class="w-16 text-left py-3 px-4 uppercase font-semibold text-sm">No</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Nama Ruangan</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Gedung</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Lokasi</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Fasilitas</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Kapasitas</th>
                                    <th class_exists("w-48 text-left py-3 px-4 uppercase font-semibold text-sm")>Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">

                                    @forelse ($ruangans as $ruangan)
                                    <tr class="border-b border-[#DBDBDB] hover:bg-gray-50">
                                        <td class="text-left py-3 px-4">{{ $loop->iteration }}</td>
                                        <td class="text-left py-3 px-4">{{ $ruangan->nama_ruang }}</td>
                                        <td class="text-left py-3 px-4">{{ $ruangan->gedung->nama_gedung }}</td>
                                        <td class="text-left py-3 px-4">{{ $ruangan->gedung->lokasi }}</td>
                                        <td class="text-left py-3 px-4">{{ $ruangan->fasilitas }}</td>
                                        <td class="text-left py-3 px-4">{{ $ruangan->kapasitas }}</td>
                                        <td class="text-left py-3 px-4">
                                            <div class="flex space-x-2">
                                                <a href="#" class="flex items-center justify-center bg-yellow-400 text-gray-900 px-4 py-1.5 rounded-md hover:bg-yellow-500 text-sm font-medium">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                    Edit
                                                </a>
                                                <form action="#" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ruangan ini?');">
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
                                    <!-- Jika data kosong --><tr>
                                        <td colspan="7" class="text-center py-4 text-gray-500">
                                            Data ruangan belum tersedia.
                                        </td>
                                    </tr>
                                @endForelse

                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- End Tabel Data --></div>

            <!-- End Card Konten Utama --></main>
        <!-- ===== End Main Content ===== --></div>

    <!-- ===== JavaScript untuk Sidebar ===== --><script>
        document.addEventListener('DOMContentLoaded', function() {
            const closeBtn = document.getElementById('sidebar-toggle');
            const openBtn = document.getElementById('sidebar-open-btn');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');

            if (closeBtn && openBtn && sidebar && mainContent) {
                closeBtn.addEventListener('click', function() {
                    sidebar.classList.add('-translate-x-full');
                    mainContent.classList.remove('ml-64');
                    // openBtn.classList.remove('hidden');
                });

                openBtn.addEventListener('click', function() {
                    sidebar.classList.remove('-translate-x-full');
                    mainContent.classList.add('ml-64');
                    openBtn.classList.add('hidden');
                });
            }
        });
    </script>
    <!-- ===== End JavaScript ===== --></body>
</html>
