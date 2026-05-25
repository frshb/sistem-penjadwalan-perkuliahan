<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modul Penjadwalan | {{ $prodi->nama_prodi ?? 'Penjadwalan' }}</title>
    <meta name="description" content="Modul Penjadwalan Perkuliahan Fakultas Teknik TSU">
    <link rel="icon" href="{{ asset('tsuwhite.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Memperkecil ukuran font dropdown di dalam tabel agar pas */
        .table-select {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem; /* 14px */
            min-width: 100px; /* Lebar minimum */
        }
    </style>
</head>
<body class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300">

        @include('components.sidebar')

        <!-- Konten Utama -->
        <main id="main-content" :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'" class="flex-1 p-6 sm:p-10 transition-all duration-300 ease-in-out bg-gray-50">

            <div class="flex items-center">
                <div class="flex flex-col">
                    <div class="w-2 h-5 bg-teal-800 rounded-tl-md"></div>
                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 ml-3">
                    Modul Penjadwalan ({{ $prodi->nama_prodi }})
                </h1>
            </div>

            <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md mt-6 border border-transparent">
                <h2 class="text-2xl font-bold text-gray-700 mb-6">
                    Penjadwalan
                </h2>

                <!-- Tombol Aksi Utama -->
                <div class="flex items-center space-x-4 mb-6">
                    <!-- Tombol Buat Jadwal (Genetika) -->
                    <!-- Tombol Buat Jadwal (Genetika) - NOW LINKS TO WIZARD -->
                    <a href="{{ route('jadwal.otomatis.step1') }}" class="px-5 py-2 bg-yellow-600 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-75">
                        Buat Jadwal (Otomatis)
                    </a>

                    <!-- Tombol Simpan Manual (terhubung ke form di bawah) -->
                    <button type="submit" form="form-jadwal-manual" class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-opacity-75">
                        Simpan Jadwal Manual
                    </button>
                </div>

                <!-- Notifikasi Sukses -->
                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-100 text-green-800 border border-green-300 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('info'))
                    <div class="mb-4 p-4 bg-blue-100 text-blue-800 border border-blue-300 rounded-lg">
                        {{ session('info') }}
                    </div>
                @endif

                <form action="{{ route('jadwal.save_manual') }}" method="POST" id="form-jadwal-manual">
                    @csrf
                    <input type="hidden" name="id_prodi" value="{{ $prodi->id_prodi }}">

                    <div class="overflow-hidden rounded-lg border border-[#DBDBDB]">
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-teal-800 text-white">
                                    <tr>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm text-left">No</th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Mata Kuliah</th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Dosen</th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Tipe</th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Semester</th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Kode Matkul</th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Hari</th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Jam</th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Ruangan</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-700 dark:text-gray-300">

                                    @forelse ($matakuliahs as $matkul)
                                        @php
                                            // Cek apakah matkul ini sudah ada di jadwal
                                            $jadwal = $jadwalDibuat->get($matkul->kode_matkul);
                                        @endphp
                                        <tr class="border-b border-[#DBDBDB] hover:bg-gray-50">
                                            <!-- No -->
                                            <td class="py-3 px-4">{{ $loop->iteration }}</td>

                                            <!-- Mata Kuliah -->
                                            <td class="py-3 px-4 font-medium">{{ $matkul->nama_matkul }}</td>

                                            <!-- Dropdown Dosen -->
                                            <td class="py-2 px-2">
                                                <select name="jadwal[{{ $matkul->kode_matkul }}][id_dosen]" class="table-select border border-gray-300 rounded-md w-full">
                                                    <option value="">Pilih Dosen</option>
                                                    @foreach ($dosens as $dosen)
                                                        <option value="{{ $dosen->id_dosen }}"
                                                            {{ ($jadwal && $jadwal->id_dosen == $dosen->id_dosen) ? 'selected' : '' }}>
                                                            {{ $dosen->nama_dosen }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <!-- Dropdown Tipe -->
                                            <td class="py-2 px-2">
                                                <select name="jadwal[{{ $matkul->kode_matkul }}][tipe]" class="table-select border border-gray-300 rounded-md w-full">
                                                    <option value="Teori" {{ ($jadwal && $jadwal->tipe == 'Teori') ? 'selected' : ($matkul->jenis == 'teori' ? 'selected' : '') }}>Teori</option>
                                                    <option value="Praktikum" {{ ($jadwal && $jadwal->tipe == 'Praktikum') ? 'selected' : ($matkul->jenis == 'praktikum' ? 'selected' : '') }}>Praktikum</option>
                                                </select>
                                            </td>

                                            <!-- Semester (Statis dari matkul) -->
                                            <td class="py-2 px-2">
                                                <input type="hidden" name="jadwal[{{ $matkul->kode_matkul }}][semester]" value="{{ $matkul->semester }}">
                                                <span class="px-2">{{ $matkul->semester }}</span>
                                            </td>

                                            <!-- Kode Matkul -->
                                            <td class="py-3 px-4">{{ $matkul->kode_matkul }}</td>

                                            <!-- Dropdown Hari -->
                                            <td class="py-2 px-2">
                                                <select name="jadwal[{{ $matkul->kode_matkul }}][hari]" class="table-select border border-gray-300 rounded-md w-full">
                                                    <option value="">Pilih Hari</option>
                                                    @foreach ($haris as $hari)
                                                        <option value="{{ $hari }}" {{ ($jadwal && $jadwal->hari == $hari) ? 'selected' : '' }}>{{ $hari }}</option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <!-- Dropdown Jam -->
                                            <td class="py-2 px-2">
                                                <select name="jadwal[{{ $matkul->kode_matkul }}][jam]" class="table-select border border-gray-300 rounded-md w-full">
                                                    <option value="">Pilih Jam</option>
                                                    @foreach ($jams as $jam)
                                                        <option value="{{ $jam }}" {{ ($jadwal && $jadwal->jam == $jam) ? 'selected' : '' }}>{{ $jam }}</option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <!-- Dropdown Ruangan -->
                                            <td class="py-2 px-2">
                                                <select name="jadwal[{{ $matkul->kode_matkul }}][id_ruang]" class="table-select border border-gray-300 rounded-md w-full">
                                                    <option value="">Pilih Ruangan</option>
                                                    @foreach ($ruangans as $ruangan)
                                                        <option value="{{ $ruangan->id_ruang }}"
                                                            {{ ($jadwal && $jadwal->id_ruang == $ruangan->id_ruang) ? 'selected' : '' }}>
                                                            {{ $ruangan->nama_ruang }} (Kaps: {{ $ruangan->kapasitas }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-gray-500">
                                                Tidak ada mata kuliah yang perlu dijadwalkan untuk prodi ini.
                                            </td>
                                        </tr>
                                    @endForelse

                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- JavaScript untuk Sidebar -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const closeBtn = document.getElementById('sidebar-toggle');
            const openBtn = document.getElementById('sidebar-open-btn');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            if (closeBtn && openBtn && sidebar && mainContent) {
                closeBtn.addEventListener('click', () => {
                    sidebar.classList.add('-translate-x-full');
                    mainContent.classList.remove('ml-64');
                    openBtn.classList.remove('hidden');
                });
                openBtn.addEventListener('click', () => {
                    sidebar.classList.remove('-translate-x-full');
                    mainContent.classList.add('ml-64');
                    openBtn.classList.add('hidden');
                });
            }
        });
    </script>
</body>
</html>
