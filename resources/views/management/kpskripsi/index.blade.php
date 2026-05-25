<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Data | KP & Skripsi</title>
    <link rel="icon" href="{{ asset('tsuwhite.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300">

    <div class="flex min-h-screen" x-data="{ sidebarOpen: true }">
        @include('components.sidebar')

        <!-- Main Content -->
        <main id="main-content" :class="sidebarOpen ? 'lg:ml-64' : ''" class="flex-1 min-w-0 p-6 sm:p-10 transition-all duration-300 ease-in-out ml-0 bg-gray-50 flex flex-col">
            
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div class="flex items-center">
                    <div class="flex flex-col">
                        <div class="w-2 h-5 bg-teal-800 rounded-tl-md"></div>
                        <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800 ml-3">Management Data</h1>
                </div>
                @include('components.header-profile')
            </div>

            <!-- Action Headers -->
            <div class="flex justify-between items-center mb-6">
                 <h2 class="text-xl font-bold text-gray-700">KP & Skripsi</h2>
            </div>

            <!-- Under Construction Area -->
            <div class="flex-1 flex flex-col items-center justify-center p-4 sm:p-8 bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="w-full max-w-md text-center">
                    
                    <!-- Construction 2D Illustration (SVG) -->
                    <svg class="w-48 h-48 sm:w-72 sm:h-72 mx-auto mb-4 sm:mb-6" viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Background Blob -->
                        <path d="M336.5 168.5C362.5 204.5 316 261.5 258 274.5C200 287.5 160.5 248.5 125 259.5C89.5 270.5 39 269 22.5 220.5C6 172 63.5 151 77 114.5C90.5 78 57.5 38 98.5 16.5C139.5 -5 166.5 29 217.5 24.5C268.5 20 310.5 45.5 329 90.5C347.5 135.5 310.5 132.5 336.5 168.5Z" fill="#E6FFFA"/>
                        
                        <!-- Device/Screen -->
                        <rect x="80" y="60" width="240" height="160" rx="10" fill="white" stroke="#2C7A7B" stroke-width="4"/>
                        <rect x="95" y="75" width="210" height="130" rx="5" fill="#E6FFFA"/>
                        
                        <!-- Code Lines -->
                        <rect x="110" y="90" width="100" height="8" rx="4" fill="#38B2AC"/>
                        <rect x="110" y="110" width="160" height="8" rx="4" fill="#CBD5E0"/>
                        <rect x="110" y="130" width="140" height="8" rx="4" fill="#CBD5E0"/>
                        <rect x="110" y="150" width="180" height="8" rx="4" fill="#CBD5E0"/>
                        <rect x="110" y="170" width="120" height="8" rx="4" fill="#CBD5E0"/>

                        <!-- Character / Worker -->
                        <!-- Head -->
                        <circle cx="280" cy="180" r="30" fill="#FBD38D"/> 
                        <!-- Helmet -->
                        <path d="M250 165 C250 140, 310 140, 310 165 Z" fill="#F6E05E" stroke="#D69E2E" stroke-width="2"/>
                        
                        <!-- Body -->
                        <path d="M250 210 Q280 260 310 210 V230 H250 Z" fill="#2C5282"/>
                        
                        <!-- Arm holding Wrench -->
                        <path d="M310 220 L330 200" stroke="#FBD38D" stroke-width="12" stroke-linecap="round"/>
                        
                        <!-- Wrench -->
                        <path d="M325 185 L345 205 M322 182 L330 190" stroke="#718096" stroke-width="8" stroke-linecap="round"/>
                        
                        <!-- Gear -->
                        <circle cx="50" cy="240" r="20" fill="none" stroke="#F6AD55" stroke-width="6" stroke-dasharray="10 5" class="animate-spin-slow"/>
                        <circle cx="360" cy="80" r="15" fill="none" stroke="#4FD1C5" stroke-width="4" stroke-dasharray="8 4" class="animate-spin-reverse"/>
                        
                        <!-- Crane Element -->
                        <line x1="200" y1="20" x2="200" y2="60" stroke="#2D3748" stroke-width="4"/>
                        <circle cx="200" cy="60" r="4" fill="#2D3748"/>
                    </svg>
                    
                    <!-- CSS Animation for Gears -->
                    <style>
                        .animate-spin-slow { animation: spin 8s linear infinite; }
                        .animate-spin-reverse { animation: spin 6s linear infinite reverse; }
                        @keyframes spin { 100% { transform: rotate(360deg); } }
                    </style>

                    <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-2 sm:mb-3">Halaman Sedang Dalam Perbaikan</h2>
                    <p class="text-sm sm:text-base text-gray-500 mb-4 sm:mb-6 px-4">Fitur Manajemen KP & Skripsi sedang dalam tahap pengembangan. Mohon kembali lagi nanti.</p>
                    
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-3 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700 transition duration-300 text-sm sm:text-base">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>

        </main>
    </div>


</body>
</html>
