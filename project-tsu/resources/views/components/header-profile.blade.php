@php
    $currentUser = Auth::user();
    $currentRole = strtolower($currentUser->role->nama_role ?? '');
    
    $baseQuery = \App\Models\Notification::where(function($q) use ($currentUser, $currentRole) {
        $q->where('user_id', $currentUser->id_user)
          ->orWhere('role_target', $currentRole)
          ->orWhereNull('role_target');
    })
    ->where(function($q) use ($currentUser) {
        $q->whereNull('deleted_by')
          ->orWhereJsonDoesntContain('deleted_by', $currentUser->id_user);
    });

    $unreadCount = (clone $baseQuery)->where('is_read', false)->count();
    $userNotifications = (clone $baseQuery)->orderBy('created_at', 'desc')->take(10)->get();
@endphp

<div id="header-profile" class="relative w-fit ml-auto flex items-center gap-3" x-data="{ 
    dropdownOpen: false, 
    notifOpen: false,
    modalOpen: false,
    editMode: false,
    showPassword: false,
    markRead(id, redirectUrl = null) {
        fetch('/notifications/' + id + '/read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            const target = redirectUrl || data.redirect_url;
            if (target) {
                window.location.href = target;
            } else {
                window.location.reload();
            }
        })
        .catch(() => {
            if (redirectUrl) window.location.href = redirectUrl;
            else window.location.reload();
        });
    },
    markAllRead() {
        fetch('/notifications/read-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => window.location.reload());
    },
    deleteNotif(id) {
        fetch('/notifications/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => window.location.reload());
    },
    deleteAllNotif() {
        if (!confirm('Apakah Anda yakin ingin menghapus semua notifikasi dari akun Anda?')) return;
        fetch('/notifications/delete-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => window.location.reload());
    }
}">

    {{-- CHAT ICON CONTAINER --}}
    <div id="chat-icon-container" class="relative"></div>

    {{-- LONCENG NOTIFIKASI --}}
    <div class="relative">
        <button 
            @click="notifOpen = !notifOpen; dropdownOpen = false" 
            @click.away="notifOpen = false"
            type="button" 
            class="relative p-2.5 bg-white rounded-full shadow-sm border border-gray-200 hover:bg-teal-50 text-gray-600 hover:text-teal-700 transition-colors focus:outline-none cursor-pointer"
            title="Pemberitahuan & Notifikasi"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>

            @if($unreadCount > 0)
                <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-[11px] font-extrabold text-white ring-2 ring-white animate-pulse">
                    {{ $unreadCount }}
                </span>
            @endif
        </button>

        {{-- DROPDOWN NOTIFIKASI --}}
        <div x-show="notifOpen" 
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl py-2 z-50 border border-gray-100 origin-top-right overflow-hidden"
             style="display: none;">
            
            <div class="px-4 py-3 bg-gradient-to-r from-teal-700 to-teal-800 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span class="font-bold text-sm">Pemberitahuan Sistem</span>
                </div>
                @if($unreadCount > 0)
                    <button @click="markAllRead()" class="text-xs font-semibold text-teal-200 hover:text-white underline cursor-pointer" title="Tandai semua notifikasi sebagai dibaca">
                        Tandai Semua Dibaca
                    </button>
                @endif
            </div>

            <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                @forelse($userNotifications as $notif)
                    <div class="p-3.5 hover:bg-gray-50 transition flex items-start justify-between gap-3 group {{ !$notif->is_read ? 'bg-teal-50/50' : '' }}">
                        
                        <div class="flex items-start gap-3 flex-1 min-w-0 cursor-pointer" @click="markRead({{ $notif->id }}, '{{ $notif->url ?? '' }}')">
                            <div class="flex-shrink-0 mt-1">
                                @if($notif->tipe === 'success')
                                    <span class="p-1.5 rounded-full bg-emerald-100 text-emerald-600 block">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                @elseif($notif->tipe === 'warning')
                                    <span class="p-1.5 rounded-full bg-amber-100 text-amber-600 block">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    </span>
                                @else
                                    <span class="p-1.5 rounded-full bg-teal-100 text-teal-600 block">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-gray-900 truncate">{{ $notif->judul }}</p>
                                    <span class="text-[10px] text-gray-400 shrink-0 ml-2">{{ $notif->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-xs text-gray-600 mt-1 leading-normal line-clamp-3">{{ $notif->pesan }}</p>
                            </div>
                        </div>

                        {{-- TOMBOL HAPUS INDIVIDUAL --}}
                        <button 
                            @click.stop="deleteNotif({{ $notif->id }})" 
                            type="button"
                            class="p-1 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition opacity-60 group-hover:opacity-100 cursor-pointer shrink-0 mt-0.5"
                            title="Hapus notifikasi ini"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>

                    </div>
                @empty
                    <div class="p-6 text-center text-gray-400">
                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        <p class="text-xs">Belum ada notifikasi baru.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- PROFILE DROPDOWN --}}
    <div class="relative">
        <div @click="dropdownOpen = !dropdownOpen; notifOpen = false" @click.away="dropdownOpen = false" class="flex items-center space-x-3 bg-white px-4 py-2 rounded-full shadow-sm border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors select-none">
            <div class="bg-teal-100 p-2 rounded-full text-teal-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <div class="hidden sm:flex flex-col text-right">
                <span class="text-sm font-semibold text-gray-800">{{ Auth::user()->username ?? 'Guest' }}</span>
                <span class="text-xs text-gray-500 capitalize">{{ Auth::user()->role->nama_role ?? '-' }}</span>
            </div>
            <svg class="w-4 h-4 text-gray-400 hidden sm:block transition-transform duration-200" :class="dropdownOpen ? 'transform rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>

        <div x-show="dropdownOpen" 
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl py-2 z-50 border border-gray-100 origin-top-right"
             style="display: none;">
            
            <div class="px-4 py-3 border-b border-gray-100 sm:hidden bg-gray-50 mb-1">
                 <p class="text-sm font-bold text-gray-800 truncate">{{ Auth::user()->username ?? 'Guest' }}</p>
                 <p class="text-xs text-gray-500 capitalize">{{ Auth::user()->role->nama_role ?? '-' }}</p>
            </div>

            <div class="px-2 space-y-1">
                <a href="#" @click.prevent="dropdownOpen = false; modalOpen = true" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-700 rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Lihat Profil
                </a>

                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </a>
            </div>
            
            <form id="logout-form-header" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </div>
    </div>

    {{-- MODAL PROFIL --}}
    <template x-teleport="body">
        <div x-show="modalOpen" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="modalOpen" @click="modalOpen = false" class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="modalOpen" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative">
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Profil Pengguna</h3>
                        <button @click="modalOpen = false" aria-label="Close Profile Modal" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div x-show="!editMode">
                            <div class="flex items-center justify-center mb-6">
                                <div class="h-20 w-20 bg-teal-100 rounded-full flex items-center justify-center text-teal-600 text-3xl font-bold">
                                    {{ substr(Auth::user()->username ?? 'G', 0, 1) }}
                                </div>
                            </div>
                            
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div class="col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Username</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ Auth::user()->username }}</dd>
                                </div>
                                <div class="col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Role</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-semibold capitalize">{{ Auth::user()->role->nama_role ?? '-' }}</dd>
                                </div>
                            </dl>

                            <div class="mt-8 flex justify-end">
                                <button @click="editMode = true" class="inline-flex justify-center w-full sm:w-auto px-4 py-2 bg-yellow-500 text-white text-sm font-medium rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    Edit Profil
                                </button>
                            </div>
                        </div>

                        <form x-show="editMode" action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Username</label>
                                    <input type="text" name="username" value="{{ Auth::user()->username }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Role</label>
                                    <input type="text" value="{{ Auth::user()->role->nama_role ?? '-' }}" disabled class="mt-1 block w-full bg-gray-100 border border-gray-300 rounded-md shadow-sm py-2 px-3 text-gray-500 sm:text-sm cursor-not-allowed">
                                    <p class="mt-1 text-xs text-gray-500">Role tidak dapat diubah.</p>
                                </div>

                                <div class="border-t border-gray-100 pt-4 mt-4">
                                    <h4 class="text-sm font-medium text-gray-900 mb-3">Ubah Password <span class="text-gray-400 font-normal">(Opsional)</span></h4>
                                    
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Password Saat Ini</label>
                                            <input type="password" name="current_password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm" placeholder="Diperlukan jika mengubah password">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Password Baru</label>
                                            <input type="password" name="new_password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Konfirmasi Password Baru</label>
                                            <input type="password" name="new_password_confirmation" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end space-x-3">
                                <button type="button" @click="editMode = false" class="inline-flex justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                    Batal
                                </button>
                                <button type="submit" class="inline-flex justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>

    @if ($errors->any() || session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" class="fixed bottom-4 right-4 z-[110] max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        @if (session('success'))
                            <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @else
                            <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @endif
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900">
                            {{ session('success') ? 'Berhasil!' : 'Terjadi Kesalahan' }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ session('success') ?? $errors->first() }}
                        </p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button @click="show = false" aria-label="Close Notification" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Include Chat Modal --}}
<x-chat-modal />
