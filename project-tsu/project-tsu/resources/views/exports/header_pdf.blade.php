<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <!-- Cache Buster: {{ time() }} -->
    <style>
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 10pt; }
        
        /* CSS Table Layout for strictly borderless design */
        .dt-table {
            display: table;
            width: 100%;
            border-spacing: 0;
            border-collapse: collapse;
            border: none;
            margin-bottom: 2px;
        }
        .dt-row {
            display: table-row;
            border: none;
        }
        .dt-cell {
            display: table-cell;
            vertical-align: middle;
            padding: 2px;
            border: none;
        }
        
        /* Helper classes */
        .w-logo { width: 100px; vertical-align: middle; }
        .w-title { vertical-align: middle; }
        
        .w-label { width: 140px; font-weight: bold; } /* Slightly wider for "Perguruan Tinggi" */
        .w-sep { width: 10px; text-align: center; }
        .w-val { /* auto */ }
        .w-gap { width: 30px; }

        .logo-img { width: 80px; height: auto; }
        
        .brand-name {
            font-size: 24pt;
            font-weight: bold;
            color: #004d66; /* Approximate teal color from logo */
            text-transform: uppercase;
            line-height: 1;
        }
        .brand-sub {
            font-size: 8pt;
            font-weight: bold;
            color: #004d66;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .meta-container {
            margin-top: 20px;
            margin-bottom: 15px;
        }
        
        /* Specific override to ensure no borders */
        div, img { border: none !important; outline: none !important; }
    </style>
</head>
<body>

<!-- 1. HEADER BRANDING (Logo + TSU Text) -->
<div class="dt-table">
    <div class="dt-row">
        <div class="dt-cell w-logo">
            <img src="{{ public_path('1151.jpg') }}" class="logo-img">
        </div>
        <div class="dt-cell w-title">
            <div class="brand-name">TSU</div>
            <div class="brand-sub">TIGA SERANGKAI UNIVERSITY</div>
        </div>
    </div>
</div>

<!-- 2. METADATA SECTION (Matches User Example) -->
<!-- Layout: 2 Columns.
     Left: Perguruan Tinggi, Fakultas, Program Studi
     Right: Waktu Cetak, Tahun Ajaran, Semester -->
<div class="dt-table meta-container">
    <!-- Row 1 -->
    <div class="dt-row">
        <div class="dt-cell w-label">Perguruan Tinggi</div>
        <div class="dt-cell w-sep">:</div>
        <div class="dt-cell w-val">Universitas Tiga Serangkai</div>
        
        <div class="dt-cell w-gap"></div>
        
        <div class="dt-cell w-label" style="width: 100px;">Waktu Cetak</div>
        <div class="dt-cell w-sep">:</div>
        <div class="dt-cell w-val">{{ date('d F Y') }}</div>
    </div>
    
    <!-- Row 2 -->
    <div class="dt-row">
        <div class="dt-cell w-label">Fakultas</div>
        <div class="dt-cell w-sep">:</div>
        <div class="dt-cell w-val">Fakultas Teknik</div>
        
        <div class="dt-cell w-gap"></div>
        
        <div class="dt-cell w-label">Tahun Ajaran</div>
        <div class="dt-cell w-sep">:</div>
        <div class="dt-cell w-val">2025/2026</div>
    </div>
    
    <!-- Row 3 -->
    <div class="dt-row">
        <div class="dt-cell w-label">Program Studi</div>
        <div class="dt-cell w-sep">:</div>
        <div class="dt-cell w-val">
            @if(isset($prodi) && isset($prodi->nama_prodi))
                {{ $prodi->nama_prodi }}
            @elseif(isset($userProdiName) && $userProdiName)
                {{ $userProdiName }}
            @else
                - 
            @endif
        </div>
        
        <div class="dt-cell w-gap"></div>
        
        <div class="dt-cell w-label">Semester</div>
        <div class="dt-cell w-sep">:</div>
        <div class="dt-cell w-val">Ganjil</div>
    </div>
</div>

<!-- 3. OPTIONAL REPORT TITLE (If user wants it, or we can skip it to match image exactly) -->
<!-- The user image didn't show a big title, but usually exports have one. I'll make it small or subtle if needed. 
     For now, I'll stick to the metadata-only approach as per image. -->

</body>
</html>
