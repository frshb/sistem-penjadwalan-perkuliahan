@if(isset($isPdf) && $isPdf)
    @include('exports.header_pdf_v3', ['title' => 'DAFTAR DOSEN'])
@else
    @include('exports.header', ['title' => 'DAFTAR DOSEN'])
@endif

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th>Nama Dosen</th>
            <th>NIDN</th>
            <th>Prodi</th>
            <th>Fakultas</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dosens as $index => $dosen)
        <tr>
            <td style="text-align: center;">{{ $index + 1 }}</td>
            <td>{{ $dosen->nama_dosen }}</td>
            <td>{{ $dosen->nidn }}</td>
            <td>Teknik Informatika</td>
            <td>Fakultas Teknik</td>
        </tr>
        @endforeach
    </tbody>
</table>

@if(isset($isPdf) && $isPdf)
</body>
</html>
@endif
