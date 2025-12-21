<!-- Button to Open Sidebar (Visible when sidebar is closed) -->
<button @click="sidebarOpen = true" 
        :class="sidebarOpen ? 'hidden' : ''"
        class="fixed top-6 left-6 z-20 text-gray-600 hover:text-gray-900 transition-all duration-300 ease-in-out">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
</button>

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
       class="w-64 flex-shrink-0 bg-white shadow-2xl flex flex-col transition-all duration-300 ease-in-out fixed inset-y-0 left-0 z-30 border-r border-gray-200">
    <div class="h-20 flex items-center justify-between px-6 border-b border-gray-200">
        <img src="{{ asset('1151.jpg') }}" alt="TSU Logo" class="h-10">

        <div class="flex items-center space-x-2">
            <button @click="sidebarOpen = false" class="text-gray-500 hover:text-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>
    </div>

    <nav class="px-4 py-4 space-y-1 flex-1 overflow-y-auto">
        <a href="{{ route('dashboard') }}" class="flex items-center {{ request()->routeIs('dashboard') ? 'px-4 py-2 text-xs font-medium text-teal-800 bg-teal-50 border-r-4 border-teal-600' : 'px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} rounded-lg transition-all duration-200">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6-4h.01M12 12h.01M15 12h.01M12 15h.01M15 15h.01M9 15h.01"></path></svg>
            Dashboard
        </a>

 
        <p class="px-4 pt-4 pb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Management Data</p>

        <a href="{{ route('prodi.index') }}" class="flex items-center {{ request()->routeIs('prodi.index') ? 'px-4 py-2 text-xs font-medium text-teal-800 bg-teal-50 border-r-4 border-teal-600' : 'px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} rounded-lg transition-all duration-200">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            Management Prodi
        </a>

        @if(!Auth::user()->isKaprodi())
        <a href="{{ route('ruangan.index') }}" class="flex items-center {{ request()->routeIs('ruangan.index') ? 'px-4 py-2 text-xs font-medium text-teal-800 bg-teal-50 border-r-4 border-teal-600' : 'px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} rounded-lg transition-all duration-200">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
            Management Data Ruangan
        </a>
        @endif

        <a href="{{ route('matakuliah.index') }}" class="flex items-center {{ request()->routeIs('matakuliah.index') ? 'px-4 py-2 text-xs font-medium text-teal-800 bg-teal-50 border-r-4 border-teal-600' : 'px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} rounded-lg transition-all duration-200">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            Management Mata Kuliah
        </a>
        
        <a href="{{ route('dosen.index') }}" class="flex items-center {{ request()->routeIs('dosen.index') ? 'px-4 py-2 text-xs font-medium text-teal-800 bg-teal-50 border-r-4 border-teal-600' : 'px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} rounded-lg transition-all duration-200">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            Management Data Dosen
        </a>

        <a href="{{ route('mahasiswa.index') }}" class="flex items-center {{ request()->routeIs('mahasiswa.index') ? 'px-4 py-2 text-xs font-medium text-teal-800 bg-teal-50 border-r-4 border-teal-600' : 'px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} rounded-lg transition-all duration-200">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            Management Data Mahasiswa
        </a>

        <a href="{{ route('management.kpskripsi.index') }}" class="flex items-center {{ request()->routeIs('management.kpskripsi.index') ? 'px-4 py-2 text-xs font-medium text-teal-800 bg-teal-50 border-r-4 border-teal-600' : 'px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} rounded-lg transition-all duration-200">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Management KP & Skripsi
        </a>
        
        <p class="px-4 pt-4 pb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Modul Penjadwalan</p>

        <a href="{{ route('jadwal.otomatis.step1') }}" class="flex items-center {{ request()->routeIs('jadwal.otomatis.step1') ? 'px-4 py-2 text-xs font-medium text-teal-800 bg-teal-50 border-r-4 border-teal-600' : 'px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} rounded-lg transition-all duration-200">
             <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
            Penjadwalan Otomatis
        </a>
        
        <a href="{{ route('jadwal.manual') }}" class="flex items-center {{ request()->routeIs('jadwal.manual') ? 'px-4 py-2 text-xs font-medium text-teal-800 bg-teal-50 border-r-4 border-teal-600' : 'px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} rounded-lg transition-all duration-200">
             <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            Penjadwalan Manual
        </a>
    </nav>
    <div class="px-4 py-4 border-t border-gray-200">
        <a href="{{ route('settings.roles.index') }}" class="flex items-center {{ request()->routeIs('settings.roles.*') ? 'px-4 py-2 text-xs font-medium text-teal-800 bg-teal-50 border-r-4 border-teal-600' : 'px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} rounded-lg transition-all duration-200">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            Management Role
        </a>
        
        <a href="{{ route('settings.academic_calendar.index') }}" class="flex items-center {{ request()->routeIs('settings.academic_calendar.*') ? 'px-4 py-2 text-xs font-medium text-teal-800 bg-teal-50 border-r-4 border-teal-600' : 'px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} rounded-lg transition-all duration-200">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            Kalender Akademik
        </a>
    <div class="mt-2 text-xs text-gray-400 text-center">
            &copy; 2025 Fakultas Teknik TSU
        </div>
    </div>
</aside>

<script>
    // Force remove dark class just in case it persisted in localstorage/html
    document.documentElement.classList.remove('dark');
    localStorage.removeItem('theme');
</script>
