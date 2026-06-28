<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Data | Program Studi</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body x-data="{ sidebarOpen: true, showAddModal: false, showEditModal: false, showExportMenu: false, editData: { id: '', nama: '', kode: '' } }"  @close-modal.window="showAddModal = false; showEditModal = false;" class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300">
    <div class="flex min-h-screen">
        @include('components.sidebar')
        <main id="main-content" :class="sidebarOpen ? 'lg:ml-64' : ''" class="flex-1 min-w-0 p-6 sm:p-10 transition-all duration-300 ease-in-out ml-0">
            <div>
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <button @click="sidebarOpen = !sidebarOpen" class="flex flex-col hover:opacity-80 transition cursor-pointer" title="Toggle Sidebar">
                        <div class="w-2 h-5 bg-teal-800 rounded-tl-md"></div>
                        <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                    </button>
                    <h1 class="text-2xl font-bold text-gray-800 ml-3">Manajemen Program Studi</h1>
                </div>
                @include('components.header-profile')
            </div>


            <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md mt-6 border border-transparent">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-700 mb-4 sm:mb-0">Daftar Program Studi</h2>

                    <div class="flex items-center space-x-2">
                        <!-- Dropdown Menu Export -->
                        <div class="relative" @click.away="showExportMenu = false">
                            <button @click="showExportMenu = !showExportMenu" class="inline-flex items-center px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg shadow-md transition-all duration-200 text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Export
                                <svg class="w-3.5 h-3.5 ml-1.5 transition-transform duration-200" :class="showExportMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div x-show="showExportMenu" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl z-20 border border-gray-200 p-1" 
                                 style="display: none;">
                                <a href="{{ route('prodi.export.excel') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 rounded-md hover:bg-teal-50 hover:text-teal-800 transition-colors">
                                    <svg class="w-4 h-4 mr-2.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Export Excel
                                </a>
                                <a href="{{ route('prodi.export.pdf') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 rounded-md hover:bg-teal-50 hover:text-teal-800 transition-colors">
                                    <svg class="w-4 h-4 mr-2.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    Export PDF
                                </a>
                            </div>
                        </div>
                        <button @click="showAddModal = true" class="flex items-center px-5 py-2.5 bg-yellow-600 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-75">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Tambah Prodi
                        </button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-[#DBDBDB]">
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white whitespace-nowrap">
                            <thead class="bg-teal-800 text-white">
                                <tr>
                                    <th class="w-16 text-left py-4 px-3 uppercase font-semibold text-xs">No</th>
                                    <th class="text-left py-4 px-3 uppercase font-semibold text-xs">Program Studi</th>
                                    <th class="text-left py-4 px-3 uppercase font-semibold text-xs">Kode Prodi</th>
                                    <th class="w-48 text-left py-4 px-3 uppercase font-semibold text-xs">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                @forelse ($prodis as $prodi)
                                    <tr class="border-b border-[#DBDBDB] hover:bg-gray-50">
                                        <td class="text-left py-4 px-3 text-sm">{{ $loop->iteration }}</td>
                                        <td class="text-left py-4 px-3 text-sm">{{ $prodi->nama_prodi }}</td>
                                        <td class="text-left py-4 px-3 text-sm">{{ $prodi->kode_prodi }}</td>
                                        <td class="text-left py-4 px-3 text-sm">
                                            <div class="flex space-x-2">

                                                <a href="{{ route('prodi.show', $prodi->id_prodi) }}" class="flex items-center justify-center bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600 text-xs font-medium transition duration-150">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                    Detail
                                                </a>


                                                <button
                                                    @click="showEditModal = true;
                                                            document.getElementById('edit_id_prodi').value = '{{ $prodi->id_prodi }}';
                                                            document.getElementById('edit_nama_prodi').value = '{{ $prodi->nama_prodi }}';
                                                            document.getElementById('edit_kode_prodi').value = '{{ $prodi->kode_prodi }}';
                                                            document.getElementById('form-edit-prodi').action = '/management/prodi/{{ $prodi->id_prodi }}';
                                                            "
                                                    class="flex items-center justify-center bg-yellow-400 text-gray-900 px-3 py-1 rounded-md hover:bg-yellow-500 text-xs font-medium transition duration-150">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                    Edit
                                                </button>
                                                <button
                                                    onclick="confirmDelete('{{ route('prodi.destroy', $prodi->id_prodi) }}')"
                                                    class="flex items-center justify-center bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-700 text-xs font-medium transition duration-150">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Hapus
                                                </button>
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
    <div id="modal-overlay" x-show="showAddModal || showEditModal" x-transition.opacity class="fixed inset-0 bg-[rgba(0,0,0,0.5)] z-40" ></div>
    <div id="tambah-prodi-modal" x-show="showAddModal" x-transition @click.away="showAddModal = false" class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl z-50 w-full max-w-md border">
        <div class="p-6">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <h2 class="text-xl font-bold text-teal-800">Tambah Program Studi</h2>
                <button @click="showAddModal = false" type="button" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
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
                    <button type="button" @click="showAddModal = false" class="px-5 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL EDIT PRODI ===== -->
    <div id="edit-prodi-modal" x-show="showEditModal" x-transition @click.away="showEditModal = false" class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl z-50 w-full max-w-md border">
        <div class="p-6">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <h2 class="text-xl font-bold text-teal-800">Edit Program Studi</h2>
                <button @click="showEditModal = false" type="button" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
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
                    <button type="button" @click="showEditModal = false" class="px-5 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Include Popup Sukses -->
    @include('components.success-popup')
    <!-- Include Popup Delete Confirm -->
    @include('components.delete-confirm-popup')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Handlers for Success Popups ---
            // If checking specifically for Alpine.js state changes from outside, we might need dispatch events.
            // But here the form submits via AJAX.

            // --- Handle Form Submit (AJAX) ---
            function handleFormSubmit(formId, errorId, modalType) {
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
                        // Close Modal via Event
                        window.dispatchEvent(new CustomEvent('close-modal'));

                        if (modalType === 'add') {
                            document.getElementById('form-tambah-prodi').reset();
                        }

                        // Tampilkan popup sukses
                        if(window.showSuccessPopup) {
                            window.showSuccessPopup(data.message || 'Berhasil!');
                        } else {
                            alert(data.message || 'Berhasil!');
                            window.location.reload();
                        }

                        // Optional: reload table data or page
                        setTimeout(() => window.location.reload(), 1000);
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

            handleFormSubmit('form-tambah-prodi', 'tambah-errors', 'add');
            handleFormSubmit('form-edit-prodi', 'edit-errors', 'edit');
        });
    </script>
</body>
</html>
