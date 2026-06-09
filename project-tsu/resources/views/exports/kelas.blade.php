@if(isset($isPdf) && $isPdf)
    @include('exports.header_pdf_v3', ['title' => 'DAFTAR KELAS'])
@else
    @include('exports.header', ['title' => 'DAFTAR KELAS'])
@endif

<table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 11px; font-family: sans-serif;">
    <thead>
        <tr style="background-color: #f0f0f0;">
            <th style="border: 1px solid #000; padding: 6px; width: 5%; text-align: center;">No</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: left;">Nama Kelas</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: center;">Semester</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: left;">Mata Kuliah</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: center;">Kapasitas</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: left;">Dosen Pengampu</th>
            <th style="border: 1px solid #000; padding: 6px; text-align: left;">Prodi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($kelas as $index => $k)
        <tr>
            <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $index + 1 }}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{ $k->nama_kelas }}</td>
            <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $k->semester }}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{ $k->matakuliah->nama_matkul ?? '-' }} ({{ $k->kode_matkul }})</td>
            <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $k->kapasitas }}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{ $k->dosen->nama_dosen ?? 'Belum Ditentukan' }}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{ $k->prodi->nama_prodi ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
