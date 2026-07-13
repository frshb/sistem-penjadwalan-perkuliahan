<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pengguna | Sistem Penjadwalan</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 overflow-x-hidden min-h-screen font-sans transition-colors duration-300">

    <div class="flex min-h-screen" x-data="{ sidebarOpen: true }">
        @include('components.sidebar')

        <main id="main-content" :class="sidebarOpen ? 'lg:ml-64' : 'ml-10'" class="flex-1 min-w-0 p-6 sm:p-10 transition-all duration-300 ease-in-out ml-0">
            <div class="flex justify-between items-start gap-4 mb-8">
                    <div class="flex flex-col">
                        <a href="{{ route('settings.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-teal-700 transition-colors mb-3 group">
                            <svg class="w-4 h-4 mr-1 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            Kembali ke Pengaturan
                        </a>
                        <div class="flex items-center">
                            <button @click="sidebarOpen = !sidebarOpen" class="flex flex-col hover:opacity-80 transition cursor-pointer" title="Toggle Sidebar">
                                <div class="w-2 h-5 bg-teal-600 rounded-tl-md"></div>
                                <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                            </button>
                            <h1 class="text-3xl font-bold text-gray-900 ml-3 tracking-tight">Registrasi Pengguna</h1>
                        </div>
                        <p class="text-sm text-gray-500 mt-1 ml-[1.35rem]">Buat akun untuk Kaprodi, Sekretaris Prodi, Dekan, atau Dosen</p>
                    </div>
                    @include('components.header-profile')
                </div>

            <div class="bg-white rounded-xl shadow-lg p-6 sm:p-10 border border-gray-200 w-full" x-data="registrationForm()">

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
                                <p class="text-xs text-gray-500 mt-1">Wajib dipilih untuk akun Kaprodi dan Sekretaris Prodi.</p>
                            </div>

                            <!-- Dosen Link (Required for Dosen/Dekan, Optional for Kaprodi) -->
                            <div x-show="showDosen" x-transition class="md:col-span-2" style="display: none;">
                                <label for="id_dosen" class="block text-sm font-medium text-gray-700 mb-2">Link Data Dosen <span class="text-red-500" x-show="selectedRole == roleIds.dekan || selectedRole == roleIds.dosen || selectedRole == roleIds.sekprodi">*</span><span class="text-gray-400 font-normal" x-show="selectedRole == roleIds.kaprodi">(Opsional)</span></label>
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

                            <div x-data="{ showPassword: false }">
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                                <div class="relative">
                                    <input :type="showPassword ? 'text' : 'password'" id="password" name="password" class="w-full rounded-lg border-gray-300 focus:border-teal-500 focus:ring focus:ring-teal-200 transition shadow-sm p-3 pr-11" placeholder="Minimal 6 karakter" required>
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
                        </div>

                        <div class="pt-6 border-t border-gray-100 flex justify-end">
                            <button type="submit" class="bg-gradient-to-r from-teal-600 to-teal-500 hover:from-teal-700 hover:to-teal-600 text-white font-bold py-3 px-8 rounded-xl shadow-lg transform transition hover:-translate-y-0.5 focus:ring-4 focus:ring-teal-500/30">
                                Buat Akun
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Daftar Akun Terdaftar -->
                <div class="mt-8 bg-white rounded-xl shadow-lg p-6 sm:p-10 border border-gray-200 w-full">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Daftar Akun Terdaftar</h2>
                    <div class="overflow-x-auto rounded-xl border border-gray-200 overflow-hidden">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-teal-800 text-white">
                                    <th class="px-6 py-4 font-bold text-sm uppercase tracking-wider">No</th>
                                    <th class="px-6 py-4 font-bold text-sm uppercase tracking-wider">Username</th>
                                    <th class="px-6 py-4 font-bold text-sm uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-4 font-bold text-sm uppercase tracking-wider">Program Studi</th>
                                    <th class="px-6 py-4 font-bold text-sm uppercase tracking-wider">Dosen Terkait</th>
                                    <th class="px-6 py-4 font-bold text-sm uppercase tracking-wider text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($users as $index => $user)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 text-gray-700">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $user->username }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $user->role->nama_role ?? '-' }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $user->prodi->nama_prodi ?? '-' }}</td>
                                        <td class="px-6 py-4 text-gray-700">
                                            @if($user->dosen)
                                                {{ $user->dosen->nama_dosen }} <span class="text-xs text-gray-500">({{ $user->dosen->nidn }})</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center space-x-2">
                                                <a href="{{ route('settings.users.edit', $user->id_user) }}" class="inline-flex items-center px-3 py-1.5 bg-yellow-400 hover:bg-yellow-500 text-white text-sm font-bold rounded-md shadow-sm transition-colors" title="Edit">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                    Edit
                                                </a>
                                                <form action="{{ route('settings.users.destroy', $user->id_user) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun \'{{ $user->username }}\'?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-md shadow-sm transition-colors" title="Hapus">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada data akun terdaftar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
        </main>
    </div>

    <script>
        function registrationForm() {
            return {
                selectedRole: '', 
                showProdi: false,
                showDosen: false,
                roleIds: @json($roleIds),
                updateFields() {
                    this.showProdi = (this.selectedRole == this.roleIds.kaprodi || this.selectedRole == this.roleIds.sekprodi);
                    this.showDosen = (this.selectedRole == this.roleIds.kaprodi || this.selectedRole == this.roleIds.sekprodi || this.selectedRole == this.roleIds.dekan || this.selectedRole == this.roleIds.dosen);
                }
            }
        }
    </script>
</body>
</html>
