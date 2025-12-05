<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Data | Data Ruangan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .building-tab.active {
            border-bottom-color: #0d9488;
            color: #0d9488;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-gray-100/50 overflow-x-hidden">

    <button id="sidebar-open-btn" class="fixed top-6 left-6 z-20 text-gray-600 hover:text-gray-900 hidden transition-all duration-300 ease-in-out">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>
    <div class="flex min-h-screen">
        @include('components.sidebar')
            <main id="main-content" class="flex-1 p-6 sm:p-10 transition-all duration-300 ease-in-out ml-64">

                <div class="flex items-center">
                    <div class="flex flex-col">
                        <div class="w-2 h-5 bg-teal-600 rounded-tl-md"></div>
                        <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-800 ml-3">
                        Management Data
                    </h1>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-6 mb-4">
                    <h2 class="text-2xl font-bold text-gray-700 mb-4 sm:mb-0">
                        Daftar Ruangan
                    </h2>
                    <button id="open-modal-btn" class="px-5 py-2 bg-yellow-600 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-75">
                    Tambah Ruang Baru
                    </button>
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

                    <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md">
                        <h3 class="text-xl font-bold text-gray-700 mb-6">
                            Hasil Pencarian untuk: "{{ $searchTerm }}"
                        </h3>

                        <div class="overflow-hidden rounded-lg border border-[#DBDBDB]">
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
                                            <th class="border-b px-4 py-2 text-left">Ketersediaan</th>
                                            <th class="w-48 text-left py-3 px-4 uppercase font-semibold text-sm">Aksi</th>
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
                                            <td class="text-left py-3 px-4">{{ $ruangan->ketersediaan ? 'Tersedia' : 'Tidak Tersedia' }}</td>
                                            <td class="text-left py-3 px-4">
                                                <div class="flex space-x-2">
                                                <button onclick="openEditModal( '{{ $ruangan->id_ruang }}', `{{ addslashes($ruangan->nama_ruang) }}`, '{{ $ruangan->kapasitas }}',`{{ addslashes($ruangan->fasilitas ?? '') }}`,'{{ $ruangan->id_gedung }}','{{ $ruangan->ketersediaan }}')"
                                                    class="flex items-center justify-center bg-yellow-400 text-gray-900 px-4 py-1.5 rounded-md hover:bg-yellow-500 text-sm font-medium">
                                                    <svg class="w-4 h-4 mr-1" ...></svg>
                                                    Edit
                                                </button>
                                                    <form action="{{ route('ruangan.destroy', $ruangan->id_ruang) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ruangan ini?');">
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
                        <button class="building-tab px-4 py-2 border-b-2 border-transparent text-gray-600 hover:text-teal-700 active whitespace-nowrap" data-target="all">
                            Semua Gedung
                        </button>
                        @foreach ($ruangansByGedung as $namaGedung => $ruangansInGedung)
                            <button class="building-tab px-4 py-2 border-b-2 border-transparent text-gray-600 hover:text-teal-700 whitespace-nowrap" data-target="#gedung-{{ Str::slug($namaGedung) }}">
                                {{ $namaGedung }}
                            </button>
                        @endforeach
                    </div>

                    @forelse ($ruangansByGedung as $namaGedung => $ruangansInGedung)
                        <div class="building-content mb-8" id="gedung-{{ Str::slug($namaGedung) }}">

                            <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md">
                                <h3 class="text-xl font-bold text-gray-700 mb-1">
                                    {{ $namaGedung }}
                                </h3>
                                <p class="text-sm text-gray-500 mb-6">{{ $ruangansInGedung->first()->gedung->lokasi ?? 'Lokasi tidak diketahui' }}</p>

                                <div class="overflow-hidden rounded-lg border border-[#DBDBDB]">
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
                                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Ketersediaan</th>
                                                    <th class="w-48 text-left py-3 px-4 uppercase font-semibold text-sm">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-700">
                                                @forelse ($ruangansInGedung as $ruangan)
                                                <tr class="border-b border-[#DBDBDB] hover:bg-gray-50">
                                                    <td class="text-left py-3 px-4">{{ $loop->iteration }}</td>
                                                    <td class="text-left py-3 px-4">{{ $ruangan->nama_ruang }}</td>
                                                    <td class="text-left py-3 px-4">{{ $ruangan->gedung->nama_gedung }}</td>
                                                    <td class="text-left py-3 px-4">{{ $ruangan->gedung->lokasi }}</td>
                                                    <td class="text-left py-3 px-4">{{ $ruangan->fasilitas }}</td>
                                                    <td class="text-left py-3 px-4">{{ $ruangan->kapasitas }}</td>
                                                    <td class="text-left py-3 px-4">{{ $ruangan->ketersediaan ? 'Tersedia' : 'Tidak Tersedia' }}</td>
                                                    <td class="text-left py-3 px-4">
                                                        <div class="flex space-x-2">
                                                        <button onclick="openEditModal( '{{ $ruangan->id_ruang }}', `{{ addslashes($ruangan->nama_ruang) }}`, '{{ $ruangan->kapasitas }}',`{{ addslashes($ruangan->fasilitas ?? '') }}`,'{{ $ruangan->id_gedung }}','{{ $ruangan->ketersediaan }}')"
                                                        class="flex items-center justify-center bg-yellow-400 text-gray-900 px-4 py-1.5 rounded-md hover:bg-yellow-500 text-sm font-medium">
                                                        <svg class="w-4 h-4 mr-1" ...></svg>
                                                        Edit
                                                        </button>
                                                            <form action="{{ route('ruangan.destroy', $ruangan->id_ruang) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ruangan ini?');">
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
            </main>
    </div>

    <!-- MODAL OVERLAY -->
    <div id="modal-overlay" class="fixed inset-0 bg-[rgba(0,0,0,0.5)] z-40 hidden transition-opacity duration-300"></div>

    <!-- MODAL TAMBAH RUANG -->
    <div id="tambah-ruang-modal" class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl z-50 w-full max-w-md hidden transform transition-all duration-300 scale-95">
        <div class="p-6">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <h2 class="text-xl font-bold text-teal-800">Tambah Ruangan</h2>
                <button onclick="closeModal('tambah')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="form-tambah-ruang" action="{{ route('ruangan.store') }}" method="POST" class="mt-6 space-y-4">
                @csrf
                <div id="tambah-errors" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm"></div>

                <div class="flex items-center space-x-4">
                    <label for="nama_ruang" class="w-1/3 text-lg text-gray-700 font-medium">Nama Ruang :</label>
                    <input type="text" id="nama_ruang" name="nama_ruang" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                </div>

                <div class="flex items-center space-x-4">
                    <label for="kapasitas" class="w-1/3 text-lg text-gray-700 font-medium">Kapasitas :</label>
                    <input type="number" id="kapasitas" name="kapasitas" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" min="0" required>
                </div>

                <div class="flex items-start space-x-4">
                    <label for="fasilitas" class="w-1/3 text-lg text-gray-700 font-medium">Fasilitas :</label>
                    <textarea id="fasilitas" name="fasilitas" rows="3" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"></textarea>
                </div>

                <div class="flex items-center space-x-4">
                    <label for="id_gedung" class="w-1/3 text-lg text-gray-700 font-medium">Gedung :</label>
                    <select id="id_gedung" name="id_gedung" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                        <option value="">-- Pilih Gedung --</option>
                        @foreach($gedungs as $gedung)
                            <option value="{{ $gedung->id_gedung }}">{{ $gedung->nama_gedung }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center space-x-4">
                    <label for="ketersediaan" class="w-1/3 text-lg text-gray-700 font-medium">Ketersediaan :</label>
                    <select id="ketersediaan" name="ketersediaan" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                        <option value="1">Tersedia</option>
                        <option value="0">Tidak Tersedia</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <button type="button" onclick="closeModal('tambah')" class="px-5 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT RUANG -->
    <div id="edit-ruang-modal" class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl z-50 w-full max-w-md hidden transform transition-all duration-300 scale-95">
        <div class="p-6">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <h2 class="text-xl font-bold text-teal-800">Edit Ruangan</h2>
                <button onclick="closeModal('edit')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="form-edit-ruang" method="POST" class="mt-6 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id_ruang" name="id_ruang">
                <div id="edit-errors" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm"></div>

                <div class="flex items-center space-x-4">
                    <label for="edit_nama_ruang" class="w-1/3 text-lg text-gray-700 font-medium">Nama Ruang :</label>
                    <input type="text" id="edit_nama_ruang" name="nama_ruang" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                </div>

                <div class="flex items-center space-x-4">
                    <label for="edit_kapasitas" class="w-1/3 text-lg text-gray-700 font-medium">Kapasitas :</label>
                    <input type="number" id="edit_kapasitas" name="kapasitas" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" min="0" required>
                </div>

                <div class="flex items-start space-x-4">
                    <label for="edit_fasilitas" class="w-1/3 text-lg text-gray-700 font-medium">Fasilitas :</label>
                    <textarea id="edit_fasilitas" name="fasilitas" rows="3" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"></textarea>
                </div>

                <div class="flex items-center space-x-4">
                    <label for="edit_id_gedung" class="w-1/3 text-lg text-gray-700 font-medium">Gedung :</label>
                    <select id="edit_id_gedung" name="id_gedung" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                        <option value="">-- Pilih Gedung --</option>
                        @foreach($gedungs as $gedung)
                            <option value="{{ $gedung->id_gedung }}">{{ $gedung->nama_gedung }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center space-x-4">
                    <label for="edit_ketersediaan" class="w-1/3 text-lg text-gray-700 font-medium">Ketersediaan :</label>
                    <select id="edit_ketersediaan" name="ketersediaan" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                        <option value="1">Tersedia</option>
                        <option value="0">Tidak Tersedia</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <button type="button" onclick="closeModal('edit')" class="px-5 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700">Update</button>
                </div>
            </form>
        </div>
        </div>

    <!-- Include Popup Sukses -->
    @include('components.success-popup')

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== Logika Sidebar =====
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

    // ===== JavaScript untuk Filter Tab Gedung =====
    const tabs = document.querySelectorAll('.building-tab');
    const contents = document.querySelectorAll('.building-content');

    if (tabs.length > 0 && contents.length > 0) {
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const target = this.getAttribute('data-target');
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                contents.forEach(content => {
                    if (target === 'all') {
                        content.style.display = 'block';
                    } else {
                        content.style.display = ('#' + content.id === target) ? 'block' : 'none';
                    }
                });
            });
        });
    }

       // ==========================================
    //  MODAL HANDLER
    // ==========================================

    function openModal(type) {
        const overlay = document.getElementById("modal-overlay");

        if (type === "tambah") {
            const modal = document.getElementById("tambah-ruang-modal");
            modal.classList.remove("hidden");
            overlay.classList.remove("hidden");

            setTimeout(() => {
                modal.classList.remove("scale-95", "opacity-0");
                overlay.classList.remove("opacity-0");
            }, 10);
        }

        if (type === "edit") {
            const modal = document.getElementById("edit-ruang-modal");
            modal.classList.remove("hidden");
            overlay.classList.remove("hidden");

            setTimeout(() => {
                modal.classList.remove("scale-95", "opacity-0");
                overlay.classList.remove("opacity-0");
            }, 10);
        }
    }

    function closeModal(type) {
        const overlay = document.getElementById("modal-overlay");

        if (type === "tambah") {
            const modal = document.getElementById("tambah-ruang-modal");
            modal.classList.add("scale-95", "opacity-0");
            overlay.classList.add("opacity-0");

            setTimeout(() => {
                modal.classList.add("hidden");
                overlay.classList.add("hidden");
            }, 200);
        }

        if (type === "edit") {
            const modal = document.getElementById("edit-ruang-modal");
            modal.classList.add("scale-95", "opacity-0");
            overlay.classList.add("opacity-0");

            setTimeout(() => {
                modal.classList.add("hidden");
                overlay.classList.add("hidden");
            }, 200);
        }
    }

    // ==========================================
    //  EDIT HANDLER
    // ==========================================
    function openEditModal(id, nama_ruang, kapasitas, fasilitas, id_gedung, ketersediaan) {
        const form = document.getElementById("form-edit-ruang");
        form.action = "/management/ruangan/" + id;

        document.getElementById("edit_nama_ruang").value = nama_ruang;
        document.getElementById("edit_kapasitas").value = kapasitas;
        document.getElementById("edit_fasilitas").value = fasilitas ?? '';
        document.getElementById("edit_id_gedung").value = id_gedung;
        document.getElementById("edit_ketersediaan").value = ketersediaan;

        openModal("edit");
    }

    // ==========================================
    //  DELETE HANDLER
    // ==========================================
    function deleteRuang(id) {
        if (confirm("Yakin ingin menghapus data ruangan ini?")) {
            document.getElementById("form-delete-" + id).submit();
        }
    }

    // ==========================================
    //  BIND BUTTON TAMBAH
    // ==========================================
    const tambahBtn = document.getElementById("open-modal-btn");
    if (tambahBtn) {
        tambahBtn.addEventListener("click", function () {
            openModal("tambah");
        });
    }

    // ==========================================
    //  OVERLAY CLICK → TUTUP MODAL
    // ==========================================
    document.getElementById("modal-overlay").addEventListener("click", function() {
        closeModal("tambah");
        closeModal("edit");
    });

    // ==========================================
    //  EXPORT FUNGSI KE GLOBAL UNTUK HTML BUTTON
    // ==========================================
    window.openEditModal = openEditModal;
    window.openModal = openModal;
    window.closeModal = closeModal;
    window.deleteRuang = deleteRuang;

});
</script>

    </body>
</html>
