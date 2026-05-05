<!DOCTYPE html>
<html>
<head>
    <style>
        body { 
            font-family: sans-serif; 
            position: relative;
            font-size: 12px;
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
        }

        th, td { 
            border: 1px solid black; 
            padding: 6px; 
            text-align: center;
        }

        th { 
            background: #e5e7eb; 
            font-weight: bold;
            color: #000000; 
        }

        td {
            color: #1a1a1a;
            font-weight: normal;
        }

        td:last-child {
            text-align: right;
            padding-right: 10px;
        }

        tr:last-child td {
            font-weight: bold;
        }

        tr:last-child td:first-child {
            text-align: right;
        }

        .no-border td {
            border: none !important;
        }

        .header-text {
            text-align: center;
            width: 100%;
        }

        .title-main {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }

        .title-sub {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }

        .header-text span {
            display: block;
            text-align: center;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.05;
            z-index: -1;
        }

        .double-line {
            border-top: 3px solid black;
            border-bottom: 1px solid black;
            margin: 8px 0 12px 0;
        }

        .ttd-center {
            text-align: center;
            margin-top: 80px;
        }

        .ttd-role {
            margin-bottom: 8px;
        }

        .ttd-nama {
            margin-bottom: 10px;
        }

        .ttd-garis {
            margin-top: 50px;   
        }

        .ttd-nip {
            margin-top: 10px;
        }

        .tanggal-kanan {
            text-align: right;
            margin-top: 40px;
        }
    </style>
</head>
<body>

<img src="{{ public_path('logo.png') }}" class="watermark" width="400">

<!-- HEADER -->
<table class="no-border">
    <tr class="no-border">
        <td class="header-text">
            <div class="title-main">SMK BOPKRI 2 YOGYAKARTA</div>
            <div class="title-sub">LAPORAN PENERIMAAN (KM)</div>
            <span>Periode: {{ $start ?? '-' }} s/d {{ $end ?? '-' }}</span>
        </td>
    </tr>
</table>

<div class="double-line"></div>

<br>

<!-- TABLE -->
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
            <td style="text-align:left;">{{ $row->uraian }}</td>
            <td>Rp {{ number_format($row->jumlah, 0, ',', '.') }}</td>
        </tr>
        @endforeach

        <tr>
            <td colspan="3"><b>TOTAL</b></td>
            <td><b>Rp {{ number_format($total, 0, ',', '.') }}</b></td>
        </tr>
    </tbody>
</table>

@php
    \Carbon\Carbon::setLocale('id');

    $role = $role ?? 'Bendahara';

    if ($role === 'Kepala Sekolah') {
        $nama = 'Drs. Budi Santoso';
    } else {
        $role = 'Bendahara';
        $nama = 'Rina Putri, S.E.';
    }

    $nip = $nip ?? '-';
@endphp

<!-- ===================== -->
<!-- TTD -->
<!-- ===================== -->
<div class="ttd-center">
    <p class="ttd-role">{{ $role }},</p>

    <p class="ttd-nama"><b>{{ $nama }}</b></p>

    <p class="ttd-garis">-------------------------</p>
    <p class="ttd-nip">NIP: {{ $nip }}</p>
</div>

<div class="tanggal-kanan">
    Yogyakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
</div>

</body>
</html>