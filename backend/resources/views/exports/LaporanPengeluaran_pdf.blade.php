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
        }

        td {
            color: #1a1a1a;
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
        }

        .title-sub {
            font-size: 16px;
            font-weight: bold;
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
            <div class="title-sub">LAPORAN PENGELUARAN (KK)</div>
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
            <th>Program</th>
            <th>Sumber Dana</th>
            <th>Uraian</th>
            <th>Nominal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
        <tr>
            <td>{{ date('d-m-Y', strtotime($row->tanggal)) }}</td>
            <td>{{ $row->program }}</td>
            <td>{{ $row->sumber_dana }}</td>
            <td style="text-align:left;">{{ $row->uraian }}</td>
            <td style="text-align:right;">Rp {{ number_format($row->nominal, 0, ',', '.') }}</td>
        </tr>
        @endforeach

        <tr>
            <td colspan="4"><b>TOTAL PENGELUARAN</b></td>
            <td style="text-align:right;"><b>Rp {{ number_format($total, 0, ',', '.') }}</b></td>
        </tr>
    </tbody>
</table>

@php
    \Carbon\Carbon::setLocale('id');

    $role_ttd = ucfirst($role ?? 'Bendahara');
    $nama_ttd = $nama ?? '-';
    $nip_ttd = $nip_ttd ?? '-';
@endphp


<div class="ttd-center">
    <p class="ttd-role">{{ $role_ttd }},</p>

    <p class="ttd-nama"><b>{{ $nama_ttd }}</b></p>

    <p class="ttd-garis">-------------------------</p>
    <p class="ttd-nip">NIP: {{ $nip_ttd }}</p>
</div>

<div class="tanggal-kanan">
    Yogyakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
</div>

</body>
</html>