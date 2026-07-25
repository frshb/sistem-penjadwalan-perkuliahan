<div x-data="{
        show: false,
        actionUrl: '',
        openModal(url) {
            this.actionUrl = url;
            this.show = true;
        },
        closeModal() {
            this.show = false;
            setTimeout(() => this.actionUrl = '', 300);
        }
    }"
    @open-delete-modal.window="openModal($event.detail.url)"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 scale-90"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-90"
    class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm"
    style="display: none;">
    
    <div @click.away="closeModal()" class="bg-white rounded-lg shadow-2xl p-8 max-w-sm w-full text-center relative">

        <!-- Icon Warning -->
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
            <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>

        <!-- Judul & Pesan -->
        <h3 class="text-2xl font-bold text-gray-900 mb-2">Konfirmasi Hapus</h3>
        <p class="text-gray-600 mb-8">
            Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.
        </p>

        <!-- Tombol Aksi -->
        <div class="flex justify-center space-x-4">
            <button type="button" @click="closeModal()" class="px-5 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition duration-200 focus:outline-none focus:ring-2 focus:ring-gray-400">
                Batal
            </button>
            <form :action="actionUrl" method="POST" class="inline-block">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-5 py-2.5 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 shadow-md">
                    Ya, Hapus
                </button>
            </form>
        </div>

    </div>
</div>

<script>
    window.confirmDelete = function(url) {
        window.dispatchEvent(new CustomEvent('open-delete-modal', { detail: { url: url } }));
    };
    window.closeDeleteModal = function() {
        // Alpine handles closing, but we can provide this for compatibility
        // Not strictly needed since Alpine state controls the modal
    };
</script>
