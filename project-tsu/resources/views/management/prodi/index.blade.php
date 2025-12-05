<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Data | Program Studi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100/50 overflow-x-hidden min-h-screen">

    <!-- Hamburger -->
    <button id="sidebar-open-btn" class="fixed top-6 left-6 z-20 text-gray-600 hover:text-gray-900 hidden transition-all duration-300 ease-in-out">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
    </button>

    <div class="flex min-h-screen">

        <!-- Side bar aktif dosen -->
        @include('components.sidebar')
        <main id="main-content" class="flex-1 p-6 sm:p-10 transition-all duration-300 ease-in-out ml-64">
            <div class="flex items-center">
                <div class="flex flex-col">
                    <div class="w-2 h-5 bg-teal-800 rounded-tl-md"></div>
                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 ml-3">Management Data</h1>
            </div>

            <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md mt-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-700 mb-4 sm:mb-0">Daftar Program Studi</h2>

                    <!-- Tombol Tambah -->
                    <button id="open-modal-btn" class="flex items-center px-5 py-2 bg-yellow-600 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-75">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Tambah Prodi
                    </button>
                </div>

                <div class="overflow-hidden rounded-lg border border-[#DBDBDB]">
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-teal-800 text-white">
                                <tr>
                                    <th class="w-16 text-left py-3 px-4 uppercase font-semibold text-sm">No</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Program Studi</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Kode Prodi</th>
                                    <th class="w-48 text-left py-3 px-4 uppercase font-semibold text-sm">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                @forelse ($prodis as $prodi)
                                    <tr class="border-b border-[#DBDBDB] hover:bg-gray-50">
                                        <td class="text-left py-3 px-4">{{ $loop->iteration }}</td>
                                        <td class="text-left py-3 px-4">{{ $prodi->nama_prodi }}</td>
                                        <td class="text-left py-3 px-4">{{ $prodi->kode_prodi }}</td>
                                        <td class="text-left py-3 px-4">
                                            <div class="flex space-x-2">
                                                <!-- Tombol Edit -->
                                                <button
                                                    onclick="openEditModal('{{ $prodi->id_prodi }}', '{{ $prodi->nama_prodi }}', '{{ $prodi->kode_prodi }}')"
                                                    class="flex items-center justify-center bg-yellow-400 text-gray-900 px-4 py-1.5 rounded-md hover:bg-yellow-500 text-sm font-medium transition duration-150">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                    Edit
                                                </button>

                                                <form action="{{ route('prodi.destroy', $prodi->id_prodi) }}" method="POST" onsubmit="return confirm('Hapus prodi ini?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="flex items-center justify-center bg-red-600 text-white px-4 py-1.5 rounded-md hover:bg-red-700 text-sm font-medium transition duration-150">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4 text-gray-500">Data belum tersedia.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- ===== MODAL TAMBAH PRODI ===== -->
    <div id="modal-overlay" class="fixed inset-0 bg-[rgba(0,0,0,0.5)] z-40 hidden transition-opacity duration-300"></div>

    <div id="tambah-prodi-modal" class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl z-50 w-full max-w-md hidden transform transition-all duration-300 scale-95">
        <div class="p-6">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <h2 class="text-xl font-bold text-teal-800">Tambah Program Studi</h2>
                <button onclick="closeModal('tambah')" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <form id="form-tambah-prodi" action="{{ route('prodi.store') }}" method="POST" class="mt-6 space-y-6">
                @csrf
                <div id="tambah-errors" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm"></div>
                <div class="flex items-center space-x-4">
                    <label for="nama_prodi" class="w-1/3 text-lg text-gray-700 font-medium">Nama Prodi :</label>
                    <input type="text" id="nama_prodi" name="nama_prodi" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                </div>
                <div class="flex items-center space-x-4">
                    <label for="kode_prodi" class="w-1/3 text-lg text-gray-700 font-medium">Kode Prodi :</label>
                    <input type="text" id="kode_prodi" name="kode_prodi" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <div class="flex justify-end space-x-4 pt-6">
                    <button type="button" onclick="closeModal('tambah')" class="px-5 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL EDIT PRODI ===== -->
    <div id="edit-prodi-modal" class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl z-50 w-full max-w-md hidden transform transition-all duration-300 scale-95">
        <div class="p-6">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <h2 class="text-xl font-bold text-teal-800">Edit Program Studi</h2>
                <button onclick="closeModal('edit')" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <form id="form-edit-prodi" method="POST" class="mt-6 space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id_prodi" name="id_prodi">
                <div id="edit-errors" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm"></div>

                <div class="flex items-center space-x-4">
                    <label for="edit_nama_prodi" class="w-1/3 text-lg text-gray-700 font-medium">Nama Prodi :</label>
                    <input type="text" id="edit_nama_prodi" name="nama_prodi" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                </div>
                <div class="flex items-center space-x-4">
                    <label for="edit_kode_prodi" class="w-1/3 text-lg text-gray-700 font-medium">Kode Prodi :</label>
                    <input type="text" id="edit_kode_prodi" name="kode_prodi" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>

                <div class="flex justify-end space-x-4 pt-6">
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
            // --- Logic Sidebar ---
            const closeBtn = document.getElementById('sidebar-toggle');
            const openBtn = document.getElementById('sidebar-open-btn');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            if (closeBtn && openBtn && sidebar && mainContent) {
                closeBtn.addEventListener('click', function() { sidebar.classList.add('-translate-x-full'); mainContent.classList.remove('ml-64'); openBtn.classList.remove('hidden'); });
                openBtn.addEventListener('click', function() { sidebar.classList.remove('-translate-x-full'); mainContent.classList.add('ml-64'); openBtn.classList.add('hidden'); });
            }

            // --- Helper: Toggle Blur ---
            function toggleBlur(isBlur) {
                if (isBlur) mainContent.classList.add('filter', 'blur-sm', 'pointer-events-none');
                else mainContent.classList.remove('filter', 'blur-sm', 'pointer-events-none');
            }

            // --- Logic Modal Tambah ---
            const modalTambah = document.getElementById('tambah-prodi-modal');
            const overlay = document.getElementById('modal-overlay');

            document.getElementById('open-modal-btn').addEventListener('click', function() {
                modalTambah.classList.remove('hidden');
                modalTambah.classList.remove('scale-95');
                modalTambah.classList.add('scale-100');
                overlay.classList.remove('hidden');
                toggleBlur(true);
            });

            // --- Logic Modal Edit ---
            const modalEdit = document.getElementById('edit-prodi-modal');
            const formEdit = document.getElementById('form-edit-prodi');
            const inputEditId = document.getElementById('edit_id_prodi');
            const inputEditNama = document.getElementById('edit_nama_prodi');
            const inputEditKode = document.getElementById('edit_kode_prodi');

            window.openEditModal = function(id, nama, kode) {
                inputEditId.value = id;
                inputEditNama.value = nama;
                inputEditKode.value = kode;

                // PENTING: URL Update Dinamis
                formEdit.action = `/management/prodi/${id}`;

                modalEdit.classList.remove('hidden');
                modalEdit.classList.remove('scale-95');
                modalEdit.classList.add('scale-100');
                overlay.classList.remove('hidden');
                toggleBlur(true);
            };

            // --- Fungsi Tutup Modal ---
            window.closeModal = function(type) {
                overlay.classList.add('hidden');
                toggleBlur(false);

                if (type === 'tambah') {
                    modalTambah.classList.add('hidden');
                    modalTambah.classList.remove('scale-100');
                    modalTambah.classList.add('scale-95');
                    document.getElementById('form-tambah-prodi').reset();
                    document.getElementById('tambah-errors').classList.add('hidden');
                } else if (type === 'edit') {
                    modalEdit.classList.add('hidden');
                    modalEdit.classList.remove('scale-100');
                    modalEdit.classList.add('scale-95');
                    document.getElementById('edit-errors').classList.add('hidden');
                }
            };

            overlay.addEventListener('click', function() {
                closeModal('tambah');
                closeModal('edit');
            });

            // --- Handle Form Submit (AJAX) ---
            function handleFormSubmit(formId, errorId) {
                const form = document.getElementById(formId);
                const errorBox = document.getElementById(errorId);

                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(form);

                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(async response => {
                        const text = await response.text();
                        try {
                            const data = JSON.parse(text);
                            if (!response.ok) {
                                throw data;
                            }
                            return data;
                        } catch (e) {
                            console.error('Server Error Response:', text);
                            throw new Error('Terjadi kesalahan server. Cek console browser.');
                        }
                    })
                    .then(data => {
                        if (formId === 'form-tambah-prodi') closeModal('tambah');
                        else closeModal('edit');

                        // Tampilkan popup sukses
                        window.showSuccessPopup(data.message || 'Berhasil!');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        let errorMessage = 'Terjadi kesalahan.';

                        if (error.errors) {
                            errorMessage = Object.values(error.errors).flat().join('<br>');
                        } else if (error.message) {
                            errorMessage = error.message;
                        }

                        errorBox.innerHTML = errorMessage;
                        errorBox.classList.remove('hidden');
                    });
                });
            }

            handleFormSubmit('form-tambah-prodi', 'tambah-errors');
            handleFormSubmit('form-edit-prodi', 'edit-errors');
        });
    </script>
</body>
</html>
