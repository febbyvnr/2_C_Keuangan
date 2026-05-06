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

        #watermark {
            position: fixed;
            top: 25%;
            left: 35%;
            width: 30%;
            opacity: 0.15;
            z-index: -1000;
            text-align: center;
        }
        #watermark img { width: 100%; }

        .header { text-align: center; margin-bottom: 16px; }
        .header h1 { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .header h2 { font-size: 13px; font-weight: bold; text-transform: uppercase; }
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
        .col-no { width: 5%; text-align: center; }
        .col-nominal { width: 17%; text-align: right; }
        .total-row td { font-weight: bold; border-top: 2px solid #000; }
        
        /* --- FOOTER PERSIS SS KE-2 --- */
        .footer { 
            margin-top: 40px; 
            width: 100%; 
        }
        .signature-block {
            text-align: center; /* Posisikan ke Tengah */
            width: 100%;
        }
        .nama-ttd {
            font-weight: bold;
            font-size: 12px;
            margin-top: 2px;
        }
        .signature-gap {
            height: 80px; /* Jarak lega buat TTD basah */
        }
        .dashed-line {
            border-bottom: 1.5px dashed #000; /* Garis putus-putus sesuai SS */
            width: 250px;
            margin: 0 auto 5px auto;
        }
        .date-right {
            text-align: right;
            margin-top: 20px;
            font-size: 11px;
        }
    </style>
</head>
<body>

    @php
        $logoPath = public_path('logo.png');
        $logoData = '';
        if(file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
        }
    @endphp

    @if($logoData)
    <div id="watermark">
        <img src="data:image/png;base64,{{ $logoData }}" alt="Logo">
    </div>
    @endif

    <div class="header">
        <h1>SMK BOPKRI 2 YOGYAKARTA</h1>
        <h2>DATA TARIF</h2>
        <p style="font-size: 10px;">Dicetak: {{ $tanggalCetak ?? date('d F Y') }}</p>
    </div>
    <div class="header-line"></div>

    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th>Jenis Tarif</th>
                <th>TA Anggaran</th>
                <th>Deskripsi Tarif</th>
                <th class="col-nominal">Nominal</th>
                <th>Tgl Penetapan</th>
            </tr>
        </thead>
        <tbody>
            @if(!isset($data) || collect($data)->isEmpty())
                <tr><td colspan="6" style="text-align:center; padding:15px;">Data Kosong</td></tr>
            @else
                @php $totalNominal = 0; @endphp
                @foreach($data as $index => $item)
                    @php $totalNominal += $item['nominal']; @endphp
                    <tr>
                        <td class="col-no">{{ $index + 1 }}</td>
                        <td>{{ $item['jenis_tarif'] }}</td>
                        <td>{{ $item['tahun_anggaran'] }}</td>
                        <td>{{ $item['deskripsi'] }}</td>
                        <td class="col-nominal">{{ number_format($item['nominal'], 0, ',', '.') }}</td>
                        <td style="text-align:center">{{ $item['tgl_penetapan'] }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="4" style="text-align: right; padding-right: 10px;">TOTAL NOMINAL</td>
                    <td style="text-align: right;">{{ number_format($totalNominal, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <!-- Blok Tanda Tangan di Tengah -->
        <div class="signature-block">
            <p>Bendahara,</p>
            <p class="nama-ttd">Siti Aminah, S.E</p>
            
            <div class="signature-gap"></div> <!-- Space Kosong -->
            
            <div class="dashed-line"></div>
            <p>NIP. 19850505 201001 2 002</p>
        </div>
        
        <!-- Tanggal di Kanan Bawah -->
        <div class="date-right">
            <p>Yogyakarta, {{ $tanggalCetak ?? date('d F Y') }}</p>
        </div>
    </div>
</body>
</html>