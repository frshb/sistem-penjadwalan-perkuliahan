@props(['tahunAkademik'])

<!-- MODAL APPROVAL DECISION (SETUJUI / REVISI) -->
<div id="modal-approval-decision-overlay" class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 transform transition-all">
        
        <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4">
            <div class="flex items-center gap-3">
                <div id="mad-icon-box" class="w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-lg">
                    ✨
                </div>
                <div>
                    <h3 id="mad-title" class="text-base font-bold text-gray-900">Keputusan Review Jadwal</h3>
                    <p id="mad-subtitle" class="text-xs text-gray-500">Berikan tanggapan hasil evaluasi Anda.</p>
                </div>
            </div>
            <button onclick="tutupModalDecisionApproval()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center transition">✕</button>
        </div>

        <form id="form-approval-decision" method="POST" onsubmit="submitApprovalDecision(event)">
            @csrf
            <input type="hidden" id="mad-action-type" value="setujui">
            
            <div class="mb-4">
                <label for="mad-catatan" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    Catatan / Alasan <span id="mad-required-badge" class="text-gray-400 hidden">(Opsional)</span>
                </label>
                <textarea id="mad-catatan" name="catatan_revisi" rows="4" 
                    placeholder="Tuliskan catatan evaluasi atau alasan revisi jadwal di sini..."
                    class="w-full text-sm rounded-2xl border border-gray-250 p-3.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition resize-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                <button type="button" onclick="tutupModalDecisionApproval()" class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 text-xs font-semibold transition">
                    Batal
                </button>
                <button type="submit" id="mad-submit-btn" class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-2">
                    <span>Kirim Keputusan</span>
                </button>
            </div>
        </form>

    </div>
</div>
