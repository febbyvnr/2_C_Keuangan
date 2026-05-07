<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Jenis Tarif</title>
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
            background: #d9d9d9;
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
    <h3>LAPORAN JENIS TARIF</h3>
    <p>Daftar Referensi Jenis Tarif Sekolah</p>
</div>

<div class="line"></div>

<table>
    <thead>
        <tr>
            <th style="width: 10%; background: #d9d9d9;">No</th>
            <th style="width: 90%; background: #d9d9d9;">Deskripsi Jenis Tarif</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $index => $row)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td class="text-left">{{ $row->DESKRIPSI_JENIS_TARIF ?? '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="2" class="text-center">Tidak ada data jenis tarif</td>
        </tr>
        @endforelse

        <tr class="total-row">
            <td class="text-center">TOTAL</td>
            <td class="text-left">{{ $data->count() }}</td>
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