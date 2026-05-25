<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjadwalan Otomatis - Step 4 | TSU</title>
    <link rel="icon" href="{{ asset('tsuwhite.png') }}" type="image/png">
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
            
            <div class="space-y-12 mt-8">
                @php
                    $groupedSchedule = collect($schedule)->groupBy(['prodi', 'kurikulum', 'semester']);
                @endphp

                @forelse ($groupedSchedule as $prodi => $kurikulums)
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                        <h3 class="text-xl font-bold text-teal-800 mb-6 flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            {{ $prodi }}
                        </h3>

                        @foreach ($kurikulums as $kurik => $semesters)
                            @foreach ($semesters as $sem => $items)
                                <div class="mb-8 last:mb-0">
                                    <div class="flex items-center mb-3">
                                        <span class="bg-teal-100 text-teal-800 text-sm font-bold px-3 py-1 rounded-full border border-teal-200 shadow-sm">
                                            Kurikulum {{ $kurik }}
                                        </span>
                                        <span class="mx-2 text-gray-300">|</span>
                                        <span class="bg-blue-100 text-blue-800 text-sm font-bold px-3 py-1 rounded-full border border-blue-200 shadow-sm">
                                            Semester {{ $sem }}
                                        </span>
                                    </div>

                                    <div class="overflow-x-auto rounded-lg border border-[#DBDBDB]">
                                        <table class="w-full min-w-[1000px] bg-white">
                                            <thead class="bg-teal-800 text-white">
                                                <tr>
                                                    <th class="w-16 text-left py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">No</th>
                                                    <th class="text-left py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">Mata Kuliah</th>
                                                    <th class="text-left py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">Dosen</th>
                                                    <th class="text-center py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">Tipe</th>
                                                    <th class="text-center py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">Kode</th>
                                                    <th class="text-center py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">Kelas</th>
                                                    <th class="text-center py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">Hari</th>
                                                    <th class="text-center py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">Jam</th>
                                                    <th class="text-center py-3 px-4 uppercase font-semibold text-xs whitespace-nowrap">Ruangan</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-700">
                                                @foreach ($items as $item)
                                                <tr class="border-b border-[#DBDBDB] hover:bg-gray-50">
                                                    <td class="text-left py-3 px-4 text-sm whitespace-nowrap">{{ $loop->parent->parent->parent->iteration }}.{{ $loop->iteration }}</td>
                                                    <td class="text-left py-3 px-4 text-sm whitespace-nowrap font-medium text-gray-800">{{ $item['subject_name'] }}</td>
                                                    <td class="text-left py-3 px-4 text-sm whitespace-nowrap">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            {{ $item['lecturer'] }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center py-3 px-4 text-sm whitespace-nowrap">
                                                        <span class="{{ $item['type'] == 'Teori' ? 'text-blue-600 bg-blue-50 border-blue-100' : 'text-purple-600 bg-purple-50 border-purple-100' }} border px-2 py-0.5 rounded text-xs">
                                                            {{ $item['type'] }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center py-3 px-4 text-sm whitespace-nowrap font-mono text-xs text-gray-500">{{ $item['subject_code'] }}</td>
                                                    <td class="text-center py-3 px-4 text-sm whitespace-nowrap">
                                                        <span class="font-bold text-teal-700 bg-teal-50 px-2 py-1 rounded">{{ $item['class_code'] }}</span>
                                                    </td>
                                                    <td class="text-center py-3 px-4 text-sm whitespace-nowrap font-medium">{{ $item['day'] }}</td>
                                                    <td class="text-center py-3 px-4 text-sm whitespace-nowrap font-mono text-xs">{{ $item['time'] }}</td>
                                                    <td class="text-center py-3 px-4 text-sm whitespace-nowrap">
                                                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">
                                                            {{ $item['room'] }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                @empty
                    <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada jadwal</h3>
                        <p class="mt-1 text-sm text-gray-500">Silakan ulangi proses penjadwalan dari awal.</p>
                    </div>
                @endforelse
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
