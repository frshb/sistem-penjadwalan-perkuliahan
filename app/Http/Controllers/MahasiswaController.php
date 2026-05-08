<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
// use App\Models\Mahasiswa;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MahasiswaExport;

class MahasiswaController extends Controller
{

    /**
     * Display the Mahasiswa index page.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Default: Empty or All based on logic
        $mahasiswa = collect(); 

        // if ($user->hasRole('admin') || $user->hasRole('dekan')) {
        //     $mahasiswa = Mahasiswa::all();
        // } elseif ($user->hasRole('kaprodi')) {
        //     // Check if Kaprodi is linked to a Dosen and that Dosen has a Prodi
        //     if ($user->dosen && $user->dosen->id_prodi) {
        //         $mahasiswa = Mahasiswa::where('id_prodi', $user->dosen->id_prodi)->get();
        //     } else {
        //         // Return empty if configuration error (Kaprodi user not linked properly)
        //         $mahasiswa = collect();
        //     }
        // }
        
        // Note: The view is currently "Under Construction" but passing data for future use
        // Note: The view is currently "Under Construction" but passing data for future use
        return view('management.mahasiswa.index', compact('mahasiswa'));
    }

    public function exportExcel()
    {
        return Excel::download(new MahasiswaExport, 'daftar-mahasiswa.xlsx');
    }

    public function exportPdf()
    {
        $mahasiswa = collect();
        $pdf = \PDF::loadView('exports.mahasiswa', [
            'data' => $mahasiswa,
            'isPdf' => true
        ])->setPaper('a4', 'landscape');
        return $pdf->download('daftar-mahasiswa.pdf');
    }
}
