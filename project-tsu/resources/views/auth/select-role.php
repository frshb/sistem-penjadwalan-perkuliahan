<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Role - Sistem Penjadwalan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="min-h-screen flex flex-col items-center justify-center p-6">

    <!-- LOGO -->
    <div class="mb-6 flex flex-col items-center">
        <img src="/images/logo-tsu.png" class="h-16" alt="Logo Universitas">
        <h2 class="text-2xl font-semibold mt-4 text-gray-700">
            Pilih Peran Anda
        </h2>
        <p class="text-gray-500 text-sm">
            Anda memiliki lebih dari satu peran. Pilih untuk melanjutkan.
        </p>
    </div>

    <!-- ROLE LIST -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 w-full max-w-4xl">

        @foreach($roles as $role)
        <form method="POST" action="/auth/select-role">
            @csrf
            <input type="hidden" name="role" value="{{ $role }}">

            <button type="submit"
                    class="group bg-white shadow-md rounded-xl p-6 border hover:border-teal-600 transition-all cursor-pointer flex flex-col items-center text-center w-full">

                <!-- ICON -->
                <div class="h-12 w-12 bg-teal-600 text-white flex items-center justify-center rounded-xl mb-3 group-hover:bg-teal-700 transition">
                    @if($role === 'super_admin')
                        <span class="text-xl">🌐</span>
                    @elseif($role === 'admin_fakultas')
                        <span class="text-xl">🏛️</span>
                    @elseif($role === 'dekan')
                        <span class="text-xl">🎓</span>
                    @elseif($role === 'kaprodi')
                        <span class="text-xl">📘</span>
                    @elseif($role === 'dosen')
                        <span class="text-xl">👨‍🏫</span>
                    @else
                        <span class="text-xl">🔰</span>
                    @endif
                </div>

                <!-- ROLE NAME -->
                <span class="text-lg font-semibold capitalize text-gray-700 group-hover:text-teal-700">
                    {{ str_replace('_', ' ', $role) }}
                </span>
            </button>
        </form>
        @endforeach

    </div>

</div>

</body>
</html>
