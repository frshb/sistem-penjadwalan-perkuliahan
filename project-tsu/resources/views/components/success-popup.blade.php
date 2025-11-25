<!-- 1. Popup untuk Session PHP (Reload Biasa) -->
@if (session('success'))
    <div id="session-success-popup" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity duration-300" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="bg-white rounded-lg shadow-2xl p-8 max-w-sm w-full text-center transform transition-all scale-100 animate-bounce-in relative">

            <!-- Icon Sukses (Checkmark Hijau) -->
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
                <svg class="h-10 w-10 text-teal-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <!-- Judul & Pesan -->
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Berhasil!</h3>
            <p class="text-gray-600 mb-8">
                {{ session('success') }}
            </p>

            <!-- Tombol Tutup -->
            <button onclick="closePopup('session-success-popup')" class="w-full bg-teal-800 text-white font-semibold py-3 px-4 rounded-lg hover:bg-green-700 transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                OK, Lanjutkan
            </button>
        </div>
    </div>
@endif

<div id="js-success-popup" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity duration-300" style="background-color: rgba(0, 0, 0, 0.5); display: none;">
    <div class="bg-white rounded-lg shadow-2xl p-8 max-w-sm w-full text-center transform transition-all scale-100 animate-bounce-in relative">

        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
            <svg class="h-10 w-10 text-teal-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <h3 class="text-2xl font-bold text-gray-900 mb-2">Berhasil!</h3>
        <p id="js-success-message" class="text-gray-600 mb-8">Data berhasil disimpan.</p>
        <button onclick="window.location.reload()" class="w-full bg-teal-800 text-white font-semibold py-3 px-4 rounded-lg hover:bg-teal-600 transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
            OK, Lanjutkan
        </button>
    </div>
</div>

<script>
    function closePopup(id) {
        const popup = document.getElementById(id);
        if (popup) {
            popup.style.opacity = '0';
            setTimeout(() => popup.remove(), 300);
        }
    }

    // Fungsi global untuk memanggil popup via JS
    window.showSuccessPopup = function(message) {
        const popup = document.getElementById('js-success-popup');
        const msgEl = document.getElementById('js-success-message');

        if (popup && msgEl) {
            msgEl.innerText = message;
            popup.classList.remove('hidden');
            popup.style.display = 'flex'; // Pastikan display flex agar tengah

            // Animasi masuk sederhana (opsional)
            popup.style.opacity = '0';
            setTimeout(() => {
                popup.style.opacity = '1';
            }, 10);
        } else {
            console.error('Elemen popup tidak ditemukan!');
        }
    }
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
