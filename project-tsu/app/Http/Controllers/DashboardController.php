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
        

        $activeYears = \App\Models\TahunAkademik::where('status_aktif', 1)->get();
        $activeYear = $activeYears->first();

        $sksBeban = 0;
        if ($authUser->id_dosen && $activeYear) {
            $sksBeban = \App\Models\Jadwal::where('id_dosen', $authUser->id_dosen)
                ->where('id_tahunakademik', $activeYear->id_tahunakademik)
                ->sum('durasi_sks');
        }

        $jadwalDosen = null;
        if ($authUser->id_dosen && $activeYear) {
            $jadwalDosen = \App\Models\Jadwal::with(['kelas', 'matakuliah', 'ruangan', 'hari', 'slotMulai'])
                ->where('id_dosen', $authUser->id_dosen)
                ->where('id_tahunakademik', $activeYear->id_tahunakademik)
                ->get()
                ->map(function ($j) {
                    $slotId = $j->id_slot_mulai;
                    $sks = $j->durasi_sks;
                    $slotSelesai = \App\Models\Slot_waktu::find($slotId + $sks - 1);

                    return [
                        'hari' => $j->hari->nama_hari ?? '-',
                        'jam_mulai' => $j->slotMulai->waktu_mulai ?? '-',
                        'jam_selesai' => $slotSelesai->waktu_selesai ?? '-',
                        'kelas' => $j->kelas->nama_kelas ?? '-',
                        'kode_mk' => $j->kode_matkul ?? '-',
                        'nama_mk' => $j->matakuliah->nama_matkul ?? '-',
                        'sks' => $sks,
                        'ruangan' => $j->ruangan->nama_ruang ?? '-',
                    ];
                })
                ->sortBy(function ($item) {
                    $order = [
                        'senin' => 1,
                        'selasa' => 2,
                        'rabu' => 3,
                        'kamis' => 4,
                        'jumat' => 5,
                        'sabtu' => 6,
                        'minggu' => 7
                    ];
                    return [$order[strtolower($item['hari'])] ?? 8, $item['jam_mulai']];
                })
                ->values();
        }

        $displayName = $authUser->username;
        if ($authUser->dosen && $authUser->dosen->nama_dosen) {
            $displayName = $authUser->dosen->nama_dosen;
        }

        $roleName = $authUser->role ? $authUser->role->nama_role : 'User';

        $user = [
            'name' => $displayName,
            'role' => $roleName,
            'sks_beban' => $sksBeban
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

        return view('dashboard.index', compact('user', 'kalenderAkademik', 'activeYear', 'activeYears', 'jadwalDosen'));
    }
}