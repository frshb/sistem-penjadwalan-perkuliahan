<div x-show="showProfileModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showProfileModal" @click="showProfileModal = false" x-transition.opacity class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="showProfileModal" x-transition.scale.origin.bottom class="relative z-50 inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-xl font-bold text-teal-800" id="modal-title">Profil Dosen</h3>
                    <button @click="showProfileModal = false" class="text-gray-400 hover:text-gray-600 focus:outline-none transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <template x-if="profileData">
                    <div>
                        <div class="flex items-center space-x-5 bg-teal-50 p-4 rounded-xl border border-teal-100 mb-6">
                            <div class="flex-shrink-0">
                                <div class="w-20 h-20 rounded-full bg-teal-600 flex items-center justify-center text-white text-3xl font-bold shadow-md uppercase" 
                                     x-text="profileData.nama_dosen.charAt(0)">
                                </div>
                            </div>
                            <div>
                                <h4 class="text-2xl font-bold text-gray-800" x-text="profileData.nama_dosen"></h4>
                                <p class="text-sm text-teal-700 font-medium mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                                    NIDN: <span x-text="profileData.nidn" class="ml-1"></span>
                                </p>
                                <p class="text-sm text-gray-600 mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m3-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    <span x-text="profileData.prodi"></span> &bull; <span x-text="profileData.fakultas"></span>
                                </p>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-800 mb-3 flex items-center border-b border-gray-200 pb-2">
                                <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Riwayat Mengajar
                            </h4>
                            
                            <div class="max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                                <template x-if="profileData.jadwals && profileData.jadwals.length > 0">
                                    <ul class="space-y-3">
                                        <template x-for="(jadwal, index) in profileData.jadwals" :key="index">
                                            <li class="bg-gray-50 border border-gray-200 rounded-lg p-3 hover:bg-gray-100 transition-colors">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <p class="font-semibold text-gray-800" x-text="jadwal.matkul"></p>
                                                        <p class="text-xs text-gray-500 mt-0.5">
                                                            Kelas: <span class="font-medium" x-text="jadwal.kelas"></span> | 
                                                            SKS: <span class="font-medium" x-text="jadwal.sks"></span>
                                                        </p>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-sm font-medium text-teal-700 bg-teal-100 px-2 py-0.5 rounded" x-text="jadwal.hari"></p>
                                                        <p class="text-xs text-gray-600 mt-1" x-text="jadwal.waktu"></p>
                                                        <p class="text-xs text-gray-500" x-text="jadwal.ruang"></p>
                                                    </div>
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                </template>
                                <template x-if="!profileData.jadwals || profileData.jadwals.length === 0">
                                    <div class="text-center py-6 bg-gray-50 rounded-lg border border-gray-100 border-dashed">
                                        <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                        <p class="text-gray-500 text-sm">Belum ada riwayat jadwal mengajar.</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                <div class="mt-6 flex justify-end">
                    <button @click="showProfileModal = false" class="px-5 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg shadow-sm hover:bg-gray-300 focus:outline-none transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

