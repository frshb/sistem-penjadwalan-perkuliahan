<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prodi;

class ProdiController extends Controller
{
    public function index()
    {
        $prodis = Prodi::all();
        return view('management.prodi.index', [
            'prodis' => $prodis
        ]);
    }
}
