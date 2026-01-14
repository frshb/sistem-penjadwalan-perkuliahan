<style>
    @page { margin: 20px 25px; }
    body { margin: 0; padding: 0; }
</style>
<table>
    <thead>
        <tr>
            <td colspan="6" style="text-align: center; font-weight: bold; font-size: 14px; text-transform: uppercase;">
                {{ $title ?? 'LAPORAN DATA' }}
            </td>
        </tr>
    </thead>
</table>

<table style="width: 100%; border: none; font-size: 12px; font-family: sans-serif; margin-bottom: 20px;">
    <tr>
        <td style="width: 15%; font-weight: bold;">Perguruan Tinggi</td>
        <td style="width: 35%;">: Universitas Tiga Serangkai</td>
        <td style="width: 5%;"></td>
        <td style="width: 15%; font-weight: bold;">Waktu Cetak</td>
        <td style="width: 30%;">: {{ date('d F Y') }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">Fakultas</td>
        <td>: Fakultas Teknik</td>
        <td></td>
        <td style="font-weight: bold;">Tahun Ajaran</td>
        <td>: 2025/2026</td> <!-- Static for now per user image context, or make dynamic later -->
    </tr>
    <tr>
        <td style="font-weight: bold;">Program Studi</td>
        <td>: </td>
        <td></td>
        <td style="font-weight: bold;">Semester</td>
        <td>: Ganjil</td>
    </tr>
</table>
