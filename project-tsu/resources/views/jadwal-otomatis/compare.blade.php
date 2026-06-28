<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perbandingan Hasil GA — Penjadwalan</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ sidebarOpen: true }" class="bg-slate-50 overflow-x-hidden min-h-screen font-sans">

@include('components.sidebar')

<main :class="sidebarOpen ? 'lg:ml-64' : ''" class="flex-1 transition-all duration-300">

    {{-- ── Top Header ── --}}
    <div class="bg-white border-b border-slate-200 px-6 sm:px-10 py-5 sticky top-0 z-10">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = !sidebarOpen" class="flex flex-col hover:opacity-80 transition cursor-pointer" title="Toggle Sidebar">
                <div class="w-1.5 h-4 bg-teal-700 rounded-t"></div>
                <div class="w-1.5 h-2.5 bg-amber-400 rounded-b"></div>
            </button>
            <div>
                <h1 class="text-xl font-bold text-slate-800 leading-tight">Perbandingan Hasil GA</h1>
                <p class="text-xs text-slate-400 mt-0.5">Uji Coba & Analisis Optimalitas Jadwal</p>
            </div>
        </div>
    </div>

    <div class="px-6 sm:px-10 py-8">
        <div class="max-w-6xl mx-auto space-y-6">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm font-medium shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- ── Filter & Top Menu ── --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <form method="GET" action="{{ route('jadwal.otomatis.compare_trials') }}" id="form-filter" class="flex items-center gap-3 w-full md:w-auto">
                    <label for="tahun_akademik_id" class="text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Tahun Akademik:</label>
                    <select name="tahun_akademik_id" id="tahun_akademik_id" onchange="this.form.submit()"
                        class="pl-4 pr-10 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-400 outline-none text-sm font-semibold text-slate-700 appearance-none min-w-[240px]">
                        @foreach($tahunAkademikList as $ta)
                            <option value="{{ $ta->id_tahunakademik }}" {{ $selectedTahun && $ta->id_tahunakademik === $selectedTahun->id_tahunakademik ? 'selected' : '' }}>
                                {{ $ta->nama_tahunakademik }}
                            </option>
                        @endforeach
                    </select>
                </form>
                <div class="flex items-center gap-2 w-full md:w-auto justify-end">
                    <a href="{{ route('jadwal.otomatis.index') }}" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Generate Baru
                    </a>
                </div>
            </div>

            {{-- ── Main List Panel ── --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left: Trial List (2 Cols on large screen) --}}
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                            <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wide">Daftar Hasil Uji Coba</h2>
                            <span class="text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-medium" id="trials-count">
                                {{ $trials->count() }} Terdaftar
                            </span>
                        </div>
                        <div class="divide-y divide-slate-100 max-h-[500px] overflow-y-auto">
                            @forelse($trials as $trial)
                                <div class="p-5 hover:bg-slate-50 transition flex items-start gap-4 trial-row" 
                                     data-id="{{ $trial->id }}"
                                     data-label="{{ $trial->label }}"
                                     data-fitness="{{ $trial->fitness }}"
                                     data-generasi="{{ $trial->generasi }}"
                                     data-total-kelas="{{ $trial->total_kelas }}"
                                     data-dosen-conflicts="{{ $trial->dosen_conflicts }}"
                                     data-ruangan-conflicts="{{ $trial->ruangan_conflicts }}"
                                     data-soft-violations="{{ $trial->soft_violations }}"
                                     data-json="{{ $trial->jadwal_json }}"
                                     data-date="{{ $trial->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}">
                                    
                                    {{-- Checkbox --}}
                                    <div class="pt-1 flex-shrink-0">
                                        <input type="checkbox" onchange="handleCheckboxChange()" value="{{ $trial->id }}" 
                                            class="w-4 h-4 text-teal-600 border-slate-300 rounded focus:ring-teal-500 cursor-pointer trial-checkbox">
                                    </div>

                                    {{-- Details --}}
                                    <div class="flex-1 min-w-0 space-y-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <h3 class="font-bold text-sm text-slate-800 truncate" title="{{ $trial->label }}">{{ $trial->label }}</h3>
                                            <span class="text-[10px] text-slate-400 font-mono whitespace-nowrap">{{ $trial->created_at->diffForHumans() }}</span>
                                        </div>

                                        {{-- Stats Row --}}
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                            <span class="inline-flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $trial->fitness >= 90 ? 'bg-green-500' : 'bg-amber-400' }}"></span>
                                                Fit: <strong>{{ $trial->fitness }}%</strong>
                                            </span>
                                            <span>·</span>
                                            <span>Kelas: <strong>{{ $trial->total_kelas }}</strong></span>
                                            <span>·</span>
                                            <span class="{{ $trial->dosen_conflicts + $trial->ruangan_conflicts > 0 ? 'text-rose-500 font-semibold bg-rose-50 px-1 rounded' : 'text-slate-400' }}">
                                                Bentrok: <strong>{{ $trial->dosen_conflicts + $trial->ruangan_conflicts }}</strong>
                                            </span>
                                            <span>·</span>
                                            <span class="text-amber-600">Soft Violations: <strong>{{ $trial->soft_violations }}</strong></span>
                                        </div>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="flex flex-col sm:flex-row items-center gap-2 flex-shrink-0">
                                        <button type="button" onclick="previewTrial({{ $trial->id }})"
                                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
                                            Pratinjau
                                        </button>
                                        
                                        <form action="{{ route('jadwal.otomatis.apply_trial', $trial->id) }}" method="POST" 
                                            onsubmit="return confirm('Apakah Anda yakin ingin menggunakan jadwal uji coba \'{{ $trial->label }}\'? Jadwal aktif saat ini di worksheet akan DITIMPA.')">
                                            @csrf
                                            <button type="submit"
                                                class="px-3 py-1.5 bg-teal-50 hover:bg-teal-100 text-teal-700 text-xs font-semibold rounded-lg transition border border-teal-200">
                                                Gunakan
                                            </button>
                                        </form>

                                        <form action="{{ route('jadwal.otomatis.delete_trial', $trial->id) }}" method="POST" 
                                            onsubmit="return confirm('Hapus uji coba ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-500 transition rounded-lg hover:bg-slate-100">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="p-10 text-center text-slate-400 text-sm">
                                    <div class="text-3xl mb-2">📋</div>
                                    Belum ada hasil uji coba disimpan untuk semester ini.<br>
                                    Silakan lakukan generate jadwal terlebih dahulu.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Right: Side-by-Side Comparison Panel (1 Col) --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden sticky top-[92px]">
                        <div class="px-6 py-4 border-b border-slate-100">
                            <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wide">Analisis Perbandingan</h2>
                        </div>
                        
                        {{-- Compare Placeholder --}}
                        <div id="comparison-placeholder" class="p-8 text-center text-slate-400 text-sm space-y-3">
                            <div class="text-4xl text-slate-300">⚖️</div>
                            <p>
                                Pilih <strong>2 atau lebih</strong> uji coba di samping untuk membandingkan kualitasnya secara berdampingan.
                            </p>
                        </div>

                        {{-- Compare Active Content --}}
                        <div id="comparison-active" class="hidden p-5 space-y-4">
                            <div class="bg-slate-50 border border-slate-200/60 rounded-xl p-4 space-y-4">
                                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Hasil Perbandingan</h3>
                                
                                <div id="comparison-cards" class="space-y-3">
                                    {{-- Will be generated by JS --}}
                                </div>

                                <div class="border-t border-slate-200 pt-3 text-[11px] text-slate-400 leading-relaxed">
                                    💡 <strong>Rekomendasi:</strong> Pilih jadwal dengan <strong>Fitness tertinggi</strong> dan <strong>Bentrok 0</strong>. Jika ada beberapa pilihan, pilih yang memiliki <strong>Soft Violations terendah</strong>.
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>{{-- end grid --}}

            {{-- ── Preview Section ── --}}
            <div id="preview-card" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex-1">
                        <h2 class="font-bold text-slate-800" id="preview-title">Pratinjau Uji Coba</h2>
                        <p class="text-xs text-slate-400 mt-0.5" id="preview-meta">Detail jadwal uji coba</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" id="preview-search" placeholder="Cari kelas / dosen…"
                            oninput="filterPreviewTable()"
                            class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 outline-none focus:ring-2 focus:ring-teal-400 w-44 bg-white">
                        <select id="preview-filter-hari" onchange="filterPreviewTable()"
                            class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 outline-none focus:ring-2 focus:ring-teal-400 bg-white">
                            <option value="">Semua Hari</option>
                            <option>Senin</option><option>Selasa</option><option>Rabu</option>
                            <option>Kamis</option><option>Jumat</option>
                        </select>
                        <button type="button" onclick="closePreview()" class="text-slate-400 hover:text-slate-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-800 text-slate-100 text-xs uppercase tracking-wide sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3 text-left">Hari</th>
                                <th class="px-4 py-3 text-left">Jam</th>
                                <th class="px-4 py-3 text-left">Kelas</th>
                                <th class="px-4 py-3 text-left">Kode MK</th>
                                <th class="px-4 py-3 text-left">Mata Kuliah</th>
                                <th class="px-4 py-3 text-left">Jenis</th>
                                <th class="px-4 py-3 text-center">SKS</th>
                                <th class="px-4 py-3 text-left">Dosen</th>
                                <th class="px-4 py-3 text-left">Ruangan</th>
                                <th class="px-4 py-3 text-left">Prodi</th>
                                <th class="px-4 py-3 text-center">Smt</th>
                            </tr>
                        </thead>
                        <tbody id="preview-tbody" class="divide-y divide-slate-100"></tbody>
                    </table>
                    <div id="preview-empty" class="hidden py-10 text-center text-slate-400 text-sm">Tidak ada data yang cocok.</div>
                </div>
                <div class="px-6 py-3 border-t border-slate-100 text-xs text-slate-400 flex items-center justify-between" bg-slate-50>
                    <span id="preview-count"></span>
                    <form id="form-apply-preview" method="POST" action="">
                        @csrf
                        <button type="submit" class="px-4 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                            Terapkan Jadwal Uji Coba Ini
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
// Keep list of checked rows
let checkedTrials = [];

