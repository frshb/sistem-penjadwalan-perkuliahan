@if(isset($isPdf) && $isPdf)
    @include('exports.header_pdf_v3', ['title' => 'DAFTAR PROGRAM STUDI'])
@else
    @include('exports.header', ['title' => 'DAFTAR PROGRAM STUDI'])
@endif

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th>Program Studi</th>
            <th>Kode Prodi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($prodis as $index => $prodi)
        <tr>
            <td style="text-align: center;">{{ $index + 1 }}</td>
            <td>{{ $prodi->nama_prodi }}</td>
            <td>{{ $prodi->kode_prodi }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@if(isset($isPdf) && $isPdf)
</body>
</html>
@endif
