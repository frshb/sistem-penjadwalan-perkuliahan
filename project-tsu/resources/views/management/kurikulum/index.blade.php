<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurikulum & Tahun Akademik</title>
    <meta name="description" content="Kelola Master Kurikulum dan Tahun Akademik">
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    x-data="{
        sidebarOpen: true,
        activeTab: 'tahun',

        showModalTambahTahun: false,
        editTahun: null,
        showModalEditTahun: false,
        showModalHapusTahun: false,
        hapusTahunId: null,
        hapusTahunNama: '',

        showModalTambahKurikulum: false,
        editKurikulum: null,
        showModalEditKurikulum: false,
        showModalHapusKurikulum: false,
        hapusKurikulumId: null,
        hapusKurikulumNama: '',

        openEditTahun(data) {
            this.editTahun = { ...data };
            this.showModalEditTahun = true;
        },
        openHapusTahun(id, nama) {
            this.hapusTahunId = id;
            this.hapusTahunNama = nama;
            this.showModalHapusTahun = true;
        },
        openEditKurikulum(data) {
            this.editKurikulum = { ...data };
            this.showModalEditKurikulum = true;
        },
        openHapusKurikulum(id, nama) {
            this.hapusKurikulumId = id;
            this.hapusKurikulumNama = nama;
            this.showModalHapusKurikulum = true;
        },
    }"
    class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300"
>

@include('components.sidebar')

