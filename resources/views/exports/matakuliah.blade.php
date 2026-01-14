@if(isset($isPdf) && $isPdf)
    @include('exports.header_pdf_v3', ['title' => 'DAFTAR MATA KULIAH'])
@else
    @include('exports.header', ['title' => 'DAFTAR MATA KULIAH'])
@endif

<table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 11px; font-family: sans-serif;">
    <thead>
        <tr style="background-color: #f0f0f0;">
            <th style="border: 1px solid #000; padding: 6px; width: 5%; text-align: center;">No</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: left;">Kode Matkul</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: left;">Mata Kuliah</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: center;">SKS</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: center;">Tipe</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: center;">Semester</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: left;">Kurikulum</th>
        </tr>
    </thead>
    <tbody>
        @foreach($matkuls as $index => $matkul)
        <tr>
            <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $index + 1 }}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{ $matkul->kode_matkul }}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{ $matkul->nama_matkul }}</td>
            <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $matkul->sks }}</td>
            <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $matkul->jenis }}</td>
            <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $matkul->semester }}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{ $matkul->kurikulum->nama_kurikulum ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
