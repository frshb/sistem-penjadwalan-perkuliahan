<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Prodi | {{ $prodi->nama_prodi }}</title>
    <link rel="icon" href="{{ asset('tsuwhite.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300 font-sans text-gray-900">

    @include('components.sidebar')

    <main id="main-content" :class="sidebarOpen ? 'lg:ml-64' : ''" x-data="{ sidebarOpen: true }" class="flex-1 p-6 sm:p-10 transition-all duration-300 ease-in-out bg-gray-50 min-h-screen lg:ml-64">
        

        <!-- Header Top Bar -->
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('prodi.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-teal-700 transition-colors bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-100">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>
            @include('components.header-profile')
        </div>

        <!-- Hero/Banner Profile Prodi -->
        <div class="relative overflow-hidden bg-gradient-to-br from-teal-800 via-teal-700 to-teal-900 rounded-2xl shadow-xl border border-teal-600/50 mb-8 p-8 sm:p-10 text-white">
            <!-- Decorative background elements -->
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-40 h-40 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-48 h-48 bg-teal-400 opacity-20 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 right-1/4 w-24 h-24 bg-indigo-500 opacity-10 rounded-full blur-2xl transform -translate-y-1/2"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start gap-6 sm:gap-8">
                <!-- Icon/Avatar Placeholder for Prodi -->
                <div class="flex-shrink-0 w-24 h-24 sm:w-28 sm:h-28 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 flex items-center justify-center shadow-[0_0_15px_rgba(255,255,255,0.1)] transition-transform hover:scale-105 duration-300">
                    <svg class="w-12 h-12 sm:w-14 sm:h-14 text-teal-50 drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                
                <div class="flex-1 text-center md:text-left flex flex-col justify-center">
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 border border-white/20 text-xs font-medium text-teal-50 mb-3 backdrop-blur-sm shadow-sm w-fit mx-auto md:mx-0">
                        <span class="w-2 h-2 rounded-full bg-teal-300 mr-2 animate-pulse shadow-[0_0_5px_#5eead4]"></span>
                        Detail Program Studi
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white mb-3 drop-shadow-lg">
                        {{ $prodi->nama_prodi }}
                    </h1>
                    <p class="text-teal-100/90 text-sm sm:text-base max-w-2xl font-light leading-relaxed">
                        Informasi komprehensif mengenai data akademis, akreditasi, biaya, serta kurikulum yang diselenggarakan pada program studi ini.
                    </p>
                </div>
            </div>
        </div>


        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-y-8 gap-x-12">
                

                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Kode</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">{{ $info['kode'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Akreditasi</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">{{ $info['akreditasi'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Rasio Dosen : Mahasiswa</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">{{ $info['rasio_dosen_mhs'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">SK Selenggara</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">{{ $info['sk_selenggara'] }}</dd>
                </div>


                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Biaya Kuliah</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">{{ $info['biaya_kuliah'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Akreditasi Internasional</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">{{ $info['akreditasi_int'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Rasio Diterima : Pendaftar</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">{{ $info['rasio_terima_daftar'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal SK Selenggara</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">{{ $info['tgl_sk'] }}</dd>
                </div>


                  <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal Berdiri</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">{{ $info['tgl_berdiri'] }}</dd>
                </div>
                 <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Telepon</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        {{ $info['telp'] }}
                    </dd>
                </div>
                 <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Fax</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                         {{ $info['telp'] }}
                    </dd>
                </div>

            </div>
        </div>

        <div class="mt-8 flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">Kurikulum Program Studi</h2>
            <button onclick="document.getElementById('addKurikulumModal').classList.remove('hidden')" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg shadow-sm flex items-center transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Kurikulum
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">Nama Kurikulum</th>
                            <th class="px-6 py-4">Kelas</th>
                            <th class="px-6 py-4">Mata Kuliah</th>
                            <th class="px-6 py-4">Dosen Pengampu</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($prodi->kurikulums as $index => $kurikulum)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $kurikulum->nama_kurikulum }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $kurikulum->kelas ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ is_array($kurikulum->matkul) ? implode(', ', $kurikulum->matkul) : ($kurikulum->matkul ?? '-') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ is_array($kurikulum->dosen_pengampu) ? implode(', ', $kurikulum->dosen_pengampu) : ($kurikulum->dosen_pengampu ?? '-') }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $kurikulum->status === 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $kurikulum->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <button onclick="openEditModal('{{ $kurikulum->id_kurikulum }}', '{{ $kurikulum->nama_kurikulum }}', '{{ $kurikulum->status }}', '{{ $kurikulum->kelas }}', {{ json_encode($kurikulum->matkul) }}, {{ json_encode($kurikulum->dosen_pengampu) }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <form action="{{ route('prodi.kurikulum.destroy', [$prodi->id_prodi, $kurikulum->id_kurikulum]) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus kurikulum ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada kurikulum untuk program studi ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Modal -->
        <div id="addKurikulumModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Tambah Kurikulum</h3>
                    <form action="{{ route('prodi.kurikulum.store', $prodi->id_prodi) }}" method="POST">
                        @csrf
                        <div class="mb-4 text-left">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="nama_kurikulum">Nama Kurikulum</label>
                            <input type="text" name="nama_kurikulum" id="nama_kurikulum" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                        </div>
                        <div class="mb-4 text-left">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="status">Status</label>
                            <select name="status" id="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="mb-4 text-left">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="kelas">Kelas</label>
                            <input type="text" name="kelas" id="kelas" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Contoh: A, B, C">
                        </div>
                        <div class="mb-4 text-left">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="matkul">Mata Kuliah (Bisa pilih lebih dari 1)</label>
                            <select name="matkul[]" id="matkul" multiple size="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500">
                                @foreach($matkuls as $mk)
                                    <option value="{{ $mk->nama_matkul }}">{{ $mk->nama_matkul }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4 text-left">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="dosen_pengampu">Dosen Pengampu (Bisa pilih lebih dari 1)</label>
                            <select name="dosen_pengampu[]" id="dosen_pengampu" multiple size="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500">
                                @foreach($dosens as $dsn)
                                    <option value="{{ $dsn->nama_dosen }}">{{ $dsn->nama_dosen }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <button type="button" onclick="document.getElementById('addKurikulumModal').classList.add('hidden')" class="mr-2 px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 focus:outline-none">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-md hover:bg-teal-700 focus:outline-none">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div id="editKurikulumModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Edit Kurikulum</h3>
                    <form id="editKurikulumForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-4 text-left">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_nama_kurikulum">Nama Kurikulum</label>
                            <input type="text" name="nama_kurikulum" id="edit_nama_kurikulum" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                        </div>
                        <div class="mb-4 text-left">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_status">Status</label>
                            <select name="status" id="edit_status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="mb-4 text-left">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_kelas">Kelas</label>
                            <input type="text" name="kelas" id="edit_kelas" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div class="mb-4 text-left">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_matkul">Mata Kuliah (Bisa pilih lebih dari 1)</label>
                            <select name="matkul[]" id="edit_matkul" multiple size="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500">
                                @foreach($matkuls as $mk)
                                    <option value="{{ $mk->nama_matkul }}">{{ $mk->nama_matkul }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4 text-left">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_dosen_pengampu">Dosen Pengampu (Bisa pilih lebih dari 1)</label>
                            <select name="dosen_pengampu[]" id="edit_dosen_pengampu" multiple size="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500">
                                @foreach($dosens as $dsn)
                                    <option value="{{ $dsn->nama_dosen }}">{{ $dsn->nama_dosen }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <button type="button" onclick="document.getElementById('editKurikulumModal').classList.add('hidden')" class="mr-2 px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 focus:outline-none">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-md hover:bg-teal-700 focus:outline-none">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function openEditModal(id, nama, status, kelas, matkulArray, dosenArray) {
                document.getElementById('edit_nama_kurikulum').value = nama;
                document.getElementById('edit_status').value = status;
                document.getElementById('edit_kelas').value = kelas || '';

                let matkulSelect = document.getElementById('edit_matkul');
                for (let option of matkulSelect.options) {
                    option.selected = matkulArray ? matkulArray.includes(option.value) : false;
                }

                let dosenSelect = document.getElementById('edit_dosen_pengampu');
                for (let option of dosenSelect.options) {
                    option.selected = dosenArray ? dosenArray.includes(option.value) : false;
                }
                
                // Set the form action URL dynamically
                let baseUrl = "{{ url('/management/prodi/' . $prodi->id_prodi . '/kurikulum') }}";
                document.getElementById('editKurikulumForm').action = baseUrl + '/' + id;
                
                document.getElementById('editKurikulumModal').classList.remove('hidden');
            }
        </script>

    </main>
</body>
</html>
