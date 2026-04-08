<!DOCTYPE html>
<html>
<head>
    <style>
        body { 
            font-family: sans-serif; 
            position: relative;
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
        }

        th, td { 
            border: 1px solid black; 
            padding: 6px; 
        }

        /* HEADER TABLE LEBIH SOFT */
        th { 
            background: #f5f5f5; 
        }

        .no-border td {
            border: none !important;
        }

        .header-text {
            text-align: center;
        }

        /* JUDUL */
        .title-main {
            font-size: 18px;
            font-weight: bold;
        }

        .title-sub {
            font-size: 16px;
            font-weight: bold;
        }

        /* WATERMARK (LEBIH HALUS & TENGAH) */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.05;
            z-index: -1;
        }

        /* GARIS DOBEL */
        .double-line {
            border-top: 3px solid black;
            border-bottom: 1px solid black;
            margin: 8px 0 12px 0;
        }

    </style>
</head>
<body>

<!-- WATERMARK LOGO -->
<img src="{{ public_path('logo.png') }}" class="watermark" width="400">

<!-- HEADER -->
<table class="no-border">
    <tr class="no-border">
        <td class="header-text">
            <div class="title-main">SMK BOPKRI 2 YOGYAKARTA</div>
            <div class="title-sub">LAPORAN PENERIMAAN (KM) </div>
            <span>Periode: {{ $start ?? '-' }} s/d {{ $end ?? '-' }}</span>
        </td>
    </tr>
</table>

<!-- GARIS KOP -->
<div class="double-line"></div>

<br>

<!-- TABLE UTAMA -->
<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Jenis</th>
            <th>Uraian</th>
            <th>Jumlah</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
        <tr>
            <td>{{ date('d-m-Y', strtotime($row->tanggal)) }}</td>
            <td>{{ $row->jenis }}</td>
            <td>{{ $row->uraian }}</td>
            <td>Rp {{ number_format($row->jumlah, 0, ',', '.') }}</td>
        </tr>
        @endforeach

        <tr>
            <td colspan="3"><b>TOTAL</b></td>
            <td><b>Rp {{ number_format($total, 0, ',', '.') }}</b></td>
        </tr>
    </tbody>
</table>

<br><br><br><br>

<!-- TANDA TANGAN -->
<table class="no-border">
    <tr class="no-border">
        
        <td style="text-align: center; width: 50%;">
            Bendahara<br><br><br><br>

            <b>Rina Putri, S.E.</b><br>
            NIP: 1987654321
        </td>

        <td style="text-align: center; width: 50%;">
            Kepala Sekolah<br><br><br><br>

            <b>Drs. Budi Santoso</b><br>
            NIP: 1976543210
        </td>

    </tr>
</table>

<br><br>

@php
\Carbon\Carbon::setLocale('id');
@endphp

<p style="text-align:right; margin-top: 40px;">
    Yogyakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
</p>

</body>
</html>