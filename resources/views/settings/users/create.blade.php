<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pengguna | Sistem Penjadwalan</title>
    <link rel="icon" href="{{ asset('tsuwhite.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 overflow-x-hidden min-h-screen font-sans transition-colors duration-300">

    <div class="flex min-h-screen" x-data="{ sidebarOpen: true }">
        @include('components.sidebar')

        <main id="main-content" :class="sidebarOpen ? 'lg:ml-64' : 'ml-10'" class="flex-1 min-w-0 p-6 sm:p-10 transition-all duration-300 ease-in-out ml-0">
            @include('components.header-profile')

            <div class="container mx-auto px-4 sm:px-8 py-8">
                <div class="flex flex-col sm:flex-row justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800">Registrasi Pengguna</h2>
                        <p class="text-gray-500 mt-1">Buat akun untuk Kaprodi, Dekan, atau Dosen</p>
                    </div>
                    <a href="{{ route('settings.index') }}" class="mt-4 sm:mt-0 flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Kembali ke Pengaturan
                    </a>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 sm:p-10 border border-gray-200 max-w-4xl mx-auto" x-data="{ 
                    selectedRole: '', 
                    showProdi: false,
                    showDosen: false,
                    updateFields() {
                        // Role IDs: Kaprodi=2, Dekan=3, Dosen=4
                        this.showProdi = (this.selectedRole == '2');
                        this.showDosen = (this.selectedRole == '2' || this.selectedRole == '4');
                    }
                }">

                    @if(session('success'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                            <p class="font-bold">Berhasil</p>
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                            <div class="font-bold">Terjadi Kesalahan</div>
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('settings.users.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Role Selection -->
                        <div>
                            <label for="id_role" class="block text-sm font-medium text-gray-700 mb-2">Role Akun</label>
                            <select id="id_role" name="id_role" x-model="selectedRole" @change="updateFields()" class="w-full rounded-lg border-gray-300 focus:border-teal-500 focus:ring focus:ring-teal-200 transition shadow-sm p-3">
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id_role }}">{{ $role->nama_role }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- Prodi Selection (Only for Kaprodi) -->
                            <div x-show="showProdi" x-transition class="md:col-span-2" style="display: none;">
                                <label for="id_prodi" class="block text-sm font-medium text-gray-700 mb-2">Program Studi <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="id_prodi" name="id_prodi" class="w-full rounded-lg border-gray-300 focus:border-teal-500 focus:ring focus:ring-teal-200 transition shadow-sm p-3 appearance-none">
                                        <option value="">-- Pilih Program Studi --</option>
                                        @foreach($prodis as $prodi)
                                            <option value="{{ $prodi->id_prodi }}">{{ $prodi->nama_prodi }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-700">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Wajib dipilih untuk akun Kaprodi.</p>
                            </div>

                            <!-- Dosen Link (Optional for Kaprodi, Recommended for Dosen) -->
                            <div x-show="showDosen" x-transition class="md:col-span-2" style="display: none;">
                                <label for="id_dosen" class="block text-sm font-medium text-gray-700 mb-2">Link Data Dosen <span class="text-gray-400 font-normal">(Opsional)</span></label>
                                <div class="relative">
                                    <select id="id_dosen" name="id_dosen" class="w-full rounded-lg border-gray-300 focus:border-teal-500 focus:ring focus:ring-teal-200 transition shadow-sm p-3 appearance-none">
                                        <option value="">-- Tidak Terhubung ke Data Dosen --</option>
                                        @foreach($dosens as $dusen)
                                            <option value="{{ $dusen->id_dosen }}">{{ $dusen->nama_dosen }} ({{ $dusen->nidn }})</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-700">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Hubungkan akun ini dengan data dosen yang ada untuk sinkronisasi nama.</p>
                            </div>

                            <!-- Account Credentials -->
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                                <input type="text" id="username" name="username" class="w-full rounded-lg border-gray-300 focus:border-teal-500 focus:ring focus:ring-teal-200 transition shadow-sm p-3" placeholder="Contoh: kaprodi_if" required>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                                <input type="password" id="password" name="password" class="w-full rounded-lg border-gray-300 focus:border-teal-500 focus:ring focus:ring-teal-200 transition shadow-sm p-3" placeholder="Minimal 6 karakter" required>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-gray-100 flex justify-end">
                            <button type="submit" class="bg-gradient-to-r from-teal-600 to-teal-500 hover:from-teal-700 hover:to-teal-600 text-white font-bold py-3 px-8 rounded-xl shadow-lg transform transition hover:-translate-y-0.5 focus:ring-4 focus:ring-teal-500/30">
                                Buat Akun
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
