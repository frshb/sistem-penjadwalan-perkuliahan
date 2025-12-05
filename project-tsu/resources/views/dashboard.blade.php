<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Sistem Penjadwalan Perkuliahan</title>
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
        <main>
                {{-- HEADER --}}
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>
                </div>

                {{-- TOP SECTION --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- HELLO USER CARD --}}
                    <div class="col-span-1 bg-gradient-to-r from-yellow-400 to-yellow-600 text-white rounded-xl shadow p-5 flex items-center justify-between">
                        <div>
                            <p class="text-lg">Halo,</p>
                            <p class="text-2xl font-bold">John Doe</p>
                            <p class="text-sm opacity-80">Admin Fakultas Teknik</p>
                        </div>
                        <img src="https://cdn-icons-png.flaticon.com/512/201/201818.png" class="w-20" alt="User Icon">
                    </div>

                    {{-- EMPTY BLUE CARD --}}
                    <div class="col-span-2 bg-gradient-to-r from-teal-700 to-blue-900 rounded-xl shadow p-5"></div>

                </div>

                {{-- SECOND SECTION --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">

                    {{-- JADWAL --}}
                    <div class="col-span-2 bg-white rounded-xl shadow p-5 h-56">
                        <h2 class="text-lg font-semibold mb-3">Jadwal</h2>
                        <div class="w-full h-full bg-gray-100 rounded-lg"></div>
                    </div>

                    {{-- KALENDER AKADEMIK --}}
                    <div class="col-span-1 bg-white rounded-xl shadow p-5 h-56">
                        <h2 class="text-lg font-semibold mb-3">Kalender Akademik</h2>

                        <div class="space-y-3">
                            <div class="w-full h-10 bg-blue-200 rounded-lg"></div>
                            <div class="w-full h-10 bg-blue-200 rounded-lg"></div>
                            <div class="w-full h-10 bg-blue-200 rounded-lg"></div>
                        </div>
                    </div>

                </div>

                {{-- BOTTOM SECTION --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">

                    {{-- MANAGEMENT DATA --}}
                    <div class="bg-yellow-100 rounded-xl shadow p-5">
                        <h2 class="text-lg font-semibold mb-1">Management Data</h2>
                        <p class="text-sm text-gray-700">Kelola semua informasi fundamental sistem</p>
                    </div>

                    {{-- MODUL PENJADWALAN --}}
                    <div class="bg-yellow-400 rounded-xl shadow p-5">
                        <h2 class="text-lg font-semibold mb-1">Modul Penjadwalan</h2>
                        <p class="text-sm text-gray-800">Buat & atur jadwal otomatis dan manual</p>
                    </div>

                </div>

            </div>
        </main>
    </div>



</body>
</html>
