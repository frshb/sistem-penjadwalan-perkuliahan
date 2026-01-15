<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Prodi | {{ $prodi->nama_prodi }}</title>
    <link rel="icon" href="{{ asset('favicon_square.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100/50 overflow-x-hidden min-h-screen transition-colors duration-300 font-sans text-gray-900">

    @include('components.sidebar')

    <main id="main-content" :class="sidebarOpen ? 'lg:ml-64' : ''" x-data="{ sidebarOpen: true }" class="flex-1 p-6 sm:p-10 transition-all duration-300 ease-in-out bg-gray-50 min-h-screen lg:ml-64">
        

        <div class="flex justify-between items-center mb-8">
            <div class="flex items-center">
                 <a href="{{ route('prodi.index') }}" class="mr-4 text-gray-500 hover:text-teal-800 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-800">{{ $prodi->nama_prodi }}</h1>
            </div>
            @include('components.header-profile')
        </div>


        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-y-8 gap-x-12">
                

                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Kode</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">{{ $info['kode'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Akreditasi</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">{{ $info['akreditasi'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Rasio Dosen : Mahasiswa</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">{{ $info['rasio_dosen_mhs'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">SK Selenggara</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">{{ $info['sk_selenggara'] }}</dd>
                </div>


                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Biaya Kuliah</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">{{ $info['biaya_kuliah'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Akreditasi Internasional</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">{{ $info['akreditasi_int'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Rasio Diterima : Pendaftar</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">{{ $info['rasio_terima_daftar'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal SK Selenggara</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">{{ $info['tgl_sk'] }}</dd>
                </div>


                  <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal Berdiri</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900">{{ $info['tgl_berdiri'] }}</dd>
                </div>
                 <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Telepon</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        {{ $info['telp'] }}
                    </dd>
                </div>
                 <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Fax</dt>
                    <dd class="mt-1 text-base font-bold text-gray-900 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                         {{ $info['telp'] }}
                    </dd>
                </div>

            </div>
        </div>


    </main>
</body>
</html>

    </main>
</body>
</html>
