<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penerimaan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 28px 36px;
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
            table-layout: fixed;
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
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .col-no { width: 42px; }
        .col-tanggal { width: 92px; }
        .col-debit, .col-kredit, .col-saldo { width: 90px; }

        .saldo-row td {
            font-weight: bold;
        }

        .ttd-wrapper {
            width: 100%;
            margin-top: 45px;
            position: relative;
            min-height: 180px;
        }

        .ttd-box {
            width: 280px;
            margin: 0 auto;
            text-align: center;
        }

        .ttd-box .jabatan {
            margin-bottom: 12px;
            font-size: 12px;
        }

        .ttd-box .nama {
            margin-top: 0;
            font-size: 12px;
            font-weight: bold;
        }

        .ttd-box .garis {
            margin: 55px auto 10px auto;
            width: 200px;
            border-top: 1px dashed #000;
        }

        .ttd-box .nip {
            font-size: 12px;
        }

        .tanggal-cetak {
            width: 100%;
            text-align: right;
            margin-top: 55px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>SMK BOPKRI 2 YOGYAKARTA</h2>
        <h3>LAPORAN PENERIMAAN</h3>
        <p>Periode {{ $periode }}</p>
    </div>

    <div class="line"></div>

    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-tanggal">Tanggal</th>
                <th>Uraian</th>
                <th class="col-debit">Debit</th>
                <th class="col-kredit">Kredit</th>
                <th class="col-saldo">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $item)
                <tr>
                    <td class="text-center">{{ $item->no }}</td>
                    <td class="text-center">
                        {{ $item->tanggal ? date('d-m-Y', strtotime($item->tanggal)) : '-' }}
                    </td>
                    <td>{{ $item->uraian }}</td>
                    <td class="text-right">{{ number_format((float) $item->debit, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format((float) $item->kredit, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format((float) $item->saldo, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data penerimaan</td>
                </tr>
            @endforelse
            <tr class="saldo-row">
                <td colspan="5" class="text-center">SALDO AKHIR</td>
                <td class="text-right">{{ number_format((float) $saldoAkhir, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="ttd-wrapper">
        <div class="ttd-box">
            <div class="jabatan">Bendahara,</div>
            <div class="nama">Rina Putri, S.E.</div>
            <div class="garis"></div>
            <div class="nip">NIP: 19800101</div>
        </div>

        <div class="tanggal-cetak">
            Yogyakarta, {{ $tanggalCetak }}
        </div>
    </div>
</body>
</html>