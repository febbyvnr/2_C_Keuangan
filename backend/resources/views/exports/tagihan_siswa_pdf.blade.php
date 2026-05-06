<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Tagihan Siswa</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            position: relative;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid black;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background: #f5f5f5;
            font-weight: bold;
        }

        .no-border td {
            border: none !important;
        }

        .header-text {
            text-align: center;
        }

        .title-main {
            font-size: 16px;
            font-weight: bold;
        }

        .title-sub {
            font-size: 14px;
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
            margin: 8px 0 16px 0;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .ttd-center {
            text-align: center;
            margin-top: 70px;
        }

        .ttd-role {
            margin-bottom: 8px;
        }

        .ttd-nama {
            margin-bottom: 10px;
            font-weight: bold;
        }

        .ttd-garis {
            margin-top: 50px;
        }

        .ttd-nip {
            margin-top: 10px;
        }

        .tanggal-kanan {
            text-align: right;
            margin-top: 35px;
        }

        .nowrap {
            white-space: nowrap;
        }
    </style>
</head>
<body>

<img src="{{ public_path('logo.png') }}" class="watermark" width="400">

<table class="no-border">
    <tr>
        <td class="header-text">
            <div class="title-main">SMK BOPKRI 2 YOGYAKARTA</div>
            <div class="title-sub">LAPORAN TAGIHAN SISWA</div>
            <span>Periode: {{ $periode ?? '-' }}</span>
        </td>
    </tr>
</table>

<div class="double-line"></div>

<table>
    <thead>
        <tr>
            <th style="width: 4%;">No</th>
            <th style="width: 7%;">ID</th>
            <th style="width: 18%;">Nama Siswa</th>
            <th style="width: 13%;">Jenis</th>
            <th style="width: 12%;">Periode</th>
            <th style="width: 13%;">Jatuh Tempo</th>
            <th style="width: 12%;">Total</th>
            <th style="width: 12%;">Bayar</th>
            <th style="width: 11%;">Sisa</th>
            <th style="width: 11%;">Status</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
            $totalTagihan = 0;
            $totalBayar = 0;
            $totalSisa = 0;
        @endphp

        @forelse($data as $item)
            @php
                $jumlahTagihan = (float) ($item['JUMLAH_TAGIHAN_SISWA'] ?? 0);

                $sudahBayar = isset($item['TOTAL_PEMBAYARAN'])
                    ? (float) $item['TOTAL_PEMBAYARAN']
                    : (float) collect($item['PEMBAYARAN'] ?? [])->sum('JUMLAH_BAYAR');

                $sisa = isset($item['SISA_TAGIHAN'])
                    ? (float) $item['SISA_TAGIHAN']
                    : max(0, $jumlahTagihan - $sudahBayar);

                $totalTagihan += $jumlahTagihan;
                $totalBayar += $sudahBayar;
                $totalSisa += $sisa;
            @endphp

            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $item['ID_TAGIHAN_SISWA'] ?? '-' }}</td>
                <td class="text-left">
                    {{ $item['NAMA_SISWA_TETAP'] 
                        ?? $item['SISWA']['NAMA_SISWA_TETAP'] 
                        ?? '-' }}
                </td>
                <td class="text-left">
                    {{ $item['JENIS_TAGIHAN']['DESKRIPSI_JENIS_TAGIHAN'] 
                        ?? $item['jenis_tagihan']['DESKRIPSI_JENIS_TAGIHAN'] 
                        ?? $item['jenisTagihan']['DESKRIPSI_JENIS_TAGIHAN'] 
                        ?? '-' }}
                </td>
                <td>
                    {{ $item['BULAN_TAGIHAN_SISWA'] ?? '-' }}
                    {{ $item['TAHUN_TAGIHAN_SISWA'] ?? '' }}
                </td>
                <td class="nowrap">
                    {{ !empty($item['DUEDATETIME_TAGIHAN_SISWA'])
                        ? \Carbon\Carbon::parse($item['DUEDATETIME_TAGIHAN_SISWA'])->translatedFormat('d F Y')
                        : '-' }}
                </td>
                <td class="text-right nowrap">
                    {{ number_format($jumlahTagihan, 0, ',', '.') }}
                </td>
                <td class="text-right nowrap">
                    {{ number_format($sudahBayar, 0, ',', '.') }}
                </td>
                <td class="text-right nowrap">
                    {{ number_format($sisa, 0, ',', '.') }}
                </td>
                <td>
                    {{ $item['STATUS_TAGIHAN_SISWA'] ?? '-' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10">Tidak ada data tagihan siswa</td>
            </tr>
        @endforelse

        <tr>
            <td colspan="6"><b>TOTAL</b></td>
            <td class="text-right"><b>{{ number_format($totalTagihan, 0, ',', '.') }}</b></td>
            <td class="text-right"><b>{{ number_format($totalBayar, 0, ',', '.') }}</b></td>
            <td class="text-right"><b>{{ number_format($totalSisa, 0, ',', '.') }}</b></td>
            <td></td>
        </tr>
    </tbody>
</table>

@php
    \Carbon\Carbon::setLocale('id');
    $role = ucfirst($role ?? 'Bendahara');
    $nama = $nama ?? '-';
    $nip = $nip_ttd ?? '-';
@endphp

<div class="ttd-center">
    <p class="ttd-role">{{ $role }},</p>
    <p class="ttd-nama">{{ $nama }}</p>
    <p class="ttd-garis">-------------------------</p>
    <p class="ttd-nip">NIP: {{ $nip }}</p>
</div>

<div class="tanggal-kanan">
    Yogyakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
</div>

</body>
</html>