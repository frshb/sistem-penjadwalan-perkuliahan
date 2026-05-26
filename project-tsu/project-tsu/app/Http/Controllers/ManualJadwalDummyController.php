<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ManualJadwalDummyController extends Controller
{
    /**
     * Show the manual scheduling page (Dummy/Frontend only).
     */
    public function index()
    {
        // Strictly return the view, no DB queries.
        return view('penjadwalan.penjadwalan-manual');
    }
}
