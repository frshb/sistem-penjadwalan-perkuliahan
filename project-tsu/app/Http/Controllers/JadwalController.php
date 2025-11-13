<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index()
    {
        $jadwal = Jadwal::with(['dosen', 'matkul','kelas', 'ruang', 'hari', 'slot'])->get();
        return view();
    }
}
#belum selesai
