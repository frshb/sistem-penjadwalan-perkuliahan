<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Data | Tambah Ruangan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100/50 overflow-x-hidden">

    @include('management.ruangan.sidebar') <main id="main-content" class="flex-1 p-6 sm:p-10 transition-all duration-300 ease-in-out ml-64">
        <div class="flex items-center">
             <h1 class="text-3xl font-bold text-gray-800 ml-3">Tambah Ruangan Baru</h1>
        </div>

        <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md mt-6">

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Oops! Ada kesalahan:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('ruangan.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="nama_ruang" class="block text-sm font-medium text-gray-700">Nama Ruangan</label>
                        <input type="text" name="nama_ruang" id="nama_ruang" value="{{ old('nama_ruang') }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm" required>
                    </div>

                    <div>
                        <label for="id_gedung" class="block text-sm font-medium text-gray-700">Gedung</label>
                        <select name="id_gedung" id="id_gedung"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm" required>
                            <option value="">-- Pilih Gedung --</option>
                            @foreach ($gedungs as $gedung)
                                <option value="{{ $gedung->id_gedung }}" {{ old('id_gedung') == $gedung->id_gedung ? 'selected' : '' }}>
                                    {{ $gedung->nama_gedung }} ({{ $gedung->lokasi }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="kapasitas" class="block text-sm font-medium text-gray-700">Kapasitas</label>
                        <input type="number" name="kapasitas" id="kapasitas" value="{{ old('kapasitas') }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm" required>
                    </div>

                    <div>
                        <label for="fasilitas" class="block text-sm font-medium text-gray-700">Fasilitas (pisahkan dengan koma)</label>
                        <textarea name="fasilitas" id="fasilitas" rows="3"
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">{{ old('fasilitas') }}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('ruangan.index') }}"
                       class="px-5 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg shadow-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-75">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-opacity-75">
                        Simpan
                    </button>
                </div>
            </form>

        </div>
    </main>

    </body>
</html>
