@if(isset($isPdf) && $isPdf)
    @include('exports.header_pdf_v3', ['title' => 'DAFTAR DOSEN'])
@else
    @include('exports.header', ['title' => 'DAFTAR DOSEN'])
@endif

<table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 11px; font-family: sans-serif;">
    <thead>
        <tr style="background-color: #f0f0f0;">
            <th style="border: 1px solid #000; padding: 6px; width: 5%; text-align: center;">No</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: left;">Nama Dosen</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: left;">NIDN</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: left;">Prodi</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: left;">Fakultas</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: left;">Mata Kuliah</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dosens as $index => $dosen)
        <tr>
            <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $index + 1 }}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{ $dosen->nama_dosen }}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{ $dosen->nidn }}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{ $dosen->prodi->nama_prodi ?? 'Belum Dipilih' }}</td> 
            <td style="border: 1px solid #000; padding: 5px;">Fakultas Teknik</td>
            <td style="border: 1px solid #000; padding: 5px;">
                @php
                    $ganjil = $dosen->mataKuliahs->filter(fn($m) => ((int)$m->semester) % 2 !== 0);
                    $genap = $dosen->mataKuliahs->filter(fn($m) => ((int)$m->semester) % 2 === 0);
                    
                    $lines = [];
                    if ($ganjil->isNotEmpty()) {
                        $lines[] = 'Ganjil: ' . $ganjil->map(fn($m) => $m->nama_matkul . ' (Sem. ' . $m->semester . ')')->implode(', ');
                    }
                    if ($genap->isNotEmpty()) {
                        $lines[] = 'Genap: ' . $genap->map(fn($m) => $m->nama_matkul . ' (Sem. ' . $m->semester . ')')->implode(', ');
                    }
                    echo count($lines) > 0 ? implode(' | ', $lines) : 'Belum mengampu';
                @endphp
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
