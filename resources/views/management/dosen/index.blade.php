<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Data | Dosen</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body x-data="{ 
    sidebarOpen: true, 
    showAddModal: false, 
    showEditModal: false,
    showProfileModal: false,
    profileData: null,
    viewProfile(data) {
        this.profileData = data;
        this.showProfileModal = true;
    },
    editNama: '',
    editNidn: '',
    editProdi: '',
    editPrioritas: '',
    editPrioritasList: [''], 
    addPrioritasList: [''],
    editUrl: '',
    isLoading: true, 
    init() { setTimeout(() => this.isLoading = false, 2000) },
    addTimeSlot(type) {
        if (type === 'add') this.addPrioritasList.push('');
        if (type === 'edit') this.editPrioritasList.push('');
    },
    removeTimeSlot(index, type) {
        if (type === 'add' && this.addPrioritasList.length > 1) this.addPrioritasList.splice(index, 1);
        if (type === 'edit' && this.editPrioritasList.length > 1) this.editPrioritasList.splice(index, 1);
    },
    openEditModal(nidn, nama, prodi, prioritas) {
        this.editNama = nama;
        this.editNidn = nidn;
        this.editProdi = prodi;
        this.editPrioritas = prioritas;
        
        // Split priority string into array. Handle newlines predominantly, fallback to comma if no newline found (legacy support)
        if (prioritas) {
            if (prioritas.includes('\n')) {
                this.editPrioritasList = prioritas.split('\n');
            } else if (prioritas.includes(', ')) { 
                 this.editPrioritasList = prioritas.split(', ');
            } else {
                 this.editPrioritasList = [prioritas];
            }
        } else {
            this.editPrioritasList = [''];
        }
        
        this.editUrl = '{{ route('dosen.index') }}/' + nidn; 
        this.showEditModal = true;
    },
    confirmDelete(url) {
        if (confirm('Apakah Anda yakin ingin menghapus dosen ini?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            const csrfToken = document.querySelector('meta[name=csrf-token]').content;
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
}" class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300 font-sans">
    
    @include('components.sidebar')

    <main id="main-content" :class="sidebarOpen ? 'lg:ml-64' : ''" class="flex-1 p-6 sm:p-10 transition-all duration-300 ease-in-out bg-gray-50">
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
                        Daftar Dosen
                    </h2>
                    <div class="flex space-x-2">
                        <div x-data="{ exportOpen: false }" class="relative">
                            <button @click="exportOpen = !exportOpen" @click.away="exportOpen = false" class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-opacity-75 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Export Data
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div x-show="exportOpen" x-transition class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 overflow-hidden border border-gray-100" style="display: none;">
                                <a href="{{ route('dosen.export.excel') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-700 transition-colors">
                                    <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Export Excel
                                </a>
                                <a href="{{ route('dosen.export.pdf') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors">
                                    <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    Export PDF
                                </a>
                            </div>
                        </div>
                        <button @click="showAddModal = true" class="px-5 py-2 bg-yellow-600 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-75 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Tambah Dosen
                        </button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-[#DBDBDB]">
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-teal-800 text-white">
                                <tr>
                                    <th class="w-16 text-center py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">No</th>
                                    <th class="text-center py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">Prodi</th>
                                    <th class="text-center py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">Fakultas</th>
                                    <th class="text-center py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">Nama Dosen</th>
                                    <th class="text-center py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">NIDN</th>

                                    <th class="w-48 text-center py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                @forelse ($dosens as $dosen)
                                    <tr class="border-b border-[#DBDBDB] hover:bg-gray-50">
                                        <td class="text-center py-3 px-4 text-sm whitespace-nowrap">{{ ($dosens->currentPage() - 1) * $dosens->perPage() + $loop->iteration }}</td>
                                        <td class="text-center py-3 px-4 text-sm whitespace-nowrap">Teknik Informatika</td>
                                        <td class="text-center py-3 px-4 text-sm whitespace-nowrap">Fakultas Teknik</td>
                                        <td class="text-center py-3 px-4 text-sm whitespace-nowrap">
                                            <button @click="viewProfile({{ Js::from([
                                                'nama_dosen' => $dosen->nama_dosen,
                                                'nidn' => $dosen->nidn,
                                                'prodi' => $dosen->prodi->nama_prodi ?? 'Teknik Informatika',
                                                'fakultas' => 'Fakultas Teknik',
                                                'jadwals' => $dosen->jadwals->map(function($j) {
                                                    return [
                                                        'matkul' => $j->matkul->nama_matkul ?? '-',
                                                        'sks' => $j->matkul->sks ?? '-',
                                                        'kelas' => $j->kelas ?? '-',
                                                        'hari' => $j->hari->nama_hari ?? '-',
                                                        'waktu' => ($j->slot->waktu_mulai ?? '-') . ' - ' . ($j->slot->waktu_selesai ?? '-'),
                                                        'ruang' => $j->ruang->nama_ruang ?? '-',
                                                    ];
                                                })
                                            ]) }})" class="font-semibold text-teal-600 hover:text-teal-800 hover:underline focus:outline-none transition-colors">
                                                {{ $dosen->nama_dosen }}
                                            </button>
                                        </td>
                                        <td class="text-center py-3 px-4 text-sm whitespace-nowrap">{{ $dosen->nidn }}</td>

                                        <td class="text-center py-3 px-4 text-sm whitespace-nowrap">
                                            <div class="flex justify-center space-x-2">
                                                <button @click="openEditModal('{{ $dosen->nidn }}', '{{ $dosen->nama_dosen }}', '{{ $dosen->id_prodi }}', '{{ $dosen->ketersediaan_waktu }}')" class="flex items-center justify-center bg-yellow-400 text-gray-900 px-3 py-1 rounded-md hover:bg-yellow-500 text-xs font-medium transition-colors">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                    Edit
                                                </button>
                                                <button @click="confirmDelete('{{ route('dosen.destroy', $dosen->kode_dosen ?? $dosen->nidn) }}')" class="flex items-center justify-center bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-700 text-xs font-medium transition-colors">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-gray-500">
                                            Data dosen belum tersedia.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 flex justify-center">
                    {{ $dosens->links() }}
                </div>

            </div>
            </div>
    </main>
    </div>

    <!-- ===== AWAL MODAL TAMBAH DOSEN ===== -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
            <div x-show="showAddModal" @click="showAddModal = false" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showAddModal" class="relative z-50 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                        <h3 class="text-xl font-bold text-teal-800" id="modal-title">Tambah Dosen</h3>
                        <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <form action="{{ route('dosen.store') }}" method="POST" class="mt-6 space-y-6">
                        @csrf
                        <div class="flex items-center space-x-4">
                            <label for="nama_dosen" class="w-1/3 text-lg text-gray-700 font-medium">Nama Dosen :</label>    
                            <input type="text" id="nama_dosen" name="nama_dosen" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                        </div>
                        <div class="flex items-center space-x-4">
                            <label for="nidn" class="w-1/3 text-lg text-gray-700 font-medium">NIDN :</label>
                            <input type="text" id="nidn" name="nidn" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
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

    <!-- ===== AKHIR MODAL TAMBAH DOSEN ===== -->

    <!-- ===== AWAL MODAL EDIT DOSEN ===== -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
             <div x-show="showEditModal" @click="showEditModal = false" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showEditModal" class="inline-block align-bottom bg-white relative z-50 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                        <h3 class="text-xl font-bold text-teal-800">Edit Dosen</h3>
                        <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <form :action="editUrl" method="POST" class="mt-6 space-y-6">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center space-x-4">
                            <label for="edit_nama_dosen" class="w-1/3 text-lg text-gray-700 font-medium">Nama Dosen :</label>
                            <input type="text" id="edit_nama_dosen" name="nama_dosen" x-model="editNama" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                        </div>
                        <div class="flex items-center space-x-4">
                            <label for="edit_nidn" class="w-1/3 text-lg text-gray-700 font-medium">NIDN :</label>
                            <input type="text" id="edit_nidn" name="nidn" x-model="editNidn" class="w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
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

    @include('components.dosen-profile-modal')
</body>
</html>
