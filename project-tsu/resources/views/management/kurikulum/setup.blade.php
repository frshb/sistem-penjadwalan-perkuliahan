<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Kurikulum | Data Kelas</title>
    <meta name="description" content="Setup Kurikulum Sistem Penjadwalan Perkuliahan">
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    x-data="kurikulumSetup()"
    class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300"
>

@include('components.sidebar')

<main id="main-content"
      :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'"
      class="flex-1 min-w-0 p-6 sm:p-10 transition-all duration-300 ease-in-out">

    <div>
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center">
                <div class="flex flex-col">
                    <div class="w-2 h-5 bg-teal-600 rounded-tl-md"></div>
                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 ml-3">
                    Setup Kurikulum: {{ $tahunAkademik->nama_tahunakademik }}
                </h1>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="flex items-center bg-white rounded-xl shadow-sm border border-gray-100 p-1">
                    <button type="button" @click="undo()" :disabled="historyIndex <= 0" 
                            class="p-2 rounded-lg transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100 text-gray-600" title="Undo (Batal)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    </button>
                    <div class="w-px h-5 bg-gray-200 mx-1"></div>
                    <button type="button" @click="redo()" :disabled="historyIndex >= history.length - 1" 
                            class="p-2 rounded-lg transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100 text-gray-600" title="Redo (Ulangi)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"/></svg>
                    </button>
                </div>
                @include('components.header-profile')
            </div>
        </div>

        <form action="{{ route('kurikulum.store') }}" method="POST" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            @csrf
            <input type="hidden" name="id_tahunakademik" value="{{ $tahunAkademik->id_tahunakademik }}">

            <div class="space-y-8">
                @foreach($prodis as $prodi)
                <div class="border rounded-xl p-6 bg-gray-50/50">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-teal-700">{{ $prodi->nama_prodi }}</h3>
                        <button type="button" @click="addRow({{ $prodi->id_prodi }})" class="px-4 py-2 bg-teal-600 text-white text-sm rounded-lg hover:bg-teal-700 transition">
                            + Tambah Mata Kuliah
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-200 rounded-t-lg">
                                <tr>
                                    <th class="px-4 py-3 rounded-tl-lg">Mata Kuliah</th>
                                    <th class="px-4 py-3">Dosen Pengampu</th>
                                    <th class="px-4 py-3 w-32">Jumlah Kelas</th>
                                    <th class="px-4 py-3 rounded-tr-lg w-16 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, index) in rows[{{ $prodi->id_prodi }}]" :key="row.id">
                                    <tr :class="row.is_active ? 'bg-white hover:bg-gray-50' : 'bg-gray-100 opacity-60 grayscale'" class="border-b transition-all duration-300">
                                        <td class="px-4 py-2">
                                            <input type="hidden" :name="`kurikulum_data[${row.globalIndex}][id_prodi]`" value="{{ $prodi->id_prodi }}" :disabled="!row.is_active">
                                            <select :name="`kurikulum_data[${row.globalIndex}][kode_matkul]`" x-model="row.kode_matkul" @change="inputChanged()" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500 text-sm disabled:bg-gray-200" :disabled="!row.is_active" required>
                                                <option value="">Pilih Mata Kuliah</option>
                                                @foreach($mataKuliahs as $mk)
                                                    <option value="{{ $mk->kode_matkul }}">{{ $mk->nama_matkul }} ({{ $mk->sks }} SKS)</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-4 py-2">
                                            <select :name="`kurikulum_data[${row.globalIndex}][id_dosen]`" x-model="row.id_dosen" @change="inputChanged()" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500 text-sm disabled:bg-gray-200" :disabled="!row.is_active" required>
                                                <option value="">Pilih Dosen</option>
                                                @foreach($dosens as $dosen)
                                                    <option value="{{ $dosen->id_dosen }}">{{ $dosen->nama_dosen }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="number" min="1" :name="`kurikulum_data[${row.globalIndex}][jumlah_kelas]`" x-model="row.jumlah_kelas" @change="inputChanged()" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500 text-sm disabled:bg-gray-200" :disabled="!row.is_active" required>
                                        </td>
                                        <td class="px-4 py-3 text-center flex items-center justify-center gap-3">
                                            <button type="button" @click="row.is_active = !row.is_active; inputChanged()" 
                                                    :class="row.is_active ? 'bg-teal-500 hover:bg-teal-600' : 'bg-gray-400 hover:bg-gray-500'"
                                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2" 
                                                    role="switch" 
                                                    :title="row.is_active ? 'Nonaktifkan Baris' : 'Aktifkan Baris'">
                                                <span aria-hidden="true" 
                                                      :class="row.is_active ? 'translate-x-5' : 'translate-x-0'"
                                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                                            </button>

                                            <button type="button" @click="removeRow({{ $prodi->id_prodi }}, index)" class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Hapus Baris">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <template x-if="rows[{{ $prodi->id_prodi }}].length === 0">
                            <div class="text-center py-4 text-gray-500 text-sm">
                                Belum ada mata kuliah yang ditambahkan untuk prodi ini.
                            </div>
                        </template>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-8 flex justify-end gap-4 border-t pt-6">
                <a href="{{ route('kurikulum.index') }}" class="px-6 py-2.5 border border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-yellow-500 text-white font-semibold rounded-xl hover:bg-yellow-600 transition shadow-md">
                    Simpan & Generate Kelas
                </button>
            </div>
        </form>

    </div>
</main>

<script>
    function kurikulumSetup() {
        return {
            sidebarOpen: true,
            globalCounter: 0,
            rows: {
                @foreach($prodis as $prodi)
                {{ $prodi->id_prodi }}: [],
                @endforeach
            },
            history: [],
            historyIndex: -1,
            isRestoring: false,

            saveHistory() {
                if (this.isRestoring) return;
                // Truncate future history if we are not at the end
                if (this.historyIndex < this.history.length - 1) {
                    this.history = this.history.slice(0, this.historyIndex + 1);
                }
                this.history.push(JSON.parse(JSON.stringify(this.rows)));
                this.historyIndex++;
            },

            undo() {
                if (this.historyIndex > 0) {
                    this.isRestoring = true;
                    this.historyIndex--;
                    this.rows = JSON.parse(JSON.stringify(this.history[this.historyIndex]));
                    this.isRestoring = false;
                }
            },

            redo() {
                if (this.historyIndex < this.history.length - 1) {
                    this.isRestoring = true;
                    this.historyIndex++;
                    this.rows = JSON.parse(JSON.stringify(this.history[this.historyIndex]));
                    this.isRestoring = false;
                }
            },

            addRow(prodiId) {
                this.rows[prodiId].push({
                    id: Date.now() + Math.random(),
                    globalIndex: this.globalCounter++,
                    kode_matkul: '',
                    id_dosen: '',
                    jumlah_kelas: 1,
                    is_active: true
                });
                this.saveHistory();
            },
            removeRow(prodiId, index) {
                this.rows[prodiId].splice(index, 1);
                this.saveHistory();
            },
            inputChanged() {
                this.saveHistory();
            },
            init() {
                // Add 1 default row to each prodi
                @foreach($prodis as $prodi)
                this.rows[{{ $prodi->id_prodi }}].push({
                    id: Date.now() + Math.random(),
                    globalIndex: this.globalCounter++,
                    kode_matkul: '',
                    id_dosen: '',
                    jumlah_kelas: 1,
                    is_active: true
                });
                @endforeach
                this.saveHistory();
            }
        }
    }
</script>

</body>
</html>
