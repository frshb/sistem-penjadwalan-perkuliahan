<!DOCTYPE html>
<html>
<head>
    <title>Hasil Penjadwalan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; }
        h2 { text-align: center; margin-bottom: 5px; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h2>Hasil Penjadwalan Perkuliahan</h2>
    <p class="text-center">Daftar jadwal yang telah dibuat (Otomatis & Manual)</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Hari</th>
                <th>Waktu</th>
                <th>Mata Kuliah</th>
                <th>Kelas</th>
                <th>Dosen</th>
                <th>Ruangan</th>
                <th>Jenis</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($jadwal as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->hari->nama_hari ?? '-' }}</td>
                    <td>{{ $item->slot->waktu_mulai ?? '-' }} - {{ $item->slot->waktu_selesai ?? '-' }}</td>
                    <td>{{ $item->matkul->nama_matkul ?? '-' }}</td>
                    <td class="text-center">{{ $item->kelas ?? '-' }}</td>
                    <td>{{ $item->dosen->nama_dosen ?? '-' }}</td>
                    <td>{{ $item->ruang->nama_ruang ?? '-' }}</td>
                    <td>{{ $item->jenis_jadwal ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
