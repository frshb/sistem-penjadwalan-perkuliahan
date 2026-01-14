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

        // 1. Fetch Manual Events from DB
        $manualEvents = \App\Models\Holiday::whereDate('date', '>=', now())
            ->get()
            ->map(function ($event) {
                return [
                    'title' => $event->name,
                    'date' => \Carbon\Carbon::parse($event->date),
                    'is_national' => false,
                    'source' => 'manual'
                ];
            });

        // 2. Fetch National Holidays from API (Cached for 24 hours)
        $apiEvents = \Illuminate\Support\Facades\Cache::remember('holidays_api', 86400, function () {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(3)->get('https://dayoffapi.vercel.app/api');
                if ($response->successful()) {
                    return $response->json();
                }
                return [];
            } catch (\Exception $e) {
                return [];
            }
        });

        // 3. Process API Events
        $processedApiEvents = collect($apiEvents)
            ->filter(function ($event) {
                return isset($event['tanggal']) && \Carbon\Carbon::parse($event['tanggal']) >= now();
            })
            ->map(function ($event) {
                return [
                    'title' => $event['keterangan'] ?? 'Libur Nasional',
                    'date' => \Carbon\Carbon::parse($event['tanggal']),
                    'is_national' => true,
                    'source' => 'api'
                ];
            });

        // 4. Merge, Sort, and Limit
        $kalenderAkademik = $manualEvents->merge($processedApiEvents)
            ->sortBy('date')
            ->values()
            ->take(5);

        return view('dashboard.index', compact('user', 'kalenderAkademik'));
    }
}