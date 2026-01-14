<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistem Penjadwalan</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans min-h-screen flex items-center justify-center p-4">

    <!-- Card Container -->
    <div class="w-full max-w-5xl bg-white rounded-[2rem] shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-[600px]">
        
        <!-- Left Side: Image (Campus) -->
        <div class="w-full md:w-1/2 relative bg-gray-900 hidden md:block">
            <!-- Image Overlay/Styling -->
            <img src="{{ asset('kampus-tsu.png') }}" alt="Campus Building" class="absolute inset-0 w-full h-full object-cover opacity-90">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
            
            <!-- Logo on Image (Optional or as in reference) -->
            <div class="absolute top-8 left-8">
                 <!-- Maybe user wants logo here too? Keeping it simple based on typical designs -->
            </div>

            <div class="absolute bottom-10 left-10 text-white p-4">
               {{-- <h2 class="text-3xl font-bold mb-2">Welcome Back!</h2>
                <p class="text-gray-200">Sistem Penjadwalan Perkuliahan</p> --}}
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="w-full md:w-1/2 p-8 sm:p-12 flex flex-col justify-center bg-white relative">
            
            <!-- Logo & Brand Header -->
            <div class="flex flex-col items-center mb-10">
                <div class="flex items-center gap-4 mb-2">
                    <img src="{{ asset('1151.jpg') }}" alt="TSU Logo" class="h-14 w-auto"> 
                    
                    <!-- Divider (Removed since Uni name is gone, checking visual balance) -->
                    <!-- If we remove Uni name, we might not need divider or it separates Logo from Faculty? 
                         Let's keep logo | Faculty structure? 
                         User said "text TSU Tiga Serangkai University dihapus".
                         So just Logo + Faculty Name? -->
                    
                    <!-- Divider -->
                    <div class="hidden sm:block h-8 w-px bg-gray-300 mx-1"></div>

                    <!-- Faculty Name -->
                    <div class="text-left">
                        <p class="text-[0.6rem] font-bold text-gray-800 uppercase tracking-wide leading-tight mb-0.5">Sistem Informasi Penjadwalan</p>
                        <h2 class="text-lg font-bold text-[#8ba4e6] tracking-wide leading-none uppercase">Fakultas Teknik</h2>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('authenticate') }}" class="w-full max-w-md mx-auto space-y-5">
                @csrf
                
                <div>
                    <label for="username" class="block text-gray-500 text-sm mb-1 ml-1">Username / email</label>
                    <input type="text" id="username" name="username" 
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 transition-colors text-gray-700 placeholder-gray-400"
                           value="{{ old('username') }}" required autofocus>
                    @error('username')
                        <p class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-gray-500 text-sm mb-1 ml-1">Password</label>
                    <input type="password" id="password" name="password" 
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 transition-colors text-gray-700 placeholder-gray-400"
                           required>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('password.request') }}" class="text-sm text-gray-800 hover:text-teal-700 font-medium">Lupa Password?</a>
                </div>

                <div class="pt-2 flex justify-center">
                    <button type="submit" class="w-auto px-10 bg-[#0E6973] hover:bg-[#095058] text-white font-bold text-sm py-2 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                        Sign In
                    </button>
                </div>
            </form>
            
            {{-- <div class="mt-8 text-center text-sm text-gray-500">
                Belum Punya Akun? <a href="{{ route('users.register') }}" class="text-gray-900 font-bold hover:underline">Register</a>
            </div> --}}

            <div class="mt-6 flex justify-center">
                 {{-- <button type="button" class="flex items-center gap-2 px-6 py-2 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors text-gray-700 font-medium text-sm">
                    Sign With 
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                </button> --}}
            </div>

        </div>
    </div>
</body>
</html>
