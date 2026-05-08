@if(isset($isPdf) && $isPdf)
    @include('exports.header_pdf_v3', ['title' => 'DAFTAR MATA KULIAH'])
@else
    @include('exports.header', ['title' => 'DAFTAR MATA KULIAH'])
@endif

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th>Kode Matkul</th>
            <th>Mata Kuliah</th>
            <th style="width: 10%;">SKS</th>
            <th style="width: 15%;">Tipe</th>
            <th style="width: 15%;">Semester</th>
            <th>Kurikulum</th>
        </tr>
    </thead>
    <tbody>
        @foreach($matkuls as $index => $matkul)
        <tr>
            <td style="text-align: center;">{{ $index + 1 }}</td>
            <td>{{ $matkul->kode_matkul }}</td>
            <td>{{ $matkul->nama_matkul }}</td>
            <td style="text-align: center;">{{ $matkul->sks }}</td>
            <td style="text-align: center;">{{ $matkul->jenis }}</td>
            <td style="text-align: center;">{{ $matkul->semester }}</td>
            <td>{{ $matkul->kurikulum->nama_kurikulum ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@if(isset($isPdf) && $isPdf)
</body>
</html>
@endif
