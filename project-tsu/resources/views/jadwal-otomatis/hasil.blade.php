<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Penjadwalan GA</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ sidebarOpen: true }" class="bg-gray-100/50 overflow-x-hidden min-h-screen font-sans">

@include('components.sidebar')

<main :class="sidebarOpen ? 'lg:ml-64' : ''" class="flex-1 p-6 sm:p-10 transition-all duration-300">

    <div class="flex items-center mb-8">
        <div class="flex flex-col">
            <div class="w-2 h-5 bg-teal-800 rounded-tl-md"></div>
            <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 ml-3">Hasil Penjadwalan Otomatis</h1>
    </div>

    {{-- STATISTIK --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Fitness Score</p>
            <p class="text-3xl font-black mt-1 {{ $fitness >= 90 ? 'text-green-600' : ($fitness >= 70 ? 'text-yellow-500' : 'text-red-500') }}">
                {{ $fitness }}%
            </p>
            <p class="text-xs text-gray-400 mt-1">{{ $fitness >= 90 ? 'Sangat Baik' : ($fitness >= 70 ? 'Cukup Baik' : 'Perlu Review') }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Total Kelas</p>
            <p class="text-3xl font-black text-teal-700 mt-1">{{ $totalKelas }}</p>
            <p class="text-xs text-gray-400 mt-1">kelas dijadwalkan</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Generasi</p>
            <p class="text-3xl font-black text-blue-600 mt-1">{{ $generasi }}</p>
            <p class="text-xs text-gray-400 mt-1">dari {{ $generasi }} maks</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Populasi</p>
            <p class="text-3xl font-black text-purple-600 mt-1">{{ $populasi }}</p>
            <p class="text-xs text-gray-400 mt-1">individu per generasi</p>
        </div>
    </div>

    {{-- FITNESS BAR --}}
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm mb-8">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-gray-700">Kualitas Jadwal</span>
            <span class="text-sm font-bold {{ $fitness >= 90 ? 'text-green-600' : ($fitness >= 70 ? 'text-yellow-500' : 'text-red-500') }}">{{ $fitness }}%</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-4">
            <div class="h-4 rounded-full transition-all duration-700 {{ $fitness >= 90 ? 'bg-green-500' : ($fitness >= 70 ? 'bg-yellow-400' : 'bg-red-400') }}"
                 style="width: {{ $fitness }}%"></div>
        </div>
        @if ($fitness < 100)
        <p class="text-xs text-gray-400 mt-2">
            ⚠ Masih ada kemungkinan bentrok. Silakan review dan edit manual di workspace.
        </p>
        @else
        <p class="text-xs text-green-600 mt-2">
            ✅ Tidak ada bentrok terdeteksi oleh algoritma.
        </p>
        @endif
    </div>

    {{-- TABEL HASIL --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-800">Preview Jadwal Hasil GA</h2>
            <span class="text-sm text-gray-400">{{ $tahunAkademik->nama_tahunakademik }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-teal-700 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left">Hari</th>
                        <th class="px-4 py-3 text-left">Jam</th>
                        <th class="px-4 py-3 text-left">Kelas</th>
                        <th class="px-4 py-3 text-left">Kode MK</th>
                        <th class="px-4 py-3 text-left">Mata Kuliah</th>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-left">SKS</th>
                        <th class="px-4 py-3 text-left">Dosen</th>
                        <th class="px-4 py-3 text-left">Ruangan</th>
                        <th class="px-4 py-3 text-left">Prodi</th>
                        <th class="px-4 py-3 text-left">Smt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($jadwalRows as $i => $row)
                    <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-teal-50/30' }} hover:bg-teal-50 transition">
                        <td class="px-4 py-3 font-semibold text-teal-700">{{ $row['hari'] }}</td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $row['jam_mulai'] }} – {{ $row['jam_selesai'] }}</td>
                        <td class="px-4 py-3 font-bold">{{ $row['nama_kelas'] }}</td>
                        <td class="px-4 py-3 font-mono text-teal-700">{{ $row['kode_mk'] }}</td>
                        <td class="px-4 py-3">{{ $row['nama_mk'] }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $row['jenis'] === 'Praktikum' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $row['jenis'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">{{ $row['sks'] }}</td>
                        <td class="px-4 py-3">{{ $row['dosen'] }}</td>
                        <td class="px-4 py-3">{{ $row['ruangan'] ?: '-' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $row['prodi'] }}</td>
                        <td class="px-4 py-3 text-center">{{ $row['semester'] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="px-4 py-8 text-center text-gray-400">Tidak ada hasil.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ACTION --}}
    <form action="{{ route('jadwal.otomatis.simpan') }}" method="POST">
        @csrf
        <input type="hidden" name="jadwal_json" value="{{ $jadwalJson }}">
        <input type="hidden" name="tahun_akademik_id" value="{{ $tahunAkademik->id_tahunakademik }}">

        <div class="flex items-center justify-between">
            <a href="{{ route('jadwal.otomatis.index') }}"
               class="px-6 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl font-semibold flex items-center gap-2 transition">
                ← Ulangi Generate
            </a>
            <button type="submit"
                    class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl shadow flex items-center gap-2 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan & Buka di Workspace Manual
            </button>
        </div>
    </form>

</main>
</body>
</html>
