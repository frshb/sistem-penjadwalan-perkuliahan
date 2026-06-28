<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Akademik | TSU</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300" x-data="{ sidebarOpen: true, showModal: false, editMode: false, modalData: { id: null, name: '', date: '', description: '' } }">

    <div class="flex min-h-screen">
        @include('components.sidebar')
        <main id="main-content" :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'" class="flex-1 min-w-0 p-6 sm:p-10 transition-all duration-300 ease-in-out">
            <div>
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <button @click="sidebarOpen = !sidebarOpen" class="flex flex-col hover:opacity-80 transition cursor-pointer" title="Toggle Sidebar">
                        <div class="w-2 h-5 bg-teal-800 rounded-tl-md"></div>
                        <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                    </button>
                    <h1 class="text-2xl font-bold text-gray-800 ml-3">Kalender Akademik</h1>
                </div>
                @include('components.header-profile')
            </div>

            <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md mt-6 border border-transparent">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-700 mb-4 sm:mb-0">Daftar Hari Besar</h2>
                    <button @click="showModal = true; editMode = false; modalData = { id: null, name: '', date: '', description: '' }" class="flex items-center px-4 py-2 bg-yellow-600 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-75">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Tambah Hari Besar
                    </button>
                </div>

                <div class="overflow-hidden rounded-lg border border-[#DBDBDB]">
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white whitespace-nowrap">
                            <thead class="bg-teal-800 text-white">
                                <tr>
                                    <th class="w-16 text-left py-4 px-3 uppercase font-semibold text-xs">No</th>
                                    <th class="text-left py-4 px-3 uppercase font-semibold text-xs">Nama Hari Besar</th>
                                    <th class="text-left py-4 px-3 uppercase font-semibold text-xs">Tanggal</th>
                                    <th class="text-left py-4 px-3 uppercase font-semibold text-xs">Keterangan</th>
                                    <th class="w-48 text-left py-4 px-3 uppercase font-semibold text-xs">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                @forelse ($academicCalendars as $calendar)
                                    <tr class="border-b border-[#DBDBDB] hover:bg-gray-50">
                                        <td class="text-left py-4 px-3 text-sm">{{ $loop->iteration }}</td>
                                        <td class="text-left py-4 px-3 text-sm font-medium">{{ $calendar->name }}</td>
                                        <td class="text-left py-4 px-3 text-sm">{{ \Carbon\Carbon::parse($calendar->date)->translatedFormat('d F Y') }}</td>
                                        <td class="text-left py-4 px-3 text-sm">{{ $calendar->description ?? '-' }}</td>
                                        <td class="text-left py-4 px-3 text-sm">
                                            <div class="flex space-x-2">
                                                <button @click="showModal = true; editMode = true; modalData = { id: '{{ $calendar->id }}', name: '{{ $calendar->name }}', date: '{{ $calendar->date->format('Y-m-d') }}', description: '{{ $calendar->description }}' }" class="flex items-center justify-center bg-yellow-400 text-gray-900 px-3 py-1 rounded-md hover:bg-yellow-500 text-xs font-medium transition duration-150">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                    Edit
                                                </button>
                                                <form action="{{ route('settings.academic_calendar.destroy', $calendar->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="flex items-center justify-center bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-700 text-xs font-medium transition duration-150">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-4 text-gray-500">Data belum tersedia.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>


            </div>
        </main>
    </div>

    <!-- Modal -->
    <div x-show="showModal" class="fixed inset-0 z-[999] overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
            <div x-show="showModal" @click="showModal = false" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showModal" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-[1000]">
                <form :action="editMode ? '/settings/academic-calendar/' + modalData.id : '{{ route('settings.academic_calendar.store') }}'" method="POST">
                    @csrf
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title" x-text="editMode ? 'Edit Hari Besar' : 'Tambah Hari Besar'">
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Hari Besar</label>
                                        <input type="text" name="name" id="name" x-model="modalData.name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="date" class="block text-sm font-medium text-gray-700">Tanggal</label>
                                        <input type="date" name="date" id="date" x-model="modalData.date" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="description" class="block text-sm font-medium text-gray-700">Keterangan</label>
                                        <textarea name="description" id="description" x-model="modalData.description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-teal-600 text-base font-medium text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan
                        </button>
                        <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('components.success-popup')
</body>
</html>