// ─────────────────────────────────────────────────
// Checkbox selection and side-by-side builder
// ─────────────────────────────────────────────────
function handleCheckboxChange() {
    const checkboxes = document.querySelectorAll('.trial-checkbox:checked');
    const panelActive = document.getElementById('comparison-active');
    const panelPlaceholder = document.getElementById('comparison-placeholder');
    const cardsContainer = document.getElementById('comparison-cards');
    
    checkedTrials = [];
    checkboxes.forEach(cb => {
        const row = cb.closest('.trial-row');
        checkedTrials.push({
            id: row.dataset.id,
            label: row.dataset.label,
            fitness: parseFloat(row.dataset.fitness),
            generasi: parseInt(row.dataset.generasi),
            totalKelas: parseInt(row.dataset.totalKelas),
            dosenConflicts: parseInt(row.dataset.dosenConflicts),
            ruanganConflicts: parseInt(row.dataset.ruanganConflicts),
            softViolations: parseInt(row.dataset.softViolations),
            date: row.dataset.date
        });
    });

    if (checkedTrials.length < 2) {
        panelActive.classList.add('hidden');
        panelPlaceholder.classList.remove('hidden');
        return;
    }

    panelPlaceholder.classList.add('hidden');
    panelActive.classList.remove('hidden');

    // Find the best scores among checked items
    const maxFitness = Math.max(...checkedTrials.map(t => t.fitness));
    const minConflicts = Math.min(...checkedTrials.map(t => t.dosenConflicts + t.ruanganConflicts));
    const minSoft = Math.min(...checkedTrials.map(t => t.softViolations));

    cardsContainer.innerHTML = '';
    
    checkedTrials.forEach(t => {
        const isBestFit = t.fitness === maxFitness;
        const totalConflicts = t.dosenConflicts + t.ruanganConflicts;
        const isBestConflicts = totalConflicts === minConflicts;
        const isBestSoft = t.softViolations === minSoft;

        const card = document.createElement('div');
        // If it's overall the best (highest fitness and no conflicts), style it slightly differently
        const isOverallWinner = isBestFit && totalConflicts === 0;
        card.className = `p-4 rounded-xl border-2 transition bg-white ${
            isOverallWinner 
                ? 'border-green-500 shadow-md ring-1 ring-green-400' 
                : (isBestFit ? 'border-teal-500 shadow-sm' : 'border-slate-200 hover:border-slate-300')
        }`;

        card.innerHTML = `
            <div class="flex items-center justify-between mb-2">
                <span class="font-bold text-xs text-slate-700 truncate max-w-[150px]" title="${t.label}">${t.label}</span>
                ${isOverallWinner ? '<span class="bg-green-100 text-green-800 text-[9px] font-bold px-1.5 py-0.5 rounded-full flex items-center gap-0.5">🏆 Terbaik</span>' : ''}
            </div>

            <div class="space-y-2">
                {{-- Fitness Progress --}}
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="text-slate-400 font-medium">Fitness:</span>
                        <span class="font-bold ${isBestFit ? 'text-teal-600' : 'text-slate-700'}">${t.fitness}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div class="h-1.5 rounded-full ${isBestFit ? 'bg-teal-500' : 'bg-slate-400'}" style="width: ${t.fitness}%"></div>
                    </div>
                </div>

                {{-- Conflict badges --}}
                <div class="flex flex-wrap gap-1.5 pt-1 text-[10px]">
                    <span class="px-2 py-0.5 rounded font-semibold ${
                        totalConflicts > 0 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-green-50 text-green-700 border border-green-200'
                    }">
                        Bentrok Hard: ${totalConflicts}
                    </span>
                    <span class="px-2 py-0.5 rounded font-semibold ${
                        isBestSoft ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-50 text-slate-600 border border-slate-200'
                    }">
                        Soft Violations: ${t.softViolations}
                    </span>
                </div>

                {{-- Action Apply --}}
                <div class="pt-2 flex justify-between items-center text-[10px] text-slate-400 border-t border-slate-100 mt-2">
                    <span>${t.totalKelas} kelas terjadwal</span>
                    <form action="/jadwal-otomatis/trial/${t.id}/apply" method="POST" 
                        onsubmit="return confirm('Apakah Anda yakin ingin menggunakan jadwal uji coba \'${t.label}\'? Jadwal aktif saat ini di worksheet akan DITIMPA.')">
                        @csrf
                        <button type="submit" class="text-teal-600 hover:text-teal-800 font-bold">
                            Terapkan →
                        </button>
                    </form>
                </div>
            </div>
        `;
        cardsContainer.appendChild(card);
    });
}

