<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Hari & Waktu | Sistem Penjadwalan</title>
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
                        <h1 class="text-3xl font-bold text-gray-900 ml-3 tracking-tight">Manajemen Hari & Slot Waktu</h1>
                    </div>
                    <p class="text-sm text-gray-500 mt-1 ml-[1.35rem]">Kelola daftar hari operasional dan alokasi slot waktu perkuliahan.</p>
                </div>
                @include('components.header-profile')
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl mb-6 flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl mb-6 flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl mb-6 flex flex-col gap-1">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                            <span class="text-sm">{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Main Content Container -->
            <div x-data="waktuManagement()">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- PANEL HARI --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                        <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                            <div>
                                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Daftar Hari
                                </h2>
                                <p class="text-xs text-gray-500 mt-1">Hari operasional perkuliahan</p>
                            </div>
                            <button @click="openHariModal()" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-medium text-sm transition-colors shadow-sm shadow-teal-200 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Tambah
                            </button>
                        </div>
                        <div class="p-5 overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 text-gray-500 bg-gray-50">
                                        <th class="py-3 px-4 font-semibold text-xs uppercase tracking-wider rounded-tl-lg">ID</th>
                                        <th class="py-3 px-4 font-semibold text-xs uppercase tracking-wider">Nama Hari</th>
                                        <th class="py-3 px-4 font-semibold text-xs uppercase tracking-wider text-right rounded-tr-lg w-28">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($hari as $h)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-3 px-4 font-mono text-gray-500">{{ $h->id_hari }}</td>
                                        <td class="py-3 px-4 font-medium text-gray-900 capitalize">{{ $h->nama_hari }}</td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button @click="openHariModal({{ $h->id_hari }}, '{{ $h->nama_hari }}')" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                </button>
                                                <form action="{{ route('settings.hari.destroy', $h->id_hari) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus hari ini? Pastikan hari ini tidak sedang digunakan pada jadwal.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="py-8 text-center text-gray-500">Belum ada data hari.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- PANEL SLOT WAKTU --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                        <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                            <div>
                                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Daftar Slot Waktu
                                </h2>
                                <p class="text-xs text-gray-500 mt-1">Pembagian jam ke dan durasi</p>
                            </div>
                            <button @click="openSlotModal()" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-medium text-sm transition-colors shadow-sm shadow-teal-200 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Tambah
                            </button>
                        </div>
                        <div class="p-5 overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 text-gray-500 bg-gray-50">
                                        <th class="py-3 px-4 font-semibold text-xs uppercase tracking-wider rounded-tl-lg">Slot</th>
                                        <th class="py-3 px-4 font-semibold text-xs uppercase tracking-wider">Jam Mulai</th>
                                        <th class="py-3 px-4 font-semibold text-xs uppercase tracking-wider">Jam Selesai</th>
                                        <th class="py-3 px-4 font-semibold text-xs uppercase tracking-wider">Sesi</th>
                                        <th class="py-3 px-4 font-semibold text-xs uppercase tracking-wider text-right rounded-tr-lg w-28">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($slotWaktu as $slot)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-gray-900 text-center w-6 h-6 rounded-full bg-gray-100">{{ $slot->jam_ke }}</div>
                                        </td>
                                        <td class="py-3 px-4 font-medium text-gray-900">{{ \Carbon\Carbon::parse($slot->waktu_mulai)->format('H:i') }}</td>
                                        <td class="py-3 px-4 font-medium text-gray-900">{{ \Carbon\Carbon::parse($slot->waktu_selesai)->format('H:i') }}</td>
                                        <td class="py-3 px-4">
                                            <span class="px-2.5 py-1 text-[11px] font-bold uppercase rounded-full {{ $slot->sesi === 'pagi' ? 'bg-sky-100 text-sky-700' : 'bg-indigo-100 text-indigo-700' }}">
                                                {{ $slot->sesi }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button @click="openSlotModal({{ $slot->id_slot }}, {{ $slot->jam_ke }}, '{{ $slot->waktu_mulai }}', '{{ $slot->waktu_selesai }}', '{{ $slot->sesi }}')" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                </button>
                                                <form action="{{ route('settings.slot.destroy', $slot->id_slot) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus slot waktu ini? Pastikan slot ini tidak sedang digunakan pada jadwal.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-gray-500">Belum ada data slot waktu.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                {{-- MODAL HARI --}}
                <div x-show="isHariModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" x-cloak>
                    <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" @click="isHariModalOpen = false"
                         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 overflow-hidden relative z-10 transform transition-all"
                         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900" x-text="hariForm.id ? 'Edit Hari' : 'Tambah Hari'"></h3>
                            <button @click="isHariModalOpen = false" class="text-gray-400 hover:text-gray-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <form :action="hariForm.id ? `{{ route('settings.hari.update', '__ID__') }}`.replace('__ID__', hariForm.id) : `{{ route('settings.hari.store') }}`" method="POST">
                            @csrf
                            <template x-if="hariForm.id">
                                @method('PUT')
                            </template>
                            <div class="p-6 space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Hari</label>
                                    <input type="text" name="nama_hari" x-model="hariForm.nama" required class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:ring-2 focus:ring-teal-500 outline-none transition-all text-sm" placeholder="Misal: Senin">
                                </div>
                            </div>
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                                <button type="button" @click="isHariModalOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
                                <button type="submit" class="px-4 py-2 text-sm font-medium bg-teal-600 hover:bg-teal-700 text-white rounded-xl shadow-sm transition-colors">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- MODAL SLOT WAKTU --}}
                <div x-show="isSlotModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" x-cloak>
                    <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" @click="isSlotModalOpen = false"
                         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden relative z-10 transform transition-all"
                         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900" x-text="slotForm.id ? 'Edit Slot Waktu' : 'Tambah Slot Waktu'"></h3>
                            <button @click="isSlotModalOpen = false" class="text-gray-400 hover:text-gray-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <form :action="slotForm.id ? `{{ route('settings.slot.update', '__ID__') }}`.replace('__ID__', slotForm.id) : `{{ route('settings.slot.store') }}`" method="POST">
                            @csrf
                            <template x-if="slotForm.id">
                                @method('PUT')
                            </template>
                            <div class="p-6 space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Ke (Urutan Slot)</label>
                                    <input type="number" name="jam_ke" x-model="slotForm.jam_ke" required min="1" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:ring-2 focus:ring-teal-500 outline-none transition-all text-sm" placeholder="Misal: 1">
                                    <p class="text-xs text-gray-500 mt-1">Urutan slot (contoh: slot 1, 2, 3)</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Waktu Mulai</label>
                                        <input type="time" name="waktu_mulai" x-model="slotForm.waktu_mulai" required class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:ring-2 focus:ring-teal-500 outline-none transition-all text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Waktu Selesai</label>
                                        <input type="time" name="waktu_selesai" x-model="slotForm.waktu_selesai" required class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:ring-2 focus:ring-teal-500 outline-none transition-all text-sm">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Sesi</label>
                                    <select name="sesi" x-model="slotForm.sesi" required class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:ring-2 focus:ring-teal-500 outline-none transition-all text-sm">
                                        <option value="pagi">Pagi</option>
                                        <option value="malam">Malam</option>
                                    </select>
                                </div>
                            </div>
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                                <button type="button" @click="isSlotModalOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
                                <button type="submit" class="px-4 py-2 text-sm font-medium bg-teal-600 hover:bg-teal-700 text-white rounded-xl shadow-sm transition-colors">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div> <!-- End x-data -->

        </main>
    </div>

    <script>
        function waktuManagement() {
            return {
                isHariModalOpen: false,
                isSlotModalOpen: false,
                hariForm: { id: null, nama: '' },
                slotForm: { id: null, jam_ke: '', waktu_mulai: '', waktu_selesai: '', sesi: 'pagi' },

                openHariModal(id = null, nama = '') {
                    this.hariForm = { id, nama };
                    this.isHariModalOpen = true;
                },

                openSlotModal(id = null, jam_ke = '', waktu_mulai = '', waktu_selesai = '', sesi = 'pagi') {
                    this.slotForm = { id, jam_ke, waktu_mulai, waktu_selesai, sesi };
                    this.isSlotModalOpen = true;
                }
            }
        }
    </script>
</body>
</html>
