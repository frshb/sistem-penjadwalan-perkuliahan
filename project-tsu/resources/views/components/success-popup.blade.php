@if (session('success'))
<div id="success-popup" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity duration-300">
    <div class="bg-white rounded-lg shadow-2xl p-8 max-w-sm w-full text-center transform transition-all scale-100 animate-bounce-in">

        <!-- Icon Sukses (Checkmark Hijau) -->
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
            <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <!-- Judul & Pesan -->
        <h3 class="text-2xl font-bold text-gray-900 mb-2">Berhasil!</h3>
        <p class="text-gray-600 mb-8">
            {{ session('success') }}
        </p>

        <!-- Tombol Tutup -->
        <button onclick="closeSuccessPopup()" class="w-full bg-green-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-green-700 transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
            OK, Lanjutkan
        </button>
    </div>
</div>

<script>
    // Fungsi untuk menutup popup
    function closeSuccessPopup() {
        const popup = document.getElementById('success-popup');
        if (popup) {
            popup.classList.add('opacity-0', 'pointer-events-none');
            setTimeout(() => {
                popup.remove();
            }, 300); // Hapus elemen setelah transisi selesai
        }
    }

    // Opsional: Tutup otomatis setelah 3 detik
    // setTimeout(closeSuccessPopup, 3000);
</script>

<style>
    @keyframes bounce-in {
        0% { transform: scale(0.9); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    .animate-bounce-in {
        animation: bounce-in 0.3s ease-out forwards;
    }
</style>
@endif
