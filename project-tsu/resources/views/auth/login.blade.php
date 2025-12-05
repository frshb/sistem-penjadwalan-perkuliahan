<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Penjadwalan Perkuliahan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="min-h-screen flex items-center justify-center p-6">
    <div class="bg-white shadow-xl rounded-xl w-full max-w-5xl overflow-hidden flex">

        <!-- LEFT IMAGE -->
        <div class="w-1/2 hidden md:block">
            <img src="/images/gedung-tsu.jpg"
                 alt="Campus Image"
                 class="w-full h-full object-cover">
        </div>

        <!-- RIGHT FORM -->
        <div class="w-full md:w-1/2 py-10 px-10">

            <!-- LOGO -->
            <div class="flex justify-center mb-6">
                <img src="/images/logo-tsu.png" class="h-16" alt="Logo Universitas">
            </div>

            <h2 class="text-2xl font-semibold text-center mb-8 text-gray-700">
                Selamat Datang
            </h2>

            <p class="text-center text-sm text-gray-500 mb-6">
                Silahkan login menggunakan akun yang telah diberikan
            </p>

            <form method="POST" action="/api/auth/login">
                @csrf

                <!-- USERNAME -->
                <label class="text-sm text-gray-600">Username</label>
                <input type="text" name="username" required
                       class="w-full mt-1 p-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-teal-600 focus:outline-none">

                <!-- PASSWORD -->
                <label class="text-sm text-gray-600 mt-4 block">Password</label>
                <input type="password" name="password" required
                       class="w-full mt-1 p-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-teal-600 focus:outline-none">

                <div class="flex justify-end mt-2">
                    <a href="#" class="text-xs text-gray-500 hover:text-teal-700">Lupa Password?</a>
                </div>

                <!-- BUTTON -->
                <button type="submit"
                        class="w-full mt-6 bg-teal-700 hover:bg-teal-800 text-white p-3 rounded-lg font-semibold">
                    Sign In
                </button>
            </form>

            <!-- FOOTER TEXT -->
            <div class="text-center mt-5 text-xs text-gray-400">
                Sistem Penjadwalan Perkuliahan Fakultas Teknik • UTS
            </div>

        </div>
    </div>
</div>

</body>
</html>
