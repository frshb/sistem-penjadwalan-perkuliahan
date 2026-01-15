<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjadwalan Manual | TSU</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300" 
      x-data="manualSchedulingApp()">

    <div class="flex min-h-screen">
        @include('components.sidebar')

        <!-- Main Content -->
        <main id="main-content" :class="sidebarOpen ? 'lg:ml-64' : ''" class="flex-1 min-w-0 p-6 sm:p-10 transition-all duration-300 ease-in-out ml-0 bg-gray-50 flex flex-col">
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
                
                <!-- Filter Skeleton -->
                <div class="flex gap-4">
                     <div class="w-full h-12 bg-white rounded-lg shadow-sm border border-gray-200"></div>
                </div>

                <!-- Table Skeleton -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 space-y-4">
                    <div class="h-12 bg-gray-200 rounded-lg"></div>
                    <div class="h-12 bg-gray-200 rounded-lg"></div>
                    <div class="h-12 bg-gray-200 rounded-lg"></div>
                </div>
            </div>

            <div x-show="!isLoading">
            
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div class="flex items-center">
                    <div class="flex flex-col">
                        <div class="w-2 h-5 bg-teal-800 rounded-tl-md"></div>
                        <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800 ml-3">Modul Penjadwalan</h1>
                </div>
                @include('components.header-profile')
            </div>

            <!-- Action Headers & Filters -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                 <h2 class="text-xl font-bold text-gray-700">Penjadwalan Manual</h2>
                 
                 <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                     <!-- Filters -->
                     <select x-model="filters.kurikulum" class="block w-full sm:w-40 border border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                        <option value="">Semua Kurikulum</option>
                        <option value="2020">Kurikulum 2020</option>
                        <option value="2024">Kurikulum 2024</option>
                     </select>

                     <select x-model="filters.semester" class="block w-full sm:w-40 border border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                        <option value="">Semua Semester</option>
                        <option value="1">Semester 1</option>
                        <option value="3">Semester 3</option>
                        <option value="5">Semester 5</option>
                        <option value="7">Semester 7</option>
                     </select>

                     <button @click="openModal()" class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 transition duration-200 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Jadwal
                    </button>
                 </div>
            </div>

            <!-- Grouped Tables Container -->
            <div class="space-y-8">
                <template x-for="(group, groupIndex) in groupedSchedules" :key="group.key">
                    <div>
                        <!-- Group Label -->
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="text-lg font-bold text-gray-700 flex items-center">
                                <span class="w-1 h-6 bg-teal-600 rounded mr-2"></span>
                                <span x-text="'Kurikulum ' + group.kurikulum"></span>
                                <span class="mx-2 text-gray-400">/</span>
                                <span x-text="'Semester ' + group.semester"></span>
                            </h3>
                            <span class="bg-gray-100 text-gray-600 text-xs font-semibold px-2.5 py-0.5 rounded border border-gray-200" x-text="group.schedules.length + ' Jadwal'"></span>
                        </div>

                        <!-- Table Wrapper (Matches Dosen Page Style) -->
                        <div class="overflow-hidden rounded-lg border border-[#DBDBDB] shadow-sm bg-white">
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead class="bg-teal-800 text-white">
                                        <tr>
                                            <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wider w-12 border-b border-teal-700">No</th>
                                            <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wider border-b border-teal-700">Mata Kuliah</th>
                                            <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wider border-b border-teal-700">Dosen</th>
                                            <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wider border-b border-teal-700">Kelas</th>
                                            <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wider border-b border-teal-700">Hari & Jam</th>
                                            <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wider border-b border-teal-700">Ruangan</th>
                                            <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wider w-24 border-b border-teal-700">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-700">
                                        <template x-for="(schedule, index) in group.schedules" :key="schedule.id">
                                            <tr class="border-b border-[#DBDBDB] hover:bg-gray-50 transition-colors">
                                                <td class="py-4 px-4 text-sm" x-text="index + 1"></td>
                                                <td class="py-4 px-4 text-sm font-medium text-gray-900" x-text="schedule.matkul"></td>
                                                <td class="py-4 px-4 text-sm" x-text="schedule.dosen"></td>
                                                <td class="py-4 px-4 text-sm">
                                                    <div class="flex flex-wrap gap-1">
                                                        <template x-for="cls in schedule.kelas">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800" x-text="cls"></span>
                                                        </template>
                                                        <span x-show="schedule.is_gabungan" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 ml-1">Gabungan</span>
                                                    </div>
                                                </td>
                                                <td class="py-4 px-4 text-sm">
                                                    <div x-text="schedule.hari"></div>
                                                    <div class="text-xs text-gray-500" x-text="schedule.jam"></div>
                                                </td>
                                                <td class="py-4 px-4 text-sm font-semibold text-teal-700" x-text="schedule.ruang"></td>
                                                <td class="py-4 px-4 text-sm">
                                                    <div class="flex space-x-2">
                                                        <button @click="editSchedule(schedule)" class="flex items-center justify-center bg-yellow-400 text-gray-900 px-3 py-1 rounded-md hover:bg-yellow-500 text-xs font-medium transition-colors">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                            Edit
                                                        </button>
                                                        <button @click="deleteSchedule(schedule.id)" class="flex items-center justify-center bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-700 text-xs font-medium transition-colors">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                            Hapus
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <div x-show="groupedSchedules.length === 0" class="bg-white rounded-lg shadow-md border border-gray-200 p-10 text-center">
                     <p class="text-gray-500 text-lg">Tidak ada jadwal yang sesuai filter atau data belum tersedia.</p>
                     <button @click="openModal()" class="mt-4 text-teal-600 hover:text-teal-800 font-medium">Tambah Jadwal Baru</button>
                </div>
            </div>

        </main>
    </div>

    <!-- Modal -->
    <div x-show="showModal" class="fixed inset-0 z-[999] overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
            <div x-show="showModal" @click="closeModal()" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showModal" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-[1000]">
                <form @submit.prevent="saveSchedule">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" x-text="editMode ? 'Edit Jadwal' : 'Tambah Jadwal'"></h3>
                        
                        <div class="space-y-4">
                            <!-- Kurikulum & Semester -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Kurikulum</label>
                                    <select x-model="formData.kurikulum" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                                        <option value="2020">Kurikulum 2020</option>
                                        <option value="2024">Kurikulum 2024</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Semester</label>
                                    <select x-model="formData.semester" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                                        <option value="1">1</option>
                                        <option value="3">3</option>
                                        <option value="5">5</option>
                                        <option value="7">7</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Mata Kuliah -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mata Kuliah</label>
                                <select x-model="formData.matkul" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                                    <option value="">Pilih Mata Kuliah</option>
                                    <optgroup label="Semester 1">
                                        <option value="Pengantar TI">Pengantar TI</option>
                                        <option value="Algoritma & Pemrograman">Algoritma & Pemrograman</option>
                                    </optgroup>
                                    <optgroup label="Semester 3">
                                        <option value="Sistem Basis Data">Sistem Basis Data</option>
                                        <option value="Pemrogaman Web">Pemrogaman Web</option>
                                        <option value="Struktur Data">Struktur Data</option>
                                    </optgroup>
                                    <optgroup label="Semester 5">
                                        <option value="Jaringan Komputer">Jaringan Komputer</option>
                                        <option value="Kecerdasan Buatan">Kecerdasan Buatan</option>
                                        <option value="Kewirausahaan">Kewirausahaan</option>
                                    </optgroup>
                                     <optgroup label="Semester 7">
                                        <option value="Kerja Praktik">Kerja Praktik</option>
                                        <option value="Metodologi Penelitian">Metodologi Penelitian</option>
                                    </optgroup>
                                </select>
                            </div>

                            <!-- Dosen -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Dosen Pengampu</label>
                                <select x-model="formData.dosen" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                                    <option value="">Pilih Dosen</option>
                                    <option value="Dr. Budi Santoso">Dr. Budi Santoso</option>
                                    <option value="Siti Aminah M.Kom">Siti Aminah M.Kom</option>
                                    <option value="Prof. Andi Wijaya">Prof. Andi Wijaya</option>
                                    <option value="Rahmat Hidayat M.T.">Rahmat Hidayat M.T.</option>
                                    <option value="Eko Prasetyo S.T., M.Kom">Eko Prasetyo S.T., M.Kom</option>
                                </select>
                            </div>

                             <!-- Kelas Setup -->
                             <div class="bg-gray-50 p-3 rounded-md border border-gray-200">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-medium text-gray-700">Kelas</label>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_gabungan" x-model="formData.is_gabungan" class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded">
                                        <label for="is_gabungan" class="ml-2 block text-sm text-gray-900">Kelas Gabungan?</label>
                                    </div>
                                </div>

                                <div x-show="!formData.is_gabungan">
                                    <select x-model="formData.selectedClassSingle" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                                        <option value="">Pilih Kelas</option>
                                        <template x-for="cls in availableClasses">
                                            <option :value="cls" x-text="cls"></option>
                                        </template>
                                    </select>
                                </div>

                                <div x-show="formData.is_gabungan">
                                    <div class="grid grid-cols-3 gap-2 mt-2">
                                        <template x-for="cls in availableClasses" :key="cls">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" :value="cls" x-model="formData.selectedClassesMulti" class="form-checkbox h-4 w-4 text-teal-600">
                                                <span class="ml-2 text-sm text-gray-700" x-text="cls"></span>
                                            </label>
                                        </template>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">*Pilih lebih dari satu kelas untuk digabungkan.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Hari -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Hari</label>
                                    <select x-model="formData.hari" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                                        <option value="">Pilih Hari</option>
                                        <option value="Senin">Senin</option>
                                        <option value="Selasa">Selasa</option>
                                        <option value="Rabu">Rabu</option>
                                        <option value="Kamis">Kamis</option>
                                        <option value="Jumat">Jumat</option>
                                        <option value="Sabtu">Sabtu</option>
                                    </select>
                                </div>

                                <!-- Jam -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Jam</label>
                                    <select x-model="formData.jam" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                                        <option value="">Pilih Jam</option>
                                        <option value="08:00 - 09:40">08:00 - 09:40</option>
                                        <option value="09:40 - 11:20">09:40 - 11:20</option>
                                        <option value="13:00 - 14:40">13:00 - 14:40</option>
                                        <option value="14:40 - 16:20">14:40 - 16:20</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Ruangan -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ruangan</label>
                                <select x-model="formData.ruang" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                                    <option value="">Pilih Ruangan</option>
                                    <option value="R.301">R.301 (Lab Komputer)</option>
                                    <option value="R.302">R.302 (Teori)</option>
                                    <option value="R.303">R.303 (Teori)</option>
                                    <option value="Aula">Aula Utama</option>
                                    <option value="Lab Jaringan">Lab Jaringan</option>
                                </select>
                            </div>

                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-teal-600 text-base font-medium text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan
                        </button>
                        <button type="button" @click="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

            </div>
        </main>
    </div>

    <!-- Script Alpine.js Logic -->
    <script>
        function manualSchedulingApp() {
            return {
                isLoading: true,
                init() { setTimeout(() => this.isLoading = false, 2000) },
                sidebarOpen: true,
                showModal: false,
                editMode: false,
                availableClasses: ['IF-1A', 'IF-1B', 'IF-3A', 'IF-3B', 'IF-5A', 'IF-5B', 'IF-7A', 'SI-1A', 'SI-3A'],
                
                filters: {
                    kurikulum: '',
                    semester: ''
                },

                schedules: [
                    // Semester 1
                    { id: 1, kurikulum: '2024', semester: 1, matkul: 'Pengantar TI', dosen: 'Eko Prasetyo S.T., M.Kom', hari: 'Senin', jam: '08:00 - 09:40', ruang: 'R.302', kelas: ['IF-1A'], is_gabungan: false },
                    { id: 2, kurikulum: '2024', semester: 1, matkul: 'Algoritma & Pemrograman', dosen: 'Dr. Budi Santoso', hari: 'Rabu', jam: '09:40 - 11:20', ruang: 'R.301', kelas: ['IF-1A', 'IF-1B'], is_gabungan: true },
                    
                    // Semester 3
                    { id: 3, kurikulum: '2020', semester: 3, matkul: 'Pemrogaman Web', dosen: 'Dr. Budi Santoso', hari: 'Senin', jam: '08:00 - 09:40', ruang: 'R.301', kelas: ['IF-3A'], is_gabungan: false },
                    { id: 4, kurikulum: '2020', semester: 3, matkul: 'Sistem Basis Data', dosen: 'Siti Aminah M.Kom', hari: 'Selasa', jam: '13:00 - 14:40', ruang: 'R.301', kelas: ['IF-3B'], is_gabungan: false },
                    { id: 5, kurikulum: '2020', semester: 3, matkul: 'Struktur Data', dosen: 'Rahmat Hidayat M.T.', hari: 'Kamis', jam: '09:40 - 11:20', ruang: 'R.303', kelas: ['SI-3A'], is_gabungan: false },

                    // Semester 5
                    { id: 6, kurikulum: '2020', semester: 5, matkul: 'Kewirausahaan', dosen: 'Siti Aminah M.Kom', hari: 'Selasa', jam: '13:00 - 14:40', ruang: 'Aula', kelas: ['IF-5A', 'SI-3A'], is_gabungan: true },
                    { id: 7, kurikulum: '2020', semester: 5, matkul: 'Jaringan Komputer', dosen: 'Rahmat Hidayat M.T.', hari: 'Jumat', jam: '08:00 - 09:40', ruang: 'Lab Jaringan', kelas: ['IF-5A'], is_gabungan: false },
                    { id: 8, kurikulum: '2020', semester: 5, matkul: 'Kecerdasan Buatan', dosen: 'Prof. Andi Wijaya', hari: 'Senin', jam: '13:00 - 14:40', ruang: 'R.302', kelas: ['IF-5B'], is_gabungan: false },

                    // Semester 7
                    { id: 9, kurikulum: '2020', semester: 7, matkul: 'Metodologi Penelitian', dosen: 'Prof. Andi Wijaya', hari: 'Rabu', jam: '13:00 - 14:40', ruang: 'R.302', kelas: ['IF-7A'], is_gabungan: false },
                    { id: 10, kurikulum: '2020', semester: 7, matkul: 'Kerja Praktik', dosen: 'Eko Prasetyo S.T., M.Kom', hari: 'Kamis', jam: '14:40 - 16:20', ruang: 'Aula', kelas: ['IF-7A', 'SI-7A'], is_gabungan: true },
                ],

                formData: {
                    id: null,
                    kurikulum: '2020',
                    semester: 1,
                    matkul: '',
                    dosen: '',
                    hari: '',
                    jam: '',
                    ruang: '',
                    is_gabungan: false,
                    selectedClassSingle: '',
                    selectedClassesMulti: []
                },

                // Logic to Group Schedules
                get groupedSchedules() {
                    // 1. Filter first
                    const filtered = this.schedules.filter(s => {
                        const filterKurikulum = this.filters.kurikulum === '' || s.kurikulum == this.filters.kurikulum;
                        const filterSemester = this.filters.semester === '' || s.semester == this.filters.semester;
                        return filterKurikulum && filterSemester;
                    });

                    // 2. Group by Kurikulum & Semester
                    const groups = {};
                    filtered.forEach(item => {
                        const key = `k${item.kurikulum}_s${item.semester}`;
                        if (!groups[key]) {
                            groups[key] = {
                                key: key,
                                kurikulum: item.kurikulum,
                                semester: item.semester,
                                schedules: []
                            };
                        }
                        groups[key].schedules.push(item);
                    });

                    // 3. Convert to Array & Sort
                    return Object.values(groups).sort((a, b) => {
                        // Sort by Kurikulum (desc), then Semester (asc)
                        if (a.kurikulum !== b.kurikulum) return b.kurikulum - a.kurikulum;
                        return a.semester - b.semester;
                    });
                },

                openModal() {
                    this.resetForm();
                    this.editMode = false;
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                },

                editSchedule(schedule) {
                    this.editMode = true;
                    this.formData = {
                        id: schedule.id,
                        kurikulum: schedule.kurikulum,
                        semester: schedule.semester,
                        matkul: schedule.matkul,
                        dosen: schedule.dosen,
                        hari: schedule.hari,
                        jam: schedule.jam,
                        ruang: schedule.ruang,
                        is_gabungan: schedule.is_gabungan,
                        selectedClassSingle: schedule.is_gabungan ? '' : schedule.kelas[0],
                        selectedClassesMulti: schedule.is_gabungan ? [...schedule.kelas] : []
                    };
                    this.showModal = true;
                },

                saveSchedule() {
                    // Tentukan kelas final berdasarkan mode gabungan
                    let finalClasses = [];
                    if (this.formData.is_gabungan) {
                        finalClasses = this.formData.selectedClassesMulti;
                    } else {
                        if (this.formData.selectedClassSingle) {
                            finalClasses = [this.formData.selectedClassSingle];
                        }
                    }

                    // Validasi sederhana
                    if (!this.formData.matkul || !this.formData.dosen || finalClasses.length === 0) {
                        alert('Mohon lengkapi data (Matkul, Dosen, dan Kelas wajib diisi).');
                        return;
                    }

                    if (this.editMode) {
                        // Update Data
                        const index = this.schedules.findIndex(s => s.id === this.formData.id);
                        if (index !== -1) {
                            this.schedules[index] = {
                                ...this.schedules[index],
                                kurikulum: this.formData.kurikulum,
                                semester: this.formData.semester,
                                matkul: this.formData.matkul,
                                dosen: this.formData.dosen,
                                hari: this.formData.hari,
                                jam: this.formData.jam,
                                ruang: this.formData.ruang,
                                is_gabungan: this.formData.is_gabungan,
                                kelas: finalClasses
                            };
                        }
                    } else {
                        // Add Data
                        const newId = this.schedules.length > 0 ? Math.max(...this.schedules.map(s => s.id)) + 1 : 1;
                        this.schedules.push({
                            id: newId,
                            kurikulum: this.formData.kurikulum,
                            semester: this.formData.semester,
                            matkul: this.formData.matkul,
                            dosen: this.formData.dosen,
                            hari: this.formData.hari,
                            jam: this.formData.jam,
                            ruang: this.formData.ruang,
                            is_gabungan: this.formData.is_gabungan,
                            kelas: finalClasses
                        });
                    }

                    this.closeModal();
                },

                deleteSchedule(id) {
                    if (confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
                        this.schedules = this.schedules.filter(s => s.id !== id);
                    }
                },

                resetForm() {
                    this.formData = {
                        id: null,
                        kurikulum: '2020',
                        semester: 1,
                        matkul: '',
                        dosen: '',
                        hari: '',
                        jam: '',
                        ruang: '',
                        is_gabungan: false,
                        selectedClassSingle: '',
                        selectedClassesMulti: []
                    };
                }
            }
        }
    </script>
</body>
</html>
