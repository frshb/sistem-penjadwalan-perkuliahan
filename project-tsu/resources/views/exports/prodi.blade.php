@if(isset($isPdf) && $isPdf)
    @include('exports.header_pdf_v3', ['title' => 'DAFTAR PROGRAM STUDI'])
@else
    @include('exports.header', ['title' => 'DAFTAR PROGRAM STUDI'])
@endif

<table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 11px; font-family: sans-serif;">
    <thead>
        <tr style="background-color: #f0f0f0;">
            <th style="border: 1px solid #000; padding: 6px; width: 5%; text-align: center;">No</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: left;">Program Studi</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: left;">Kode Prodi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($prodis as $index => $prodi)
        <tr>
            <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $index + 1 }}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{ $prodi->nama_prodi }}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{ $prodi->kode_prodi }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
