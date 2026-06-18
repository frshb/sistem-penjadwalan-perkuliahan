<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="tahun-akademik-id" content="{{ $tahunAkademik->id_tahunakademik }}">
    <title>Portal Dosen Pengampu</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .dosen-dropzone {
            transition: all 0.2s ease;
        }
        .dosen-dropzone.drag-over {
            background-color: #f0fdf4;
            border-color: #22c55e;
        }
        .matkul-item {
            cursor: grab;
        }
        .matkul-item:active {
            cursor: grabbing;
        }
        .matkul-item.dragging {
            opacity: 0.5;
        }
    </style>
</head>

<body
    x-data="{
        sidebarOpen: true,
        matkulSidebarOpen: true,
        showFilter: false,
        activeTab: 'belum',
        focusMode: false
    }"
    class="bg-gray-100/50 min-h-screen overflow-x-hidden"
>

@include('components.sidebar')

<main :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'" class="transition-all duration-300 p-6 sm:p-8">

    {{-- HEADER --}}
    <div class="flex items-center justify-between" x-show="!focusMode">
        <div>
            <div class="flex items-center gap-3">
                <div class="flex flex-col">
                    <div class="w-2 h-5 bg-teal-600 rounded-tl-md"></div>
                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Portal Dosen Pengampu</h1>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mt-2">
                <a href="{{ route('dosen-pengampu.pilih-tahun') }}" class="hover:text-teal-600 transition font-medium">
                    Portal Dosen Pengampu
                </a>
                <span>/</span>
                <span class="text-teal-600 font-semibold">{{ $tahunAkademik->nama_tahunakademik }}</span>
            </div>
        </div>
        @include('components.header-profile')
    </div>

    {{-- ACTION BAR --}}
    <div class="mt-8 flex items-center justify-between" :class="focusMode ? 'mt-0 bg-white p-3 rounded-2xl border border-gray-150 shadow-sm' : ''">
        <div class="flex items-center gap-3">
            <div x-show="focusMode" class="text-sm font-bold text-teal-800 bg-teal-50 px-3.5 py-2 rounded-xl border border-teal-200 flex items-center gap-2 mr-2">
                <span>Periode: {{ $tahunAkademik->nama_tahunakademik }}</span>
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <!-- Toggle Sidebar Mata Kuliah -->
            <button
                @click="matkulSidebarOpen = !matkulSidebarOpen"
                :class="matkulSidebarOpen ? 'bg-teal-50 border-teal-200 text-teal-700 hover:bg-teal-100' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'"
                class="px-4 py-3 border rounded-xl font-semibold flex items-center gap-2 transition text-sm shadow-sm"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                </svg>
                <span x-text="matkulSidebarOpen ? 'Sembunyikan Mata Kuliah' : 'Tampilkan Mata Kuliah'"></span>
            </button>

            <!-- Mode Fokus -->
            <button
                @click="focusMode = !focusMode; sidebarOpen = !focusMode; matkulSidebarOpen = !focusMode"
                :class="focusMode ? 'bg-gray-800 border-transparent text-white hover:bg-gray-900' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'"
                class="px-4 py-3 border rounded-xl font-semibold flex items-center gap-2 transition text-sm shadow-sm"
            >
                <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="focusMode" style="display: none;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!focusMode">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l-5-5m11 5v-4m0 4h-4m4 0l-5-5" />
                </svg>
                <span x-text="focusMode ? 'Normal View' : 'Workspace Fokus'"></span>
            </button>
        </div>
    </div>

    {{-- CONTENT --}}
    <div 
        :class="focusMode ? 'h-[calc(100vh-100px)] mt-4 gap-4' : 'h-[calc(100vh-170px)] mt-8 gap-6'"
        class="grid grid-cols-12 overflow-hidden transition-all duration-300"
    >

        {{-- SIDEBAR MATA KULIAH --}}
        <div 
            x-show="matkulSidebarOpen" 
            class="col-span-12 xl:col-span-3 min-h-0 flex"
        >
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 h-full flex flex-col w-full overflow-hidden">

                <div class="p-5 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-800">Daftar Mata Kuliah</h2>

                    <div class="mt-5 flex items-center gap-2">
                        <div class="relative flex-1">
                            <input
                                type="text"
                                id="search-matkul"
                                placeholder="Cari mata kuliah..."
                                class="w-full pl-4 pr-10 py-3 rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-teal-500 outline-none text-sm"
                                oninput="filterMatkul()"
                            >
                            <svg class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
                            </svg>
                        </div>
                    </div>

                    <div class="mt-5 border-b border-gray-100">
                        <div class="flex items-center gap-6 text-sm font-semibold">
                            <button @click="activeTab = 'belum'; filterMatkul()" :class="activeTab === 'belum' ? 'pb-3 border-b-2 border-teal-500 text-teal-600' : 'pb-3 text-gray-500 hover:text-teal-600'" class="flex items-center gap-2">
                                Belum Terisi
                                <span id="count-belum" class="px-2 py-0.5 rounded-full bg-teal-100 text-teal-700 text-xs">{{ $matkuls->filter(fn($m) => $m->pengampus->isEmpty())->count() }}</span>
                            </button>
                            <button @click="activeTab = 'sudah'; filterMatkul()" :class="activeTab === 'sudah' ? 'pb-3 border-b-2 border-green-500 text-green-600' : 'pb-3 text-gray-500 hover:text-green-600'" class="flex items-center gap-2">
                                Sudah Terisi
                                <span id="count-sudah" class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs">{{ $matkuls->filter(fn($m) => $m->pengampus->isNotEmpty())->count() }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto min-h-0 px-2 pb-2" id="matkul-list-container">
                    @forelse ($matkuls as $item)
                    <div
                        id="matkul-{{ $item->kode_matkul }}"
                        class="matkul-item bg-white border border-gray-200 rounded-2xl p-4 mx-3 my-3 hover:shadow-md hover:border-teal-300 transition-all duration-200"
                        draggable="true"
                        data-id="{{ $item->kode_matkul }}"
                        data-prodi="{{ $item->program_studi->nama_prodi ?? '-' }}"
                        data-dosen-id="{{ $item->pengampus->first()->id_dosen ?? '' }}"
                        data-status="{{ $item->pengampus->isEmpty() ? 'belum' : 'sudah' }}"
                        data-nama="{{ $item->nama_matkul }}"
                        data-kode-mk="{{ $item->kode_matkul }}"
                        data-sks="{{ $item->sks }}"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-bold text-gray-800">{{ $item->nama_matkul }}</h3>
                                <p class="text-sm text-gray-500 mt-1">{{ $item->program_studi->nama_prodi ?? '-' }}</p>
                                <div class="flex flex-wrap items-center gap-2 mt-3">
                                    <span class="text-xs px-2 py-1 bg-teal-50 text-teal-700 rounded-lg font-mono">{{ $item->kode_matkul }}</span>
                                    <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded-lg">{{ $item->sks }} SKS</span>
                                </div>
                            </div>
                            <span class="status-badge px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1 {{ $item->pengampus->isEmpty() ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}">
                                <span class="w-2 h-2 rounded-full {{ $item->pengampus->isEmpty() ? 'bg-red-500' : 'bg-green-500' }}"></span>
                                <span class="status-text">{{ $item->pengampus->isEmpty() ? 'Belum' : 'Sudah' }}</span>
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-gray-500">Data mata kuliah belum tersedia.</div>
                    @endforelse
                </div>

            </div>
        </div>

        {{-- WORKSPACE DOSEN --}}
        <div 
            :class="matkulSidebarOpen ? 'xl:col-span-9' : 'xl:col-span-12'"
            class="col-span-12 flex flex-col gap-4 min-h-0 transition-all duration-300"
        >
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 w-full flex flex-col overflow-hidden h-full">

                <div class="p-5 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-800">Workspace Penugasan Dosen</h2>
                        <div class="relative w-64">
                            <input type="text" id="search-dosen" oninput="filterDosen()" placeholder="Cari Dosen..." class="w-full pl-4 pr-10 py-2 rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-teal-500 outline-none text-sm">
                            <svg class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                        </div>
                    </div>
                </div>

                <div class="flex-1 overflow-auto p-5 bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" id="dosen-grid">
                        @foreach($dosens as $dosen)
                        <div class="dosen-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col h-[350px]"
                             data-dosen-id="{{ $dosen->id_dosen }}"
                             data-dosen-nama="{{ strtolower($dosen->nama_dosen) }}">
                            
                            <!-- Header Dosen -->
                            <div class="bg-teal-700 px-4 py-3 flex items-center justify-between">
                                <h3 class="font-bold text-white text-sm truncate" title="{{ $dosen->nama_dosen }}">{{ $dosen->nama_dosen }}</h3>
                                <span class="bg-white text-teal-800 text-xs font-bold px-2 py-1 rounded-md total-sks">0 SKS</span>
                            </div>

                            <!-- Dropzone Container -->
                            <div class="dosen-dropzone flex-1 overflow-y-auto p-3 bg-gray-50/50 space-y-3" data-dosen-id="{{ $dosen->id_dosen }}">
                                <!-- Kartu matkul yang dijatuhkan akan masuk ke sini -->
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const matkulItems = document.querySelectorAll('.matkul-item');
    const dropzones = document.querySelectorAll('.dosen-dropzone');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // 1. Inisialisasi Matkul yang sudah punya dosen ke dalam dropzone dosen masing-masing
    matkulItems.forEach(item => {
        const dosenId = item.getAttribute('data-dosen-id');
        if (dosenId) {
            const dropzone = document.querySelector(`.dosen-dropzone[data-dosen-id="${dosenId}"]`);
            if (dropzone) {
                const card = createCardForDropzone(item);
                dropzone.appendChild(card);
            }
        }
    });

    updateAllDosenSks();
    filterMatkul(); // initial filter

    // 2. Setup Drag and Drop
    let draggedItem = null;

    matkulItems.forEach(item => {
        item.addEventListener('dragstart', function(e) {
            draggedItem = this;
            setTimeout(() => this.classList.add('dragging'), 0);
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', this.getAttribute('data-id'));
        });

        item.addEventListener('dragend', function() {
            draggedItem = null;
            this.classList.remove('dragging');
        });
    });

    dropzones.forEach(zone => {
        zone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        zone.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });

        zone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            const matkulId = e.dataTransfer.getData('text/plain');
            const targetDosenId = this.getAttribute('data-dosen-id');
            const item = document.getElementById(`matkul-${matkulId}`);

            if (item && targetDosenId) {
                // Cek apakah matkul sudah di dosen ini
                if (item.getAttribute('data-dosen-id') === targetDosenId) return;

                simpanDosenPengampu(matkulId, targetDosenId, item, this);
            }
        });
    });

    function simpanDosenPengampu(matkulId, dosenId, itemElement, dropzoneElement) {
        fetch('{{ route("dosen-pengampu.simpan") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                kode_matkul: matkulId,
                id_dosen: dosenId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Hapus card lama di dropzone jika ada (karena 1 matkul = 1 dosen di UI ini)
                const oldCard = document.querySelector(`.assigned-card[data-matkul-id="${matkulId}"]`);
                if (oldCard) oldCard.remove();

                // Update data-attributes di elemen asli
                itemElement.setAttribute('data-dosen-id', dosenId);
                itemElement.setAttribute('data-status', 'sudah');
                
                // Update badge di sidebar
                const badge = itemElement.querySelector('.status-badge');
                badge.className = 'status-badge px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1 bg-green-50 text-green-600';
                badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-green-500"></span><span class="status-text">Sudah</span>';

                // Tambahkan card baru ke dropzone
                const newCard = createCardForDropzone(itemElement);
                dropzoneElement.appendChild(newCard);

                updateAllDosenSks();
                updateSidebarCounts();
                filterMatkul();
            } else {
                alert(data.message || 'Gagal menugaskan dosen.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan jaringan.');
        });
    }

    window.hapusDosenPengampu = function(matkulId) {
        if (!confirm('Lepaskan dosen dari mata kuliah ini?')) return;

        fetch('{{ route("dosen-pengampu.hapus") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                kode_matkul: matkulId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Hapus card dari dropzone
                const card = document.querySelector(`.assigned-card[data-matkul-id="${matkulId}"]`);
                if (card) card.remove();

                // Update item di sidebar
                const itemElement = document.getElementById(`matkul-${matkulId}`);
                if (itemElement) {
                    itemElement.setAttribute('data-dosen-id', '');
                    itemElement.setAttribute('data-status', 'belum');
                    
                    const badge = itemElement.querySelector('.status-badge');
                    badge.className = 'status-badge px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1 bg-red-50 text-red-600';
                    badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-red-500"></span><span class="status-text">Belum</span>';
                }

                updateAllDosenSks();
                updateSidebarCounts();
                filterMatkul();
            } else {
                alert(data.message || 'Gagal menghapus penugasan dosen.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan jaringan.');
        });
    }

    function createCardForDropzone(itemElement) {
        const matkulId = itemElement.getAttribute('data-id');
        const nama = itemElement.getAttribute('data-nama');
        const prodi = itemElement.getAttribute('data-prodi');
        const sks = parseInt(itemElement.getAttribute('data-sks')) || 0;
        
        const div = document.createElement('div');
        div.className = `assigned-card bg-white border border-gray-200 p-3 rounded-lg shadow-sm flex items-center justify-between group`;
        div.setAttribute('data-matkul-id', matkulId);
        div.setAttribute('data-sks', sks);
        
        div.innerHTML = `
            <div class="flex-1 min-w-0">
                <h4 class="font-bold text-sm text-gray-800 truncate" title="${nama}">${nama}</h4>
                <p class="text-xs text-gray-500 truncate" title="${prodi}">${prodi}</p>
                <span class="text-[10px] font-semibold px-2 py-0.5 bg-blue-50 text-blue-600 rounded mt-1 inline-block">${sks} SKS</span>
            </div>
            <button onclick="hapusDosenPengampu('${matkulId}')" class="text-red-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded-md opacity-0 group-hover:opacity-100 transition" title="Lepaskan Mata Kuliah">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        `;
        return div;
    }

    function updateAllDosenSks() {
        document.querySelectorAll('.dosen-card').forEach(card => {
            let totalSks = 0;
            card.querySelectorAll('.assigned-card').forEach(assigned => {
                totalSks += parseInt(assigned.getAttribute('data-sks')) || 0;
            });
            card.querySelector('.total-sks').innerText = `${totalSks} SKS`;
            
            // Highlight jika SKS berlebih
            if (totalSks > 12) {
                card.querySelector('.total-sks').classList.replace('bg-white', 'bg-red-100');
                card.querySelector('.total-sks').classList.replace('text-teal-800', 'text-red-700');
            } else {
                card.querySelector('.total-sks').classList.replace('bg-red-100', 'bg-white');
                card.querySelector('.total-sks').classList.replace('text-red-700', 'text-teal-800');
            }
        });
    }

    function updateSidebarCounts() {
        const belum = document.querySelectorAll('.matkul-item[data-status="belum"]').length;
        const sudah = document.querySelectorAll('.matkul-item[data-status="sudah"]').length;
        document.getElementById('count-belum').innerText = belum;
        document.getElementById('count-sudah').innerText = sudah;
    }
});

// Expose fungsi filter ke global
window.filterMatkul = function() {
    const activeTab = document.querySelector('[x-data]').__x.$data.activeTab;
    const searchVal = document.getElementById('search-matkul').value.toLowerCase();
    
    document.querySelectorAll('.matkul-item').forEach(item => {
        const status = item.getAttribute('data-status');
        const text = item.innerText.toLowerCase();
        
        let show = true;
        if (activeTab !== status) show = false;
        if (searchVal && !text.includes(searchVal)) show = false;
        
        item.style.display = show ? 'block' : 'none';
    });
}

window.filterDosen = function() {
    const searchVal = document.getElementById('search-dosen').value.toLowerCase();
    document.querySelectorAll('.dosen-card').forEach(card => {
        const nama = card.getAttribute('data-dosen-nama');
        card.style.display = nama.includes(searchVal) ? 'flex' : 'none';
    });
}
</script>

</body>
</html>
