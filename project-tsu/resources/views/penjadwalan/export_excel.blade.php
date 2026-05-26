<table>
    <thead>
        <tr>
            <th colspan="8" style="text-align: center; font-weight: bold; font-size: 14px;">Hasil Penjadwalan Perkuliahan</th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: center;">Daftar jadwal yang telah dibuat (Otomatis & Manual)</th>
        </tr>
        <tr>
            <th></th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #d9d9d9;">No</th>
            <th style="font-weight: bold; background-color: #d9d9d9;">Hari</th>
            <th style="font-weight: bold; background-color: #d9d9d9;">Waktu</th>
            <th style="font-weight: bold; background-color: #d9d9d9;">Mata Kuliah</th>
            <th style="font-weight: bold; background-color: #d9d9d9;">Kelas</th>
            <th style="font-weight: bold; background-color: #d9d9d9;">Dosen</th>
            <th style="font-weight: bold; background-color: #d9d9d9;">Ruangan</th>
            <th style="font-weight: bold; background-color: #d9d9d9;">Jenis</th>
        </tr>
    </thead>
    <tbody>
        @foreach($jadwal as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->hari->nama_hari ?? '-' }}</td>
                <td>{{ $item->slot->waktu_mulai ?? '-' }} - {{ $item->slot->waktu_selesai ?? '-' }}</td>
                <td>{{ $item->matkul->nama_matkul ?? '-' }}</td>
                <td>{{ $item->kelas ?? '-' }}</td>
                <td>{{ $item->dosen->nama_dosen ?? '-' }}</td>
                <td>{{ $item->ruang->nama_ruang ?? '-' }}</td>
                <td>{{ $item->jenis_jadwal ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
