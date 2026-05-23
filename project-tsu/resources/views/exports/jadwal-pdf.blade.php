<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9px; color: #1f2937; }

    .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #0f766e; padding-bottom: 10px; }
    .header h1 { font-size: 16px; font-weight: bold; color: #0f766e; }
    .header p { font-size: 10px; color: #6b7280; margin-top: 2px; }

    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    thead tr { background-color: #0f766e; color: white; }
    thead th { padding: 6px 5px; text-align: left; font-size: 8px; font-weight: bold; border: 1px solid #0d9488; white-space: nowrap; }
    tbody tr:nth-child(even) { background-color: #f0fdf4; }
    tbody tr:nth-child(odd)  { background-color: #ffffff; }
    tbody td { padding: 5px 5px; border: 1px solid #e5e7eb; vertical-align: middle; }

    .hari-badge { font-weight: bold; color: #0f766e; }
    .kode-badge { font-family: monospace; background: #e0f2fe; color: #0369a1; padding: 1px 4px; border-radius: 3px; font-size: 8px; }
    .sesi-pagi   { background: #fef3c7; color: #92400e; padding: 1px 5px; border-radius: 3px; }
    .sesi-siang  { background: #dbeafe; color: #1e40af; padding: 1px 5px; border-radius: 3px; }
    .sesi-malam  { background: #ede9fe; color: #5b21b6; padding: 1px 5px; border-radius: 3px; }

    .footer { margin-top: 14px; font-size: 8px; color: #9ca3af; text-align: right; }
</style>
</head>
<body>
    <div class="header">
        <h1>Jadwal Perkuliahan</h1>
        <p>{{ $tahunAkademik->nama_tahunakademik }} &nbsp;·&nbsp; Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Hari</th>
                <th>Program Studi</th>
                <th>Smt</th>
                <th>Sesi</th>
                <th>Kelas</th>
                <th>Kode MK</th>
                <th>Mata Kuliah</th>
                <th>Jenis</th>
                <th>SKS</th>
                <th>Dosen</th>
                <th>Ruangan</th>
                <th>Mulai</th>
                <th>Selesai</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($jadwals as $j)
            <tr>
                <td class="hari-badge">{{ $j['hari'] }}</td>
                <td>{{ $j['prodi'] }}</td>
                <td style="text-align:center">{{ $j['semester'] }}</td>
                <td>
                    <span class="sesi-{{ strtolower($j['sesi']) }}">{{ $j['sesi'] }}</span>
                </td>
                <td><strong>{{ $j['kelas'] }}</strong></td>
                <td><span class="kode-badge">{{ $j['kode_mk'] }}</span></td>
                <td>{{ $j['nama_mk'] }}</td>
                <td>{{ $j['jenis'] }}</td>
                <td style="text-align:center">{{ $j['sks'] }}</td>
                <td>{{ $j['dosen'] }}</td>
                <td>{{ $j['ruangan'] }}</td>
                <td style="white-space:nowrap">{{ $j['jam_mulai'] }}</td>
                <td style="white-space:nowrap">{{ $j['jam_selesai'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="13" style="text-align:center; padding:20px; color:#9ca3af;">
                    Belum ada jadwal tersimpan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Total: {{ count($jadwals) }} entri jadwal
    </div>
</body>
</html>
