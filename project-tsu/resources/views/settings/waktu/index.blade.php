<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Hari & Waktu | Sistem Penjadwalan</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50 overflow-x-hidden min-h-screen font-sans transition-colors duration-300">

    <div class="flex min-h-screen" x-data="{ sidebarOpen: true }">
        @include('components.sidebar')

        <main id="main-content" :class="sidebarOpen ? 'lg:ml-64' : 'ml-10'" class="flex-1 min-w-0 p-6 sm:p-10 transition-all duration-300 ease-in-out ml-0">

            <div class="flex justify-between items-start gap-4 mb-8">
                <div class="flex flex-col">
                    <a href="{{ route('settings.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-teal-700 transition-colors mb-3 group">
                        <svg class="w-4 h-4 mr-1 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        Kembali ke Pengaturan
                    </a>
                    <div class="flex items-center">
                        <button @click="sidebarOpen = !sidebarOpen" class="flex flex-col hover:opacity-80 transition cursor-pointer" title="Toggle Sidebar">
                            <div class="w-2 h-5 bg-teal-600 rounded-tl-md"></div>
                            <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                        </button>
                        <h1 class="text-3xl font-bold text-gray-900 ml-3 tracking-tight">Manajemen Hari & Slot Waktu</h1>
                    </div>
                    <p class="text-sm text-gray-500 mt-1 ml-[1.35rem]">Kelola status aktif hari, slot waktu, beserta pemetaannya.</p>
                </div>
                @include('components.header-profile')
            </div>

            <div id="toast-container" class="fixed top-5 right-5 z-[100] flex flex-col gap-2"></div>

            <!-- Main Content Container -->
            <div x-data="waktuManagement()">
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    {{-- PANEL HARI --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                        <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                            <div>
                                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Status Hari
                                </h2>
                                <p class="text-xs text-gray-500 mt-1">Aktifkan hari operasional</p>
                            </div>
                        </div>
                        <div class="p-5 overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 text-gray-500 bg-gray-50">
                                        <th class="py-3 px-4 font-semibold text-xs uppercase tracking-wider rounded-tl-lg">No</th>
                                        <th class="py-3 px-4 font-semibold text-xs uppercase tracking-wider">Nama Hari</th>
                                        <th class="py-3 px-4 font-semibold text-xs uppercase tracking-wider text-right rounded-tr-lg w-28">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($hari as $h)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-3 px-4 font-mono text-gray-500">{{ $loop->iteration }}</td>
                                        <td class="py-3 px-4 font-medium text-gray-900 capitalize">{{ $h->nama_hari }}</td>
                                        <td class="py-3 px-4 text-right">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" class="sr-only peer" {{ $h->is_active ? 'checked' : '' }} @change="toggleHari({{ $h->id_hari }}, $event.target.checked)">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600"></div>
                                            </label>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="py-8 text-center text-gray-500">Belum ada data hari.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- PANEL SLOT WAKTU --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                        <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                            <div>
                                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Status Slot Waktu
                                </h2>
                                <p class="text-xs text-gray-500 mt-1">Aktifkan slot jam kuliah</p>
                            </div>
                        </div>
                        <div class="p-5 overflow-x-auto h-[450px] overflow-y-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 text-gray-500 bg-gray-50">
                                        <th class="py-3 px-4 font-semibold text-xs uppercase tracking-wider rounded-tl-lg">Slot</th>
                                        <th class="py-3 px-4 font-semibold text-xs uppercase tracking-wider">Jam</th>
                                        <th class="py-3 px-4 font-semibold text-xs uppercase tracking-wider text-right rounded-tr-lg w-28">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($slotWaktu as $slot)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-gray-900 text-center w-6 h-6 rounded-full bg-gray-100">{{ $slot->jam_ke }}</div>
                                        </td>
                                        <td class="py-3 px-4 font-medium text-gray-900">
                                            {{ \Carbon\Carbon::parse($slot->waktu_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->waktu_selesai)->format('H:i') }}
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" class="sr-only peer" {{ $slot->is_active ? 'checked' : '' }} @change="toggleSlot({{ $slot->id_slot }}, $event.target.checked)">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600"></div>
                                            </label>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="py-8 text-center text-gray-500">Belum ada data slot waktu.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- PANEL PEMETAAN --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col mb-8">
                    <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <div>
                            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                Pemetaan Hari & Slot Waktu
                            </h2>
                            <p class="text-xs text-gray-500 mt-1">Tentukan slot waktu mana saja yang berlaku pada setiap hari.</p>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="space-y-6">
                            @foreach($hari as $h)
                                @if($h->is_active)
                                <div class="border border-gray-100 rounded-xl p-5 bg-white shadow-sm">
                                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
                                        <h3 class="font-bold text-gray-800 capitalize flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full bg-teal-500"></div>
                                            {{ $h->nama_hari }}
                                        </h3>
                                        <button @click="updateMapping({{ $h->id_hari }})" class="px-3 py-1.5 bg-teal-50 text-teal-700 hover:bg-teal-100 rounded-lg text-xs font-semibold transition-colors">
                                            Simpan Pemetaan
                                        </button>
                                    </div>
                                    <div class="flex flex-wrap gap-3">
                                        @php
                                            $mappedSlots = $h->slotWaktus->pluck('id_slot')->toArray();
                                        @endphp
                                        @foreach($slotWaktu as $slot)
                                            @if($slot->is_active)
                                            <label class="inline-flex items-center gap-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 px-3 py-2 rounded-xl cursor-pointer transition-colors">
                                                <input type="checkbox" id="mapping-{{ $h->id_hari }}-{{ $slot->id_slot }}" value="{{ $slot->id_slot }}" class="w-4 h-4 text-teal-600 rounded border-gray-300 focus:ring-teal-500" {{ in_array($slot->id_slot, $mappedSlots) ? 'checked' : '' }}>
                                                <span class="text-sm font-medium text-gray-700">Slot {{ $slot->jam_ke }}</span>
                                            </label>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                </div>

            </div> <!-- End x-data -->

        </main>
    </div>

    <script>
        function showToast(message, isSuccess = true) {
            const toastContainer = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg transform transition-all duration-300 translate-x-full opacity-0 ${isSuccess ? 'bg-teal-600 text-white' : 'bg-red-600 text-white'}`;
            
            const icon = isSuccess 
                ? `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`
                : `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`;
                
            toast.innerHTML = `${icon}<span class="text-sm font-medium">${message}</span>`;
            toastContainer.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            }, 10);
            
            // Remove after 3s
            setTimeout(() => {
                toast.classList.add('opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function waktuManagement() {
            return {
                csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),

                async toggleHari(id, isChecked) {
                    try {
                        const res = await fetch(`/settings/waktu/hari/${id}/toggle`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken }
                        });
                        const data = await res.json();
                        if (data.success) {
                            showToast(data.message, true);
                            setTimeout(() => window.location.reload(), 800); // Reload to reflect changes in mapping
                        }
                    } catch (error) {
                        showToast('Gagal memperbarui status hari.', false);
                    }
                },

                async toggleSlot(id, isChecked) {
                    try {
                        const res = await fetch(`/settings/waktu/slot/${id}/toggle`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken }
                        });
                        const data = await res.json();
                        if (data.success) {
                            showToast(data.message, true);
                            setTimeout(() => window.location.reload(), 800); // Reload to reflect changes in mapping
                        }
                    } catch (error) {
                        showToast('Gagal memperbarui status slot.', false);
                    }
                },

                async updateMapping(hariId) {
                    // Get all checked checkboxes for this hari
                    const checkboxes = document.querySelectorAll(`input[id^="mapping-${hariId}-"]:checked`);
                    const slotIds = Array.from(checkboxes).map(cb => cb.value);

                    try {
                        const res = await fetch(`/settings/waktu/mapping`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                            body: JSON.stringify({ id_hari: hariId, slot_ids: slotIds })
                        });
                        const data = await res.json();
                        if (data.success) {
                            showToast(data.message, true);
                        } else {
                            showToast('Gagal menyimpan pemetaan.', false);
                        }
                    } catch (error) {
                        showToast('Terjadi kesalahan sistem.', false);
                    }
                }
            }
        }
    </script>
</body>
</html>
