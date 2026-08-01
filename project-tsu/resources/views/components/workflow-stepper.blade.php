@props(['status' => 'draft', 'catatanRevisi' => null])

@php
    $stages = [
        'draft'           => ['label' => 'Draft',            'desc' => 'Penyusunan Admin',         'step' => 1],
        'review_sekprodi' => ['label' => 'Review Sekprodi',  'desc' => 'Pemeriksaan Sekprodi',     'step' => 2],
        'review_kaprodi'  => ['label' => 'Review Kaprodi',   'desc' => 'Pemeriksaan Kaprodi',      'step' => 3],
        'menunggu_dekan'  => ['label' => 'Menunggu Dekan',   'desc' => 'Persetujuan Dekan',        'step' => 4],
        'disetujui'       => ['label' => 'Disetujui',        'desc' => 'Telah Disahkan Dekan',     'step' => 5],
        'published'       => ['label' => 'Published',        'desc' => 'Resmi Dipublikasikan',     'step' => 6],
    ];

    $currentStep = match($status) {
        'draft', 'revisi_sekprodi', 'revisi_kaprodi', 'revisi' => 1,
        'review_sekprodi' => 2,
        'review_kaprodi'  => 3,
        'disetujui_kaprodi', 'menunggu_dekan', 'menunggu_persetujuan' => 4,
        'disetujui'       => 5,
        'published'       => 6,
        default           => 1,
    };
@endphp

<div class="bg-white border border-gray-150 rounded-3xl p-5 shadow-xs mb-6">
    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-2xl flex items-center justify-center font-bold text-sm shadow-xs
                {{ in_array($status, ['revisi', 'revisi_sekprodi', 'revisi_kaprodi']) ? 'bg-amber-500 text-white' : ($status === 'published' ? 'bg-emerald-600 text-white' : 'bg-teal-600 text-white') }}">
                @if(in_array($status, ['revisi', 'revisi_sekprodi', 'revisi_kaprodi']))
                    ⚡
                @elseif($status === 'published')
                    ✓
                @else
                    {{ $currentStep }}
                @endif
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-gray-900">Workflow Approval & Status Jadwal</h3>
                    @if(in_array($status, ['revisi', 'revisi_sekprodi', 'revisi_kaprodi']))
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-ping"></span>
                            {{ $status === 'revisi_sekprodi' ? 'Revisi dari Sekprodi' : ($status === 'revisi_kaprodi' ? 'Revisi dari Kaprodi' : 'Perlu Revisi dari Dekan') }}
                        </span>
                    @elseif($status === 'published')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                            ● Published (Aktif)
                        </span>
                    @elseif($status === 'disetujui')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">
                            ✓ Disetujui Dekan
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-100 text-teal-800 border border-teal-200">
                            Dalam Proses Approval
                        </span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 mt-0.5">Evaluasi akademik berjenjang sebelum publikasi jadwal kuliah.</p>
            </div>
        </div>

        <button onclick="bukaModalTimelineApproval()" class="px-3.5 py-2 bg-gray-50 hover:bg-teal-50 text-gray-700 hover:text-teal-700 rounded-xl border border-gray-200 hover:border-teal-300 text-xs font-semibold flex items-center gap-2 transition">
            <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Audit Trail & Riwayat Timeline</span>
        </button>
    </div>

    {{-- Catatan Revisi Banner --}}
    @if(in_array($status, ['revisi', 'revisi_sekprodi', 'revisi_kaprodi']) && $catatanRevisi)
        <div class="mb-4 p-4 bg-amber-50 border border-amber-300 rounded-2xl flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-amber-200 text-amber-900 flex items-center justify-center shrink-0 font-bold text-base">
                ⚠️
            </div>
            <div>
                <h4 class="text-xs font-bold text-amber-950 uppercase tracking-wider">Catatan Permintaan Revisi ({{ $status === 'revisi_sekprodi' ? 'Sekretaris Prodi' : ($status === 'revisi_kaprodi' ? 'Kaprodi' : 'Dekan') }}):</h4>
                <p class="text-xs text-amber-900 mt-1 font-medium italic">"{{ $catatanRevisi }}"</p>
                <p class="text-[11px] text-amber-700 mt-1">Silakan Admin melakukan penyesuaian jadwal pada workspace di bawah, kemudian klik tombol <strong>{{ $status === 'revisi_sekprodi' ? 'Review Jadwal (Kirim ke Sekre Prodi)' : ($status === 'revisi_kaprodi' ? 'Review Jadwal (Kirim ke Sekre Prodi)' : 'Ajukan ke Dekan') }}</strong>.</p>
            </div>
        </div>
    @endif

    {{-- Stepper Progress Bar --}}
    <div class="relative flex items-center justify-between overflow-x-auto pb-2 scrollbar-none">
        <div class="absolute left-6 right-6 top-5 h-1 bg-gray-150 z-0"></div>

        @foreach($stages as $key => $stg)
            @php
                $isPassed = $currentStep > $stg['step'];
                $isCurrent = $currentStep === $stg['step'];
            @endphp
            <div class="relative z-10 flex flex-col items-center group shrink-0 px-3">
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-xs font-bold transition duration-300 border-2 
                    {{ $isCurrent ? (in_array($status, ['revisi', 'revisi_sekprodi', 'revisi_kaprodi']) ? 'bg-amber-500 border-amber-300 text-white shadow-md shadow-amber-200 ring-4 ring-amber-100' : 'bg-teal-600 border-teal-400 text-white shadow-md shadow-teal-200 ring-4 ring-teal-100') : '' }}
                    {{ $isPassed ? 'bg-emerald-500 border-emerald-400 text-white shadow-xs' : '' }}
                    {{ !$isCurrent && !$isPassed ? 'bg-white border-gray-250 text-gray-400' : '' }}">
                    @if($isPassed)
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    @else
                        {{ $stg['step'] }}
                    @endif
                </div>

                <div class="text-center mt-2">
                    <span class="block text-xs font-bold {{ $isCurrent ? 'text-teal-900' : ($isPassed ? 'text-emerald-700' : 'text-gray-400') }}">
                        {{ $stg['label'] }}
                    </span>
                    <span class="block text-[10px] text-gray-400 font-medium hidden md:block">
                        {{ $stg['desc'] }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</div>
