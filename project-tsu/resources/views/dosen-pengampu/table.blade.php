{{-- ================================================ --}}
{{-- SUMMARY CARDS --}}
{{-- ================================================ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $totalKelas = $kelasList->count();
        $sudahTerplot = 0;
        $belumTerplot = 0;
        $totalSksTerplot = 0;
        foreach($kelasList as $kelas) {
            $adaPengampu = $pengampus->where('id_kelas', $kelas->id_kelas)->count() > 0;
            if($adaPengampu) {
                $sudahTerplot++;
                $mk = $kelas->matakuliah;
                if($mk) $totalSksTerplot += $mk->sks;
            } else {
                $belumTerplot++;
            }
        }
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Total Kelas</p>
            <p class="text-xl font-bold text-gray-800">{{ $totalKelas }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Sudah Terplot</p>
            <p class="text-xl font-bold text-gray-800">{{ $sudahTerplot }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Belum Terplot</p>
            <p class="text-xl font-bold text-gray-800">{{ $belumTerplot }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Total SKS Terplot</p>
            <p class="text-xl font-bold text-gray-800">{{ $totalSksTerplot }}</p>
        </div>
    </div>
</div>
{{-- ================================================ --}}
{{-- SEARCH BAR --}}
{{-- ================================================ --}}
<div class="mb-6">
    <div class="relative w-full">
        <input
            type="text"
            id="table-search"
            placeholder="Cari kelas, mata kuliah, atau nama dosen pengampu..."
            class="w-full pl-5 pr-12 py-3.5 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-sm font-medium transition-all"
        />
        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
    </div>
</div>

{{-- ================================================ --}}
{{-- UNIFIED TOOLBAR FILTERS --}}
{{-- ================================================ --}}
<div class="bg-gray-50/75 p-5 rounded-2xl border border-gray-200 mb-6 flex flex-row items-center gap-3 w-full">
    <!-- Dropdown Filter Semester -->
    <div class="relative flex-1 min-w-[140px]">
        <select id="table-filter-semester" class="w-full pl-3.5 pr-8 py-2.5 bg-white border border-gray-300 rounded-xl shadow-sm text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 appearance-none cursor-pointer">
            <option value="">Semua Semester</option>
            @php
                $keterangan = strtolower($tahunAkademik->keterangan ?? '');
                $semesters = str_contains($keterangan, 'genap') ? [2, 4, 6, 8] : [1, 3, 5, 7];
            @endphp
            @foreach($semesters as $sem)
                <option value="{{ $sem }}">Semester {{ $sem }}</option>
            @endforeach
        </select>
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>
    </div>

    <!-- Dropdown Filter Prodi -->
    <div class="relative flex-[2] min-w-[160px]">
        <select id="table-filter-prodi" class="w-full pl-3.5 pr-8 py-2.5 bg-white border border-gray-300 rounded-xl shadow-sm text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 appearance-none cursor-pointer">
            <option value="">Semua Prodi</option>
            @foreach($prodis as $prodi)
                @if(strtolower($prodi->nama_prodi) !== 'dosen eksternal fakultas')
                    <option value="{{ $prodi->id_prodi }}">{{ $prodi->nama_prodi }}</option>
                @endif
            @endforeach
        </select>
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>
    </div>

    <!-- Dropdown Filter Nama Kelas -->
    <div class="relative flex-1 min-w-[180px]">
        <select id="table-filter-namakelas" class="w-full pl-3.5 pr-8 py-2.5 bg-white border border-gray-300 rounded-xl shadow-sm text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 appearance-none cursor-pointer">
            <option value="">Semua Nama Kelas</option>
            @php
                $uniqueNamaKelas = $kelasList->pluck('nama_kelas')->unique()->sort();
            @endphp
            @foreach($uniqueNamaKelas as $nk)
                <option value="{{ strtolower($nk) }}">{{ $nk }}</option>
            @endforeach
        </select>
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>
    </div>

</div>

<div>
    <div class="overflow-hidden rounded-lg border border-[#DBDBDB] mb-6">
        <div class="overflow-x-auto w-full pb-4">
            <table class="min-w-full bg-white text-sm">
                <thead class="bg-teal-800 text-white">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold uppercase text-xs tracking-wider">No</th>
                        <th class="px-4 py-2 text-left font-semibold uppercase text-xs tracking-wider cursor-pointer hover:bg-teal-700 select-none group" onclick="sortDosenTable(this, 1, 'string')">
                            <div class="flex items-center justify-between">
                                <span>Nama Kelas</span>
                                <span class="sort-icon text-teal-300 group-hover:text-white transition-colors ml-2">⇅</span>
                            </div>
                        </th>
                        <th class="px-4 py-2 text-center font-semibold uppercase text-xs tracking-wider cursor-pointer hover:bg-teal-700 select-none group" onclick="sortDosenTable(this, 2, 'number')">
                            <div class="flex items-center justify-center">
                                <span>Semester</span>
                                <span class="sort-icon text-teal-300 group-hover:text-white transition-colors ml-2">⇅</span>
                            </div>
                        </th>
                        <th class="px-4 py-2 text-left font-semibold uppercase text-xs tracking-wider cursor-pointer hover:bg-teal-700 select-none group" onclick="sortDosenTable(this, 3, 'string')">
                            <div class="flex items-center justify-between">
                                <span>Kode MK</span>
                                <span class="sort-icon text-teal-300 group-hover:text-white transition-colors ml-2">⇅</span>
                            </div>
                        </th>
                        <th class="px-4 py-2 text-left font-semibold uppercase text-xs tracking-wider cursor-pointer hover:bg-teal-700 select-none group" onclick="sortDosenTable(this, 4, 'string')">
                            <div class="flex items-center justify-between">
                                <span>Mata Kuliah</span>
                                <span class="sort-icon text-teal-300 group-hover:text-white transition-colors ml-2">⇅</span>
                            </div>
                        </th>
                        <th class="px-4 py-2 text-left font-semibold uppercase text-xs tracking-wider cursor-pointer hover:bg-teal-700 select-none group" onclick="sortDosenTable(this, 5, 'string')">
                            <div class="flex items-center justify-between">
                                <span>Program Studi</span>
                                <span class="sort-icon text-teal-300 group-hover:text-white transition-colors ml-2">⇅</span>
                            </div>
                        </th>
                        <th class="px-4 py-2 text-center font-semibold uppercase text-xs tracking-wider cursor-pointer hover:bg-teal-700 select-none group" onclick="sortDosenTable(this, 6, 'number')">
                            <div class="flex items-center justify-center">
                                <span>SKS</span>
                                <span class="sort-icon text-teal-300 group-hover:text-white transition-colors ml-2">⇅</span>
                            </div>
                        </th>
                        <th class="px-4 py-2 text-left font-semibold uppercase text-xs tracking-wider cursor-pointer hover:bg-teal-700 select-none group" onclick="sortDosenTable(this, 7, 'string')">
                            <div class="flex items-center justify-between">
                                <span>Dosen Pengampu</span>
                                <span class="sort-icon text-teal-300 group-hover:text-white transition-colors ml-2">⇅</span>
                            </div>
                        </th>
                        <th class="px-4 py-2 text-center font-semibold uppercase text-xs tracking-wider cursor-pointer hover:bg-teal-700 select-none group" onclick="sortDosenTable(this, 8, 'number')">
                            <div class="flex items-center justify-center">
                                <span>Total SKS</span>
                                <span class="sort-icon text-teal-300 group-hover:text-white transition-colors ml-2">⇅</span>
                            </div>
                        </th>
                        @if(Auth::user()->hasPermissionAccess('management_data', 'Dosen Pengampu', 'edit'))
                        <th class="px-4 py-2 text-center font-semibold uppercase text-xs tracking-wider">Aksi</th>
                        @endif
                    </tr>
                </thead>
            <tbody id="table-body">
                @php $no = 1; @endphp
                @foreach($kelasList as $kelas)
                    @php
                        $pengampu = $pengampus->where('id_kelas', $kelas->id_kelas)->first();
                        $dosen = $pengampu ? $pengampu->dosen : null;
                        $sksDosen = 0;
                        if($dosen) {
                            foreach($dosen->pengampus as $p) {
                                $mk = $p->kelas?->matakuliah;
                                if($mk) $sksDosen += $mk->sks;
                            }
                        }
                        $status = $dosen ? 'sudah' : 'belum';
                    @endphp
                    <tr class="border-b border-slate-100 hover:bg-slate-50/50" 
                        data-kelas-id="{{ $kelas->id_kelas }}" 
                        data-status="{{ $status }}"
                        data-nama-kelas="{{ $kelas->nama_kelas }}"
                        data-nama-matkul="{{ $kelas->matakuliah->nama_matkul ?? '' }}"
                        data-prodi="{{ $kelas->id_prodi }}"
                        data-prodi-nama="{{ $kelas->prodi->nama_prodi ?? '-' }}"
                        data-semester="{{ $kelas->semester }}"
                        data-matkul="{{ $kelas->matakuliah->kode_matkul ?? '' }}"
                        data-sks="{{ $kelas->matakuliah->sks ?? 0 }}"
                        data-jml-mhs="{{ $kelas->jumlah_mahasiswa ?? 0 }}"
                        data-nama-dosen="{{ $dosen->nama_dosen ?? '' }}">
                        <td class="px-4 py-3 text-slate-500">{{ $no }}</td>
                        <td class="px-4 py-3"><span class="font-semibold text-slate-800">{{ $kelas->nama_kelas }}</span></td>
                        <td class="px-4 py-3 text-slate-600">{{ $kelas->semester }}</td>
                        <td class="px-4 py-3"><span class="font-mono text-xs bg-slate-100 px-2 py-1 rounded text-slate-600">{{ $kelas->matakuliah->kode_matkul ?? '-' }}</span></td>
                        <td class="px-4 py-3 text-slate-600">{{ $kelas->matakuliah->nama_matkul ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $kelas->prodi->nama_prodi ?? '-' }}</td>
                        <td class="px-4 py-3 text-center text-slate-700 font-medium">{{ $kelas->matakuliah->sks ?? 0 }}</td>
                        <td class="px-4 py-3">
                            @if($dosen)
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-medium text-slate-800">{{ $dosen->nama_dosen }}</span>
                                </div>
                            @else
                                <span class="text-slate-400 font-medium">-</span>
                            @endif
                            <input type="hidden" id="mk-{{ $kelas->id_kelas }}" value="{{ $kelas->matakuliah->kode_matkul ?? '-' }}" />
                            <input type="hidden" id="prodi-{{ $kelas->id_kelas }}" value="{{ $kelas->id_prodi }}" />
                            @if($pengampu)
                                <input type="hidden" id="pengampu-{{ $kelas->id_kelas }}" value="{{ $pengampu->id }}" />
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($dosen)
                                <span class="font-bold {{ $sksDosen > 12 ? 'text-red-600' : 'text-slate-800' }}">{{ $sksDosen }}</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        @if(Auth::user()->hasPermissionAccess('management_data', 'Dosen Pengampu', 'edit'))
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="openDosenModal({{ $kelas->id_kelas }})" class="flex items-center justify-center bg-yellow-400 text-gray-900 px-3 py-1 rounded-md hover:bg-yellow-500 text-xs font-medium transition-colors" title="Edit Pengampu">
                                    Edit
                                </button>
                                @if($pengampu)
                                <button onclick="removeDosen({{ $kelas->id_kelas }}, {{ $pengampu->id }}, event)" class="flex items-center justify-center bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-700 text-xs font-medium transition-colors" title="Reset/Lepas Dosen">
                                    Reset
                                </button>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                    @php $no++; @endphp
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Pilih Dosen (UX for Older Users) -->
<div id="dosen-search-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm transition-opacity" style="opacity: 0; transition: opacity 0.3s ease-in-out;">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[85vh] flex flex-col mx-4 overflow-hidden transform scale-95 transition-all duration-300" id="dosen-search-modal-content">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-white relative z-10">
            <h3 class="text-xl font-bold text-gray-800">Edit Data Dosen Pengampu</h3>
            <button onclick="closeDosenModal()" class="text-gray-400 hover:text-red-600 hover:bg-red-50 p-2 rounded-xl transition duration-200 focus:outline-none focus:ring-4 focus:ring-red-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <!-- Data Kelas Detail -->
        <div class="px-6 py-5 bg-gradient-to-br from-slate-50 to-slate-100 border-b border-gray-100 flex gap-6 items-center">
            <div class="min-w-[5rem] min-h-[5rem] px-4 py-2 rounded-2xl bg-white shadow-sm border border-gray-200 flex flex-col items-center justify-center text-teal-700 shrink-0">
                <span class="text-[10px] font-bold text-gray-400 tracking-widest mb-1">KELAS</span>
                <span class="text-xl md:text-2xl font-black leading-none whitespace-nowrap text-center" id="modal-detail-kelas">-</span>
            </div>
            <div class="flex-1 grid grid-cols-2 gap-x-4 gap-y-3">
                <div>
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Mata Kuliah</p>
                    <p class="text-sm font-bold text-gray-800 truncate" id="modal-detail-matkul">-</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Program Studi</p>
                    <p class="text-sm font-bold text-gray-800 truncate" id="modal-detail-prodi">-</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Kode MK & SKS</p>
                    <p class="text-sm font-bold text-gray-800"><span id="modal-detail-kode">-</span> <span class="text-gray-400 mx-1">•</span> <span id="modal-detail-sks">-</span> SKS</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Semester / Jml Mhs</p>
                    <p class="text-sm font-bold text-gray-800">Sem <span id="modal-detail-semester">-</span> <span class="text-gray-400 mx-1">•</span> <span id="modal-detail-jml">-</span> Mhs</p>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="p-6 border-b border-gray-100 bg-gray-50/80">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" id="modal-search-dosen" oninput="filterModalDosen()" placeholder="Ketik nama dosen untuk mencari..." class="w-full pl-16 pr-6 py-5 text-xl border-2 border-gray-200 rounded-2xl focus:ring-4 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all shadow-sm placeholder-gray-400 font-medium bg-white" />
            </div>
        </div>

        <!-- List Dosen -->
        <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50/50" id="modal-dosen-list">
            @foreach($dosens as $dosen)
                @php
                    $sksDosen = 0;
                    foreach($dosen->pengampus as $p) {
                        $mk = $p->kelas?->matakuliah;
                        if($mk) $sksDosen += $mk->sks;
                    }
                @endphp
                <button onclick="selectDosen({{ $dosen->id_dosen }})" class="modal-dosen-item w-full text-left p-6 bg-white border-2 border-gray-100 rounded-2xl hover:border-teal-500 hover:shadow-lg hover:bg-teal-50/40 focus:ring-4 focus:ring-teal-100 transition-all duration-200 flex items-center justify-between group" data-nama="{{ strtolower($dosen->nama_dosen) }}">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-teal-50 to-emerald-100 text-teal-700 flex items-center justify-center font-bold text-2xl uppercase shadow-sm border border-teal-100 group-hover:scale-105 transition-transform">
                            {{ substr($dosen->nama_dosen, 0, 2) }}
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-gray-800 group-hover:text-teal-800 transition">{{ $dosen->nama_dosen }}</h4>
                            <div class="flex items-center gap-3 mt-2">
                                <span class="text-sm font-semibold text-gray-600 bg-gray-100 px-3 py-1 rounded-xl">{{ $dosen->prodi->nama_prodi ?? 'Umum' }}</span>
                                <span class="text-sm font-medium text-gray-400">NIDN: {{ $dosen->nidn ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 mb-1.5 font-bold uppercase tracking-widest">Beban Saat Ini</p>
                        <span class="inline-block px-4 py-2 rounded-xl text-lg font-bold shadow-sm {{ $sksDosen > 12 ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-teal-50 text-teal-700 border border-teal-200' }}">
                            {{ $sksDosen }} SKS
                        </span>
                    </div>
                </button>
            @endforeach
            
            <div id="modal-dosen-empty" class="hidden text-center py-20">
                <div class="w-28 h-28 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-14 h-14 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <h4 class="text-2xl font-bold text-gray-700 mb-2">Dosen Tidak Ditemukan</h4>
                <p class="text-lg text-gray-500">Coba gunakan kata kunci atau ejaan lain.</p>
            </div>
        </div>
    </div>
</div>

<script>
// Overriding open and close functions to include animation
window.openDosenModal = function(kelasId) {
    currentChangeKelasId = kelasId;
    
    const tr = document.querySelector('tr[data-kelas-id="'+kelasId+'"]');
    if (tr) {
        document.getElementById('modal-detail-kelas').textContent = tr.dataset.namaKelas || '-';
        document.getElementById('modal-detail-matkul').textContent = tr.dataset.namaMatkul || '-';
        document.getElementById('modal-detail-prodi').textContent = tr.dataset.prodiNama || '-';
        document.getElementById('modal-detail-kode').textContent = tr.dataset.matkul || '-';
        document.getElementById('modal-detail-sks').textContent = tr.dataset.sks || '0';
        document.getElementById('modal-detail-semester').textContent = tr.dataset.semester || '-';
        document.getElementById('modal-detail-jml').textContent = tr.dataset.jmlMhs || '0';
    }

    const modal = document.getElementById('dosen-search-modal');
    const content = document.getElementById('dosen-search-modal-content');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.style.opacity = '1';
            if(content) content.classList.replace('scale-95', 'scale-100');
        }, 10);
        const inp = modal.querySelector('input[type="text"]');
        if (inp) { inp.value = ''; filterModalDosen(); inp.focus(); }
    }
};

window.closeDosenModal = function() {
    const modal = document.getElementById('dosen-search-modal');
    const content = document.getElementById('dosen-search-modal-content');
    if (modal) {
        modal.style.opacity = '0';
        if(content) content.classList.replace('scale-100', 'scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
};

function filterModalDosen() {
    var search = document.getElementById('modal-search-dosen').value.toLowerCase();
    var items = document.querySelectorAll('.modal-dosen-item');
    var visible = 0;
    items.forEach(function(item) {
        if(item.dataset.nama.includes(search)) {
            item.style.display = 'flex';
            visible++;
        } else {
            item.style.display = 'none';
        }
    });
    document.getElementById('modal-dosen-empty').style.display = visible === 0 ? 'block' : 'none';
}

// ── Table Filtering Logic ──
(function() {
    const searchInput = document.getElementById('table-search');
    const filterProdi = document.getElementById('table-filter-prodi');
    const filterSemester = document.getElementById('table-filter-semester');
    const filterNamaKelas = document.getElementById('table-filter-namakelas');
    
    function applyFilters(saveState = true) {
        const search = (searchInput?.value || '').toLowerCase();
        const prodi = filterProdi?.value || '';
        const semester = filterSemester?.value || '';
        const namaKelasFilter = filterNamaKelas?.value || '';
        
        if (saveState) {
            sessionStorage.setItem('dosenTableSearch', searchInput?.value || '');
            sessionStorage.setItem('dosenTableProdi', prodi);
            sessionStorage.setItem('dosenTableSemester', semester);
            sessionStorage.setItem('dosenTableNamaKelas', namaKelasFilter);
        }

        let no = 1;
        document.querySelectorAll('#table-body tr').forEach(function(row) {
            const namaKelas = (row.dataset.namaKelas || '').toLowerCase();
            const namaMatkul = (row.dataset.namaMatkul || '').toLowerCase();
            const namaDosen = (row.dataset.namaDosen || '').toLowerCase();
            const rowProdi = row.dataset.prodi || '';
            const rowSemester = parseInt(row.dataset.semester) || 0;
            const rowMatkul = row.dataset.matkul || '';
            const rowStatus = row.dataset.status || '';

            let show = true;
            
            // Search filter
            if (search && !namaKelas.includes(search) && !namaMatkul.includes(search) && !namaDosen.includes(search)) show = false;
            
            // Dropdowns filter
            if (prodi && rowProdi !== prodi) show = false;
            if (semester && rowSemester !== parseInt(semester)) show = false;
            if (namaKelasFilter && namaKelas !== namaKelasFilter) show = false;

            row.style.display = show ? '' : 'none';
            
            if (show) {
                const noCell = row.querySelector('td:first-child');
                if (noCell) noCell.textContent = no++;
            }
        });
    }
    
    // Add event listeners
    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (filterProdi) filterProdi.addEventListener('change', applyFilters);
    if (filterSemester) filterSemester.addEventListener('change', applyFilters);
    if (filterNamaKelas) filterNamaKelas.addEventListener('change', applyFilters);

    // Restore state from sessionStorage if available
    if (sessionStorage.getItem('dosenTableSearch') !== null && searchInput) {
        searchInput.value = sessionStorage.getItem('dosenTableSearch');
    }
    if (sessionStorage.getItem('dosenTableProdi') !== null && filterProdi) {
        filterProdi.value = sessionStorage.getItem('dosenTableProdi');
    }
    if (sessionStorage.getItem('dosenTableSemester') !== null && filterSemester) {
        filterSemester.value = sessionStorage.getItem('dosenTableSemester');
    }
    if (sessionStorage.getItem('dosenTableNamaKelas') !== null && filterNamaKelas) {
        filterNamaKelas.value = sessionStorage.getItem('dosenTableNamaKelas');
    }

    // Initial Filter Apply (don't overwrite saved state with empty on first load)
    applyFilters(false);

    // Sorting Logic
    let currentSortColumn = -1;
    let isAscending = true;
    const tbody = document.getElementById('table-body');
    const originalRows = Array.from(tbody.querySelectorAll('tr'));
    const headers = document.querySelectorAll('thead th');

    function resetTableSort() {
        currentSortColumn = -1;
        isAscending = true;
        
        headers.forEach((h, index) => {
            if (index === 0 || index === headers.length - 1) return;
            const icon = h.querySelector('.sort-icon');
            if (icon) {
                icon.innerHTML = '⇅';
                icon.classList.remove('text-amber-400');
                icon.classList.add('text-teal-300');
            }
        });

        tbody.innerHTML = '';
        originalRows.forEach(row => tbody.appendChild(row));
        applyFilters();
    }

    window.sortDosenTable = function(headerElement, columnIndex, type = 'string') {
        if (currentSortColumn === columnIndex) {
            if (isAscending) {
                isAscending = false;
            } else {
                resetTableSort();
                return;
            }
        } else {
            currentSortColumn = columnIndex;
            isAscending = true;
        }

        headers.forEach((h, index) => {
            if (index === 0 || index === headers.length - 1) return;
            if (index === columnIndex) {
                const icon = h.querySelector('.sort-icon');
                if (icon) {
                    icon.innerHTML = isAscending ? '▲' : '▼';
                    icon.classList.remove('text-teal-300');
                    icon.classList.add('text-amber-400');
                }
            } else {
                const icon = h.querySelector('.sort-icon');
                if (icon) {
                    icon.innerHTML = '⇅';
                    icon.classList.remove('text-amber-400');
                    icon.classList.add('text-teal-300');
                }
            }
        });

        const rows = Array.from(tbody.querySelectorAll('tr'));
        rows.sort((a, b) => {
            let valA = a.cells[columnIndex].textContent.trim();
            let valB = b.cells[columnIndex].textContent.trim();

            if (type === 'number') {
                valA = parseFloat(valA) || 0;
                valB = parseFloat(valB) || 0;
                return isAscending ? valA - valB : valB - valA;
            }

            return isAscending 
                ? valA.localeCompare(valB, 'id', { sensitivity: 'base' })
                : valB.localeCompare(valA, 'id', { sensitivity: 'base' });
        });

        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));
        applyFilters();
    };

})();
</script>
