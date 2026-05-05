<!DOCTYPE html>
<html>
<head>
    <style>
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 11px;
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

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }
    </style>
</head>
<body>

<img src="{{ public_path('logo.png') }}" class="watermark" width="400">

<table class="no-border">
    <tr class="no-border">
        <td class="header-text">
            <div class="title-main">SMK BOPKRI 2 YOGYAKARTA</div>
            <div class="title-sub">DAFTAR DATA KEGIATAN</div>
            <span>Data Master Kegiatan</span>
        </td>
    </tr>
</table>

<div class="double-line"></div>

<br>

<table>
    <thead>
        <tr>
            <th style="width: 8%;">No</th>
            <th style="width: 18%;">Kode Kegiatan</th>
            <th style="width: 49%;">Deskripsi Kegiatan</th>
            <th style="width: 25%;">Parent Kegiatan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $i => $item)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td class="text-center">{{ $item->ID_KEGIATAN }}</td>
            <td class="text-left">{{ $item->DESKRIPSI_KEGIATAN }}</td>
            <td class="text-center">{{ optional($item->parent)->DESKRIPSI_KEGIATAN ?? '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="text-center">Data kegiatan tidak tersedia</td>
        </tr>
        @endforelse
    </tbody>
</table>

<br><br><br><br>

@php
\Carbon\Carbon::setLocale('id');
@endphp

<p style="text-align:right; margin-top: 40px;">
    Yogyakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
    <br><br>
    <b>By: Admin / Bendahara</b>
</p>

</body>
</html>