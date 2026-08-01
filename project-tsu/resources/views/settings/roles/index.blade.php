<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Role | TSU</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ sidebarOpen: true }" class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300 font-sans">

    @include('components.sidebar')
    @include('components.success-popup')

    <main :class="sidebarOpen ? 'lg:ml-64' : ''" class="flex-1 p-6 sm:p-10 transition-all duration-300 ease-in-out bg-white min-h-screen">
        
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
                    <h1 class="text-3xl font-bold text-gray-900 ml-3 tracking-tight">Management Role</h1>
                </div>
                <p class="text-sm text-gray-500 mt-1 ml-[1.35rem]">Atur hak akses dan perizinan untuk setiap peran dalam sistem.</p>
            </div>
            @include('components.header-profile')
        </div>

        <!-- Alpine Component for Tabs & Form -->
        <div x-data="{ activeTab: '{{ $activeTab }}' }" class="max-w-7xl mx-auto">
            
            <!-- Tabs Navigation -->
            <div class="flex space-x-4 mb-8 border-b border-gray-100 overflow-x-auto pb-1 custom-scrollbar">
                @foreach(array_keys($rolesData) as $roleName)
                    <button 
                        @click="activeTab = '{{ $roleName }}'"
                        :class="activeTab === '{{ $roleName }}' ? 'border-teal-600 text-teal-700 bg-teal-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                        class="px-5 py-3 border-b-2 font-semibold text-sm transition-all duration-200 whitespace-nowrap rounded-t-lg focus:outline-none">
                        {{ $roleName }}
                    </button>
                @endforeach
            </div>

            <!-- Content Area -->
            <form action="{{ route('settings.roles.update') }}" method="POST">
                @csrf
                
                @foreach($rolesData as $roleName => $permissions)
                    <div x-show="activeTab === '{{ $roleName }}'" 
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            style="display: none;">

                        @if(in_array(strtolower($roleName), ['kaprodi', 'sekretaris prodi']))
                            <div class="mb-6 bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900">Pengaturan Spesifik Program Studi</h3>
                                    <p class="text-xs text-gray-500 mt-1">Pilih prodi jika Anda ingin memberikan hak akses yang berbeda dari default.</p>
                                </div>
                                <select 
                                    onchange="window.location.href='?tab={{ urlencode($roleName) }}&prodi_id=' + this.value" 
                                    class="w-full sm:w-auto min-w-[250px] border-gray-300 rounded-xl shadow-sm focus:ring-teal-500 focus:border-teal-500 text-sm font-medium text-gray-700 bg-gray-50">
                                    <option value="">-- Pengaturan Default (Semua Prodi) --</option>
                                    @foreach($prodis as $prodi)
                                        <option value="{{ $prodi->id_prodi }}" {{ $prodiId == $prodi->id_prodi && $activeTab == $roleName ? 'selected' : '' }}>
                                            {{ $prodi->nama_prodi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="space-y-6">

                            <!-- Management Data Section -->
                            <div class="bg-white rounded-2xl mb-6 shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300"
                                    x-data="{ 
                                    enabled: {{ $permissions['management_data']['enabled'] ? 'true' : 'false' }}
                                    }">
                                
                                <input type="hidden" name="permissions[{{ $roleName }}][management_data][enabled]" :value="enabled ? '1' : '0'">

                                <div class="px-6 py-5 border-b border-gray-50 bg-gray-50/30 flex justify-between items-center cursor-pointer" @click="enabled = !enabled">
                                    <div class="flex items-center space-x-3">
                                        <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-black text-gray-900 tracking-tight">Management Data</h3>
                                            <p class="text-xs text-gray-400">Pengelolaan data master</p>
                                        </div>
                                    </div>

                                    <!-- Modern Toggle -->
                                    <div class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
                                            :class="enabled ? 'bg-teal-600' : 'bg-gray-300'">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                :class="enabled ? 'translate-x-6' : 'translate-x-1'"></span>
                                    </div>
                                </div>

                                <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" x-show="enabled" x-collapse>
                                    @foreach($permissions['management_data']['items'] as $index => $item)
                                        <div class="flex flex-col p-5 rounded-2xl border border-gray-100 hover:bg-teal-50/20 hover:border-teal-200 transition-all shadow-sm bg-white" 
                                             x-data="{ 
                                                 readOn: {{ $item['enabled'] ? 'true' : 'false' }}, 
                                                 editOn: {{ ($item['enabled'] && isset($item['access']) && $item['access'] === 'edit') ? 'true' : 'false' }} 
                                             }"
                                             x-init="$watch('editOn', val => { if(val) readOn = true; }); $watch('readOn', val => { if(!val) editOn = false; })">
                                            
                                            <div class="flex items-center justify-between mb-5">
                                                <span class="text-base font-extrabold text-gray-900 select-none">{{ $item['name'] }}</span>
                                            </div>
                                        
                                            <div class="flex items-center justify-end space-x-6 border-t border-gray-100 pt-4 mt-auto">
                                                
                                                <!-- Toggle Read -->
                                                <div class="flex items-center space-x-2.5 cursor-pointer" @click="readOn = !readOn">
                                                    <span class="text-sm font-bold text-gray-600 select-none transition-colors" :class="readOn ? 'text-teal-800 font-extrabold' : ''">Read</span>
                                                    <div class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-200"
                                                            :class="readOn ? 'bg-teal-600' : 'bg-gray-300'">
                                                        <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition duration-200 ease-in-out"
                                                                :class="readOn ? 'translate-x-4.5' : 'translate-x-1'"></span>
                                                    </div>
                                                </div>
                                        
                                                <!-- Toggle Edit -->
                                                <div class="flex items-center space-x-2.5 cursor-pointer" @click="editOn = !editOn">
                                                    <span class="text-sm font-bold text-gray-600 select-none transition-colors" :class="editOn ? 'text-teal-800 font-extrabold' : ''">Edit</span>
                                                    <div class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-200"
                                                            :class="editOn ? 'bg-teal-600' : 'bg-gray-300'">
                                                        <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition duration-200 ease-in-out"
                                                                :class="editOn ? 'translate-x-4.5' : 'translate-x-1'"></span>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                            
                                            <input type="hidden" name="permissions[{{ $roleName }}][management_data][items][{{ $index }}][name]" value="{{ $item['name'] }}">
                                            <input type="hidden" name="permissions[{{ $roleName }}][management_data][items][{{ $index }}][enabled]" :value="readOn ? '1' : '0'">
                                            <input type="hidden" name="permissions[{{ $roleName }}][management_data][items][{{ $index }}][access]" :value="editOn ? 'edit' : 'read'">
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Modul Penjadwalan Section -->
                            <div class="bg-white rounded-2xl mb-6 shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300"
                                    x-data="{ 
                                    enabled: {{ $permissions['modul_penjadwalan']['enabled'] ? 'true' : 'false' }}
                                    }">
                                
                                <input type="hidden" name="permissions[{{ $roleName }}][modul_penjadwalan][enabled]" :value="enabled ? '1' : '0'">

                                <div class="px-6 py-5 border-b border-gray-50 bg-gray-50/30 flex justify-between items-center cursor-pointer" @click="enabled = !enabled">
                                    <div class="flex items-center space-x-3">
                                        <div class="p-2 bg-yellow-50 rounded-lg text-yellow-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-black text-gray-900 tracking-tight">Modul Penjadwalan</h3>
                                            <p class="text-xs text-gray-400">Pengaturan jadwal kuliah</p>
                                        </div>
                                    </div>

                                    <!-- Modern Toggle -->
                                    <div class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
                                            :class="enabled ? 'bg-teal-600' : 'bg-gray-300'">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                :class="enabled ? 'translate-x-6' : 'translate-x-1'"></span>
                                    </div>
                                </div>

                                <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" x-show="enabled" x-collapse>
                                    @foreach($permissions['modul_penjadwalan']['items'] as $index => $item)
                                        <div class="flex flex-col p-5 rounded-2xl border border-gray-100 hover:bg-yellow-50/20 hover:border-yellow-200 transition-all shadow-sm bg-white" 
                                             x-data="{ 
                                                 readOn: {{ $item['enabled'] ? 'true' : 'false' }}, 
                                                 editOn: {{ ($item['enabled'] && isset($item['access']) && $item['access'] === 'edit') ? 'true' : 'false' }} 
                                             }"
                                             x-init="$watch('editOn', val => { if(val) readOn = true; }); $watch('readOn', val => { if(!val) editOn = false; })">
                                            
                                            <div class="flex items-center justify-between mb-5">
                                                <span class="text-base font-extrabold text-gray-900 select-none">{{ $item['name'] }}</span>
                                            </div>
                                        
                                            <div class="flex items-center justify-end space-x-6 border-t border-gray-100 pt-4 mt-auto">
                                                
                                                <!-- Toggle Read -->
                                                <div class="flex items-center space-x-2.5 cursor-pointer" @click="readOn = !readOn">
                                                    <span class="text-sm font-bold text-gray-600 select-none transition-colors" :class="readOn ? 'text-yellow-800 font-extrabold' : ''">Read</span>
                                                    <div class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-200"
                                                            :class="readOn ? 'bg-yellow-500' : 'bg-gray-300'">
                                                        <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition duration-200 ease-in-out"
                                                                :class="readOn ? 'translate-x-4.5' : 'translate-x-1'"></span>
                                                    </div>
                                                </div>
                                        
                                                <!-- Toggle Edit -->
                                                <div class="flex items-center space-x-2.5 cursor-pointer" @click="editOn = !editOn">
                                                    <span class="text-sm font-bold text-gray-600 select-none transition-colors" :class="editOn ? 'text-yellow-800 font-extrabold' : ''">Edit</span>
                                                    <div class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-200"
                                                            :class="editOn ? 'bg-yellow-500' : 'bg-gray-300'">
                                                        <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition duration-200 ease-in-out"
                                                                :class="editOn ? 'translate-x-4.5' : 'translate-x-1'"></span>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                            
                                            <input type="hidden" name="permissions[{{ $roleName }}][modul_penjadwalan][items][{{ $index }}][name]" value="{{ $item['name'] }}">
                                            <input type="hidden" name="permissions[{{ $roleName }}][modul_penjadwalan][items][{{ $index }}][enabled]" :value="readOn ? '1' : '0'">
                                            <input type="hidden" name="permissions[{{ $roleName }}][modul_penjadwalan][items][{{ $index }}][access]" :value="editOn ? 'edit' : 'read'">
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach

                @if($prodiId)
                    <input type="hidden" name="prodi_id" value="{{ $prodiId }}">
                @endif
                <input type="hidden" name="active_tab" :value="activeTab">

                <div class="mt-8 pt-4 flex justify-end sticky bottom-6 z-10">
                    <button type="submit" class="bg-gradient-to-r from-teal-600 to-teal-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl hover:translate-y-[-2px] transition-all duration-300 text-sm ring-4 ring-teal-500/20">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

    </main>
    
</body>
</html>
