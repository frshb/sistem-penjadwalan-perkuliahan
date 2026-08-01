@props(['tahunAkademik'])

<!-- MODAL AUDIT TRAIL TIMELINE APPROVAL -->
<div id="modal-approval-timeline-overlay" class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-gray-100 transform transition-all max-h-[85vh] flex flex-col">
        
        <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-teal-50 border border-teal-200 text-teal-700 flex items-center justify-center font-bold text-lg">
                    📜
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Riwayat Review & Audit Trail Workflow</h3>
                    <p class="text-xs text-gray-500">Jejak rekam evaluasi, catatan revisi, dan keputusan persetujuan jadwal.</p>
                </div>
            </div>
            <button onclick="tutupModalTimelineApproval()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center transition">✕</button>
        </div>

        <div id="timeline-approval-body" class="overflow-y-auto pr-2 space-y-4 grow">
            <div class="text-center py-8 text-gray-400 text-xs">
                Mengambil data riwayat timeline...
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-end shrink-0">
            <button onclick="tutupModalTimelineApproval()" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition">
                Tutup
            </button>
        </div>

    </div>
</div>
