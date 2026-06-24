<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User | Admin Only</title>
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
               <h2 class="text-3xl font-bold mb-2">Admin Portal</h2>
               <p class="text-gray-200">Create New Accounts</p>
            </div>
        </div>

        <!-- Right Side: Register Form -->
        <div class="w-full md:w-1/2 p-8 sm:p-12 flex flex-col justify-center bg-white relative">

            <div class="flex flex-col items-center mb-8">
                <div class="flex items-center gap-4 mb-2">
                    <img src="{{ asset('1151.jpg') }}" alt="TSU Logo" class="h-12 w-auto">

                    <!-- Divider -->
                    <div class="hidden sm:block h-8 w-px bg-gray-300 mx-1"></div>

                    <!-- Faculty Name -->
                    <div class="text-left">
                        <p class="text-[0.55rem] font-bold text-gray-800 uppercase tracking-wide leading-tight mb-0.5">Sistem Informasi Penjadwalan</p>
                        <h2 class="text-base font-bold text-[#8ba4e6] tracking-wide leading-none uppercase">Fakultas Teknik</h2>
                    </div>
                </div>
            </div>

            <h3 class="text-xl font-semibold text-gray-800 mb-6 text-center">Create New User</h3>

            @if ($errors->any())
                <div class="p-3 bg-red-50 text-red-600 rounded-lg text-sm border border-red-100 mb-4">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="p-3 bg-green-50 text-green-600 rounded-lg text-sm border border-green-100 mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('users.store') }}" class="w-full max-w-md mx-auto space-y-4">
                @csrf

                @if (session('error'))
                    <div class="p-3 bg-red-50 text-red-600 rounded-lg text-sm border border-red-100">
                        {{ session('error') }}
                    </div>
                @endif

                 @if ($errors->any())
                    <div class="p-3 bg-red-50 text-red-600 rounded-lg text-sm border border-red-100">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label for="username" class="block text-gray-500 text-sm mb-1 ml-1">Username</label>
                    <input type="text" id="username" name="username" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 transition-colors text-gray-700 placeholder-gray-400" value="{{ old('username') }}" required>
                </div>

                <div x-data="{ showPassword: false }">
                    <label for="password" class="block text-gray-500 text-sm mb-1 ml-1">Password</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" class="w-full px-4 py-2.5 pr-11 rounded-xl border border-gray-300 focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 transition-colors text-gray-700 placeholder-gray-400" required minlength="6">
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

                 <div>
                    <label for="id_role" class="block text-gray-500 text-sm mb-1 ml-1">Role</label>
                    <div class="relative">
                        <select id="id_role" name="id_role" class="block w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 bg-white text-gray-700 appearance-none" onchange="toggleInputs()">
                            @foreach($roles as $role)
                                <option value="{{ $role->id_role }}" data-name="{{ $role->nama_role }}">{{ ucfirst(str_replace('_', ' ', $role->nama_role)) }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Dosen Dropdown (Hidden Default) -->
                <div id="dosen-input" class="hidden">
                    <label for="id_dosen" class="block text-gray-500 text-sm mb-1 ml-1">Pilih Dosen</label>
                    <div class="relative">
                        <select id="id_dosen" name="id_dosen" class="block w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 bg-white text-gray-700 appearance-none">
                             <option value="">-- Pilih Dosen --</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id_dosen }}">
                                    {{ $dosen->nama_dosen }}
                                    @if($dosen->prodi)
                                        ({{ $dosen->prodi->nama_prodi }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                         <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Prodi Dropdown (Hidden Default) -->
                <div id="prodi-input" class="hidden">
                    <label for="id_prodi" class="block text-gray-500 text-sm mb-1 ml-1">Pilih Program Studi</label>
                    <div class="relative">
                        <select id="id_prodi" name="id_prodi" class="block w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 bg-white text-gray-700 appearance-none">
                             <option value="">-- Pilih Program Studi --</option>
                            @foreach($prodis as $prodi)
                                <option value="{{ $prodi->id_prodi }}">
                                    {{ $prodi->nama_prodi }}
                                </option>
                            @endforeach
                        </select>
                         <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <script>
                    function toggleInputs() {
                        const roleSelect = document.getElementById('id_role');
                        const selectedOption = roleSelect.options[roleSelect.selectedIndex];
                        const roleName = selectedOption ? selectedOption.getAttribute('data-name') : '';

                        const dosenInput = document.getElementById('dosen-input');
                        const prodiInput = document.getElementById('prodi-input');

                        // Reset hidden
                        dosenInput.classList.add('hidden');
                        prodiInput.classList.add('hidden');

                        if (roleName === 'kaprodi') {
                            prodiInput.classList.remove('hidden');
                        } else if (roleName === 'dosen' || roleName === 'dekan') {
                            dosenInput.classList.remove('hidden');
                        }
                    }

                    // Run initial check
                    document.addEventListener('DOMContentLoaded', toggleInputs);
                </script>

                <div class="pt-4 flex items-center justify-between gap-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-800 text-sm font-medium">Cancel</a>
                    <button type="submit" class="bg-[#0E6973] hover:bg-[#095058] text-white font-bold text-sm py-2 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                        Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
