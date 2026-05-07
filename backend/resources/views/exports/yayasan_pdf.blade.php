<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan Yayasan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 28px 36px;
            position: relative;
        }

        .header {
            text-align: center;
            margin-bottom: 8px;
        }

        .header h2,
        .header h3,
        .header p {
            margin: 0;
        }

        .header h2 {
            font-size: 18px;
            font-weight: bold;
        }

        .header h3 {
            font-size: 16px;
            font-weight: bold;
        }

        .header p {
            font-size: 12px;
            margin-top: 4px;
        }

        .line {
            border-top: 2px solid #000;
            margin: 10px 0 14px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            word-break: break-word;
        }

        table, th, td {
            border: 1px solid #000;
        }

        th, td {
            padding: 7px 8px;
        }

        th {
            text-align: center;
            background: #f2f2f2;
            font-weight: bold;
            font-size: 11px;
        }

        td {
            font-size: 11px;
            vertical-align: top;
            white-space: normal;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .total-row td {
            font-weight: bold;
            background: #fff2cc;
        }

        .ttd-box {
            width: 380px;
            margin-left: auto;
            margin-right: 0;
            text-align: center;
        }

        .ttd-box .jabatan {
            margin-top: 0;
            margin-bottom: 56px;
            font-size: 12px;
        }

        .ttd-box .nama {
            margin-top: 0;
            margin-bottom: -8px;
            font-size: 12px;
            font-weight: bold;
            position: relative;
            z-index: 1;
        }

        .ttd-box .garis {
            margin: 16px auto 10px auto;
            width: 260px;
            border-top: 1px dashed #000;
        }

        .ttd-box .nip {
            font-size: 12px;
        }

        .ttd-date {
            text-align: right;
            margin-bottom: 12px;
            font-size: 12px;
        }

        .ttd-wrap {
            width: 100%;
            margin-top: 50px;
            text-align: right;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.05;
            z-index: -1;
        }
    </style>
</head>
<body>

<img src="{{ public_path('logo.png') }}" class="watermark" width="400">

<div class="header">
    <h2>SMK BOPKRI 2 YOGYAKARTA</h2>
    <h3>LAPORAN KEUANGAN YAYASAN</h3>
    <p>Periode Tahun Anggaran: {{ $tahun ?? '-' }}</p>
</div>

<div class="line"></div>

<table>
    <thead>
        <tr>
            <th style="width: 12%">Kode Akun</th>
            <th style="width: 34%">Nama Akun</th>
            <th style="width: 18%">Penerimaan</th>
            <th style="width: 18%">Pengeluaran</th>
            <th style="width: 18%">Saldo</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $row)
        <tr>
            <td class="text-center">{{ $row->KODE_COA ?? '-' }}</td>
            <td class="text-left">{{ $row->NAMA_COA ?? '-' }}</td>
            <td class="text-right">{{ ($row->TOTAL_MASUK ?? 0) == 0 ? '-' : 'Rp ' . number_format($row->TOTAL_MASUK ?? 0, 0, ',', '.') }}</td>
            <td class="text-right">{{ ($row->TOTAL_KELUAR ?? 0) == 0 ? '-' : 'Rp ' . number_format($row->TOTAL_KELUAR ?? 0, 0, ',', '.') }}</td>
            @php $saldo = ($row->TOTAL_MASUK ?? 0) - ($row->TOTAL_KELUAR ?? 0); @endphp
            <td class="text-right">{{ $saldo == 0 ? '-' : 'Rp ' . number_format($saldo, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">Tidak ada data</td>
        </tr>
        @endforelse

        <tr class="total-row">
            <td colspan="2" class="text-right">TOTAL KESELURUHAN</td>
            <td class="text-right">{{ ($totalMasuk ?? 0) == 0 ? '-' : 'Rp ' . number_format($totalMasuk ?? 0, 0, ',', '.') }}</td>
            <td class="text-right">{{ ($totalKeluar ?? 0) == 0 ? '-' : 'Rp ' . number_format($totalKeluar ?? 0, 0, ',', '.') }}</td>
            @php $totalSaldo = ($totalMasuk ?? 0) - ($totalKeluar ?? 0); @endphp
            <td class="text-right">{{ $totalSaldo == 0 ? '-' : 'Rp ' . number_format($totalSaldo, 0, ',', '.') }}</td>
        </tr>
    </tbody>
</table>

@php
    \Carbon\Carbon::setLocale('id');
    $roleLabel = ucwords(strtolower($role ?? 'Bendahara'));
    $nama = $nama ?? '-';
    $nip = $nip ?? '-';
@endphp

<div class="ttd-wrap">
    <div class="ttd-date">Yogyakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
    <div class="ttd-box" style="display:inline-block; vertical-align:top;">
        <p class="jabatan">{{ $roleLabel }},</p>
        <p class="nama">{{ $nama }}</p>
        <div class="garis"></div>
        <p class="nip">NIP: {{ $nip }}</p>
    </div>
</div>

</body>
</html>