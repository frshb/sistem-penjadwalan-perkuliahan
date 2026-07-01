<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjadwalan Otomatis — Algoritma Genetika</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Fitness chart canvas */
        #fitness-chart { display:block; width:100%; height:100px; }
        /* Custom range track */
        input[type=range]::-webkit-slider-thumb { cursor: pointer; }
        /* Collapse transition */
        .collapse-body { overflow:hidden; transition: max-height .35s ease, opacity .3s ease; }
        .collapse-body.closed { max-height:0!important; opacity:0; }
        /* Spinner */
        @keyframes spin { to { transform:rotate(360deg); } }
        .spin { animation: spin 1s linear infinite; }
        /* Fade-in for log lines */
        @keyframes fadein { from{opacity:0;transform:translateY(4px)} to{opacity:1;transform:none} }
        .log-line { animation: fadein .18s ease; }
        /* Smooth progress bar */
        #progress-bar { transition: width .4s cubic-bezier(.4,0,.2,1); }
        #fitness-fill  { transition: width .6s cubic-bezier(.4,0,.2,1); }


    </style>
</head>
<body x-data="{ sidebarOpen: true }" class="bg-slate-50 overflow-x-hidden min-h-screen font-sans">

@include('components.sidebar')

<main :class="sidebarOpen ? 'lg:ml-64' : ''" class="flex-1 transition-all duration-300">

    {{-- ── Top Header ── --}}
    <div class="bg-white border-b border-slate-200 px-6 sm:px-10 py-5 sticky top-0 z-10">
        <div class="flex items-center gap-3">
            <div class="flex flex-col">
                <div class="w-1.5 h-4 bg-teal-700 rounded-t"></div>
                <div class="w-1.5 h-2.5 bg-amber-400 rounded-b"></div>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 leading-tight">Buat Jadwal Otomatis</h1>
                <p class="text-sm text-slate-500 mt-1">Sistem Cerdas Penyusun Jadwal Perkuliahan</p>
            </div>
        </div>
    </div>

    <div class="px-6 sm:px-10 py-8">
    <div class="max-w-5xl mx-auto space-y-6">

    {{-- ═══════════════════════════════════════════════════
         SECTION FORM — dua kolom: Konfigurasi | Pengaturan Lanjutan
    ══════════════════════════════════════════════════════ --}}
    <div id="section-form" class="max-w-3xl mx-auto space-y-6">

        {{-- Kolom Konfigurasi Utama --}}
        <div id="left-column" class="space-y-5">

            {{-- Card: Tahun Akademik + Preset --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wide">Konfigurasi</h2>
                    <span class="text-xs text-slate-400">Langkah 1 dari 1</span>
                </div>
                <div class="p-6 space-y-6">

                    {{-- Tahun Akademik --}}
                    <div>
                        <label class="block text-sm font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Pilih Tahun Akademik
                        </label>
                        <p class="text-sm text-slate-500 mb-3">
                            Pilih semester mana yang ingin dibuatkan jadwalnya. Data dosen dan mata kuliah akan otomatis diambil dari semester ini.
                        </p>
                        <div class="relative">
                            <select id="inp-tahun"
                                class="w-full pl-4 pr-10 py-3.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none text-base font-medium text-slate-700 appearance-none shadow-sm cursor-pointer">
                                <option value="">— Silakan Pilih Tahun Akademik —</option>
                                @foreach ($tahunAkademikList as $ta)
                                    <option value="{{ $ta->id_tahunakademik }}" data-kelas-count="{{ $ta->kelas_count ?? 0 }}" {{ $tahunAkademikAktif && $ta->id_tahunakademik === $tahunAkademikAktif->id_tahunakademik ? 'selected' : '' }}>{{ $ta->nama_tahunakademik }} (Tahun Ajaran {{ $ta->tahun_ajaran }}) — [{{ $ta->kelas_count ?? 0 }} Kelas]</option>
                                @endforeach
                            </select>
                            <svg class="absolute right-4 top-4 w-5 h-5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    {{-- Preset Mode --}}
                    <div>
                        <label class="block text-sm font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Pilih Tingkat Ketelitian (Preset)
                        </label>
                        <p class="text-sm text-slate-500 mb-4">
                            Pilih seberapa detail sistem harus menganalisis data Anda.
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                            <button type="button" onclick="applyPreset('cepat')"
                                class="preset-btn p-3 rounded-xl border-2 border-slate-200 bg-slate-50 hover:border-emerald-400 hover:bg-emerald-50 text-sm font-bold text-slate-700 transition text-center shadow-sm flex flex-col items-center" data-preset="cepat">
                                <div class="text-2xl mb-1">⚡</div>
                                Cepat
                                <div class="text-slate-500 font-normal text-xs mt-1">Pop: 40, Gen: 100</div>
                            </button>

                            <button type="button" onclick="applyPreset('normal')"
                                class="preset-btn p-3 rounded-xl border-2 border-slate-200 bg-slate-50 hover:border-blue-400 hover:bg-blue-50 text-sm font-bold text-slate-700 transition text-center shadow-sm flex flex-col items-center" data-preset="normal">
                                <div class="text-2xl mb-1">⚖️</div>
                                Normal
                                <div class="text-slate-500 font-normal text-xs mt-1">Pop: 80, Gen: 300</div>
                            </button>
                            
                            <button type="button" onclick="applyPreset('akurat')"
                                class="preset-btn p-3 rounded-xl border-2 border-slate-200 bg-slate-50 hover:border-purple-400 hover:bg-purple-50 text-sm font-bold text-slate-700 transition text-center shadow-sm flex flex-col items-center" data-preset="akurat">
                                <div class="text-2xl mb-1">🎯</div>
                                Akurat
                                <div class="text-slate-500 font-normal text-xs mt-1">Pop: 150, Gen: 500</div>
                            </button>

                            <button type="button" onclick="applyPreset('custom')"
                                class="preset-btn p-3 rounded-xl border-2 border-slate-200 bg-slate-50 hover:border-amber-400 hover:bg-amber-50 text-sm font-bold text-slate-700 transition text-center flex flex-col items-center" data-preset="custom">
                                <div class="text-2xl mb-1">⚙️</div>
                                Custom
                                <div class="text-slate-500 font-normal text-xs mt-1">Atur Manual</div>
                            </button>
                        </div>
                    </div>

                    {{-- Populasi Slider --}}
                    <div id="container-populasi" class="hidden bg-slate-50 rounded-xl p-3 border border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex-1 pr-3">
                                <label class="text-sm font-bold text-slate-700 block">
                                    Variasi Jadwal (Populasi)
                                </label>
                                <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">
                                    Ibarat meminta banyak orang menyusun jadwal sekaligus. Makin banyak orang = peluang hasil sempurna makin tinggi, tapi proses makin lambat.
                                </p>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <input type="number" id="num-populasi" min="10" max="500" value="100"
                                    class="w-14 text-center border border-slate-200 rounded-md px-1.5 py-1 text-sm font-bold text-teal-700 outline-none focus:ring-2 focus:ring-teal-400 bg-white"
                                    oninput="syncSlider('populasi', this.value)">
                            </div>
                        </div>
                        <input type="range" id="range-populasi" min="10" max="500" step="10" value="100"
                            class="w-full h-1.5 rounded-full accent-teal-600 cursor-pointer mb-1"
                            oninput="syncNumber('populasi', this.value)">
                    </div>

                    {{-- Generasi Slider --}}
                    <div id="container-generasi" class="hidden bg-slate-50 rounded-xl p-3 border border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex-1 pr-3">
                                <label class="text-sm font-bold text-slate-700 block">
                                    Batas Percobaan (Generasi)
                                </label>
                                <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">
                                    Ibarat turnamen perbaikan jadwal. Makin banyak babak (angka besar) = jadwal akhir makin matang dan minim bentrok.
                                </p>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <input type="number" id="num-generasi" min="10" max="1000" value="200"
                                    class="w-14 text-center border border-slate-200 rounded-md px-1.5 py-1 text-sm font-bold text-blue-600 outline-none focus:ring-2 focus:ring-blue-400 bg-white"
                                    oninput="syncSlider('generasi', this.value)">
                            </div>
                        </div>
                        <input type="range" id="range-generasi" min="10" max="1000" step="10" value="200"
                            class="w-full h-1.5 rounded-full accent-blue-600 cursor-pointer mb-1"
                            oninput="syncNumber('generasi', this.value)">
                    </div>
                </div>
            </div>

            {{-- Card: Constraint Aktif --}}
            <div class="hidden bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <button type="button" onclick="toggleCollapse('constraint')"
                    class="w-full px-5 py-3.5 flex items-center justify-between text-left hover:bg-slate-50 transition">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-sm font-semibold text-slate-700">Constraint Aktif</span>
                        <span class="bg-slate-100 text-slate-600 text-xs font-medium px-2 py-0.5 rounded-full">14 HC · 10 SC</span>
                    </div>
                    <svg id="chevron-constraint" class="w-4 h-4 text-slate-400 transition-transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div id="collapse-constraint" class="collapse-body" style="max-height:none">
                    <div class="px-5 pb-4 space-y-3">

                        {{-- Hard Constraints - Compact Grid --}}
                        <div>
                            <p class="text-xs font-semibold text-red-600 mb-2 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                Hard Constraints
                            </p>
                            <div class="grid grid-cols-2 gap-1.5">
                                @foreach([
                                    ['HC1', 'Dosen bentrok waktu'],
                                    ['HC2', 'Ruangan bentrok waktu'],
                                    ['HC3', 'Slot valid'],
                                    ['HC4', '1 kelas 1 ruang'],
                                    ['HC5', '1 dosen 1 matkul/slot'],
                                    ['HC6', 'Dosen wajib ada'],
                                    ['HC7', 'Durasi = SKS'],
                                    ['HC8', 'No overlap'],
                                    ['HC9', 'Slot ≤ tersedia'],
                                    ['HC10', 'Ruangan terdaftar'],
                                    ['HC11', 'Paralel no bentrok'],
                                    ['HC12', 'Kelas sore di malam'],
                                    ['HC13', 'Lab untuk praktikum'],
                                    ['HC14', 'Dosen ≤ 8 SKS/hari'],
                                ] as [$kode, $label])
                                <div class="flex items-center gap-1.5 text-xs text-slate-600 bg-red-50/50 rounded-md px-2 py-1.5 border border-red-100/50">
                                    <span class="bg-red-500 text-white text-[10px] font-bold px-1 py-0.5 rounded flex-shrink-0">{{ $kode }}</span>
                                    <span class="truncate">{{ $label }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Soft Constraints - Compact Grid --}}
                        <div>
                            <p class="text-xs font-semibold text-blue-600 mb-2 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                Soft Constraints
                            </p>
                            <div class="grid grid-cols-2 gap-1.5">
                                @foreach([
                                    ['SC1', 'Beban dosen merata'],
                                    ['SC2', 'Minimasi gap dosen'],
                                    ['SC3', 'Dosen ≤ 3 kelas/hari'],
                                    ['SC4', 'Minimasi ganti ruang'],
                                    ['SC5', 'Kapasitas ruang pas'],
                                    ['SC6', 'Penggunaan ruang merata'],
                                    ['SC11', 'Kelas per hari merata'],
                                    ['SC13', 'No matkul berat berurutan'],
                                    ['SC15', 'Berat bukan slot pagi'],
                                    ['SC17', 'Mulai jam 8 pagi (Slot 1)'],
                                ] as [$kode, $label])
                                <div class="flex items-center gap-1.5 text-xs text-slate-600 bg-blue-50/50 rounded-md px-2 py-1.5 border border-blue-100/50">
                                    <span class="bg-blue-500 text-white text-[10px] font-bold px-1 py-0.5 rounded flex-shrink-0">{{ $kode }}</span>
                                    <span class="truncate">{{ $label }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Disabled Constraints - Minimal --}}
                        <div class="pt-2 border-t border-slate-100">
                            <p class="text-xs text-slate-400 mb-1.5">Dinonaktifkan (konflik)</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach(['SC7', 'SC9', 'SC10', 'SC12'] as $kode)
                                <span class="text-[10px] text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">{{ $kode }}</span>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Tombol Generate --}}
            <button id="btn-generate" onclick="startGA()"
                class="w-full py-4 bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white font-bold rounded-2xl shadow-lg shadow-teal-600/25 flex items-center justify-center gap-3 transition-all duration-200 active:scale-[.98]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span id="btn-label">Jalankan Algoritma Genetika</span>
            </button>
            <input type="hidden" id="adv-early-exit" value="95">
        </div>

        {{-- Kolom kanan: Pengaturan Lanjutan (2/5) --}}
        <div id="right-column" class="hidden lg:col-span-2 space-y-5">

            {{-- Card: Parameter GA Lanjutan --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <button type="button" onclick="toggleCollapse('advanced')"
                    class="w-full px-4 py-3 flex items-center justify-between text-left hover:bg-slate-50 transition">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        <span class="text-sm font-semibold text-slate-700">Pengaturan Lanjutan</span>
                    </div>
                    <svg id="chevron-advanced" class="w-4 h-4 text-slate-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div id="collapse-advanced" class="collapse-body closed" style="max-height:0">
                <div class="px-4 pb-4 space-y-4">

                    {{-- Core Parameters --}}
                    <div class="space-y-2.5">
                        {{-- Crossover Rate --}}
                        <div class="bg-slate-50 rounded-lg p-2.5 border border-slate-100">
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                    🧬 Kombinasi Jadwal <span class="text-[10px] font-normal text-slate-400">(Crossover)</span>
                                </label>
                                <span id="val-crossover" class="text-[11px] font-black text-purple-600">0.80</span>
                            </div>
                            <input type="range" id="adv-crossover" min="0.4" max="1.0" step="0.05" value="0.80"
                                class="w-full h-1.5 rounded-full accent-purple-600 cursor-pointer"
                                oninput="document.getElementById('val-crossover').textContent=parseFloat(this.value).toFixed(2)">
                            <p class="text-[10px] text-slate-500 mt-1 leading-tight">Menciptakan jadwal baru dengan menggabungkan keunggulan 2 draf jadwal.</p>
                        </div>

                        {{-- Mutation Rate --}}
                        <div class="bg-slate-50 rounded-lg p-2.5 border border-slate-100">
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                    ⚡ Variasi Acak <span class="text-[10px] font-normal text-slate-400">(Mutation)</span>
                                </label>
                                <span id="val-mutation" class="text-[11px] font-black text-orange-600">0.10</span>
                            </div>
                            <input type="range" id="adv-mutation" min="0.01" max="0.5" step="0.01" value="0.10"
                                class="w-full h-1.5 rounded-full accent-orange-500 cursor-pointer"
                                oninput="document.getElementById('val-mutation').textContent=parseFloat(this.value).toFixed(2)">
                            <p class="text-[10px] text-slate-500 mt-1 leading-tight">Mengacak sedikit jadwal untuk membuka kemungkinan hasil yang tak terpikirkan.</p>
                        </div>

                        {{-- Elitism --}}
                        <div class="bg-slate-50 rounded-lg p-2.5 border border-slate-100">
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                    🏆 Simpan Terbaik <span class="text-[10px] font-normal text-slate-400">(Elitism)</span>
                                </label>
                                <span id="val-elite" class="text-[11px] font-black text-teal-600">3</span>
                            </div>
                            <input type="range" id="adv-elite" min="1" max="10" step="1" value="3"
                                class="w-full h-1.5 rounded-full accent-teal-600 cursor-pointer"
                                oninput="document.getElementById('val-elite').textContent=this.value">
                            <p class="text-[10px] text-slate-500 mt-1 leading-tight">Mengamankan draf jadwal paling bagus agar tak sengaja rusak di putaran berikutnya.</p>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-3 space-y-2.5">
                        <p class="text-[11px] font-bold text-slate-700 flex items-center gap-1.5">
                            🛡️ Strategi Pencegah Kebuntuan
                        </p>

                        {{-- Stagnation --}}
                        <div class="bg-slate-50 rounded-lg p-2.5 border border-slate-100">
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                    ⏱️ Batas Sabar <span class="text-[10px] font-normal text-slate-400">(Stagnasi)</span>
                                </label>
                                <span id="val-stagnation" class="text-[11px] font-black text-red-500">10</span>
                            </div>
                            <input type="range" id="adv-stagnation" min="5" max="50" step="5" value="10"
                                class="w-full h-1.5 rounded-full accent-red-500 cursor-pointer"
                                oninput="document.getElementById('val-stagnation').textContent=this.value">
                            <p class="text-[10px] text-slate-500 mt-1 leading-tight">Merombak strategi pencarian otomatis jika jadwal tidak kunjung membaik.</p>
                        </div>

                        {{-- SA Temperature --}}
                        <div class="bg-slate-50 rounded-lg p-2.5 border border-slate-100">
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                    🌡️ Toleransi Mundur <span class="text-[10px] font-normal text-slate-400">(SA Temp)</span>
                                </label>
                                <span id="val-temp" class="text-[11px] font-black text-amber-600">5.0</span>
                            </div>
                            <input type="range" id="adv-temp" min="1" max="20" step="1" value="5"
                                class="w-full h-1.5 rounded-full accent-amber-500 cursor-pointer"
                                oninput="document.getElementById('val-temp').textContent=this.value+'.0'">
                            <p class="text-[10px] text-slate-500 mt-1 leading-tight">Keberanian mencoba jadwal yang sedikit lebih jelek demi melompat ke hasil yang jauh lebih baik.</p>
                        </div>
                    </div>

                    <div class="pt-2 flex justify-between items-center">
                        <span class="text-[10px] text-slate-400">⚙️ Parameter sudah optimal</span>
                        <button type="button" onclick="resetAdvanced()"
                            class="text-xs text-slate-500 hover:text-slate-700 font-bold transition">
                            Reset ke Bawaan
                        </button>
                    </div>
                </div>
                </div>
            </div>

            {{-- Card: Cara Membaca Hasil --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-700">📊 Cara Membaca Hasil</h3>
                </div>
                <div class="px-5 py-4 space-y-3 text-xs text-slate-600">
                    <div class="flex gap-3">
                        <span class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0 mt-1.5"></span>
                        <div><strong>Fitness ≥ 90%</strong> — Jadwal sangat baik, siap disimpan. Kemungkinan tidak ada bentrok.</div>
                    </div>
                    <div class="flex gap-3">
                        <span class="w-2 h-2 rounded-full bg-yellow-400 flex-shrink-0 mt-1.5"></span>
                        <div><strong>Fitness 70–89%</strong> — Jadwal cukup baik. Mungkin ada 1–2 bentrok, disarankan review manual.</div>
                    </div>
                    <div class="flex gap-3">
                        <span class="w-2 h-2 rounded-full bg-red-400 flex-shrink-0 mt-1.5"></span>
                        <div><strong>Fitness &lt; 70%</strong> — Masih banyak bentrok. Coba naikkan populasi/generasi atau gunakan preset Optimal.</div>
                    </div>
                    <div class="flex gap-3">
                        <span class="w-2 h-2 rounded-full bg-teal-500 flex-shrink-0 mt-1.5"></span>
                        <div><strong>Selesai Lebih Awal</strong> — Sistem menemukan jadwal optimal sebelum semua generasi habis. Tidak perlu khawatir.</div>
                    </div>
                    <div class="border-t border-slate-100 pt-3 text-slate-400 leading-relaxed">
                        <strong>Konflik Hard</strong> (D = Dosen, R = Ruangan) harus 0 sebelum jadwal layak disimpan.
                        <strong>Soft Violation</strong> adalah pelanggaran preferensi yang masih bisa diterima.
                    </div>
                </div>
            </div>

            {{-- Card: Info Algoritma --}}
            <div class="bg-gradient-to-br from-teal-600 to-teal-800 rounded-2xl p-5 text-white">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-2xl">🧬</span>
                    <h3 class="font-bold text-sm">Cara Kerja Sistem</h3>
                </div>
                <ol class="text-xs text-teal-100 leading-relaxed space-y-2 list-none">
                    <li class="flex gap-2"><span class="bg-white/20 rounded-full w-4 h-4 flex items-center justify-center text-white font-bold flex-shrink-0 text-xs mt-0.5">1</span><span>Buat ratusan jadwal acak sesuai data kelas &amp; dosen yang ada</span></li>
                    <li class="flex gap-2"><span class="bg-white/20 rounded-full w-4 h-4 flex items-center justify-center text-white font-bold flex-shrink-0 text-xs mt-0.5">2</span><span>Nilai setiap jadwal: semakin sedikit bentrok, semakin tinggi nilainya</span></li>
                    <li class="flex gap-2"><span class="bg-white/20 rounded-full w-4 h-4 flex items-center justify-center text-white font-bold flex-shrink-0 text-xs mt-0.5">3</span><span>Pilih jadwal terbaik, gabungkan bagian-bagian terbaiknya</span></li>
                    <li class="flex gap-2"><span class="bg-white/20 rounded-full w-4 h-4 flex items-center justify-center text-white font-bold flex-shrink-0 text-xs mt-0.5">4</span><span>Ulangi ratusan kali hingga tidak ada lagi bentrok</span></li>
                    <li class="flex gap-2"><span class="bg-white/20 rounded-full w-4 h-4 flex items-center justify-center text-white font-bold flex-shrink-0 text-xs mt-0.5">5</span><span>Hasil bisa langsung disimpan atau diedit manual</span></li>
                </ol>
            </div>
        </div>

    </div>{{-- end grid form --}}

    {{-- ═══════════════════════════════════════════════════
         SECTION PROGRESS
    ══════════════════════════════════════════════════════ --}}
    <div id="section-progress" class="hidden space-y-4">

        {{-- Status bar --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div id="dot-pulse" class="w-2.5 h-2.5 rounded-full bg-teal-500 animate-pulse"></div>
                    <span class="font-bold text-slate-700" id="progress-title">Memproses…</span>
                </div>
                <div class="flex items-center gap-3">
                    <span id="progress-eta" class="text-xs text-slate-400"></span>
                    <span class="text-sm font-bold text-teal-600" id="progress-pct-label">0%</span>
                    <button type="button" onclick="cancelGA()"
                        class="text-xs text-red-400 hover:text-red-600 border border-red-200 hover:border-red-400 px-3 py-1 rounded-lg transition">
                        Batalkan
                    </button>
                </div>
            </div>

            {{-- Progress bar gradient --}}
            <div class="px-6 py-3 bg-slate-50 border-b border-slate-100">
                <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                    <div id="progress-bar"
                        class="h-3 rounded-full bg-gradient-to-r from-teal-400 to-teal-600"
                        style="width:0%"></div>
                </div>
            </div>

            {{-- 4 stat cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-5 bg-slate-50/50">
                <div class="bg-white rounded-xl p-4 border border-slate-200/60 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Tahap Proses</p>
                        <p class="text-2xl font-black text-blue-600 mt-1 leading-none" id="stat-gen">0</p>
                        <p class="text-[10px] text-slate-400 mt-1.5" id="stat-gen-max">dari 200</p>
                    </div>
                    <div class="p-2 rounded-lg bg-blue-50 text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3 3L22 4"/></svg>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-4 border border-slate-200/60 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Kualitas Jadwal</p>
                        <p class="text-2xl font-black text-teal-600 mt-1 leading-none" id="stat-fitness">0%</p>
                        <p class="text-[10px] text-slate-400 mt-1.5" id="stat-fitness-delta"></p>
                    </div>
                    <div class="p-2 rounded-lg bg-teal-50 text-teal-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-4 border border-slate-200/60 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Bentrok Jadwal</p>
                        <p class="text-2xl font-black text-rose-500 mt-1 leading-none" id="stat-hard">—</p>
                        <p class="text-[10px] text-slate-400 mt-1.5" id="stat-hard-detail"></p>
                    </div>
                    <div class="p-2 rounded-lg bg-rose-50 text-rose-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-4 border border-slate-200/60 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Peringatan</p>
                        <p class="text-2xl font-black text-amber-500 mt-1 leading-none" id="stat-soft">—</p>
                        <p class="text-[10px] text-slate-400 mt-1.5">hal yang kurang ideal</p>
                    </div>
                    <div class="p-2 rounded-lg bg-amber-50 text-amber-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart + Log --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Fitness Chart --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Grafik Peningkatan Kualitas</p>
                <canvas id="fitness-chart"></canvas>
            </div>

            {{-- Log Generasi --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Log Generasi</p>
                    <button type="button" onclick="clearLog()" class="text-xs text-slate-400 hover:text-slate-600 transition">Bersihkan</button>
                </div>
                <div id="log-container"
                    class="font-mono text-xs p-4 h-48 overflow-y-auto space-y-0.5 bg-slate-950 text-slate-300">
                    <div class="text-slate-500 italic">Menunggu data generasi…</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         SECTION HASIL
    ══════════════════════════════════════════════════════ --}}
    <div id="section-hasil" class="hidden space-y-5">

        {{-- Summary header --}}
        <div id="result-summary" class="rounded-2xl border-2 p-6 flex flex-col sm:flex-row sm:items-center gap-5">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <span id="result-icon" class="text-3xl"></span>
                    <div>
                        <h2 class="text-xl font-black" id="result-title"></h2>
                        <p class="text-sm" id="result-subtitle"></p>
                    </div>
                </div>
                <div class="w-full bg-white/40 rounded-full h-4 overflow-hidden mt-3">
                    <div id="fitness-fill" class="h-4 rounded-full" style="width:0%"></div>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="bg-white/50 rounded-xl p-3">
                    <p class="text-xs opacity-60">Kualitas</p>
                    <p class="text-lg font-black" id="hasil-stat-fitness">—</p>
                </div>
                <div class="bg-white/50 rounded-xl p-3">
                    <p class="text-xs opacity-60">Tahap</p>
                    <p class="text-lg font-black" id="hasil-stat-gen">—</p>
                </div>
                <div class="bg-white/50 rounded-xl p-3">
                    <p class="text-xs opacity-60">Kelas</p>
                    <p class="text-lg font-black" id="hasil-stat-kelas">—</p>
                </div>
            </div>
        </div>

        {{-- Breakdown conflict --}}
        <div class="grid grid-cols-3 gap-6" id="conflict-breakdown">
            <div class="group bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:shadow-md hover:border-slate-300 transition-all duration-300 flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Bentrok Dosen</p>
                    <p class="text-2xl font-black text-rose-500 mt-1.5 tracking-tight" id="bd-dosen">0</p>
                    <p class="text-[10px] text-slate-400 mt-1 font-medium">Bentrok Jadwal</p>
                </div>
                <div class="p-3 rounded-xl bg-rose-50 text-rose-500 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
            </div>
            <div class="group bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:shadow-md hover:border-slate-300 transition-all duration-300 flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Bentrok Ruangan</p>
                    <p class="text-2xl font-black text-amber-500 mt-1.5 tracking-tight" id="bd-ruangan">0</p>
                    <p class="text-[10px] text-slate-400 mt-1 font-medium">Bentrok Ruang</p>
                </div>
                <div class="p-3 rounded-xl bg-amber-50 text-amber-500 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
            <div class="group bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:shadow-md hover:border-slate-300 transition-all duration-300 flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Peringatan Lunak</p>
                    <p class="text-2xl font-black text-indigo-500 mt-1.5 tracking-tight" id="bd-soft">0</p>
                    <p class="text-[10px] text-slate-400 mt-1 font-medium">Pelanggaran Lunak</p>
                </div>
                <div class="p-3 rounded-xl bg-indigo-50 text-indigo-500 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        {{-- Diagnosa Masalah (Bahasa Manusia) --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" id="diagnosa-card" style="display:none">
            <div class="px-6 py-4 bg-rose-50/50 border-b border-slate-100 flex items-center gap-3">
                <div class="p-2 bg-rose-100 rounded-xl text-rose-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-black text-slate-800 text-sm">Diagnosis Masalah Penjadwalan</h3>
                    <p class="text-xs text-slate-500 font-medium">Beberapa aturan bentrok fisik/jenis kelas masih belum terpenuhi secara otomatis</p>
                </div>
            </div>
            <div class="p-6">
                <div id="diagnosa-list" class="space-y-2.5 max-h-80 overflow-y-auto pr-2">
                    <!-- list item didiagnosa -->
                </div>
            </div>
        </div>

        {{-- Filter + Tabel --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center gap-3">
                <h3 class="font-bold text-slate-700 flex-1">Preview Jadwal</h3>
                <div class="flex items-center gap-2">
                    <input type="text" id="filter-tabel" placeholder="Cari kelas / dosen…"
                        oninput="filterTable(this.value)"
                        class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 outline-none focus:ring-2 focus:ring-teal-400 w-44">
                    <select id="filter-hari" onchange="filterTable()"
                        class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 outline-none focus:ring-2 focus:ring-teal-400">
                        <option value="">Semua Hari</option>
                        <option>Senin</option><option>Selasa</option><option>Rabu</option>
                        <option>Kamis</option><option>Jumat</option>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-800 text-slate-100 text-xs uppercase tracking-wide">
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
                    <tbody id="hasil-tbody" class="divide-y divide-slate-100"></tbody>
                </table>
                <div id="tabel-empty" class="hidden py-10 text-center text-slate-400 text-sm">Tidak ada data yang cocok.</div>
            </div>
            <div class="px-6 py-3 border-t border-slate-100 text-xs text-slate-400" id="tabel-count"></div>
        </div>

        {{-- Problem Log (collapsible) --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" id="problem-log-card" style="display:none">
            <button type="button" onclick="toggleCollapse('problog')"
                class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-slate-50 transition">
                <span class="text-sm font-bold text-slate-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Internal Problem Log GA
                </span>
                <svg id="chevron-problog" class="w-4 h-4 text-slate-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div id="collapse-problog" class="collapse-body closed" style="max-height:0">
                <div class="px-6 pb-5">
                    <div id="problem-log-list" class="font-mono text-xs space-y-1 bg-slate-950 text-slate-300 rounded-xl p-4 max-h-48 overflow-y-auto"></div>
                </div>
            </div>
        </div>

        {{-- Action buttons --}}
        <form id="form-simpan" method="POST" action="{{ route('jadwal.otomatis.simpan') }}">
            @csrf
            <input type="hidden" name="jadwal_json" id="inp-jadwal-json">
            <input type="hidden" name="tahun_akademik_id" id="inp-tahun-hidden">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <button type="button" onclick="resetForm()"
                    class="flex-1 sm:flex-none px-6 py-3 bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 rounded-xl font-semibold flex items-center justify-center gap-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Ulangi Generate
                </button>
                <button type="button" onclick="openTrialModal()"
                    class="flex-1 py-3 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold rounded-xl shadow-lg shadow-amber-600/25 flex items-center justify-center gap-2 transition-all active:scale-[.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    Simpan Sementara (Trial Run)
                </button>
                <button type="submit"
                    class="flex-1 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold rounded-xl shadow-lg shadow-green-600/25 flex items-center justify-center gap-2 transition-all active:scale-[.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan & Buka di Workspace Manual
                </button>
            </div>
        </form>

        <!-- Modal Simpan Sementara -->
        <div id="trial-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeTrialModal()"></div>
            <!-- Content -->
            <div class="relative bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-md p-6 m-4 transform scale-95 transition-all duration-300">
                <h3 class="text-lg font-bold text-slate-800 mb-2">Simpan Sementara (Trial Run)</h3>
                <p class="text-xs text-slate-500 mb-4">
                    Simpan hasil uji coba ini untuk dibandingkan dengan hasil lainnya. Jadwal ini tidak akan menimpa worksheet utama sampai Anda menerapkannya secara eksplisit.
                </p>
                
                <form id="form-simpan-trial" method="POST" action="{{ route('jadwal.otomatis.simpan_trial') }}">
                    @csrf
                    <input type="hidden" name="jadwal_json" id="trial-jadwal-json">
                    <input type="hidden" name="tahun_akademik_id" id="trial-tahun-hidden">
                    <input type="hidden" name="fitness" id="trial-fitness">
                    <input type="hidden" name="generasi" id="trial-generasi">
                    <input type="hidden" name="total_kelas" id="trial-total-kelas">
                    <input type="hidden" name="dosen_conflicts" id="trial-dosen-conflicts">
                    <input type="hidden" name="ruangan_conflicts" id="trial-ruangan-conflicts">
                    <input type="hidden" name="soft_violations" id="trial-soft-violations">

                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Label Uji Coba</label>
                        <input type="text" name="label" id="inp-trial-label" placeholder="Contoh: Uji Coba #1 (Pop 100, Gen 200)"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none text-sm text-slate-700 transition">
                    </div>
                    
                    <div class="flex gap-3 justify-end">
                        <button type="button" onclick="closeTrialModal()"
                            class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition text-sm">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold transition shadow-lg shadow-amber-500/30 text-sm">
                            Simpan Uji Coba
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>{{-- end section-hasil --}}

    </div>{{-- end max-w --}}
    </div>{{-- end px padding --}}
</main>

<script>
// ─────────────────────────────────────────────────
// State
// ─────────────────────────────────────────────────
let maxGen = 200, bestFitnessPrev = 0, prevFitness = 0;
let fitnessHistory = [];
let activeES = null;
let startTime = null;
let allRows = [];
let lastGAData = null;

let loadingIntervalId = null;
const loadingMessages = [
    "Memproses Algoritma Genetika...",
    "Mengevaluasi populasi kelas...",
    "Melakukan persilangan (crossover)...",
    "Melakukan mutasi cerdas...",
    "Menghindari bentrok dosen...",
    "Menyusun jadwal ruangan...",
    "Menghitung skor kebugaran (fitness)...",
    "Menyesuaikan kapasitas kelas...",
    "Mengurangi jam kosong dosen..."
];

// ─────────────────────────────────────────────────
// Slider helpers
// ─────────────────────────────────────────────────
function syncNumber(id, v) {
    document.getElementById('num-' + id).value = v;
    document.getElementById('range-' + id).value = v;
}
function syncSlider(id, v) {
    document.getElementById('range-' + id).value = v;
    document.getElementById('num-' + id).value = v;
}

let currentPresetMode = 'normal';

function applyPreset(key) {
    currentPresetMode = key;
    const isCustom = key === 'custom';
    const containerPop = document.getElementById('container-populasi');
    const containerGen = document.getElementById('container-generasi');
    const sectionForm = document.getElementById('section-form');
    const leftColumn = document.getElementById('left-column');
    const rightColumn = document.getElementById('right-column');

    if (isCustom) {
        if (containerPop) containerPop.classList.remove('hidden');
        if (containerGen) containerGen.classList.remove('hidden');
        if (sectionForm) sectionForm.className = "grid grid-cols-1 lg:grid-cols-5 gap-6 max-w-5xl mx-auto items-start space-y-6 lg:space-y-0";
        if (leftColumn) leftColumn.className = "lg:col-span-3 space-y-5";
        if (rightColumn) rightColumn.classList.remove('hidden');
    } else {
        if (containerPop) containerPop.classList.add('hidden');
        if (containerGen) containerGen.classList.add('hidden');
        if (sectionForm) sectionForm.className = "max-w-3xl mx-auto space-y-6";
        if (leftColumn) leftColumn.className = "space-y-5";
        if (rightColumn) rightColumn.classList.add('hidden');

        let pop = 100, gen = 300;
        if (key === 'cepat') { pop = 40; gen = 100; }
        else if (key === 'normal') { pop = 80; gen = 300; }
        else if (key === 'akurat') { pop = 150; gen = 500; }

        syncSlider('populasi', pop);
        syncSlider('generasi', gen);
    }

    document.querySelectorAll('.preset-btn').forEach(b => {
        const isActive = b.dataset.preset === key;
        b.className = b.className
            .replace(/border-(teal|slate|amber|emerald|blue|purple)-\d+/g, '')
            .replace(/bg-(teal|slate|amber|emerald|blue|purple)-\d+/g, '')
            .replace(/text-(teal|slate|amber|emerald|blue|purple)-\d+/g, '')
            .replace(/shadow-sm/g, '');
        
        if (isActive) {
            if (key === 'cepat') b.classList.add('border-emerald-500','bg-emerald-50','text-emerald-800','shadow-sm');
            else if (key === 'normal') b.classList.add('border-blue-500','bg-blue-50','text-blue-800','shadow-sm');
            else if (key === 'akurat') b.classList.add('border-purple-500','bg-purple-50','text-purple-800','shadow-sm');
            else if (key === 'custom') b.classList.add('border-amber-400','bg-amber-50','text-amber-700','shadow-sm');
        } else {
            b.classList.add('border-slate-200','bg-slate-50','text-slate-600');
        }
    });
}

// ─────────────────────────────────────────────────
// Collapse / expand
// ─────────────────────────────────────────────────
function toggleCollapse(id) {
    const el  = document.getElementById('collapse-' + id);
    const chv = document.getElementById('chevron-' + id);
    const open = !el.classList.contains('closed');
    if (open) {
        if (!el.style.maxHeight || el.style.maxHeight === 'none') {
            el.style.maxHeight = el.scrollHeight + 'px';
            el.offsetHeight; // force reflow
        }
        el.classList.add('closed');
        el.style.maxHeight = '0';
        chv && chv.classList.remove('rotate-180');
    } else {
        el.classList.remove('closed');
        el.style.maxHeight = el.scrollHeight + 'px';
        chv && chv.classList.add('rotate-180');

        const handleTransitionEnd = (e) => {
            if (e.propertyName === 'max-height' && !el.classList.contains('closed')) {
                el.style.maxHeight = 'none';
                el.removeEventListener('transitionend', handleTransitionEnd);
            }
        };
        el.addEventListener('transitionend', handleTransitionEnd);
    }
}

// ─────────────────────────────────────────────────
// Fitness mini-chart (canvas)
// ─────────────────────────────────────────────────
function drawChart() {
    const canvas = document.getElementById('fitness-chart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const W = canvas.offsetWidth; const H = 100;
    canvas.width = W; canvas.height = H;
    ctx.clearRect(0, 0, W, H);

    const data = fitnessHistory;
    if (data.length < 2) {
        ctx.fillStyle = '#e2e8f0';
        ctx.fillRect(0, H-2, W, 2);
        return;
    }
    const max = Math.max(...data, 1);
    const pts = data.map((v, i) => [
        (i / (data.length - 1)) * W,
        H - (v / max) * (H - 10) - 2
    ]);

    // Fill
    ctx.beginPath();
    ctx.moveTo(0, H);
    pts.forEach(([x,y]) => ctx.lineTo(x, y));
    ctx.lineTo(W, H);
    ctx.closePath();
    const grad = ctx.createLinearGradient(0, 0, 0, H);
    grad.addColorStop(0, 'rgba(20,184,166,.35)');
    grad.addColorStop(1, 'rgba(20,184,166,.02)');
    ctx.fillStyle = grad;
    ctx.fill();

    // Line
    ctx.beginPath();
    pts.forEach(([x,y], i) => i === 0 ? ctx.moveTo(x,y) : ctx.lineTo(x,y));
    ctx.strokeStyle = '#0d9488';
    ctx.lineWidth = 2;
    ctx.stroke();
}

// ─────────────────────────────────────────────────
// Start GA
// ─────────────────────────────────────────────────
function startGA() {
    const tahun    = document.getElementById('inp-tahun').value;
    const populasi = document.getElementById('num-populasi').value;
    const generasi = document.getElementById('num-generasi').value;
    
    // Custom parameters
    const crossover  = document.getElementById('adv-crossover').value;
    const mutation   = document.getElementById('adv-mutation').value;
    const elite      = document.getElementById('adv-elite').value;
    const stagnation = document.getElementById('adv-stagnation').value;
    const temp       = document.getElementById('adv-temp').value;
    const earlyExit  = document.getElementById('adv-early-exit').value;

    if (!tahun) {
        shakeEl('inp-tahun');
        return;
    }

    // Ubah status tombol untuk menunjukkan sedang audit data
    const btn = document.getElementById('btn-generate');
    btn.disabled = true;
    document.getElementById('btn-label').textContent = 'Menganalisis kelayakan data…';

    // Panggil Pre-run Feasibility Audit
    fetch(`{{ url('/jadwal-otomatis/audit') }}?tahun_akademik_id=${tahun}`)
        .then(res => res.json())
        .then(data => {
            if (!data.feasible && data.issues && data.issues.some(i => i.type === 'fatal')) {
                // Ada isu fatal, blokir jalannya GA
                let msg = '<div class="text-left space-y-2 text-sm mt-2">';
                data.issues.forEach(i => {
                    if (i.type === 'fatal') {
                        msg += `<div class="p-3 rounded-xl bg-rose-50 text-rose-800 border border-rose-200/60 font-medium">❌ ${i.message}</div>`;
                    }
                });
                msg += '</div>';

                showAuditModal('Gagal Audit Kelayakan', msg, false);
                resetGenerateButton();
                return;
            }

            if (data.issues && data.issues.some(i => i.type === 'warning')) {
                // Ada isu warning, tanyakan persetujuan user
                let msg = '<div class="text-left space-y-2 text-sm mt-2">';
                data.issues.forEach(i => {
                    if (i.type === 'warning') {
                        msg += `<div class="p-3 rounded-xl bg-amber-50 text-amber-800 border border-amber-200/60 font-medium">⚠️ ${i.message}</div>`;
                    }
                });
                msg += '</div>';
                
                showAuditModal('Peringatan Audit Kelayakan', msg, true, () => {
                    runGAProcess(tahun, populasi, generasi, crossover, mutation, elite, stagnation, temp, earlyExit);
                });
                resetGenerateButton();
                return;
            }

            // Layak & tanpa issue, jalankan GA langsung
            runGAProcess(tahun, populasi, generasi, crossover, mutation, elite, stagnation, temp, earlyExit);
        })
        .catch(err => {
            console.error('Audit error:', err);
            // Fallback: jalankan saja GA jika audit endpoint gagal demi toleransi kesalahan
            runGAProcess(tahun, populasi, generasi, crossover, mutation, elite, stagnation, temp, earlyExit);
        });
}

function resetGenerateButton() {
    const btn = document.getElementById('btn-generate');
    btn.disabled = false;
    document.getElementById('btn-label').textContent = 'Jalankan Algoritma Genetika';
}

function showAuditModal(title, contentHtml, showConfirm = false, onConfirm = null) {
    // Hapus modal lama jika ada
    const existing = document.getElementById('audit-modal');
    if (existing) existing.remove();

    const modal = document.createElement('div');
    modal.id = 'audit-modal';
    modal.className = 'fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300';
    
    const confirmButtonHtml = showConfirm 
        ? `<button type="button" id="btn-modal-confirm" class="flex-1 px-5 py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-2xl transition shadow-lg shadow-teal-600/20">Tetap Lanjutkan</button>`
        : '';
        
    const closeButtonLabel = showConfirm ? 'Batal' : 'Tutup';
    const closeButtonClass = showConfirm 
        ? 'flex-1 px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-2xl transition border border-slate-200'
        : 'w-full px-5 py-3 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-2xl transition';

    modal.innerHTML = `
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden transform scale-95 transition-all duration-300">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    ${showConfirm ? '⚠️' : '❌'} ${title}
                </h3>
                <button type="button" id="btn-modal-x" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                <p class="text-sm text-slate-600 leading-relaxed">
                    ${showConfirm 
                        ? 'Sistem mendeteksi beberapa potensi masalah pada data masukan Anda. Meskipun penjadwalan masih dapat dilanjutkan, hasil akhirnya berpotensi tidak 100% optimal:' 
                        : 'Sistem mendeteksi masalah kelayakan yang bersifat fatal pada data masukan Anda. Secara matematis, jadwal **tidak mungkin dapat disusun** tanpa pelanggaran berikut:'}
                </p>
                ${contentHtml}
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center gap-3">
                <button type="button" id="btn-modal-close" class="${closeButtonClass}">${closeButtonLabel}</button>
                ${confirmButtonHtml}
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Animasi fade in & scale
    setTimeout(() => {
        modal.firstElementChild.classList.remove('scale-95');
        modal.firstElementChild.classList.add('scale-100');
    }, 10);

    const closeModal = () => {
        modal.firstElementChild.classList.remove('scale-100');
        modal.firstElementChild.classList.add('scale-95');
        modal.classList.add('opacity-0');
        setTimeout(() => modal.remove(), 300);
    };

    document.getElementById('btn-modal-x').onclick = closeModal;
    document.getElementById('btn-modal-close').onclick = closeModal;
    
    if (showConfirm && onConfirm) {
        document.getElementById('btn-modal-confirm').onclick = () => {
            closeModal();
            onConfirm();
        };
    }
}

function runGAProcess(tahun, populasi, generasi, crossover = null, mutation = null, elite = null, stagnation = null, temp = null, earlyExit = null) {
    maxGen = parseInt(generasi);
    bestFitnessPrev = 0; prevFitness = 0;
    fitnessHistory = [];
    startTime = Date.now();
    allRows = [];

    // Reset UI
    document.getElementById('section-progress').classList.remove('hidden');
    document.getElementById('section-form').classList.add('opacity-60','pointer-events-none');
    document.getElementById('section-hasil').classList.add('hidden');
    document.getElementById('log-container').innerHTML = '<div class="text-slate-500 italic">Memulai proses…</div>';
    document.getElementById('stat-gen-max').textContent = 'dari ' + generasi;
    document.getElementById('stat-gen').textContent     = '0';
    document.getElementById('stat-fitness').textContent = '0%';
    document.getElementById('stat-fitness-delta').textContent = '';
    document.getElementById('stat-hard').textContent    = '—';
    document.getElementById('stat-hard-detail').textContent = '';
    document.getElementById('stat-soft').textContent    = '—';
    document.getElementById('progress-bar').style.width = '0%';
    document.getElementById('progress-pct-label').textContent = '0%';
    document.getElementById('progress-title').textContent = 'Memproses Algoritma Genetika...';
    document.getElementById('progress-eta').textContent  = '';
    document.getElementById('dot-pulse').className = 'w-2.5 h-2.5 rounded-full bg-teal-500 animate-pulse';

    const btn = document.getElementById('btn-generate');
    btn.disabled = true;
    document.getElementById('btn-label').textContent = 'Sedang memproses…';

    let url = `{{ route('jadwal.otomatis.stream') }}?tahun_akademik_id=${tahun}&populasi=${populasi}&generasi=${generasi}`;
    if (crossover !== null)  url += `&crossover=${crossover}`;
    if (mutation !== null)   url += `&mutation=${mutation}`;
    if (elite !== null)      url += `&elite=${elite}`;
    if (stagnation !== null) url += `&stagnation=${stagnation}`;
    if (temp !== null)       url += `&temp=${temp}`;
    if (earlyExit !== null)  url += `&early_exit=${earlyExit}`;
    activeES = new EventSource(url);

    activeES.onmessage = function(e) {
        const data = JSON.parse(e.data);

        if (data.done) {
            activeES.close();
            activeES = null;
            
            // Tampilkan badge early-exit jika GA selesai sebelum generasi penuh
            if (data.early_exit) {
                const reasonMap = {
                    perfect:       '✅ Solusi sempurna ditemukan!',
                    acceptable:    '✅ Solusi optimal ditemukan!',
                    excellent:     '🎯 Solusi sangat baik ditemukan!',
                    error_fallback:'⚠ Proses berhenti, hasil terbaik ditampilkan.',
                    fatal_error:   '❌ Terjadi error, hasil terbaik ditampilkan.',
                };
                const msg = reasonMap[data.early_reason] || '✅ Hasil optimal ditemukan!';
                document.getElementById('progress-title').textContent = msg;
            }

            onSelesai(data);
            return;
        }

        onProgress(data);
    };
    activeES.onerror = function() {
        if (activeES) { activeES.close(); activeES = null; }
        const hasilPanel = document.getElementById('section-hasil');
        if (hasilPanel && !hasilPanel.classList.contains('hidden')) {
            return;
        }
        onError('Koneksi ke server terputus. Coba jalankan ulang.');
    };
}

function cancelGA() {
    if (activeES) { activeES.close(); activeES = null; }
    onError('Proses dibatalkan oleh pengguna.');
}

// ─────────────────────────────────────────────────
// Progress handler
// ─────────────────────────────────────────────────
function onProgress(data) {
    const pct = Math.round((data.gen / maxGen) * 100);
    const dc  = data.dosen_konflik   || 0;
    const rc  = data.ruangan_konflik || 0;
    const kc  = data.kelas_konflik   || 0;
    const sv  = data.soft_violations || 0;
    const fit = parseFloat(data.fitness) || 0;

    document.getElementById('stat-gen').textContent     = data.gen;
    document.getElementById('stat-fitness').textContent = fit + '%';
    
    document.getElementById('stat-hard').textContent    = dc + rc + kc;
    document.getElementById('stat-hard-detail').textContent = `D:${dc} R:${rc} K:${kc}`;
    document.getElementById('stat-soft').textContent    = sv;
    document.getElementById('progress-bar').style.width = pct + '%';
    document.getElementById('progress-pct-label').textContent = pct + '%';

    // ETA
    if (data.gen > 5 && startTime) {
        const elapsed = (Date.now() - startTime) / 1000;
        const eta = Math.round(elapsed / data.gen * (maxGen - data.gen));
        document.getElementById('progress-eta').textContent = eta > 0 ? `~${eta}s` : '';
    }

    const improved = fit > bestFitnessPrev;
    const delta = fit - prevFitness;
    if (improved) {
        document.getElementById('stat-fitness-delta').textContent = '+' + (fit - bestFitnessPrev).toFixed(2) + '%';
        bestFitnessPrev = fit;
    }
    prevFitness = fit;

    fitnessHistory.push(fit);
    if (fitnessHistory.length > 200) fitnessHistory.shift();
    drawChart();

    // Log line
    const log = document.getElementById('log-container');
    if (log.children.length === 1 && log.children[0].classList.contains('italic')) {
        log.innerHTML = '';
    }
    const line = document.createElement('div');
    line.className = 'log-line flex gap-2 ' + (improved ? 'text-teal-400 font-semibold' : '');
    line.innerHTML = `<span class="text-slate-500 w-12 flex-shrink-0">G${String(data.gen).padStart(3,'0')}</span>`
        + `<span class="w-20 flex-shrink-0 ${fit>=90?'text-teal-400':fit>=70?'text-yellow-400':'text-red-400'}">${fit}%</span>`
        + `<span class="text-slate-400">H:${dc+rc} S:${sv}${improved?' <span class="text-teal-300">↑best</span>':''}</span>`;
    log.appendChild(line);

    // Jika GA melaporkan optimal, tambahkan log entry khusus
    if (data.optimal) {
        const optLine = document.createElement('div');
        optLine.className = 'log-line mt-1 px-2 py-1 rounded bg-teal-900/60 text-teal-300 text-xs font-semibold';
        const reasonLabel = {
            perfect:    '🎯 Solusi sempurna ditemukan! Proses dihentikan.',
            acceptable: '✅ Solusi optimal (≥90%) ditemukan! Proses dihentikan.',
            excellent:  '🌟 Fitness ≥95% tanpa konflik. Proses dihentikan.',
        };
        optLine.textContent = reasonLabel[data.optimal_reason] || '✅ Solusi optimal ditemukan!';
        log.appendChild(optLine);
    }

    if (log.children.length > 300) log.removeChild(log.children[0]);
    log.scrollTop = log.scrollHeight;
}

// ─────────────────────────────────────────────────
// Selesai handler
// ─────────────────────────────────────────────────
function onSelesai(data) {
    const fit = parseFloat(data.fitness) || 0;
    const dc  = parseInt(data.dosen_conflicts)   || 0;
    const rc  = parseInt(data.ruangan_conflicts) || 0;
    const sv  = parseInt(data.soft_violations)   || 0;

    lastGAData = data;

    // Update progress panel
    document.getElementById('dot-pulse').className = 'w-2.5 h-2.5 rounded-full bg-green-500';

    // Jika early-exit, progress bar menunjukkan posisi generasi aktual bukan 100%
    if (data.early_exit) {
        const earlyPct = maxGen > 0 ? Math.round((data.generasi / maxGen) * 100) : 100;
        document.getElementById('progress-bar').style.width = earlyPct + '%';
        document.getElementById('progress-pct-label').textContent = earlyPct + '%';
        // Title sudah diset di onmessage handler — jangan timpa
    } else {
        document.getElementById('progress-title').textContent = '✓ Selesai!';
        document.getElementById('progress-bar').style.width = '100%';
        document.getElementById('progress-pct-label').textContent = '100%';
    }

    document.getElementById('progress-eta').textContent = '';
    document.getElementById('stat-gen').textContent  = data.generasi;
    document.getElementById('stat-fitness').textContent = fit + '%';
    document.getElementById('stat-hard').textContent = dc + rc + (data.kelas_konflik || 0);
    document.getElementById('stat-hard-detail').textContent = `D:${dc} R:${rc} K:${data.kelas_konflik || 0}`;
    document.getElementById('stat-soft').textContent = sv;

    fitnessHistory.push(fit);
    drawChart();

    // Re-enable form
    document.getElementById('section-form').classList.remove('opacity-60','pointer-events-none');
    const btn = document.getElementById('btn-generate');
    btn.disabled = false;
    document.getElementById('btn-label').textContent = 'Jalankan Lagi';

    // ── Result summary ──
    const summary = document.getElementById('result-summary');
    const title   = document.getElementById('result-title');
    const sub     = document.getElementById('result-subtitle');
    const icon    = document.getElementById('result-icon');
    const fill    = document.getElementById('fitness-fill');

    fill.style.width = fit + '%';
    document.getElementById('hasil-stat-fitness').textContent = fit + '%';

    // Tampilkan info generasi: jika early-exit, tunjukkan "gen X dari Y"
    const genInfo = data.early_exit
        ? `gen ${data.generasi} dari ${maxGen} (early exit)`
        : `${data.generasi} generasi`;
    document.getElementById('hasil-stat-gen').textContent     = genInfo;
    document.getElementById('hasil-stat-kelas').textContent   = data.total_kelas;
    document.getElementById('bd-dosen').textContent    = dc;
    document.getElementById('bd-ruangan').textContent  = rc;
    document.getElementById('bd-soft').textContent     = sv;

    // Early-exit badge
    const earlyBadge = document.getElementById('early-exit-badge');
    if (earlyBadge) { earlyBadge.remove(); }
    if (data.early_exit) {
        const reasonLabel = {
            perfect:       'Selesai Lebih Awal — Solusi Sempurna',
            acceptable:    'Selesai Lebih Awal — Solusi Optimal',
            excellent:     'Selesai Lebih Awal — Kualitas Sangat Baik',
            error_fallback:'Berhenti Karena Error — Hasil Terbaik',
            fatal_error:   'Error Fatal — Hasil Terbaik',
        };
        const badgeEl = document.createElement('div');
        badgeEl.id = 'early-exit-badge';
        const isPositive = !data.early_reason?.includes('error');
        badgeEl.className = `mt-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold ${
            isPositive ? 'bg-teal-100 text-teal-800 border border-teal-200' : 'bg-amber-100 text-amber-800 border border-amber-200'
        }`;
        badgeEl.innerHTML = `
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="${isPositive ? 'M13 10V3L4 14h7v7l9-11h-7z' : 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'}"/>
            </svg>
            ${reasonLabel[data.early_reason] || 'Selesai Lebih Awal'}
            — gen ${data.generasi}/${maxGen}
        `;
        // Sisipkan setelah hasil-stat-gen
        const genEl = document.getElementById('hasil-stat-gen');
        if (genEl && genEl.parentNode) {
            genEl.parentNode.parentNode.insertAdjacentElement('afterend', badgeEl);
        }
    }

    if (fit >= 90 && dc+rc === 0) {
        summary.className = 'rounded-2xl border-2 p-6 flex flex-col sm:flex-row sm:items-center gap-5 bg-green-50 border-green-200 text-green-900';
        fill.className = 'h-4 rounded-full bg-green-500 transition-all duration-700';
        icon.textContent = '✅';
        title.textContent = 'Jadwal Optimal — ' + fit + '%';
        sub.textContent = data.early_exit
            ? `Solusi sempurna ditemukan di generasi ${data.generasi}. Jadwal siap disimpan.`
            : 'Tidak ada konflik hard. Jadwal siap disimpan.';
    } else if (fit >= 70 || dc+rc === 0) {
        summary.className = 'rounded-2xl border-2 p-6 flex flex-col sm:flex-row sm:items-center gap-5 bg-yellow-50 border-yellow-200 text-yellow-900';
        fill.className = 'h-4 rounded-full bg-yellow-400 transition-all duration-700';
        icon.textContent = '⚠️';
        title.textContent = 'Jadwal Cukup Baik — ' + fit + '%';
        sub.textContent = dc+rc > 0
            ? `Masih ada ${dc+rc} konflik hard. Disarankan review di workspace.`
            : `Ada ${sv} pelanggaran soft constraint.`;
    } else {
        summary.className = 'rounded-2xl border-2 p-6 flex flex-col sm:flex-row sm:items-center gap-5 bg-red-50 border-red-200 text-red-900';
        fill.className = 'h-4 rounded-full bg-red-400 transition-all duration-700';
        icon.textContent = '❌';
        title.textContent = 'Perlu Review — ' + fit + '%';
        sub.textContent = data.early_exit && data.early_reason?.includes('error')
            ? `Proses berhenti karena error. Hasil terbaik yang berhasil dikumpulkan ditampilkan.`
            : 'Banyak konflik. Coba tambah populasi/generasi atau gunakan preset Optimal.';
    }

    // Render tabel
    allRows = data.jadwal_rows || [];
    renderTable(allRows);

    // Diagnosa Masalah (Bahasa Manusia)
    const diagnosaCard = document.getElementById('diagnosa-card');
    const diagnosaList = document.getElementById('diagnosa-list');
    if (data.diagnosa && data.diagnosa.length > 0) {
        diagnosaCard.style.display = 'block';
        diagnosaList.innerHTML = data.diagnosa.map(msg => `
            <div class="flex items-start gap-3 p-3.5 rounded-2xl bg-rose-50/30 border border-rose-100/50 text-slate-700 text-xs leading-relaxed font-medium">
                <span class="flex-shrink-0 text-rose-500 font-bold text-sm">⚠️</span>
                <span>${msg}</span>
            </div>
        `).join('');
    } else {
        diagnosaCard.style.display = 'none';
        diagnosaList.innerHTML = '';
    }

    // Problem log
    if (data.problem_log && data.problem_log.length > 0) {
        const card = document.getElementById('problem-log-card');
        card.style.display = 'block';
        const list = document.getElementById('problem-log-list');
        list.innerHTML = data.problem_log.map(p =>
            `<div class="py-0.5"><span class="text-amber-400">[${p.type}]</span> <span class="text-slate-300">${p.message}</span> <span class="text-slate-500">${p.elapsed}</span></div>`
        ).join('');
    }

    // Hidden inputs
    document.getElementById('inp-jadwal-json').value  = JSON.stringify(allRows);
    document.getElementById('inp-tahun-hidden').value = document.getElementById('inp-tahun').value;

    document.getElementById('section-hasil').classList.remove('hidden');
    document.getElementById('section-hasil').scrollIntoView({ behavior:'smooth', block:'start' });
}

function onError(msg) {
    // Hentikan animasi dot
    document.getElementById('dot-pulse').className = 'w-2.5 h-2.5 rounded-full bg-red-500';
    document.getElementById('progress-title').textContent = msg || 'Terjadi error saat memproses.';
    // Re-enable form
    document.getElementById('section-form').classList.remove('opacity-60','pointer-events-none');
    const btn = document.getElementById('btn-generate');
    btn.disabled = false;
    document.getElementById('btn-label').textContent = 'Jalankan Algoritma Genetika';
    // Jika section-hasil belum muncul, sembunyikan progress agar tidak menggantung
    const hasilPanel = document.getElementById('section-hasil');
    if (hasilPanel.classList.contains('hidden')) {
        // Biarkan progress tetap terlihat dengan status error agar user tahu ada masalah
        document.getElementById('progress-bar').style.width = document.getElementById('progress-bar').style.width || '0%';
        document.getElementById('progress-pct-label').textContent = 'Error';
    }
}

// ─────────────────────────────────────────────────
// Table render + filter
// ─────────────────────────────────────────────────
const hariColor = {
    Senin:'text-blue-600 bg-blue-50', Selasa:'text-purple-600 bg-purple-50',
    Rabu:'text-teal-600 bg-teal-50',  Kamis:'text-orange-600 bg-orange-50',
    Jumat:'text-green-600 bg-green-50'
};
function renderTable(rows) {
    const tbody = document.getElementById('hasil-tbody');
    tbody.innerHTML = '';
    if (!rows.length) {
        document.getElementById('tabel-empty').classList.remove('hidden');
        document.getElementById('tabel-count').textContent = 'Tidak ada data.';
        return;
    }
    document.getElementById('tabel-empty').classList.add('hidden');
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


    document.getElementById('tabel-count').textContent = `Menampilkan ${rows.length} jadwal`;
}



function filterTable() {
    const q    = document.getElementById('filter-tabel').value.toLowerCase().trim();
    const hari = document.getElementById('filter-hari').value.toLowerCase().trim();
    const rows = document.querySelectorAll('#hasil-tbody tr');
    let visible = 0;
    rows.forEach(tr => {
        const matchQ = !q    || tr.dataset.search.includes(q);
        const matchH = !hari || tr.dataset.hari === hari;
        const show   = matchQ && matchH;
        tr.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('tabel-count').textContent = `Menampilkan ${visible} jadwal`;
    document.getElementById('tabel-empty').classList.toggle('hidden', visible > 0);
}

// ─────────────────────────────────────────────────
// Misc helpers
// ─────────────────────────────────────────────────
function resetForm() {
    // Sembunyikan kedua section
    document.getElementById('section-progress').classList.add('hidden');
    document.getElementById('section-hasil').classList.add('hidden');
    // Reset semua state
    bestFitnessPrev = 0; prevFitness = 0; fitnessHistory = []; allRows = [];
    // Reset progress panel ke state awal
    document.getElementById('progress-bar').style.width = '0%';
    document.getElementById('progress-pct-label').textContent = '0%';
    document.getElementById('progress-title').textContent = 'Memproses…';
    document.getElementById('dot-pulse').className = 'w-2.5 h-2.5 rounded-full bg-teal-500 animate-pulse';
    document.getElementById('stat-gen').textContent     = '0';
    document.getElementById('stat-fitness').textContent = '0%';
    document.getElementById('stat-hard').textContent    = '—';
    document.getElementById('stat-hard-detail').textContent = '';
    document.getElementById('stat-soft').textContent    = '—';
    document.getElementById('stat-fitness-delta').textContent = '';
    document.getElementById('progress-eta').textContent = '';
    // Reset problem log
    const pcard = document.getElementById('problem-log-card');
    if (pcard) pcard.style.display = 'none';
    // Reset diagnosa card
    const dcard = document.getElementById('diagnosa-card');
    if (dcard) dcard.style.display = 'none';
    // Re-enable form
    document.getElementById('section-form').classList.remove('opacity-60','pointer-events-none');
    document.getElementById('btn-label').textContent = 'Jalankan Algoritma Genetika';
    document.getElementById('btn-generate').disabled = false;
    // Scroll ke atas
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
function clearLog() { document.getElementById('log-container').innerHTML = ''; }
function shakeEl(id) {
    const el = document.getElementById(id);
    el.classList.add('ring-2','ring-red-400');
    el.animate([{transform:'translateX(-4px)'},{transform:'translateX(4px)'},{transform:'none'}],
               {duration:300,iterations:2});
    setTimeout(()=>el.classList.remove('ring-2','ring-red-400'),1200);
}

// Resize chart on window resize
window.addEventListener('resize', drawChart);

// Trial Modal functions
function openTrialModal() {
    if (!lastGAData) return;
    
    // Fill hidden inputs
    document.getElementById('trial-jadwal-json').value = JSON.stringify(allRows);
    document.getElementById('trial-tahun-hidden').value = document.getElementById('inp-tahun').value;
    document.getElementById('trial-fitness').value = lastGAData.fitness;
    document.getElementById('trial-generasi').value = lastGAData.generasi;
    document.getElementById('trial-total-kelas').value = lastGAData.total_kelas;
    document.getElementById('trial-dosen-conflicts').value = lastGAData.dosen_conflicts || 0;
    document.getElementById('trial-ruangan-conflicts').value = lastGAData.ruangan_conflicts || 0;
    document.getElementById('trial-soft-violations').value = lastGAData.soft_violations || 0;

    // Set default label
    const now = new Date();
    const timestamp = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    document.getElementById('inp-trial-label').value = `Uji Coba ${timestamp} (Fit: ${lastGAData.fitness}%)`;

    // Show modal
    document.getElementById('trial-modal').classList.remove('hidden');
    document.getElementById('inp-trial-label').focus();
}

function closeTrialModal() {
    document.getElementById('trial-modal').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    applyPreset('normal');
});
</script>


</body>
</html>

