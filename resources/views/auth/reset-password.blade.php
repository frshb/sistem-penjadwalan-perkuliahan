<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Sistem Penjadwalan</title>
    <link rel="icon" href="{{ asset('tsuwhite.png') }}" type="image/png">
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

                <div>
                    <label for="new_password" class="block text-gray-500 text-sm mb-1 ml-1">Password Baru</label>
                    <input type="password" id="new_password" name="new_password" 
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 transition-colors text-gray-700 placeholder-gray-400"
                           required minlength="6">
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
