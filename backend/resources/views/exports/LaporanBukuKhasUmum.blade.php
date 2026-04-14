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

        th { 
            background: #f5f5f5; 
        }

        .no-border td {
            border: none !important;
        }

        .header-text {
            text-align: center;
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

    </style>
</head>
<body>

<!-- WATERMARK -->
<img src="{{ public_path('logo.png') }}" class="watermark" width="400">

<!-- HEADER -->
<table class="no-border">
    <tr>
        <td class="header-text">
            <div class="title-main">SMK BOPKRI 2 YOGYAKARTA</div>
            <div class="title-sub">LAPORAN BUKU KAS UMUM (BKU)</div>
            <span>Periode: {{ $start ?? '-' }} s/d {{ $end ?? '-' }}</span>
        </td>
    </tr>
</table>

<div class="double-line"></div>

<br>

<!-- ===================== -->
<!-- BKU (SEMUA) -->
<!-- ===================== -->
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Uraian</th>
            <th>Debit</th>
            <th>Kredit</th>
            <th>Saldo</th>
        </tr>
    </thead>
    <tbody>
        @php 
            $no = 1; 
            $saldoAkhir = 0;
        @endphp

        @foreach($bku as $row)
        <tr>
            <td>{{ $no++ }}</td>
            <td>{{ date('d-m-Y', strtotime($row->tanggal)) }}</td>
            <td>{{ $row->uraian }}</td>
            <td>Rp {{ number_format($row->debit, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($row->kredit, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($row->saldo, 0, ',', '.') }}</td>
        </tr>

        @php $saldoAkhir = $row->saldo; @endphp
        @endforeach

        <tr>
            <td colspan="5"><b>SALDO AKHIR</b></td>
            <td><b>Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</b></td>
        </tr>
    </tbody>
</table>

<br><br><br>

@php
\Carbon\Carbon::setLocale('id');
@endphp

<p style="text-align:right;">
    Yogyakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
    <br><br>
   <b>By: {{ $role }}</b>
</p>


<!-- ===================== -->
<!-- PAGE BREAK -->
<!-- ===================== -->
<div style="page-break-before: always;"></div>

<!-- ===================== -->
<!-- P1 - TUNAI -->
<!-- ===================== -->
<h3 style="text-align:center;">LAPORAN BKU - TUNAI (P1)</h3>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Uraian</th>
            <th>Debit</th>
            <th>Kredit</th>
            <th>Saldo</th>
        </tr>
    </thead>
    <tbody>
        @php 
            $no = 1; 
            $saldoAkhir = 0;
        @endphp

        @foreach($p1 as $row)
        <tr>
            <td>{{ $no++ }}</td>
            <td>{{ date('d-m-Y', strtotime($row->tanggal)) }}</td>
            <td>{{ $row->uraian }}</td>
            <td>Rp {{ number_format($row->debit, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($row->kredit, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($row->saldo, 0, ',', '.') }}</td>
        </tr>

        @php $saldoAkhir = $row->saldo; @endphp
        @endforeach

        <tr>
            <td colspan="5"><b>SALDO AKHIR</b></td>
            <td><b>Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</b></td>
        </tr>
    </tbody>
</table>


<!-- ===================== -->
<!-- PAGE BREAK -->
<!-- ===================== -->
<div style="page-break-before: always;"></div>

<!-- ===================== -->
<!-- P2 - BANK -->
<!-- ===================== -->
<h3 style="text-align:center;">LAPORAN BKU - BANK (P2)</h3>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Uraian</th>
            <th>Debit</th>
            <th>Kredit</th>
            <th>Saldo</th>
        </tr>
    </thead>
    <tbody>
        @php 
            $no = 1; 
            $saldoAkhir = 0;
        @endphp

        @foreach($p2 as $row)
        <tr>
            <td>{{ $no++ }}</td>
            <td>{{ date('d-m-Y', strtotime($row->tanggal)) }}</td>
            <td>{{ $row->uraian }}</td>
            <td>Rp {{ number_format($row->debit, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($row->kredit, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($row->saldo, 0, ',', '.') }}</td>
        </tr>

        @php $saldoAkhir = $row->saldo; @endphp
        @endforeach

        <tr>
            <td colspan="5"><b>SALDO AKHIR</b></td>
            <td><b>Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</b></td>
        </tr>
    </tbody>
</table>

</body>
</html>