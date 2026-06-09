<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Sistem Penjadwalan</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans min-h-screen flex items-center justify-center p-4">

    <!-- Card Container -->
    <div class="w-full max-w-5xl bg-white rounded-[2rem] shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-[600px]">
        
        <!-- Left Side: Image (Campus) -->
        <div class="w-full md:w-1/2 relative bg-gray-900 hidden md:block">
            <img src="{{ asset('kampus-tsu.png') }}" alt="Campus Building" class="absolute inset-0 w-full h-full object-cover opacity-90">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
            
            <div class="absolute bottom-10 left-10 text-white p-4">
               {{-- Contextual text if needed --}}
            </div>
        </div>

        <!-- Right Side: Reset Form -->
        <div class="w-full md:w-1/2 p-8 sm:p-12 flex flex-col justify-center bg-white relative">
            
            <div class="flex flex-col items-center mb-10">
                <div class="flex items-center gap-4 mb-2">
                    <img src="{{ asset('1151.jpg') }}" alt="TSU Logo" class="h-14 w-auto"> 
                    <div class="hidden sm:block h-8 w-px bg-gray-300 mx-1"></div>
                    <div class="text-left">
                        <p class="text-[0.6rem] font-bold text-gray-800 uppercase tracking-wide leading-tight mb-0.5">Sistem Informasi Penjadwalan</p>
                        <h2 class="text-lg font-bold text-[#8ba4e6] tracking-wide leading-none uppercase">Fakultas Teknik</h2>
                    </div>
                </div>
            </div>

            <h3 class="text-2xl font-bold text-center text-gray-800 mb-2">Reset Password</h3>
            <p class="text-center text-gray-500 text-sm mb-8">Masukkan data akun anda untuk mengatur ulang kata sandi.</p>

            <form method="POST" action="{{ route('password.update') }}" class="w-full max-w-md mx-auto space-y-5">
                @csrf
                
                @if (session('status'))
                    <div class="p-3 bg-green-50 text-green-600 rounded-lg text-sm border border-green-100 mb-4">
                        {{ session('status') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="p-3 bg-red-50 text-red-600 rounded-lg text-sm border border-red-100 mb-4">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label for="username" class="block text-gray-500 text-sm mb-1 ml-1">Username</label>
                    <input type="text" id="username" name="username" 
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 transition-colors text-gray-700 placeholder-gray-400"
                           value="{{ old('username') }}" required autofocus>
                </div>

                <div>
                    <label for="old_password" class="block text-gray-500 text-sm mb-1 ml-1">Password Lama</label>
                    <input type="text" id="old_password" name="old_password" 
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 transition-colors text-gray-700 placeholder-gray-400"
                           placeholder="Tidak perlu sama persis" required>
                    <p class="text-xs text-gray-400 mt-1 ml-1">*Verifikasi longgar untuk keamanan darurat</p>
                </div>

                <div x-data="{ showPassword: false }">
                    <label for="new_password" class="block text-gray-500 text-sm mb-1 ml-1">Password Baru</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" id="new_password" name="new_password" 
                               class="w-full px-4 py-2.5 pr-11 rounded-xl border border-gray-300 focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 transition-colors text-gray-700 placeholder-gray-400"
                               required minlength="6">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-teal-600 transition-colors focus:outline-none">
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="display: none;" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-between gap-4">
                    <a href="{{ route('login') }}" class="text-gray-500 hover:text-gray-800 text-sm font-medium">Kembali ke Login</a>
                    <button type="submit" class="bg-[#0E6973] hover:bg-[#095058] text-white font-bold text-sm py-2 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                        Reset Password
                    </button>
                </div>
            </form>

        </div>
    </div>
</body>
</html>
