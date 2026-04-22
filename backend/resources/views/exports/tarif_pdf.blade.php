<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Tarif</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #111;
            padding: 28px 36px;
        }

        .header { text-align: center; margin-bottom: 16px; }
        .header h1 { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .header h2 { font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .header .periode { font-size: 11px; }
        .header-line {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 4px;
            margin-bottom: 14px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        thead tr th {
            font-weight: bold;
            text-align: center;
            padding: 6px 5px;
            border: 1px solid #000;
            font-size: 11px;
            background-color: #f2f2f2; 
        }
        tbody tr td {
            padding: 5px 5px;
            border: 1px solid #000;
            vertical-align: top;
            font-size: 11px;
        }
        .col-no        { width: 5%;  text-align: center; }
        .col-jenis     { width: 22%; }
        .col-tahun     { width: 13%; text-align: center; }
        .col-deskripsi { width: 27%; }
        .col-nominal   { width: 17%; text-align: right; }
        .col-tgl       { width: 16%; text-align: center; }
        .total-row td  { font-weight: bold; border-top: 2px solid #000; }
        .total-label   { text-align: right; padding-right: 8px; }
        .total-nominal { text-align: right; }
        .empty-row td  { text-align: center; font-style: italic; padding: 14px; }
        
        .footer { margin-top: 36px; width: 100%; }
        .footer-right  { float: right; text-align: center; font-size: 11px; min-width: 200px; }
        .jabatan       { margin-bottom: 50px; }
        .nama-ttd      { font-weight: bold; border-top: 1px solid #000; padding-top: 3px; display: inline-block; min-width: 180px; }
        .nip           { font-size: 10px; margin-top: 2px; }
        .clearfix::after { content: ""; display: table; clear: both; }
    </style>
</head>
<body>

    <div class="header">
        <h1>SMK BOPKRI 2 YOGYAKARTA</h1>
        <h2>DATA TARIF</h2>
        <p class="periode">Dicetak: {{ $tanggalCetak ?? date('d F Y') }}</p>
    </div>
    <div class="header-line"></div>

    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-jenis">Jenis Tarif</th>
                <th class="col-tahun">TA Anggaran</th>
                <th class="col-deskripsi">Deskripsi Tarif</th>
                <th class="col-nominal">Nominal</th>
                <th class="col-tgl">Tgl Penetapan</th>
            </tr>
        </thead>
        <tbody>
            @if(!isset($data) || collect($data)->isEmpty())
                <tr class="empty-row"><td colspan="6">Tidak ada data tarif.</td></tr>
            @else
                @php $totalNominal = 0; @endphp
                @foreach($data as $index => $item)
                    @php $totalNominal += $item['nominal']; @endphp
                    <tr>
                        <td class="col-no">{{ $index + 1 }}</td>
                        <td class="col-jenis">{{ $item['jenis_tarif'] }}</td>
                        <td class="col-tahun">{{ $item['tahun_anggaran'] }}</td>
                        <td class="col-deskripsi">{{ $item['deskripsi'] }}</td>
                        <td class="col-nominal">{{ number_format($item['nominal'], 0, ',', '.') }}</td>
                        <td class="col-tgl">{{ $item['tgl_penetapan'] }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="4" class="total-label">TOTAL NOMINAL</td>
                    <td class="total-nominal">{{ number_format($totalNominal, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer clearfix">
        <div class="footer-right">
            <p>Yogyakarta, {{ $tanggalCetak ?? date('d F Y') }}</p>
            <p class="jabatan">{{ $role ?? 'Bendahara' }},</p>
            <br><br><br>
            <p class="nama-ttd">Siti Aminah, S.E</p>
            <p class="nip">NIP. 19850505 201001 2 002</p>
        </div>
    </div>
</body>
</html>