<div id="header-profile" class="relative" x-data="{ 
    dropdownOpen: false, 
    modalOpen: false,
    editMode: false,
    showPassword: false
}">
    <!-- Trigger -->
    <div @click="dropdownOpen = !dropdownOpen" @click.away="dropdownOpen = false" class="flex items-center space-x-3 bg-white px-4 py-2 rounded-full shadow-sm border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors select-none">
        <div class="bg-teal-100 p-2 rounded-full text-teal-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
        </div>
        <div class="hidden sm:flex flex-col text-right">
            <span class="text-sm font-semibold text-gray-800">{{ Auth::user()->username ?? 'Guest' }}</span>
            <span class="text-xs text-gray-500 capitalize">{{ Auth::user()->role->nama_role ?? '-' }}</span>
        </div>
        <!-- Chevron Icon -->
        <svg class="w-4 h-4 text-gray-400 hidden sm:block transition-transform duration-200" :class="dropdownOpen ? 'transform rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
    </div>

    <!-- Dropdown Menu -->
    <div x-show="dropdownOpen" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl py-2 z-50 border border-gray-100 origin-top-right"
         style="display: none;">
        
        <!-- Mobile Only Info -->
        <div class="px-4 py-3 border-b border-gray-100 sm:hidden bg-gray-50 mb-1">
             <p class="text-sm font-bold text-gray-800 truncate">{{ Auth::user()->username ?? 'Guest' }}</p>
             <p class="text-xs text-gray-500 capitalize">{{ Auth::user()->role->nama_role ?? '-' }}</p>
        </div>

        <div class="px-2 space-y-1">
            <!-- Lihat Profil -->
            <a href="#" @click.prevent="dropdownOpen = false; modalOpen = true" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-700 rounded-md transition-colors">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Lihat Profil
            </a>

            <!-- Logout -->
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-md transition-colors">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Logout
            </a>
        </div>
        
        <form id="logout-form-header" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>

    <!-- Profile Modal -->
    <template x-teleport="body">
        <div x-show="modalOpen" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="modalOpen" @click="modalOpen = false" class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="modalOpen" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative">
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Profil Pengguna</h3>
                        <button @click="modalOpen = false" aria-label="Close Profile Modal" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <!-- Profile Info View -->
                        <div x-show="!editMode">
                            <div class="flex items-center justify-center mb-6">
                                <div class="h-20 w-20 bg-teal-100 rounded-full flex items-center justify-center text-teal-600 text-3xl font-bold">
                                    {{ substr(Auth::user()->username ?? 'G', 0, 1) }}
                                </div>
                            </div>
                            
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div class="col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Username</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ Auth::user()->username }}</dd>
                                </div>
                                <div class="col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Role</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-semibold capitalize">{{ Auth::user()->role->nama_role ?? '-' }}</dd>
                                </div>
                                <!-- Add other fields like Created At if available later -->
                            </dl>

                            <div class="mt-8 flex justify-end">
                                <button @click="editMode = true" class="inline-flex justify-center w-full sm:w-auto px-4 py-2 bg-yellow-500 text-white text-sm font-medium rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    Edit Profil
                                </button>
                            </div>
                        </div>

                        <!-- Edit Profile Form -->
                        <form x-show="editMode" action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Username</label>
                                    <input type="text" name="username" value="{{ Auth::user()->username }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Role</label>
                                    <input type="text" value="{{ Auth::user()->role->nama_role ?? '-' }}" disabled class="mt-1 block w-full bg-gray-100 border border-gray-300 rounded-md shadow-sm py-2 px-3 text-gray-500 sm:text-sm cursor-not-allowed">
                                    <p class="mt-1 text-xs text-gray-500">Role tidak dapat diubah.</p>
                                </div>

                                <div class="border-t border-gray-100 pt-4 mt-4">
                                    <h4 class="text-sm font-medium text-gray-900 mb-3">Ubah Password <span class="text-gray-400 font-normal">(Opsional)</span></h4>
                                    
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Password Saat Ini</label>
                                            <input type="password" name="current_password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm" placeholder="Diperlukan jika mengubah password">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Password Baru</label>
                                            <input type="password" name="new_password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Konfirmasi Password Baru</label>
                                            <input type="password" name="new_password_confirmation" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end space-x-3">
                                <button type="button" @click="editMode = false" class="inline-flex justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                    Batal
                                </button>
                                <button type="submit" class="inline-flex justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Error/Success Handling (Optional Toast) -->
    @if ($errors->any() || session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" class="fixed bottom-4 right-4 z-[110] max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        @if (session('success'))
                            <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @else
                            <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @endif
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900">
                            {{ session('success') ? 'Berhasil!' : 'Terjadi Kesalahan' }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ session('success') ?? $errors->first() }}
                        </p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button @click="show = false" aria-label="Close Notification" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
