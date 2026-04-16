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

        HEADER TABLE LEBIH SOFT
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

<img src="{{ public_path('logo.png') }}" class="watermark" width="400">

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

<table>
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="15%">Tanggal</th>
            <th width="20%">Program Kerja</th>
            <th width="15%">Sumber Dana</th>
            <th width="30%">Uraian</th>
            <th width="15%">Nominal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
        <tr>
            <td class="text-center">{{ $key + 1 }}</td>
            <td class="text-center">{{ date('d-m-Y', strtotime($row->tanggal)) }}</td>
            <td>{{ $row->program }}</td>
            <td class="text-center">{{ $row->sumber_dana }}</td>
            <td>{{ $row->uraian }}</td>
            <td class="text-right">Rp {{ number_format($row->nominal, 0, ',', '.') }}</td>
        </tr>
        @endforeach

        <tr>
            <td colspan="5" class="text-right"><b>TOTAL PENGELUARAN</b></td>
            <td class="text-right"><b>Rp {{ number_format($total, 0, ',', '.') }}</b></td>
        </tr>
    </tbody>
</table>

@php
\Carbon\Carbon::setLocale('id');
@endphp

<div style="margin-top: 50px;">
    <table class="no-border">
        <tr class="no-border">
            <td width="60%"></td>
            <td class="text-right">
                Yogyakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                <br><br>
                <b>Mengetahui,</b>
                <br>
                <b>By: Bendahara</b>
                <br><br><br><br><br>
                <b>{{ Auth::user()->name ?? }}</b>
            </td>
        </tr>
    </table>
</div>

</body>
</html>