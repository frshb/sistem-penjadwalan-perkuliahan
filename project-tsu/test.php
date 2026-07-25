<?php
auth()->login(App\Models\User::where('id_role', 1)->first());
file_put_contents('test_kelas.html', app()->make(Illuminate\Contracts\Http\Kernel::class)->handle(Illuminate\Http\Request::create('/management/kelas', 'GET', ['tahun' => 2]))->getContent());
