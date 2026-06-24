<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | TSU</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 overflow-x-hidden min-h-screen font-sans transition-colors duration-300">

    <div class="flex min-h-screen" x-data="{
        sidebarOpen: true
    }">
        @include('components.sidebar')
        <main id="main-content" :class="sidebarOpen ? 'lg:ml-64' : 'ml-10'" class="flex-1 min-w-0 p-6 sm:p-10 transition-all duration-300 ease-in-out ml-0">

            <div>
                <div class="flex items-center mb-8">
                    <div class="flex flex-col mr-3">
                        <div class="w-2 h-5 bg-teal-800 rounded-tl-md"></div>
                        <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Dashboard</h1>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Yellow Profile Box with View Profile & Logout -->
                    <div class="col-span-1 lg:col-span-2 relative min-h-[160px] rounded-2xl shadow-2xl p-6 flex items-center cursor-pointer transition-transform transform hover:scale-[1.01]"
                        x-data="{
                            open: false,
                            modalOpen: false,
                            editMode: false,
                            toggleDropdown() { this.open = !this.open },
                            closeDropdown() { this.open = false },
                            openProfile() { this.open = false; this.modalOpen = true; }
                        }">

                        <!-- Background -->
                        <div class="absolute inset-0 bg-gradient-to-r from-[#FAC435] to-[#A97B00] rounded-2xl overflow-hidden" @click="toggleDropdown()" @click.away="closeDropdown()">
                            <div class="absolute right-4 bottom-4 w-24 h-24 sm:w-32 sm:h-32 opacity-90 text-yellow-200">
                                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" class="w-full h-full fill-current">
                                    <path d="M100 0C44.8 0 0 44.8 0 100s44.8 100 100 100 100-44.8 100-100S155.2 0 100 0zm0 30c16.6 0 30 13.4 30 30s-13.4 30-30 30-30-13.4-30-30 13.4-30 30-30zm0 155c-29.9 0-56.3-13.2-74.6-34.1.4-24.6 49.7-38.1 74.6-38.1s74.2 13.5 74.6 38.1C156.3 171.8 129.9 185 100 185z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Content Content -->
                        <div class="relative z-10 text-white w-full pointer-events-none">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-yellow-100 text-base sm:text-lg mb-1">Halo,</p>
                                    <h2 class="text-2xl sm:text-4xl font-bold mb-2 break-words">{{ $user['name'] }}</h2>
                                    <p class="text-yellow-100 font-medium text-sm sm:text-base">{{ $user['role'] }}</p>
                                </div>

                                <div class="text-yellow-200 pointer-events-auto" @click="toggleDropdown()">
                                    <svg class="w-6 h-6 transition-transform duration-200" :class="{'transform rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown Menu -->
                        <div x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute top-auto right-4 mt-2 bg-white rounded-lg shadow-xl py-2 w-48 z-50 text-gray-800 border border-gray-100 origin-top-right top-[80%]"
                            style="display: none;"
                            @click.stop> <!-- Prevents closing when clicking inside -->

                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-xs text-gray-500 font-semibold uppercase">Menu Akun</p>
                            </div>

                            <!-- Lihat Profil -->
                            <a href="#" @click.prevent="openProfile()" class="flex items-center px-4 py-2 hover:bg-teal-50 hover:text-teal-600 transition-colors text-sm font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Lihat Profil
                            </a>

                            <!-- Logout -->
                            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-dashboard').submit();" class="flex items-center px-4 py-2 hover:bg-red-50 hover:text-red-600 transition-colors text-sm font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                Logout
                            </a>
                            <form id="logout-form-dashboard" action="{{ route('logout') }}" method="POST" class="hidden">
                                @csrf
                            </form>
                        </div>

                        <!-- Profile Modal Portal -->
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
                                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative">

                                        <div class="bg-gray-50 px-4 py-3 sm:px-6 border-b border-gray-200 flex justify-between items-center">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900">Profil Pengguna</h3>
                                            <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-500 focus:outline-none">
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
                    </div>


                    <div class="col-span-1 bg-[#0f3d46] rounded-2xl shadow-2xl p-6 text-white flex flex-col justify-center relative overflow-hidden min-h-[160px]">
                        <div class="relative z-10">
                            <p class="text-teal-200 text-xs sm:text-sm font-medium uppercase tracking-wider mb-2">Total Beban Mengajar</p>
                            <h3 class="text-3xl sm:text-4xl font-bold">{{ $user['sks_beban'] }} SKS</h3>
                        </div>

                        <div class="absolute -right-6 -bottom-6 w-24 h-24 sm:w-32 sm:h-32 bg-teal-600 rounded-full opacity-20"></div>
                    </div>
                </div>


                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">


                    <div class="col-span-1 lg:col-span-2 bg-white rounded-xl shadow-xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Jadwal</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                            <!-- Informatika: Code/Terminal Icon -->
                            <div class="rounded-xl p-4 sm:p-6 flex flex-col items-center justify-center text-center shadow-md hover:shadow-lg transition-shadow cursor-pointer border border-gray-200 group">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 mb-3 text-yellow-600 group-hover:scale-110 transition-transform">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                                </div>
                                <span class="font-semibold text-gray-800 text-sm sm:text-base">S1 Informatika</span>
                            </div>

                            <!-- Sistem Informasi: Database/Flow Icon -->
                            <div class="rounded-xl p-4 sm:p-6 flex flex-col items-center justify-center text-center shadow-md hover:shadow-lg transition-shadow cursor-pointer border border-gray-200 group">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 mb-3 text-purple-600 group-hover:scale-110 transition-transform">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                                </div>
                                <span class="font-semibold text-gray-800 text-sm sm:text-base">S1 Sistem Informasi</span>
                            </div>

                            <!-- Rekayasa Komputer: Chip/Hardware Icon -->
                            <div class="rounded-xl p-4 sm:p-6 flex flex-col items-center justify-center text-center shadow-md hover:shadow-lg transition-shadow cursor-pointer border border-gray-200 group">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 mb-3 text-blue-600 group-hover:scale-110 transition-transform">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                                </div>
                                <span class="font-semibold text-gray-800 text-sm sm:text-base">S1 Rekayasa Komputer</span>
                            </div>
                        </div>
                    </div>


                    <div class="col-span-1 bg-white rounded-xl shadow-xl p-6 border border-gray-200 flex flex-col h-full">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-800">Kalender Akademik</h3>
                            <a href="{{ route('settings.academic_calendar.index') }}" class="text-xs font-semibold text-teal-600 hover:text-teal-800 transition-colors uppercase tracking-wider">
                                Lihat Semua
                            </a>
                        </div>

                        <div class="space-y-4 overflow-y-auto pr-2 custom-scrollbar flex-1" style="max-height: 180px;">
                            @if($kalenderAkademik->isEmpty())
                                <div class="flex flex-col items-center justify-center h-40 text-center bg-gray-50 rounded-xl border border-dashed border-gray-300 p-6">
                                    <div class="bg-gray-100 p-3 rounded-full mb-3">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <p class="text-sm text-gray-500 font-medium">Tidak ada kegiatan akademik.</p>
                                </div>
                            @else
                            @foreach($kalenderAkademik as $event)
                                <div class="group flex items-center p-3 rounded-xl bg-gray-50 border border-transparent hover:border-teal-100 hover:bg-teal-50/50 transition-all duration-200">
                                    <!-- Date Box -->
                                    <div class="flex-shrink-0 w-14 h-14 bg-white border border-gray-200 rounded-lg flex flex-col items-center justify-center shadow-sm group-hover:shadow-md group-hover:border-teal-200 transition-all">
                                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">{{ $event['date']->translatedFormat('M') }}</span>
                                        <span class="text-xl font-extrabold text-teal-600 leading-none">{{ $event['date']->format('d') }}</span>
                                    </div>

                                    <!-- Event Details -->
                                    <div class="ml-4 flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-0.5">
                                            <h4 class="text-sm font-bold text-gray-800 line-clamp-1 leading-tight group-hover:text-teal-700 transition-colors">{{ $event['title'] }}</h4>
                                            @if(isset($event['is_national']) && $event['is_national'])
                                                <span class="px-1.5 py-0.5 bg-red-100 text-red-600 text-[9px] font-bold rounded uppercase tracking-wider border border-red-200 flex-shrink-0">Libur</span>
                                            @endif
                                            @if(isset($event['source']) && $event['source'] === 'manual')
                                                 <span class="px-1.5 py-0.5 bg-teal-100 text-teal-600 text-[9px] font-bold rounded uppercase tracking-wider border border-teal-200 flex-shrink-0">Akademik</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5 flex items-center">
                                            <span class="w-1.5 h-1.5 rounded-full {{ (isset($event['is_national']) && $event['is_national']) ? 'bg-red-500' : 'bg-yellow-400' }} mr-1.5"></span>
                                            {{ $event['date']->translatedFormat('l, d F Y') }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
                </div>


                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <div class="bg-[#fefce8] border border-yellow-200 rounded-xl p-6 shadow-xl hover:shadow-2xl transition-all cursor-pointer group" onclick="window.location='{{ route('prodi.index') }}'">
                        <h3 class="text-lg font-bold text-gray-800 mb-2 group-hover:text-yellow-700 transition-colors">Management Data</h3>
                        <p class="text-sm text-gray-600">Kelola semua informasi fundamental sistem</p>
                    </div>


                    <div class="bg-[#facc15] rounded-xl p-6 shadow-xl hover:shadow-2xl transition-all cursor-pointer group text-white relative overflow-hidden" onclick="window.location='{{ route('jadwal.index') }}'">
                        <div class="relative z-10">
                            <h3 class="text-lg font-bold mb-2 text-gray-900 group-hover:text-black transition-colors">Modul Penjadwalan</h3>
                            <p class="text-sm text-gray-800 font-medium">Buat & atur jadwal otomatis dan manual</p>
                        </div>

                        <div class="absolute right-[-10px] bottom-[-10px] w-24 h-24 bg-white opacity-20 rounded-full"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>


    @if(session('login_success'))
    <div id="login-success-popup" class="fixed inset-0 z-[100] flex items-center justify-center">

        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('kampus-tsu.png') }}');"></div>

        <div class="absolute inset-0 bg-white/30 backdrop-blur-md"></div>


        <div class="relative bg-white/90 backdrop-blur-sm rounded-3xl p-8 shadow-2xl transform transition-all scale-100 max-w-sm w-full text-center border border-white/50 animate-bounce-in">
            <div class="mb-6 flex justify-center">
                <div class="bg-teal-100 p-4 rounded-full text-teal-600 shadow-sm relative">
                    <svg class="w-12 h-12 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>

                    <div class="absolute inset-0 bg-teal-400 rounded-full animate-ping opacity-25"></div>
                </div>
            </div>

            <h3 class="text-2xl font-extrabold text-gray-800 mb-2">Welcome Back!</h3>
            <p class="text-gray-600 mb-8 font-medium">Login berhasil. Selamat beraktivitas kembali.</p>

            <!-- Auto Dismiss Script -->
            <script>
                setTimeout(function() {
                    const popup = document.getElementById('login-success-popup');
                    if(popup) {
                        popup.style.opacity = '0';
                        popup.style.transition = 'opacity 0.5s ease-out';
                        setTimeout(() => popup.remove(), 500);
                    }
                }, 3000);
            </script>
        </div>
    </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar logic handled by Alpine.js in layout/sidebar component
        });
    </script>
</body>
</html>
