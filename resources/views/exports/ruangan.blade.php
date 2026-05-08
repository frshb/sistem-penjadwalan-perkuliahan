@if(isset($isPdf) && $isPdf)
    @include('exports.header_pdf_v3', ['title' => 'DAFTAR RUANGAN'])
@else
    @include('exports.header', ['title' => 'DAFTAR RUANGAN'])
@endif

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th>Nama Ruangan</th>
            <th>Gedung</th>
            <th>Lokasi</th>
            <th>Fasilitas</th>
            <th style="width: 10%;">Kapasitas</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ruangans as $index => $ruangan)
        <tr>
            <td style="text-align: center;">{{ $index + 1 }}</td>
            <td>{{ $ruangan->nama_ruang }}</td>
            <td>{{ $ruangan->gedung->nama_gedung ?? '-' }}</td>
            <td>{{ $ruangan->gedung->lokasi ?? '-' }}</td>
            <td>{{ $ruangan->fasilitas }}</td>
            <td style="text-align: center;">{{ $ruangan->kapasitas }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@if(isset($isPdf) && $isPdf)
</body>
</html>
@endif
