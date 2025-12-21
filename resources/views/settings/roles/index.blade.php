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

    <main :class="sidebarOpen ? 'ml-64' : ''" class="flex-1 p-6 sm:p-10 transition-all duration-300 ease-in-out bg-white min-h-screen">
        
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <div class="flex flex-col">
                    <div class="w-2 h-5 bg-teal-800 rounded-tl-md"></div>
                    <div class="w-2 h-3 bg-yellow-400 rounded-bl-md"></div>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 ml-3">Management Role</h1>
            </div>
            @include('components.header-profile')
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md mt-6 border border-transparent">
            
            <!-- Alpine Component for Tabs -->
            <div x-data="{ activeTab: 'Super Admin' }" class="max-w-4xl">
                
                <!-- Tabs Navigation -->
                <div class="flex space-x-2 mb-6 overflow-x-auto pb-2 border-b border-gray-200">
                    @foreach(array_keys($rolesData) as $roleName)
                        <button 
                            @click="activeTab = '{{ $roleName }}'"
                            :class="activeTab === '{{ $roleName }}' ? 'border-teal-600 text-teal-800' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-4 py-2 border-b-2 font-medium text-sm transition-all duration-200 whitespace-nowrap focus:outline-none">
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
                             x-transition:enter-start="opacity-0 translate-x-4"
                             x-transition:enter-end="opacity-100 translate-x-0"
                             style="display: none;">

                            <div class="space-y-6">

                                <!-- Management Data Section -->
                                <div x-data="{ 
                                    enabled: {{ $permissions['management_data']['enabled'] ? 'true' : 'false' }},
                                    items: {{ json_encode($permissions['management_data']['items']) }}
                                 }">
                                    <div class="flex items-center mb-4">
                                        <span class="text-sm font-bold text-gray-700 w-48">Management Data</span>
                                        <!-- Toggle Button -->
                                        <button type="button" 
                                                @click="enabled = !enabled"
                                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
                                                :class="enabled ? 'bg-teal-800' : 'bg-gray-200'">
                                            <span class="sr-only">Enable Management Data</span>
                                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                  :class="enabled ? 'translate-x-6' : 'translate-x-1'"></span>
                                        </button>
                                    </div>

                                    <div class="ml-4 space-y-2" x-show="enabled" x-transition>
                                        <template x-for="(item, index) in items" :key="index">
                                            <div class="flex items-center">
                                                <span class="text-xs font-medium text-gray-600 w-48 pl-2" x-text="item.name"></span>
                                                <input type="checkbox" :checked="item.enabled" class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500 focus:ring-1 bg-gray-100">
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Modul Penjadwalan Section -->
                                <div x-data="{ 
                                    enabled: {{ $permissions['modul_penjadwalan']['enabled'] ? 'true' : 'false' }},
                                    items: {{ json_encode($permissions['modul_penjadwalan']['items']) }}
                                 }">
                                    <div class="flex items-center mb-4">
                                        <span class="text-sm font-bold text-gray-700 w-48">Modul Penjadwalan</span>
                                        <!-- Toggle Button -->
                                        <button type="button" 
                                                @click="enabled = !enabled"
                                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
                                                :class="enabled ? 'bg-teal-800' : 'bg-gray-200'">
                                            <span class="sr-only">Enable Modul Penjadwalan</span>
                                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                  :class="enabled ? 'translate-x-6' : 'translate-x-1'"></span>
                                        </button>
                                    </div>

                                    <div class="ml-4 space-y-2" x-show="enabled" x-transition>
                                        <template x-for="(item, index) in items" :key="index">
                                            <div class="flex items-center">
                                                <span class="text-xs font-medium text-gray-600 w-48 pl-2" x-text="item.name"></span>
                                                <input type="checkbox" :checked="item.enabled" class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500 focus:ring-1 bg-gray-100">
                                            </div>
                                        </template>
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endforeach

                    <div class="mt-8 border-t border-gray-100 pt-4 flex justify-end">
                        <button type="submit" class="bg-teal-800 text-white font-semibold py-2 px-6 rounded-md shadow hover:bg-teal-900 transition-colors text-xs">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </main>
    
</body>
</html>
