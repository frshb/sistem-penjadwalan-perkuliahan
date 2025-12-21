<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjadwalan Otomatis - Step 2 | TSU</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ sidebarOpen: true }" class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300 font-sans">

    @include('components.sidebar')


    <main :class="sidebarOpen ? 'ml-64' : ''" class="flex-1 p-6 sm:p-10 transition-all duration-300 ease-in-out bg-gray-50">


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
                    <span class="font-semibold text-teal-800">Pilih Ruangan</span>
                    <span class="mx-2">></span>
                    <span>Input Mata Kuliah</span>
                    <span class="mx-2">></span>
                    <span>Buat Jadwal</span>
                </div>
            </div>


            <div class="relative mb-12">
                <div class="absolute top-4 left-0 w-full h-2 bg-gray-300 z-0 rounded-full"></div>
                
                <div class="absolute top-4 left-0 w-1/3 h-2 bg-teal-800 z-0 rounded-l-full"></div>

                <div class="relative z-10 flex justify-between w-full max-w-4xl mx-auto">

                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full bg-teal-800 flex items-center justify-center text-white font-bold text-lg shadow-md ring-4 ring-white">
                            1
                        </div>
                        <span class="mt-2 text-xs font-semibold text-gray-800">Langkah 1</span>
                    </div>

                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full bg-teal-800 flex items-center justify-center text-white font-bold text-lg shadow-md ring-4 ring-white">
                            2
                        </div>
                        <span class="mt-2 text-xs font-semibold text-gray-800">Langkah 2</span>
                    </div>

                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full bg-gray-400 flex items-center justify-center text-white font-bold text-lg shadow-md ring-4 ring-white">
                            3
                        </div>
                        <span class="mt-2 text-xs font-semibold text-gray-400">Langkah 3</span>
                    </div>

                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full bg-gray-400 flex items-center justify-center text-white font-bold text-lg shadow-md ring-4 ring-white">
                            4
                        </div>
                        <span class="mt-2 text-xs font-semibold text-gray-400">Langkah 4</span>
                    </div>
                </div>
            </div>


            <form action="{{ route('jadwal.otomatis.step2.store') }}" method="POST"> <!-- Next Step -->
                @csrf


                <div class="border border-gray-300 rounded-xl p-8">
                    <div class="space-y-10">
                        @foreach ($gedungs as $gedung => $floors)
                            <div>
                                <h3 class="text-xl font-bold text-gray-800 mb-6">{{ $gedung }}</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                    @foreach ($floors as $lantai => $rooms)
                                        <div>
                                            <h4 class="text-sm text-gray-500 font-semibold mb-3">{{ $lantai }}</h4>
                                            <div class="space-y-3">
                                                @foreach ($rooms as $room)
                                                    <div class="flex items-center space-x-3">
                                                        <input type="checkbox" 
                                                               id="{{ Str::slug($gedung) }}_{{ Str::slug($room) }}" 
                                                               name="rooms[]" 
                                                               value="{{ $room }}"
                                                               class="w-5 h-5 accent-teal-800 cursor-pointer">
                                                        <label for="{{ Str::slug($gedung) }}_{{ Str::slug($room) }}" class="text-gray-800 font-bold text-sm cursor-pointer select-none">
                                                            {{ $room }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>


                <div class="mt-12 flex justify-between">
                    <a href="{{ route('jadwal.otomatis.step1') }}" class="px-6 py-2.5 bg-gray-500 text-white font-bold rounded-lg shadow-lg hover:bg-gray-600 transition duration-200">
                        Kembali
                    </a>
                    
                    <button type="submit" class="px-6 py-2.5 bg-teal-600 text-white font-bold rounded-lg shadow-lg hover:bg-teal-700 transition duration-200 flex items-center">
                        Lanjut ke Langkah 3
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>

        </div>
    </main>
    

</body>
</html>
