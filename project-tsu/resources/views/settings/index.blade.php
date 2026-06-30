<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan | Sistem Penjadwalan</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 overflow-x-hidden min-h-screen font-sans transition-colors duration-300">

    <div class="flex min-h-screen" x-data="{ sidebarOpen: true }">
        @include('components.sidebar')

        <main id="main-content" :class="sidebarOpen ? 'lg:ml-64' : 'ml-10'" class="flex-1 min-w-0 p-6 sm:p-10 transition-all duration-300 ease-in-out ml-0">
            @include('components.header-profile')

            <div class="mb-8">
                <h1 class="text-3xl font-bold text-teal-900">Pengaturan</h1>
                <p class="text-gray-500 mt-2">Pilih menu pengaturan yang ingin Anda kelola.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Management Role Card -->
                @if(Auth::user()->isAdmin())
                <a href="{{ route('settings.roles.index') }}" class="group relative bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 p-8 border border-gray-100 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-teal-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>

                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-teal-100 rounded-xl flex items-center justify-center mb-6 text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition-colors duration-300">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-teal-700 transition-colors">Management Role</h3>
                        <p class="text-gray-500 leading-relaxed">Kelola peran pengguna, hak akses, dan perizinan dalam sistem.</p>
                    </div>
                </a>
                @endif

                <!-- Kalender Akademik Card -->
                @if(Auth::user()->isAdmin())
                <a href="{{ route('settings.academic_calendar.index') }}" class="group relative bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 p-8 border border-gray-100 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-orange-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>

                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-orange-100 rounded-xl flex items-center justify-center mb-6 text-orange-600 group-hover:bg-orange-600 group-hover:text-white transition-colors duration-300">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-orange-700 transition-colors">Kalender Akademik</h3>
                        <p class="text-gray-500 leading-relaxed">Atur tahun ajaran, semester, dan jadwal penting akademik lainnya.</p>
                    </div>
                </a>
                @endif

                <!-- User Registration Card -->
                @if(Auth::user()->isAdmin())
                <a href="{{ route('settings.users.create') }}" class="group relative bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 p-8 border border-gray-100 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-purple-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>

                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-6 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-purple-700 transition-colors">Registrasi Akun</h3>
                        <p class="text-gray-500 leading-relaxed">Buat akun baru untuk Kaprodi, Dekan, atau Dosen.</p>
                    </div>
                </a>
                @endif

                <!-- Waktu Management Card -->
                @if(Auth::user()->isAdmin())
                <a href="{{ route('settings.waktu.index') }}" class="group relative bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 p-8 border border-gray-100 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>

                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-cyan-100 rounded-xl flex items-center justify-center mb-6 text-cyan-600 group-hover:bg-cyan-600 group-hover:text-white transition-colors duration-300">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-cyan-700 transition-colors">Hari & Slot Waktu</h3>
                        <p class="text-gray-500 leading-relaxed">Kelola daftar hari aktif dan pembagian slot waktu perkuliahan.</p>
                    </div>
                </a>
                @endif

                <!-- Setup Kurikulum & TA Card -->
                <a href="{{ route('kurikulum.index') }}" class="group relative bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 p-8 border border-gray-100 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>

                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-emerald-100 rounded-xl flex items-center justify-center mb-6 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-emerald-700 transition-colors">Kurikulum & TA</h3>
                        <p class="text-gray-500 leading-relaxed">Kelola data master kurikulum dan tahun akademik/semester aktif.</p>
                    </div>
                </a>
            </div>
        </main>
    </div>
</body>
</html>
