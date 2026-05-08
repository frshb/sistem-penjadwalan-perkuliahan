<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 0;
        }
        
        .header-container {
            width: 100%;
            margin-bottom: 20px;
            overflow: hidden; 
        }
        
        .logo-wrapper {
            float: left;
            width: 100px;
        }
        
        .title-wrapper {
            float: left;
            padding-top: 5px;
        }
        
        .brand-name {
            font-size: 24pt;
            font-weight: bold;
            color: #00667f;
            line-height: 1;
        }
        
        .brand-sub {
            font-size: 10pt;
            font-weight: bold;
            color: #00667f;
            letter-spacing: 1px;
            margin-top: 2px;
            text-transform: uppercase;
        }
        
        /* METADATA SECTION */
        .meta-container {
            width: 100%;
            margin-bottom: 2px;
            font-size: 10pt;
            overflow: hidden; /* Clearfix */
        }
        
        .meta-left {
            float: left;
            width: 60%;
        }
        
        .meta-right {
            float: right;
            width: 35%; /* Left some gap */
        }
        
        .meta-row {
            margin-bottom: 4px;
        }
        
        .label {
            display: inline-block;
            width: 130px;
            font-weight: bold;
            font-style: italic;
        }
        
        .label-sm {
            display: inline-block;
            width: 90px;
            font-weight: bold;
            font-style: italic;
        }
        
        .sep {
            display: inline-block;
            width: 10px;
            text-align: center;
        }
        
        .val {
            display: inline-block;
        }
        
        /* SEPARATOR LINE */
        .separator {
            border-bottom: 3px solid #000;
            border-top: 1px solid #000;
            height: 2px;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        
    </style>
</head>
<body>

    <!-- 1. HEADER BRANDING (Divs/Floats) -->
    <div class="header-container">
        <div class="logo-wrapper">
            <img src="{{ public_path('1151.jpg') }}" style="width: 80px; height: auto;">
        </div>
        <div class="title-wrapper">
            <div class="brand-name">TSU</div>
            <div class="brand-sub">TIGA SERANGKAI UNIVERSITY</div>
        </div>
    </div>
    
    <!-- 2. METADATA (Divs/Columns) -->
    <div class="meta-container">
        <!-- Left Column -->
        <div class="meta-left">
            <div class="meta-row">
                <span class="label">Perguruan Tinggi</span><span class="sep">:</span><span class="val">Universitas Tiga Serangkai</span>
            </div>
            <div class="meta-row">
                <span class="label">Fakultas</span><span class="sep">:</span><span class="val">Fakultas Teknik</span>
            </div>
            <div class="meta-row">
                <span class="label">Program Studi</span><span class="sep">:</span><span class="val">
                    @if(isset($prodi) && isset($prodi->nama_prodi))
                        {{ $prodi->nama_prodi }}
                    @elseif(isset($userProdiName) && $userProdiName)
                        {{ $userProdiName }}
                    @else
                        -
                    @endif
                </span>
            </div>
        </div>
        
        <!-- Right Column -->
        <div class="meta-right">
            <div class="meta-row">
                <span class="label-sm">Waktu Cetak</span><span class="sep">:</span><span class="val">{{ date('d F Y') }}</span>
            </div>
            <div class="meta-row">
                <span class="label-sm">Tahun Ajaran</span><span class="sep">:</span><span class="val">2025/2026</span>
            </div>
            <div class="meta-row">
                <span class="label-sm">Semester</span><span class="sep">:</span><span class="val">Ganjil</span>
            </div>
        </div>
    </div>
    
    <!-- 3. SEPARATOR -->
    <div class="separator"></div>

</body>
</html>
