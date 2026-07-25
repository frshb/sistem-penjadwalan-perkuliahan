<?php
$html = view('management.kelas.index', [
    'kelas' => App\Models\Kelas::get(),
    'kelasByProdi' => [],
    'searchTerm' => '',
    'prodis' => App\Models\Prodi::where('id_prodi', '!=', 99)->orderBy('nama_prodi')->get(),
    'tahunAkademiks' => App\Models\TahunAkademik::get(),
    'tahunAkademik' => App\Models\TahunAkademik::find(2),
    'mataKuliahs' => App\Models\MataKuliah::get(),
    'kurikulums' => [],
    'stats' => []
])->render();
file_put_contents('test_view.html', $html);
