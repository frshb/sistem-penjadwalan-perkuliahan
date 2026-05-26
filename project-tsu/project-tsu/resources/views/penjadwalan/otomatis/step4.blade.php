<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjadwalan Otomatis - Step 4 | TSU</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ sidebarOpen: true }" class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300 font-sans">

    @include('components.sidebar')

    <main :class="sidebarOpen ? 'lg:ml-64' : ''" class="flex-1 p-6 sm:p-10 transition-all duration-300 ease-in-out bg-gray-50">

        <div class="flex items-center mb-8">
            <div class="flex flex-col">
                <div class="w-2 h-5 bg-teal-800 rounded-tl-md"></div>
                <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 ml-3">
                Modul Penjadwalan ( S1 Informatika )
            </h1>
        </div>

        <div class="bg-white p-8 rounded-lg shadow-md border border-transparent min-h-[600px]">

            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-700">Penjadwalan</h2>
                <div class="flex text-sm text-gray-500 mt-1">
                    <span class="text-teal-800">Pilih semester</span>
                    <span class="mx-2 font-bold text-gray-400">></span>
                    <span class="text-teal-800">Pilih Ruangan</span>
                    <span class="mx-2 font-bold text-gray-400">></span>
                    <span class="text-teal-800">Input Mata Kuliah</span>
                    <span class="mx-2 font-bold text-gray-400">></span>
                    <span class="font-semibold text-teal-800">Buat Jadwal</span>
                </div>
            </div>

            <div class="relative mb-8">
                <div class="absolute top-4 left-0 w-full h-2 bg-gray-300 z-0 rounded-full"></div>
                <div class="absolute top-4 left-0 w-full h-2 bg-teal-800 z-0 rounded-full"></div>
                <div class="relative z-10 flex justify-between w-full max-w-4xl mx-auto">
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full bg-teal-800 flex items-center justify-center text-white font-bold text-lg shadow-md ring-4 ring-white">1</div>
                        <span class="mt-2 text-xs font-semibold text-gray-800">Langkah 1</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full bg-teal-800 flex items-center justify-center text-white font-bold text-lg shadow-md ring-4 ring-white">2</div>
                        <span class="mt-2 text-xs font-semibold text-gray-800">Langkah 2</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full bg-teal-800 flex items-center justify-center text-white font-bold text-lg shadow-md ring-4 ring-white">3</div>
                        <span class="mt-2 text-xs font-semibold text-gray-800">Langkah 3</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full bg-teal-800 flex items-center justify-center text-white font-bold text-lg shadow-md ring-4 ring-white">4</div>
                        <span class="mt-2 text-xs font-semibold text-gray-800">Langkah 4</span>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm mt-8">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="bg-teal-800 text-white uppercase text-xs">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold text-center rounded-tl-lg">No</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Mata Kuliah</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Dosen</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">Tipe</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">Semester</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">Kode Matkul</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">Kelas</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">Hari</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">Jam</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center rounded-tr-lg">Ruangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($schedule as $item)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 text-center font-medium">{{ $item['no'] }}</td>
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $item['subject_name'] }}</td>
                            <td class="px-6 py-4">
                                <span class="bg-gray-100 text-gray-700 px-2.5 py-1 rounded-md border border-gray-200 text-xs font-medium">
                                    {{ $item['lecturer'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="{{ $item['type'] == 'Teori' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }} px-2 py-0.5 rounded text-xs font-medium">
                                    {{ $item['type'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">{{ $item['semester'] }}</td>
                            <td class="px-6 py-4 text-center font-mono text-xs">{{ $item['subject_code'] }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-bold text-teal-700">{{ $item['class_code'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-center font-medium">{{ $item['day'] }}</td>
                            <td class="px-6 py-4 text-center font-mono text-xs">{{ $item['time'] }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">
                                    {{ $item['room'] }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="px-6 py-8 text-center text-gray-500 italic">
                                Belum ada jadwal yang digenerate. Silakan ulangi langkah-langkah sebelumnya.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-8 flex justify-end space-x-4">
                 <button type="button" class="px-6 py-2.5 bg-gray-600 text-white font-bold rounded-lg shadow hover:bg-gray-700 transition duration-200" onclick="window.print()">
                    Cetak PDF
                </button>
                <a href="{{ route('jadwal.otomatis.step1') }}" class="px-6 py-2.5 bg-teal-600 text-white font-bold rounded-lg shadow hover:bg-teal-700 transition duration-200">
                    Selesai & Kembali ke Awal
                </a>
            </div>

        </div>
    </main>
    
</body>
</html>
