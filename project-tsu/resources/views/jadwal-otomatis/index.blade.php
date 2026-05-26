<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjadwalan Otomatis - Algoritma Genetika</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ sidebarOpen: true }" class="bg-gray-100/50 overflow-x-hidden min-h-screen font-sans">

@include('components.sidebar')

<main :class="sidebarOpen ? 'lg:ml-64' : ''" class="flex-1 p-6 sm:p-10 transition-all duration-300">

    <div class="flex items-center mb-8">
        <div class="flex flex-col">
            <div class="w-2 h-5 bg-teal-800 rounded-tl-md"></div>
            <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 ml-3">Penjadwalan Otomatis</h1>
    </div>

    <div class="max-w-3xl mx-auto space-y-6">

        {{-- INFO CARD --}}
        <div class="bg-teal-50 border border-teal-200 rounded-2xl p-5 flex gap-4">
            <div class="text-3xl">🧬</div>
            <div>
                <h3 class="font-bold text-teal-800">Algoritma Genetika</h3>
                <p class="text-sm text-teal-700 mt-1">
                    Sistem mencari kombinasi jadwal terbaik secara otomatis. Hasil dapat diedit di workspace manual.
                </p>
            </div>
        </div>

        {{-- FORM PARAMETER --}}
        <div id="section-form" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            <div class="px-6 py-5 border-b border-gray-100">
                <h2 class="text-base font-bold text-gray-800">Parameter Generate</h2>
            </div>

            <div class="p-6 space-y-6">

                {{-- Tahun Akademik --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tahun Akademik</label>
                    <select id="inp-tahun" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-teal-500 outline-none text-sm font-medium">
                        <option value="">-- Pilih Tahun Akademik --</option>
                        @foreach ($tahunAkademikList as $ta)
                            <option value="{{ $ta->id_tahunakademik }}" {{ (isset($selectedTahun) && $selectedTahun == $ta->id_tahunakademik) ? 'selected' : '' }}>
                                {{ $ta->nama_tahunakademik }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- Populasi --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                        Jumlah Individu (Populasi)
                    </label>

                    {{-- Penjelasan awam --}}
                    <div class="mt-2 mb-3 flex gap-3 bg-teal-50 border border-teal-100 rounded-xl p-3">
                        <svg class="w-5 h-5 text-teal-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-5.477-3.72M9 20H4v-2a4 4 0 015.477-3.72M15 8a4 4 0 11-8 0 4 4 0 018 0zm6 4a3 3 0 11-6 0 3 3 0 016 0zm-18 0a3 3 0 116 0 3 3 0 01-6 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-teal-800">Berapa banyak "kandidat jadwal" yang dicoba sekaligus?</p>
                            <p class="text-xs text-teal-700 mt-1 leading-relaxed">
                                Bayangkan Anda meminta 100 orang berbeda untuk masing-masing membuat jadwal. Setiap orang menghasilkan satu versi jadwal yang berbeda. Semakin banyak orang yang mencoba, semakin besar peluang menemukan susunan jadwal yang paling bagus — tapi butuh waktu lebih lama.
                            </p>
                            <div class="flex gap-4 mt-2">
                                <span class="text-xs text-teal-600">↑ Lebih besar = hasil lebih baik, proses lebih lama</span>
                                <span class="text-xs text-amber-600">↓ Lebih kecil = proses cepat, hasil mungkin kurang optimal</span>
                            </div>
                        </div>
                    </div>

                    <p class="text-xs text-gray-400 mb-3">Rekomendasi: 50–150</p>
                    <div class="flex items-center gap-4">
                        <input type="range" id="range-populasi" min="10" max="500" step="10" value="100"
                            class="flex-1 accent-teal-600"
                            oninput="document.getElementById('num-populasi').value = this.value; updatePopTip(this.value)">
                        <input type="number" id="num-populasi" min="10" max="500" value="100"
                            class="w-20 text-center border border-teal-300 rounded-xl px-2 py-2 font-bold text-teal-700 outline-none"
                            oninput="document.getElementById('range-populasi').value = this.value; updatePopTip(this.value)">
                    </div>
                    <p id="tip-populasi" class="text-xs text-teal-600 mt-2">
                        ✅ Nilai ini seimbang antara kecepatan dan kualitas hasil.
                    </p>
                </div>

                {{-- Generasi --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                        Jumlah Generasi
                    </label>

                    {{-- Penjelasan awam --}}
                    <div class="mt-2 mb-3 flex gap-3 bg-blue-50 border border-blue-100 rounded-xl p-3">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-blue-800">Berapa kali jadwal "disempurnakan" secara bertahap?</p>
                            <p class="text-xs text-blue-700 mt-1 leading-relaxed">
                                Setiap generasi adalah satu putaran seleksi: jadwal-jadwal terbaik dipertahankan, yang buruk dibuang, lalu digabungkan untuk menghasilkan jadwal yang lebih baik lagi. Seperti lomba memasak yang berlangsung 200 babak — semakin banyak babak, resep finalnya semakin sempurna.
                            </p>
                            <div class="flex gap-4 mt-2">
                                <span class="text-xs text-blue-600">↑ Lebih besar = jadwal lebih matang, proses lebih lama</span>
                                <span class="text-xs text-amber-600">↓ Lebih kecil = proses cepat, jadwal belum mencapai puncaknya</span>
                            </div>
                        </div>
                    </div>

                    <p class="text-xs text-gray-400 mb-3">Rekomendasi: 100–300</p>
                    <div class="flex items-center gap-4">
                        <input type="range" id="range-generasi" min="10" max="1000" step="10" value="200"
                            class="flex-1 accent-teal-600"
                            oninput="document.getElementById('num-generasi').value = this.value; updateGenTip(this.value)">
                        <input type="number" id="num-generasi" min="10" max="1000" value="200"
                            class="w-20 text-center border border-teal-300 rounded-xl px-2 py-2 font-bold text-teal-700 outline-none"
                            oninput="document.getElementById('range-generasi').value = this.value; updateGenTip(this.value)">
                    </div>
                    <p id="tip-generasi" class="text-xs text-blue-600 mt-2">
                        ✅ Cukup untuk sebagian besar kasus penjadwalan kampus.
                    </p>
                </div>

                {{-- Constraint info --}}
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Constraint Aktif</p>
                    <div class="grid grid-cols-2 gap-2 text-xs text-gray-600">
                        <span>✅ Dosen tidak bentrok waktu</span>
                        <span>✅ Ruangan tidak bentrok waktu</span>
                        <span>✅ Kelas tidak dobel slot</span>
                        <span>✅ Tidak di slot istirahat</span>
                        <span>✅ Kelas reguler pagi/siang</span>
                        <span>✅ Kelas S malam</span>
                    </div>
                </div>

                <button id="btn-generate" onclick="startGA()"
                        class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl shadow flex items-center justify-center gap-3 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Jalankan Algoritma Genetika
                </button>

            </div>
        </div>

        {{-- PANEL PROGRESS (hidden awal) --}}
        <div id="section-progress" class="hidden bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div id="dot-pulse" class="w-2.5 h-2.5 rounded-full bg-teal-500 animate-pulse"></div>
                    <h2 class="text-base font-bold text-gray-800" id="progress-title">Memproses Algoritma Genetika...</h2>
                </div>
                <span class="text-sm text-gray-400" id="progress-pct-label">0%</span>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-4 gap-px bg-gray-100 border-b border-gray-100">
                <div class="bg-white px-5 py-4">
                    <p class="text-xs text-gray-400 mb-1">Generasi</p>
                    <p class="text-2xl font-bold text-blue-600" id="stat-gen">0</p>
                    <p class="text-xs text-gray-400" id="stat-gen-max">dari 0</p>
                </div>
                <div class="bg-white px-5 py-4">
                    <p class="text-xs text-gray-400 mb-1">Fitness terbaik</p>
                    <p class="text-2xl font-bold text-teal-600" id="stat-fitness">0%</p>
                </div>
                <div class="bg-white px-5 py-4">
                    <p class="text-xs text-gray-400 mb-1">Pelanggaran</p>
                    <p class="text-2xl font-bold text-amber-600" id="stat-viol">-</p>
                </div>
                <div class="bg-white px-5 py-4">
                    <p class="text-xs text-gray-400 mb-1">Total kelas</p>
                    <p class="text-2xl font-bold text-gray-700" id="stat-kelas">-</p>
                </div>
            </div>

            {{-- Progress bar --}}
            <div class="px-6 py-3 border-b border-gray-100">
                <div class="w-full bg-gray-100 rounded-full h-2.5">
                    <div id="progress-bar" class="h-2.5 rounded-full bg-teal-500 transition-all duration-300" style="width:0%"></div>
                </div>
            </div>

            {{-- Log --}}
            <div class="px-6 py-1">
                <p class="text-xs text-gray-400 font-medium py-2">Log Generasi</p>
                <div id="log-container"
                     class="font-mono text-xs bg-gray-50 rounded-xl border border-gray-100 p-3 h-48 overflow-y-auto space-y-0.5">
                </div>
            </div>

            <div class="px-6 py-4"></div>
        </div>

        {{-- PANEL HASIL (hidden awal) --}}
        <div id="section-hasil" class="hidden space-y-6">

            {{-- Fitness summary --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-bold text-gray-800">Hasil Generate</h2>
                    <span id="hasil-fitness-badge" class="px-3 py-1 rounded-full text-sm font-bold"></span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3 mb-2">
                    <div id="hasil-progress-bar" class="h-3 rounded-full transition-all duration-700" style="width:0%"></div>
                </div>
                <p id="hasil-fitness-note" class="text-xs text-gray-400"></p>

                <div class="grid grid-cols-3 gap-4 mt-4">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-400">Fitness</p>
                        <p class="font-bold text-teal-700" id="hasil-stat-fitness">-</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-400">Generasi berjalan</p>
                        <p class="font-bold text-blue-600" id="hasil-stat-gen">-</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-400">Total kelas</p>
                        <p class="font-bold text-gray-700" id="hasil-stat-kelas">-</p>
                    </div>
                </div>
            </div>

            {{-- Tabel preview --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800">Preview Jadwal</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-teal-700 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left whitespace-nowrap">Hari</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">Jam</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">Kelas</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">Kode MK</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">Mata Kuliah</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">Jenis</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">SKS</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">Dosen</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">Ruangan</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">Prodi</th>
                            </tr>
                        </thead>
                        <tbody id="hasil-tbody" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
            </div>

            {{-- Action buttons --}}
            <form id="form-simpan" method="POST" action="{{ route('jadwal.otomatis.simpan') }}">
                @csrf
                <input type="hidden" name="jadwal_json" id="inp-jadwal-json">
                <input type="hidden" name="tahun_akademik_id" id="inp-tahun-hidden">

                <div class="flex items-center justify-between">
                    <button type="button" onclick="resetForm()"
                            class="px-6 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl font-semibold flex items-center gap-2 transition">
                        ← Ulangi Generate
                    </button>
                    <button type="submit"
                            class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl shadow flex items-center gap-2 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan & Buka di Workspace Manual
                    </button>
                </div>
            </form>

        </div>

    </div>
</main>

<script>
let bestFitnessPrev = 0;
let maxGen = 200;

function updatePopTip(v) {
    const el = document.getElementById('tip-populasi');
    const n  = parseInt(v);
    if (n < 50) {
        el.className = 'text-xs text-amber-600 mt-2';
        el.innerText = '⚠ Terlalu kecil — variasi jadwal yang diuji sangat sedikit, hasil mungkin kurang optimal.';
    } else if (n <= 150) {
        el.className = 'text-xs text-teal-600 mt-2';
        el.innerText = '✅ Nilai ini seimbang antara kecepatan dan kualitas hasil.';
    } else if (n <= 300) {
        el.className = 'text-xs text-blue-600 mt-2';
        el.innerText = 'ℹ Cukup besar — hasilnya bisa lebih baik tapi proses akan terasa lebih lambat.';
    } else {
        el.className = 'text-xs text-amber-600 mt-2';
        el.innerText = '⚠ Sangat besar — proses akan berjalan lama. Gunakan hanya jika waktu tidak menjadi kendala.';
    }
}

function updateGenTip(v) {
    const el = document.getElementById('tip-generasi');
    const n  = parseInt(v);
    if (n < 100) {
        el.className = 'text-xs text-amber-600 mt-2';
        el.innerText = '⚠ Terlalu sedikit putaran — jadwal belum sempat disempurnakan dengan baik.';
    } else if (n <= 300) {
        el.className = 'text-xs text-blue-600 mt-2';
        el.innerText = '✅ Cukup untuk sebagian besar kasus penjadwalan kampus.';
    } else if (n <= 600) {
        el.className = 'text-xs text-blue-600 mt-2';
        el.innerText = 'ℹ Banyak putaran — gunakan jika data kelas sangat banyak atau constraint kompleks.';
    } else {
        el.className = 'text-xs text-amber-600 mt-2';
        el.innerText = '⚠ Sangat banyak putaran — waktu proses bisa cukup panjang.';
    }
}

function startGA() {
    const tahun    = document.getElementById('inp-tahun').value;
    const populasi = document.getElementById('num-populasi').value;
    const generasi = document.getElementById('num-generasi').value;

    if (!tahun) { alert('Pilih tahun akademik terlebih dahulu.'); return; }

    maxGen = parseInt(generasi);
    bestFitnessPrev = 0;

    document.getElementById('section-progress').classList.remove('hidden');
    document.getElementById('section-hasil').classList.add('hidden');
    document.getElementById('log-container').innerHTML = '';
    document.getElementById('stat-gen-max').innerText  = `dari ${generasi}`;
    document.getElementById('stat-gen').innerText      = '0';
    document.getElementById('stat-fitness').innerText  = '0%';
    document.getElementById('stat-viol').innerText     = '-';
    document.getElementById('progress-bar').style.width = '0%';
    document.getElementById('progress-pct-label').innerText = '0%';
    document.getElementById('progress-title').innerText = 'Memproses Algoritma Genetika...';

    const btn = document.getElementById('btn-generate');
    btn.disabled  = true;
    btn.innerText = 'Sedang memproses...';

    const url = `{{ route('jadwal.otomatis.stream') }}?tahun_akademik_id=${tahun}&populasi=${populasi}&generasi=${generasi}`;
    const es  = new EventSource(url);

    es.onmessage = function(e) {
        const data = JSON.parse(e.data);

        if (data.done) {
            es.close();
            onSelesai(data);
            return;
        }

        const pct = Math.round((data.gen / maxGen) * 100);
        document.getElementById('stat-gen').innerText     = data.gen;
        document.getElementById('stat-fitness').innerText = data.fitness + '%';
        document.getElementById('stat-viol').innerText    = data.pelanggaran;
        document.getElementById('progress-bar').style.width = pct + '%';
        document.getElementById('progress-pct-label').innerText = pct + '%';

        const isBest = data.fitness > bestFitnessPrev;
        if (isBest) bestFitnessPrev = data.fitness;

        const log  = document.getElementById('log-container');
        const line = document.createElement('div');
        line.className = 'flex gap-3 py-0.5' + (isBest ? ' text-teal-700 font-semibold' : '');
        line.innerHTML = `
            <span class="text-gray-400 w-16">Gen ${String(data.gen).padStart(3,'0')}</span>
            <span class="${isBest ? 'text-teal-600' : 'text-gray-600'} w-24">fitness ${data.fitness}%</span>
            <span class="text-gray-500">pelanggaran: ${data.pelanggaran}${isBest ? ' ← terbaik' : ''}</span>`;
        log.appendChild(line);
        log.scrollTop = log.scrollHeight;
    };

    es.onerror = function() {
        es.close();
        document.getElementById('progress-title').innerText = 'Terjadi error saat memproses.';
        btn.disabled  = false;
        btn.innerText = 'Jalankan Algoritma Genetika';
    };
}

function onSelesai(data) {
    document.getElementById('section-hasil').classList.remove('hidden');
    document.getElementById('dot-pulse').classList.remove('animate-pulse');
    document.getElementById('dot-pulse').classList.add('bg-green-500');
    document.getElementById('progress-title').innerText = 'Selesai!';
    document.getElementById('progress-bar').style.width = '100%';
    document.getElementById('progress-pct-label').innerText = '100%';
    document.getElementById('stat-gen').innerText     = data.generasi;
    document.getElementById('stat-fitness').innerText = data.fitness + '%';
    document.getElementById('stat-kelas').innerText   = data.total_kelas;

    const fit   = data.fitness;
    const badge = document.getElementById('hasil-fitness-badge');
    const bar   = document.getElementById('hasil-progress-bar');
    const note  = document.getElementById('hasil-fitness-note');
    bar.style.width = fit + '%';

    if (fit >= 90) {
        badge.className = 'px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-700';
        badge.innerText = fit + '% — Sangat Baik';
        bar.className   = 'h-3 rounded-full bg-green-500 transition-all duration-700';
        note.innerText  = '✅ Tidak ada bentrok terdeteksi oleh algoritma.';
    } else if (fit >= 70) {
        badge.className = 'px-3 py-1 rounded-full text-sm font-bold bg-yellow-100 text-yellow-700';
        badge.innerText = fit + '% — Cukup Baik';
        bar.className   = 'h-3 rounded-full bg-yellow-400 transition-all duration-700';
        note.innerText  = '⚠ Ada kemungkinan bentrok. Review di workspace manual.';
    } else {
        badge.className = 'px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-700';
        badge.innerText = fit + '% — Perlu Review';
        bar.className   = 'h-3 rounded-full bg-red-400 transition-all duration-700';
        note.innerText  = '⚠ Banyak bentrok. Coba tambah generasi atau populasi.';
    }

    document.getElementById('hasil-stat-fitness').innerText = fit + '%';
    document.getElementById('hasil-stat-gen').innerText     = data.generasi + ' generasi';
    document.getElementById('hasil-stat-kelas').innerText   = data.total_kelas + ' kelas';

    const tbody = document.getElementById('hasil-tbody');
    tbody.innerHTML = '';
    data.jadwal_rows.forEach((row, i) => {
        const tr = document.createElement('tr');
        tr.className = i % 2 === 0 ? 'bg-white' : 'bg-teal-50/30';
        tr.innerHTML = `
            <td class="px-4 py-3 font-semibold text-teal-700 whitespace-nowrap">${row.hari}</td>
            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">${row.jam_mulai} – ${row.jam_selesai}</td>
            <td class="px-4 py-3 font-bold">${row.nama_kelas}</td>
            <td class="px-4 py-3 font-mono text-teal-700">${row.kode_mk}</td>
            <td class="px-4 py-3">${row.nama_mk}</td>
            <td class="px-4 py-3">
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold ${row.jenis === 'Praktikum' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'}">
                    ${row.jenis}
                </span>
            </td>
            <td class="px-4 py-3 text-center">${row.sks}</td>
            <td class="px-4 py-3">${row.dosen}</td>
            <td class="px-4 py-3">${row.ruangan || '-'}</td>
            <td class="px-4 py-3 text-xs text-gray-500">${row.prodi}</td>`;
        tbody.appendChild(tr);
    });

    document.getElementById('inp-jadwal-json').value  = JSON.stringify(data.jadwal_rows);
    document.getElementById('inp-tahun-hidden').value = document.getElementById('inp-tahun').value;

    const btn = document.getElementById('btn-generate');
    btn.disabled  = false;
    btn.innerText = 'Jalankan Algoritma Genetika';

    document.getElementById('section-hasil').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function resetForm() {
    document.getElementById('section-progress').classList.add('hidden');
    document.getElementById('section-hasil').classList.add('hidden');
    bestFitnessPrev = 0;
}
</script>

</body>
</html>
