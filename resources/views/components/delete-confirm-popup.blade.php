<div id="delete-confirm-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity duration-300" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="bg-white rounded-lg shadow-2xl p-8 max-w-sm w-full text-center transform transition-all scale-100 animate-bounce-in relative">

        <!-- Icon Warning (Tanda Seru Merah/Kuning) -->
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
            <button onclick="closeDeleteModal()" class="px-5 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition duration-200 focus:outline-none focus:ring-2 focus:ring-gray-400">
                Batal
            </button>
            <form id="delete-form" action="" method="POST" class="inline-block">
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
    function confirmDelete(actionUrl) {
        const modal = document.getElementById('delete-confirm-modal');
        const form = document.getElementById('delete-form');

        if (modal && form) {
            form.action = actionUrl;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            // Animasi masuk
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.style.opacity = '1';
            }, 10);
        }
    }

    function closeDeleteModal() {
        const modal = document.getElementById('delete-confirm-modal');
        if (modal) {
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }
    }

    // Tutup modal jika klik di luar konten
    document.getElementById('delete-confirm-modal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });
</script>
