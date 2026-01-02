<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjadwalan Otomatis - Step 3 | TSU</title>
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
                    <span class="font-semibold text-teal-800">Input Mata Kuliah</span>
                    <span class="mx-2">></span>
                    <span>Buat Jadwal</span>
                </div>
            </div>


            <div class="relative mb-8">
                <div class="absolute top-4 left-0 w-full h-2 bg-gray-300 z-0 rounded-full"></div>
                <div class="absolute top-4 left-0 w-2/3 h-2 bg-teal-800 z-0 rounded-l-full"></div>
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
                        <div class="w-10 h-10 rounded-full bg-gray-400 flex items-center justify-center text-white font-bold text-lg shadow-md ring-4 ring-white">4</div>
                        <span class="mt-2 text-xs font-semibold text-gray-400">Langkah 4</span>
                    </div>
                </div>
            </div>
            
            <h2 class="text-xl font-bold text-gray-700 mb-4">Input Kelas</h2>

            <div x-data="{ activeTab: 'Informatika' }">
                

                <div class="border-b border-gray-200 mb-6 font-medium text-sm flex space-x-8">
                    @foreach(['Informatika', 'Sistem Informasi', 'Rekayasa Komputer'] as $tab)
                        <button type="button" 
                                @click="activeTab = '{{ $tab }}'" 
                                :class="activeTab === '{{ $tab }}' ? 'border-teal-600 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                class="pb-3 border-b-2 transition-colors">
                            S1 {{ $tab }}
                        </button>
                    @endforeach
                </div>


                <form action="{{ route('jadwal.otomatis.step3.store') }}" method="POST"> 
                    @csrf
                    
                    @foreach ($subjectsByProdi as $prodiName => $subjects)
                        <div x-show="activeTab === '{{ $prodiName }}'" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="space-y-6">
                            
                            @if(count($subjects) > 0)
                                @foreach ($subjects as $index => $subject)
                                    <!-- Subject Card with Local Data for classes -->
                                    <div x-data="{ classes: [''] }" class="border border-gray-300 rounded-xl p-6 bg-white shadow-sm">
                                        <h3 class="text-lg font-bold text-gray-800 mb-1">Kurikulum {{ $subject->kurikulum }}</h3>
                                        <div class="text-sm text-gray-600 mb-4">Semester {{ $subject->semester }}</div>
            
                                        <div class="flex items-start space-x-4">
                                            <!-- Dynamic Class Code Inputs Container -->
                                            <div class="flex-1 space-y-3">
                                                <!-- Loop through classes array -->
                                                <template x-for="(kls, i) in classes" :key="i">
                                                    <div class="relative">
                                                        <input type="text" 
                                                               name="data[{{ $prodiName }}][{{ $subject->code }}][classes][]" 
                                                               placeholder="isi kode kelas" 
                                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm text-gray-600 italic">
                                                    </div>
                                                </template>
                                            </div>

                                            <!-- Subject Name -->
                                            <div class="w-1/4">
                                                <div class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 text-sm flex items-center justify-between">
                                                    <span>{{ $subject->name }}</span>
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                </div>
                                            </div>

                                            <!-- Lecturer Dropdown -->
                                            <div class="w-1/4">
                                                <div class="relative">
                                                    <select name="data[{{ $prodiName }}][{{ $subject->code }}][lecturer]" class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm text-gray-600 appearance-none bg-white">
                                                        <option value="">~ dosen pengampu ~</option>
                                                        @foreach ($dosens as $dosen)
                                                            <option value="{{ $dosen->id }}">{{ $dosen->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Add Class Button -->
                                            <div>
                                                <button type="button" @click="classes.push('')" class="px-4 py-2 bg-yellow-400 text-gray-800 font-bold text-sm rounded-lg shadow hover:bg-yellow-500 transition duration-200 flex items-center whitespace-nowrap">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                    Tambah kelas
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-gray-500 text-center py-8">Tidak ada mata kuliah untuk semester yang dipilih di prodi ini.</div>
                            @endif
                        </div>
                    @endforeach


                    <div class="mt-12 flex justify-between">
                        <a href="{{ route('jadwal.otomatis.step2') }}" class="px-6 py-2.5 bg-gray-500 text-white font-bold rounded-lg shadow-lg hover:bg-gray-600 transition duration-200">
                            Kembali
                        </a>
                        
                        <button type="submit" class="px-6 py-2.5 bg-teal-600 text-white font-bold rounded-lg shadow-lg hover:bg-teal-700 transition duration-200 flex items-center">
                            Buat Jadwal (Selesai dummy)
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
            

</body>
</html>
