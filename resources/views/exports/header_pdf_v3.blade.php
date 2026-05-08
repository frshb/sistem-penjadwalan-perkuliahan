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
        .logo-table tr, .logo-table td {
            border: none !important;
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
        /* Custom Data Table Styling */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            font-size: 12pt; /* Lebih besar untuk mudah dibaca */
            margin-top: 15px;
        }
        .data-table th {
            background-color: #00667f;
            color: #ffffff;
            font-weight: bold;
            padding: 14px 10px; /* Ditambah tinggi vertikalnya */
            border: 1px solid #ddd;
            text-align: center;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 12px 10px; /* Ditambah tinggi vertikalnya */
            border: 1px solid #ddd;
            color: #333;
            line-height: 1.4;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .data-table tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <table class="logo-table" border="0" cellspacing="0" cellpadding="0" style="width: 100%; border: none; margin-bottom: 20px;">
        <tr>
            <td style="width: 100px; padding-bottom: 20px; border: none !important;">
                <img src="{{ public_path('1151.jpg') }}" width="80" style="width: 80px; height: auto;">
            </td>
            <td style="text-align: left; vertical-align: middle; padding-bottom: 20px; border: none !important;">
                <div class="header-title">TSU</div>
                <div class="header-subtitle">TIGA SERANGKAI UNIVERSITY</div>
            </td>
        </tr>
    </table>
    
    <table class="header-table" border="0" cellspacing="0" cellpadding="0">
        <!-- Row 1 -->
        <tr>
            <td class="meta-label" style="border: none !important;">Perguruan Tinggi</td>
            <td style="width: 10px; border: none !important;">:</td>
            <td class="meta-val" style="border: none !important;">Universitas Tiga Serangkai</td>
            <td style="width: 50px; border: none !important;"></td> <!-- Spacer -->
            <td class="meta-label" style="width: 120px; border: none !important;">Tanggal Export</td>
            <td style="width: 10px; border: none !important;">:</td>
            <td class="meta-val" style="border: none !important;">{{ \Carbon\Carbon::now()->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB</td>
        </tr>
        
        <!-- Row 2 -->
        <tr>
            <td class="meta-label" style="border: none !important;">Fakultas</td>
            <td style="border: none !important;">:</td>
            <td class="meta-val" style="border: none !important;">Fakultas Teknik</td>
            <td style="border: none !important;"></td>
            <td class="meta-label" style="border: none !important;">Tahun Ajaran</td>
            <td style="border: none !important;">:</td>
            <td class="meta-val" style="border: none !important;">2025/2026</td>
        </tr>
        
        <!-- Row 3 -->
        <tr>
            <td class="meta-label" style="border: none !important;">Program Studi</td>
            <td style="border: none !important;">:</td>
            <td class="meta-val" style="border: none !important;">
                @if(isset($prodi) && isset($prodi->nama_prodi))
                    {{ $prodi->nama_prodi }}
                @elseif(isset($userProdiName) && $userProdiName)
                    {{ $userProdiName }}
                @else
                    -
                @endif
            </td>
            <td style="border: none !important;"></td>
            <td class="meta-label" style="border: none !important;">Semester</td>
            <td style="border: none !important;">:</td>
            <td class="meta-val" style="border: none !important;">Ganjil</td>
        </tr>
    </table>
    
    <!-- Separator Line -->
    <div style="border-bottom: 3px solid #00667f; margin-bottom: 20px; margin-top: 10px;"></div>

