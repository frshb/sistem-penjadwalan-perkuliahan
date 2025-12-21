<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard utama.
     */
    public function index()
    {
        $authUser = Auth::user();
        

        $displayName = $authUser->username;
        if ($authUser->dosen && $authUser->dosen->nama_dosen) {
            $displayName = $authUser->dosen->nama_dosen;
        }


        $roleName = $authUser->role ? $authUser->role->nama_role : 'User';

        $user = [
            'name' => $displayName,
            'role' => $roleName,
            'sks_beban' => 0
        ];

        $kalenderAkademik = \App\Models\Holiday::orderBy('date', 'asc')
            ->whereDate('date', '>=', now())
            ->take(5)
            ->get()
            ->map(function ($event) {
                return [
                    'title' => $event->name,
                    'date' => \Carbon\Carbon::parse($event->date)->translatedFormat('d F Y'),
                    'status' => 'future' 
                ];
            });

        return view('dashboard.index', compact('user', 'kalenderAkademik'));
    }
}