<main id="main-content"
      :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'"
      class="flex-1 min-w-0 p-6 sm:p-10 transition-all duration-300 ease-in-out">

    <div>
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <div class="flex flex-col">
                    <div class="w-2 h-5 bg-teal-600 rounded-tl-md"></div>
                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 ml-3">Kurikulum & Tahun Akademik</h1>
            </div>
            @include('components.header-profile')
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mt-4 p-4 text-sm text-green-800 rounded-xl bg-green-50 border border-green-200" role="alert">
                <span class="font-medium">Berhasil!</span> {{ session('success') }}
            </div>
        @endif
        @if(session('error') || $errors->any())
            <div class="mt-4 p-4 text-sm text-red-800 rounded-xl bg-red-50 border border-red-200" role="alert">
                <span class="font-medium">Gagal!</span> {{ session('error') ?? 'Terdapat kesalahan pada input.' }}
                @if($errors->any())
                    <ul class="mt-1.5 list-disc list-inside">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        <!-- Tab Navigation -->
        <div class="mt-8 border-b border-gray-200">
            <div class="flex gap-1">
                <button
                    @click="activeTab = 'tahun'"
                    :class="activeTab === 'tahun'
                        ? 'border-b-2 border-teal-600 text-teal-700 font-bold bg-white'
                        : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    class="px-6 py-3 text-sm rounded-t-xl transition-all duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Tahun Akademik
                    <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-teal-100 text-teal-700 font-semibold">
                        {{ $tahunAkademiks->count() }}
                    </span>
                </button>
                <button
                    @click="activeTab = 'kurikulum'"
                    :class="activeTab === 'kurikulum'
                        ? 'border-b-2 border-teal-600 text-teal-700 font-bold bg-white'
                        : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    class="px-6 py-3 text-sm rounded-t-xl transition-all duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Master Kurikulum
                    <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-teal-100 text-teal-700 font-semibold">
                        {{ $kurikulums->count() }}
                    </span>
                </button>
            </div>
        </div>

        <!-- ===================== TAB: TAHUN AKADEMIK ===================== -->
        <div x-show="activeTab === 'tahun'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="mt-6 flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-700">Daftar Tahun Akademik</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Kelola tahun akademik yang tersedia untuk penjadwalan.</p>
                </div>
                @if(Auth::user()->isAdmin())
                <button
                    @click="showModalTambahTahun = true"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-xl shadow-md transition duration-200 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Tahun Akademik
                </button>
                @endif
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Semester</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Tahun Ajaran</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            @if(Auth::user()->isAdmin())
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($tahunAkademiks as $tahun)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-6 py-4 font-semibold text-gray-800">
                                {{ $tahun->nama_tahunakademik }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $tahun->tahun_ajaran }}
                            </td>
                            <td class="px-6 py-4">
                                @if(Auth::user()->isAdmin())
                                <form action="{{ route('kurikulum.toggleTahun', $tahun->id_tahunakademik) }}" method="POST" class="inline-flex items-center gap-2">
                                    @csrf
                                    <button type="submit"
                                            class="{{ $tahun->status_aktif ? 'bg-teal-500' : 'bg-gray-300' }} relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
                                            role="switch"
                                            title="Klik untuk mengubah status">
                                        <span aria-hidden="true"
                                              class="{{ $tahun->status_aktif ? 'translate-x-4' : 'translate-x-0' }} pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                                    </button>
                                    <span class="text-xs font-semibold {{ $tahun->status_aktif ? 'text-teal-700' : 'text-gray-400' }}">
                                        {{ $tahun->status_aktif ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </form>
                                @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $tahun->status_aktif ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $tahun->status_aktif ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                    {{ $tahun->status_aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                @endif
                            </td>
                            @if(Auth::user()->isAdmin())
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Edit -->
                                    <button
                                        @click="openEditTahun({
                                            id: {{ $tahun->id_tahunakademik }},
                                            nama_tahunakademik: '{{ $tahun->nama_tahunakademik }}',
                                            tahun_ajaran: '{{ $tahun->tahun_ajaran }}',
                                            status_aktif: {{ $tahun->status_aktif ? 1 : 0 }}
                                        })"
                                        class="p-2 text-teal-600 hover:text-teal-800 hover:bg-teal-50 rounded-lg transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>
                                    <!-- Hapus -->
                                    <button
                                        @click="openHapusTahun({{ $tahun->id_tahunakademik }}, '{{ $tahun->nama_tahunakademik }} {{ $tahun->tahun_ajaran }}')"
                                        class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center text-2xl">📅</div>
                                    <p class="font-semibold text-gray-700">Belum ada tahun akademik</p>
                                    <p class="text-gray-400 text-xs">Klik "Tambah Tahun Akademik" untuk memulai.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===================== TAB: MASTER KURIKULUM ===================== -->
        <div x-show="activeTab === 'kurikulum'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="mt-6 flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-700">Daftar Master Kurikulum</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Kelola master kurikulum yang dapat dipetakan ke semester mahasiswa.</p>
                </div>
                @if(Auth::user()->isAdmin())
                <button
                    @click="showModalTambahKurikulum = true"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow-md transition duration-200 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Master Kurikulum
                </button>
                @endif
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Kode</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Kurikulum</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Tahun Berlaku</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            @if(Auth::user()->isAdmin())
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($kurikulums as $kur)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-teal-50 text-teal-700 font-mono text-xs font-bold">
                                    {{ $kur->kode_kurikulum }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-800">{{ $kur->nama_kurikulum }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $kur->tahun_berlaku }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                    {{ $kur->status === 'Aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $kur->status === 'Aktif' ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                    {{ $kur->status }}
                                </span>
                            </td>
                            @if(Auth::user()->isAdmin())
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Edit -->
                                    <button
                                        @click="openEditKurikulum({
                                            id: {{ $kur->id_kurikulum }},
                                            kode_kurikulum: '{{ $kur->kode_kurikulum }}',
                                            nama_kurikulum: '{{ addslashes($kur->nama_kurikulum) }}',
                                            tahun_berlaku: '{{ $kur->tahun_berlaku }}',
                                            status: '{{ $kur->status }}'
                                        })"
                                        class="p-2 text-teal-600 hover:text-teal-800 hover:bg-teal-50 rounded-lg transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>
                                    <!-- Hapus -->
                                    <button
                                        @click="openHapusKurikulum({{ $kur->id_kurikulum }}, '{{ addslashes($kur->nama_kurikulum) }}')"
                                        class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center text-2xl">📚</div>
                                    <p class="font-semibold text-gray-700">Belum ada master kurikulum</p>
                                    <p class="text-gray-400 text-xs">Klik "Tambah Master Kurikulum" untuk memulai.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- ===================== MODAL TAMBAH TAHUN AKADEMIK ===================== -->
<div x-show="showModalTambahTahun" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
    @keydown.escape.window="showModalTambahTahun = false" @click.self="showModalTambahTahun = false" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">Tambah Tahun Akademik</h3>
            <button @click="showModalTambahTahun = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('kurikulum.storeTahunAkademik') }}" method="POST" class="p-6 space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Semester</label>
                <select name="nama_tahunakademik" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white" required>
                    <option value="" disabled selected>Pilih Semester</option>
                    <option value="Ganjil">Ganjil</option>
                    <option value="Genap">Genap</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tahun Ajaran</label>
                <input type="text" name="tahun_ajaran" placeholder="Contoh: 2025/2026" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                <select name="status_aktif" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" @click="showModalTambahTahun = false" class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-600 font-semibold rounded-xl hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-xl shadow-md transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- ===================== MODAL EDIT TAHUN AKADEMIK ===================== -->
<div x-show="showModalEditTahun" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
    @keydown.escape.window="showModalEditTahun = false" @click.self="showModalEditTahun = false" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">Edit Tahun Akademik</h3>
            <button @click="showModalEditTahun = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <template x-if="editTahun">
            <form :action="`/settings/kurikulum/tahun/${editTahun.id}`" method="POST" class="p-6 space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Semester</label>
                    <select name="nama_tahunakademik" x-model="editTahun.nama_tahunakademik" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white" required>
                        <option value="Ganjil">Ganjil</option>
                        <option value="Genap">Genap</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tahun Ajaran</label>
                    <input type="text" name="tahun_ajaran" x-model="editTahun.tahun_ajaran" placeholder="Contoh: 2025/2026" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                    <select name="status_aktif" x-model="editTahun.status_aktif" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showModalEditTahun = false" class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-600 font-semibold rounded-xl hover:bg-gray-50 transition">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow-md transition">Simpan Perubahan</button>
                </div>
            </form>
        </template>
    </div>
</div>

<!-- ===================== MODAL HAPUS TAHUN AKADEMIK ===================== -->
<div x-show="showModalHapusTahun" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
    @keydown.escape.window="showModalHapusTahun = false" @click.self="showModalHapusTahun = false" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
        <div class="p-6 text-center">
            <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Hapus Tahun Akademik?</h3>
            <p class="text-sm text-gray-500 mb-6">
                Tindakan ini akan menghapus <span class="font-semibold text-gray-700" x-text="hapusTahunNama"></span> secara permanen dan tidak dapat dibatalkan.
            </p>
            <div class="flex gap-3">
                <button @click="showModalHapusTahun = false" class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-600 font-semibold rounded-xl hover:bg-gray-50 transition">Batal</button>
                <form :action="`/settings/kurikulum/tahun/${hapusTahunId}`" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-xl shadow-md transition">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ===================== MODAL TAMBAH KURIKULUM ===================== -->
<div x-show="showModalTambahKurikulum" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
    @keydown.escape.window="showModalTambahKurikulum = false" @click.self="showModalTambahKurikulum = false" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">Tambah Master Kurikulum</h3>
            <button @click="showModalTambahKurikulum = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('kurikulum.storeKurikulum') }}" method="POST" class="p-6 space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kode Kurikulum</label>
                <input type="text" name="kode_kurikulum" placeholder="Contoh: KUR-2025" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Kurikulum</label>
                <input type="text" name="nama_kurikulum" placeholder="Contoh: Kurikulum Merdeka 2025" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tahun Berlaku</label>
                <input type="number" name="tahun_berlaku" placeholder="Contoh: 2025" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                <select name="status" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
                    <option value="Aktif">Aktif</option>
                    <option value="Tidak Aktif">Tidak Aktif</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" @click="showModalTambahKurikulum = false" class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-600 font-semibold rounded-xl hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow-md transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- ===================== MODAL EDIT KURIKULUM ===================== -->
<div x-show="showModalEditKurikulum" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
    @keydown.escape.window="showModalEditKurikulum = false" @click.self="showModalEditKurikulum = false" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">Edit Master Kurikulum</h3>
            <button @click="showModalEditKurikulum = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <template x-if="editKurikulum">
            <form :action="`/settings/kurikulum/master/${editKurikulum.id}`" method="POST" class="p-6 space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kode Kurikulum</label>
                    <input type="text" name="kode_kurikulum" x-model="editKurikulum.kode_kurikulum" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Kurikulum</label>
                    <input type="text" name="nama_kurikulum" x-model="editKurikulum.nama_kurikulum" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tahun Berlaku</label>
                    <input type="number" name="tahun_berlaku" x-model="editKurikulum.tahun_berlaku" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                    <select name="status" x-model="editKurikulum.status" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
                        <option value="Aktif">Aktif</option>
                        <option value="Tidak Aktif">Tidak Aktif</option>
                    </select>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showModalEditKurikulum = false" class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-600 font-semibold rounded-xl hover:bg-gray-50 transition">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow-md transition">Simpan Perubahan</button>
                </div>
            </form>
        </template>
    </div>
</div>

<!-- ===================== MODAL HAPUS KURIKULUM ===================== -->
<div x-show="showModalHapusKurikulum" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
    @keydown.escape.window="showModalHapusKurikulum = false" @click.self="showModalHapusKurikulum = false" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
        <div class="p-6 text-center">
            <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Hapus Kurikulum?</h3>
            <p class="text-sm text-gray-500 mb-6">
                Tindakan ini akan menghapus <span class="font-semibold text-gray-700" x-text="hapusKurikulumNama"></span> secara permanen dan tidak dapat dibatalkan.
            </p>
            <div class="flex gap-3">
                <button @click="showModalHapusKurikulum = false" class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-600 font-semibold rounded-xl hover:bg-gray-50 transition">Batal</button>
                <form :action="`/settings/kurikulum/master/${hapusKurikulumId}`" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-xl shadow-md transition">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>