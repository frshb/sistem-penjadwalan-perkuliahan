<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            table-layout: fixed; /* Fix layout */
        }
        .header-table tr, .header-table td {
            border: none !important;
            padding: 5px; /* Increased padding */
            vertical-align: top;
        }
        
        .header-title {
            font-family: Arial, sans-serif;
            font-size: 28pt; /* Increased from 24pt */
            font-weight: bold;
            color: #00667f;
        }
        
        .header-subtitle {
            font-family: Arial, sans-serif;
            font-size: 12pt; /* Increased from 10pt */
            font-weight: bold;
            color: #00667f;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .meta-label {
            font-family: Arial, sans-serif;
            font-size: 11pt; /* Increased from 10pt */
            font-weight: bold;
            font-style: italic;
            width: 150px; /* Slightly wider */
        }
        
        .meta-val {
            font-family: Arial, sans-serif;
            font-size: 11pt; /* Increased from 10pt */
        }
    </style>
</head>
<body>

    <!-- PRIMARY HEADER TABLE - BORDERLESS -->
    <table class="header-table" border="0" cellspacing="0" cellpadding="0">
        <!-- Row 1: Logo & Title -->
        <tr>
            <td style="width: 100px; padding-bottom: 20px;">
                <img src="{{ public_path('1151.jpg') }}" width="80" style="width: 80px; height: auto;">
            </td>
            <td colspan="4" style="text-align: left; vertical-align: middle; padding-bottom: 20px;">
                <div class="header-title">TSU</div>
                <div class="header-subtitle">TIGA SERANGKAI UNIVERSITY</div>
            </td>
        </tr>
    </table>
    
    <!-- METADATA TABLE - BORDERLESS -->
    <table class="header-table" border="0" cellspacing="0" cellpadding="0">
        <!-- Row 1 -->
        <tr>
            <td class="meta-label">Perguruan Tinggi</td>
            <td style="width: 10px;">:</td>
            <td class="meta-val">Universitas Tiga Serangkai</td>
            <td style="width: 50px;"></td> <!-- Spacer -->
            <td class="meta-label" style="width: 100px;">Waktu Cetak</td>
            <td style="width: 10px;">:</td>
            <td class="meta-val">{{ date('d F Y') }}</td>
        </tr>
        
        <!-- Row 2 -->
        <tr>
            <td class="meta-label">Fakultas</td>
            <td>:</td>
            <td class="meta-val">Fakultas Teknik</td>
            <td></td>
            <td class="meta-label">Tahun Ajaran</td>
            <td>:</td>
            <td class="meta-val">2025/2026</td>
        </tr>
        
        <!-- Row 3 -->
        <tr>
            <td class="meta-label">Program Studi</td>
            <td>:</td>
            <td class="meta-val">
                @if(isset($prodi) && isset($prodi->nama_prodi))
                    {{ $prodi->nama_prodi }}
                @elseif(isset($userProdiName) && $userProdiName)
                    {{ $userProdiName }}
                @else
                    -
                @endif
            </td>
            <td></td>
            <td class="meta-label">Semester</td>
            <td>:</td>
            <td class="meta-val">Ganjil</td>
        </tr>
    </table>
    
    <!-- Separator Line -->
    <div style="border-bottom: 2px solid #000; margin-bottom: 15px; margin-top: 5px;"></div>

</body>
</html>
