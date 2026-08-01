<div x-data="chatSystem()" x-init="initChat()" class="relative z-[90]">
    
    <!-- Chat Icon to be placed in header -->
    <template x-teleport="#chat-icon-container">
        <button 
            @click="openChat()" 
            type="button" 
            class="relative p-2.5 bg-white rounded-full shadow-sm border border-gray-200 hover:bg-teal-50 text-gray-600 hover:text-teal-700 transition-colors focus:outline-none cursor-pointer"
            title="Chat & Diskusi"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>

            <span x-show="totalUnread > 0" x-text="totalUnread" class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-[11px] font-extrabold text-white ring-2 ring-white animate-pulse" style="display: none;"></span>
        </button>
    </template>

    <!-- Chat Modal -->
    <div x-show="isOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm" style="display: none;">
        <div 
            @click.away="closeChat()"
            x-show="isOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl h-[80vh] flex overflow-hidden border border-slate-200"
        >
            <!-- Left Pane: Contacts -->
            <div class="w-1/3 bg-slate-50 border-r border-slate-200 flex flex-col">
                <div class="p-4 border-b border-slate-200 bg-white flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Pesan Langsung</h2>
                        <p class="text-xs text-slate-500">Komunikasi internal sistem</p>
                    </div>
                    <button @click="showNewChat = !showNewChat; searchContact = ''" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-teal-100 text-slate-600 hover:text-teal-700 flex items-center justify-center transition cursor-pointer" :title="showNewChat ? 'Kembali ke Obrolan' : 'Mulai Obrolan Baru'">
                        <svg x-show="!showNewChat" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <svg x-show="showNewChat" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-2 space-y-1 relative">
                    
                    <!-- Mode: Riwayat Chat -->
                    <div x-show="!showNewChat">
                        <template x-for="contact in contacts" :key="contact.id">
                            <button 
                                @click="selectContact(contact)"
                                :class="{'bg-teal-50 border-teal-200': selectedContact && selectedContact.id === contact.id, 'hover:bg-slate-100 border-transparent': !(selectedContact && selectedContact.id === contact.id)}"
                                class="w-full text-left p-3 rounded-xl border transition flex items-center gap-3 cursor-pointer mb-1"
                            >
                                <div class="w-10 h-10 rounded-full bg-teal-600 text-white flex items-center justify-center font-bold flex-shrink-0">
                                    <span x-text="contact.name.charAt(0).toUpperCase()"></span>
                                </div>
                                <div class="flex-1 overflow-hidden">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-bold text-slate-800 truncate" x-text="contact.name"></h4>
                                        <span class="text-[10px] text-slate-400 whitespace-nowrap" x-text="contact.last_message_time || ''"></span>
                                    </div>
                                    <div class="flex items-center justify-between gap-2 mt-0.5">
                                        <span class="text-xs text-slate-500 truncate font-medium" x-text="contact.role"></span>
                                        <span x-show="contact.unread_count > 0" class="px-1.5 py-0.5 rounded-full bg-rose-500 text-white text-[10px] font-bold" x-text="contact.unread_count"></span>
                                    </div>
                                </div>
                            </button>
                        </template>
                        <div x-show="contacts.length === 0" class="text-center p-6 text-sm text-slate-500 italic">
                            Belum ada riwayat pesan.<br>Klik tombol + di atas untuk memulai obrolan baru.
                        </div>
                    </div>

                    <!-- Mode: Pilih Kontak Baru -->
                    <div x-show="showNewChat">
                        <div class="mb-3 px-1">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input type="text" x-model="searchContact" class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-teal-500 focus:border-teal-500 sm:text-sm" placeholder="Cari nama atau role...">
                            </div>
                        </div>
                        <template x-for="user in filteredAllUsers()" :key="user.id">
                            <button 
                                @click="startNewChat(user)"
                                class="w-full text-left p-2 rounded-xl border border-transparent hover:bg-slate-100 transition flex items-center gap-3 cursor-pointer mb-1"
                            >
                                <div class="w-8 h-8 rounded-full bg-slate-400 text-white flex items-center justify-center font-bold flex-shrink-0 text-sm">
                                    <span x-text="user.name.charAt(0).toUpperCase()"></span>
                                </div>
                                <div class="flex-1 overflow-hidden">
                                    <h4 class="text-sm font-bold text-slate-800 truncate" x-text="user.name"></h4>
                                    <span class="text-xs text-slate-500 truncate font-medium" x-text="user.role"></span>
                                </div>
                            </button>
                        </template>
                    </div>

                </div>
            </div>

            <!-- Right Pane: Chat Area -->
            <div class="w-2/3 bg-slate-100 flex flex-col relative overflow-hidden">
                <!-- Header -->
                <div class="p-4 border-b border-slate-200 bg-white flex items-center justify-between z-20 shadow-sm relative">
                    <div class="flex items-center gap-3" x-show="selectedContact">
                        <div class="w-10 h-10 rounded-full bg-teal-600 text-white flex items-center justify-center font-bold">
                            <span x-text="selectedContact ? selectedContact.name.charAt(0).toUpperCase() : ''"></span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800" x-text="selectedContact ? selectedContact.name : ''"></h3>
                            <p class="text-xs font-medium text-teal-600" x-text="selectedContact ? selectedContact.role : ''"></p>
                        </div>
                    </div>
                    <div x-show="!selectedContact" class="text-slate-500 font-medium">Pilih percakapan</div>
                    
                    <div class="flex items-center gap-2">
                        <button x-show="selectedContact && messages.length > 0" @click="clearChat()" class="w-8 h-8 rounded-full hover:bg-rose-100 flex items-center justify-center text-slate-400 hover:text-rose-500 transition cursor-pointer" title="Hapus semua percakapan">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                        <button @click="closeChat()" class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition cursor-pointer" title="Tutup Chat">
                            ✕
                        </button>
                    </div>
                </div>

                <!-- Background Logo Pattern -->
                <div class="absolute inset-0 z-0 pointer-events-none mt-16" 
                     style="background-image: url('{{ asset('favicon_square.png') }}'); background-repeat: repeat; background-size: 100px; opacity: 0.05;">
                </div>

                <!-- Messages -->
                <div id="chat-messages-container" class="flex-1 overflow-y-auto p-4 space-y-4 relative z-10">
                    <div x-show="!selectedContact" class="h-full flex flex-col items-center justify-center text-slate-400">
                        <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        <p>Pilih kontak untuk mulai berkirim pesan.</p>
                    </div>

                    <template x-for="msg in messages" :key="msg.id">
                        <div :class="msg.is_mine ? 'flex justify-end items-center gap-2 group' : 'flex justify-start items-center gap-2 group'">
                            
                            <!-- Delete button (for own messages, appears on the left of the bubble) -->
                            <template x-if="msg.is_mine">
                                <button @click="deleteMessage(msg.id)" class="opacity-0 group-hover:opacity-100 p-1.5 rounded-full hover:bg-rose-100 text-rose-500 transition cursor-pointer flex-shrink-0" title="Hapus pesan ini">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </template>

                            <div :class="msg.is_mine ? 'bg-teal-600 text-white rounded-l-2xl rounded-tr-2xl' : 'bg-white border border-slate-200 text-slate-800 rounded-r-2xl rounded-tl-2xl'" class="max-w-[75%] p-3 shadow-sm relative">
                                <p class="text-sm whitespace-pre-wrap" x-text="msg.text"></p>
                                
                                <div :class="msg.is_mine ? 'text-teal-100' : 'text-slate-400'" class="text-[10px] mt-1 font-medium flex justify-end items-center gap-1">
                                    <span x-text="msg.time"></span>
                                    
                                    <!-- Read Receipts -->
                                    <template x-if="msg.is_mine">
                                        <div class="flex items-center -mr-0.5">
                                            <!-- Belum dibaca (Centang 1) -->
                                            <svg x-show="!msg.is_read" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <!-- Sudah dibaca (Centang 2 biru) -->
                                            <svg x-show="msg.is_read" class="w-3.5 h-3.5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12l5 5L20 7"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 17l5 5L20 12"></path></svg>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Delete button (for incoming messages, appears on the right of the bubble) -->
                            <template x-if="!msg.is_mine">
                                <button @click="deleteMessage(msg.id)" class="opacity-0 group-hover:opacity-100 p-1.5 rounded-full hover:bg-rose-100 text-rose-500 transition cursor-pointer flex-shrink-0" title="Hapus pesan ini">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Input Area -->
                <div class="p-4 border-t border-slate-200 bg-white" x-show="selectedContact">
                    <form @submit.prevent="sendMessage()" class="flex gap-2">
                        <textarea 
                            x-model="newMessage" 
                            @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); sendMessage(); }"
                            placeholder="Ketik pesan (Shift + Enter untuk baris baru)..." 
                            class="flex-1 px-4 py-2.5 bg-slate-100 border-transparent focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-200 rounded-xl text-sm transition resize-none overflow-y-auto custom-scrollbar"
                            rows="1"
                            style="min-height: 44px; max-height: 120px;"
                            x-init="$watch('newMessage', val => { 
                                if(val === '') { $el.style.height = '44px'; } 
                                else { $el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 120) + 'px'; }
                            })"
                            @input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 120) + 'px';"
                            required
                        ></textarea>
                        <button type="submit" :disabled="!newMessage.trim() || isSending" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white rounded-xl text-sm font-bold shadow-md transition flex items-center gap-2 cursor-pointer">
                            <span x-show="!isSending">Kirim</span>
                            <span x-show="isSending">...</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function chatSystem() {
    return {
        isOpen: false,
        contacts: [],
        allUsers: [],
        showNewChat: false,
        searchContact: '',
        messages: [],
        selectedContact: null,
        newMessage: '',
        isSending: false,
        totalUnread: 0,
        pollingInterval: null,

        initChat() {
            this.fetchUnreadCount();
            this.fetchContacts();
            
            // Poll for new messages every 10 seconds
            setInterval(() => {
                this.fetchUnreadCount();
                this.fetchContacts(true); // silent fetch updates everything
            }, 10000);
        },

        openChat() {
            this.isOpen = true;
            this.fetchContacts();
        },

        closeChat() {
            this.isOpen = false;
        },

        async fetchUnreadCount() {
            try {
                let res = await fetch('{{ route('chat.unread-count') }}');
                let data = await res.json();
                if(data.success) {
                    this.totalUnread = data.count;
                }
            } catch(e) {}
        },

        async fetchContacts(silent = false) {
            try {
                let res = await fetch('{{ route('chat.contacts') }}');
                let data = await res.json();
                if(data.success) {
                    this.contacts = data.contacts;
                    this.allUsers = data.all_users;
                    
                    // Automatically update open chat if any
                    if (this.selectedContact) {
                        let updatedContact = this.contacts.find(c => c.id === this.selectedContact.id);
                        if (updatedContact) {
                            let oldLen = this.messages.length;
                            this.messages = updatedContact.messages || [];
                            if(this.messages.length > oldLen) {
                                this.scrollToBottom();
                            }
                        }
                    }
                }
            } catch(e) {}
        },

        filteredAllUsers() {
            if(!this.searchContact) return this.allUsers;
            let term = this.searchContact.toLowerCase();
            return this.allUsers.filter(u => u.name.toLowerCase().includes(term) || u.role.toLowerCase().includes(term));
        },

        startNewChat(user) {
            this.showNewChat = false;
            this.searchContact = '';
            
            // Check if already in contacts list
            let existingContact = this.contacts.find(c => c.id === user.id);
            if(existingContact) {
                this.selectContact(existingContact);
            } else {
                // Temporarily add to contacts so we can chat
                let newContact = {
                    ...user,
                    unread_count: 0,
                    last_message: null,
                    last_message_time: null,
                    messages: []
                };
                this.contacts.unshift(newContact);
                this.selectContact(newContact);
            }
        },

        async selectContact(contact) {
            this.selectedContact = contact;
            // Instantly display messages! No waiting.
            this.messages = contact.messages || [];
            this.scrollToBottom();
            
            // Mark as read in background
            if(contact.unread_count > 0) {
                fetch(`{{ url('/chat/read') }}/${contact.id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                contact.unread_count = 0;
                this.fetchUnreadCount();
            }
        },

        async sendMessage() {
            if(!this.newMessage.trim() || !this.selectedContact) return;
            
            this.isSending = true;
            let text = this.newMessage;
            this.newMessage = '';

            try {
                let res = await fetch('{{ route('chat.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        receiver_id: this.selectedContact.id,
                        message: text
                    })
                });
                let data = await res.json();
                if(data.success) {
                    this.messages.push(data.message);
                    this.scrollToBottom();
                    this.fetchContacts(true); // update last message snippet
                }
            } catch(e) {}

            this.isSending = false;
        },

        async deleteMessage(id) {
            if(!confirm('Apakah Anda yakin ingin menghapus pesan ini?')) return;
            try {
                let res = await fetch(`{{ url('/chat/message') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                let data = await res.json();
                if(data.success) {
                    this.messages = this.messages.filter(m => m.id !== id);
                    this.fetchContacts(true); // update last message snippet
                }
            } catch (e) {}
        },

        async clearChat() {
            if(!this.selectedContact || !this.messages.length) return;
            if(!confirm(`Apakah Anda yakin ingin menghapus seluruh riwayat percakapan dengan ${this.selectedContact.name}?`)) return;
            
            try {
                let res = await fetch(`{{ url('/chat/clear') }}/${this.selectedContact.id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                let data = await res.json();
                if(data.success) {
                    this.messages = [];
                    // Update contact list to remove them if we have no chat history
                    this.contacts = this.contacts.filter(c => c.id !== this.selectedContact.id);
                    this.selectedContact = null;
                }
            } catch (e) {}
        },

        scrollToBottom() {
            setTimeout(() => {
                let container = document.getElementById('chat-messages-container');
                if(container) {
                    container.scrollTop = container.scrollHeight;
                }
            }, 50);
        }
    }
}
</script>