// ─────────────────────────────────────────────────
// Schedule Preview Table Rendering
// ─────────────────────────────────────────────────
let activePreviewRows = [];
const hariColor = {
    Senin:'text-blue-600 bg-blue-50', Selasa:'text-purple-600 bg-purple-50',
    Rabu:'text-teal-600 bg-teal-50',  Kamis:'text-orange-600 bg-orange-50',
    Jumat:'text-green-600 bg-green-50'
};

function previewTrial(trialId) {
    const row = document.querySelector(`.trial-row[data-id="${trialId}"]`);
    if (!row) return;

    const label = row.dataset.label;
    const date = row.dataset.date;
    const fitness = row.dataset.fitness;
    const jsonStr = row.dataset.json;

    try {
        activePreviewRows = JSON.parse(jsonStr);
    } catch(e) {
        alert('Gagal membaca data jadwal uji coba.');
        return;
    }

    // Set preview details
    document.getElementById('preview-title').textContent = `Pratinjau: ${label}`;
    document.getElementById('preview-meta').textContent = `Disimpan: ${date} · Fitness: ${fitness}%`;
    
    // Set form action path dynamically
    document.getElementById('form-apply-preview').action = `/jadwal-otomatis/trial/${trialId}/apply`;
    document.getElementById('form-apply-preview').onsubmit = function() {
        return confirm(`Apakah Anda yakin ingin menggunakan jadwal uji coba '${label}'? Jadwal aktif saat ini di worksheet akan DITIMPA.`);
    };

    // Show Preview Section
    const previewCard = document.getElementById('preview-card');
    previewCard.classList.remove('hidden');
    
    // Render and Scroll
    renderPreviewTable(activePreviewRows);
    previewCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function closePreview() {
    document.getElementById('preview-card').classList.add('hidden');
}

function renderPreviewTable(rows) {
    const tbody = document.getElementById('preview-tbody');
    const emptyMsg = document.getElementById('preview-empty');
    tbody.innerHTML = '';
    
    if (!rows.length) {
        emptyMsg.classList.remove('hidden');
        document.getElementById('preview-count').textContent = 'Tidak ada data.';
        return;
    }
    
    emptyMsg.classList.add('hidden');
    rows.forEach((row, i) => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50 transition ' + (i%2===0?'bg-white':'bg-slate-50/50');
        tr.dataset.search = (row.hari+' '+row.nama_kelas+' '+row.dosen+' '+row.nama_mk+' '+row.kode_mk).toLowerCase();
        tr.dataset.hari   = (row.hari || '').toLowerCase();
        const hc = hariColor[row.hari] || 'text-slate-600 bg-slate-100';
        tr.innerHTML = `
            <td class="px-4 py-3 whitespace-nowrap">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold ${hc}">${row.hari}</span>
            </td>
            <td class="px-4 py-3 text-slate-500 whitespace-nowrap text-xs font-mono">${row.jam_mulai}–${row.jam_selesai}</td>
            <td class="px-4 py-3 font-bold text-slate-800">${row.nama_kelas}</td>
            <td class="px-4 py-3 font-mono text-teal-700 text-xs">${row.kode_mk}</td>
            <td class="px-4 py-3 text-slate-700">${row.nama_mk}</td>
            <td class="px-4 py-3">
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold ${row.jenis==='praktikum'||row.jenis==='Praktikum'?'bg-purple-100 text-purple-700':'bg-blue-100 text-blue-700'}">
                    ${row.jenis}
                </span>
            </td>
            <td class="px-4 py-3 text-center text-slate-600">${row.sks}</td>
            <td class="px-4 py-3 text-slate-600 text-sm">${row.dosen}</td>
            <td class="px-4 py-3 text-slate-500 text-sm">${row.ruangan||'—'}</td>
            <td class="px-4 py-3 text-slate-400 text-xs">${row.prodi}</td>
            <td class="px-4 py-3 text-center text-slate-500">${row.semester||'—'}</td>`;
        tbody.appendChild(tr);
    });
    document.getElementById('preview-count').textContent = `Menampilkan ${rows.length} jadwal`;
}

function filterPreviewTable() {
    const q    = document.getElementById('preview-search').value.toLowerCase().trim();
    const hari = document.getElementById('preview-filter-hari').value.toLowerCase().trim();
    const rows = document.querySelectorAll('#preview-tbody tr');
    let visible = 0;
    
    rows.forEach(tr => {
        const matchQ = !q    || tr.dataset.search.includes(q);
        const matchH = !hari || tr.dataset.hari === hari;
        const show   = matchQ && matchH;
        tr.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    
    document.getElementById('preview-count').textContent = `Menampilkan ${visible} jadwal`;
    document.getElementById('preview-empty').classList.toggle('hidden', visible > 0);
}
</script>

</body>
</html>
