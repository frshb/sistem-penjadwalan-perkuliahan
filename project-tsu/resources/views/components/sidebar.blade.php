<aside id="sidebar" class="w-64 flex-shrink-0 bg-white shadow-md flex flex-col transition-all duration-300 ease-in-out fixed inset-y-0 left-0 z-30">
    <div class="h-20 flex items-center justify-between px-6 border-b border-gray-200">
        <img src="{{ asset('1151.jpg') }}" alt="TSU Logo" class="h-10">

        <button id="sidebar-toggle" class="text-gray-500 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
    </div>

    <nav class="px-4 py-4 space-y-1">
        <a href="#" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6-4h.01M12 12h.01M15 12h.01M12 15h.01M15 15h.01M9 15h.01"></path></svg>
            Dashboard
        </a>

        <p class="px-4 pt-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Management Data</p>

        <a href="{{ route('prodi.index') }}" class="flex items-center {{ request()->routeIs('prodi.index') ? 'px-4 py-2 text-white bg-teal-600 shadow-lg' : 'py-2 pl-12 pr-4 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} rounded-lg transition-colors duration-200">
            @if(request()->routeIs('prodi.index'))
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            @endif
            Management Prodi
        </a>

        <a href="{{ route('ruangan.index') }}" class="flex items-center {{ request()->routeIs('ruangan.index') ? 'px-4 py-2 text-white bg-teal-600 shadow-lg' : 'py-2 pl-12 pr-4 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} rounded-lg transition-colors duration-200">
            @if(request()->routeIs('ruangan.index'))
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
            @endif
            Management Data Ruangan
        </a>

        <a href="{{ route('matakuliah.index') }}" class="flex items-center {{ request()->routeIs('matakuliah.index') ? 'px-4 py-2 text-white bg-teal-600 shadow-lg' : 'py-2 pl-12 pr-4 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} rounded-lg transition-colors duration-200">
            @if(request()->routeIs('matakuliah.index'))
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            @endif
            Management Mata Kuliah
        </a>

        <a href="{{ route('dosen.index') }}" class="flex items-center {{ request()->routeIs('dosen.index') ? 'px-4 py-2 text-white bg-teal-600 shadow-lg' : 'py-2 pl-12 pr-4 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} rounded-lg transition-colors duration-200">
            @if(request()->routeIs('dosen.index'))
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            @endif
            Management Data Dosen
        </a>

        <a href="#" class="flex items-center py-2 pl-12 pr-4 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-100 hover:text-gray-900">
            Management Data Mahasiswa
        </a>

        <a href="#" class="flex items-center py-2 pl-12 pr-4 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-100 hover:text-gray-900">
            Management KP & Skripsi
        </a>
    </nav>

    <div class="px-4 py-4 border-t border-gray-200">
        <a href="{{ route('jadwal.index') }}" class="flex items-center {{ request()->routeIs('jadwal.index') ? 'px-4 py-2 text-white bg-teal-600 shadow-lg' : 'px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            Modul Penjadwalan
        </a>
    </div>
</aside>
