@if(isset($isPdf) && $isPdf)
    @include('exports.header_pdf_v3', ['title' => 'DAFTAR RUANGAN'])
@else
    @include('exports.header', ['title' => 'DAFTAR RUANGAN'])
@endif

<table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 11px; font-family: sans-serif;">
    <thead>
        <tr style="background-color: #f0f0f0;">
            <th style="border: 1px solid #000; padding: 6px; width: 5%; text-align: center;">No</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: left;">Nama Ruangan</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: left;">Gedung</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: left;">Lokasi</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: left;">Fasilitas</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: center;">Kapasitas</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ruangans as $index => $ruangan)
        <tr>
            <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $index + 1 }}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{ $ruangan->nama_ruang }}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{ $ruangan->gedung->nama_gedung ?? '-' }}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{ $ruangan->gedung->lokasi ?? '-' }}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{ $ruangan->fasilitas }}</td>
            <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $ruangan->kapasitas }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
