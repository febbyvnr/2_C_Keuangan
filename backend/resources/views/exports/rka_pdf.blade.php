<!DOCTYPE html>
<html>
<head>
    <title>Laporan RKA</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
            color: #000;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 18px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 7px;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .program {
            font-weight: bold;
        }
        .tanggal {
            font-size: 10px;
            color: #444;
        }
        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            width: 100%;
        }
        .signature {
            width: 250px;
            float: right;
            text-align: center;
        }
        .signature-space {
            height: 70px;
        }
    </style>
</head>

<body>
    <h2>
        LAPORAN RENCANA KEGIATAN DAN ANGGARAN (RKA)
    </h2>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="22%">Program Kerja</th>
                <th width="18%">Indikator</th>
                <th width="15%">Sumber Dana</th>
                <th width="7%">Qty</th>
                <th width="7%">Volume</th>
                <th width="8%">Satuan</th>
                <th width="10%">Harga</th>
                <th width="12%">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotal = 0;
            @endphp
            @forelse($data as $index => $item)
                @php
                    $grandTotal += $item->NOMINAL ?? 0;
                @endphp
                <tr>
                    <td class="text-center">
                        {{ $index + 1 }}
                    </td>
                    <td>
                        <div class="program">
                            {{ $item->rkt->PROGRAM_KERJA ?? '-' }}
                        </div>
                        <div class="tanggal">
                            {{ $item->TGL_AWAL
                                ? \Carbon\Carbon::parse($item->TGL_AWAL)->format('d/m/Y')
                                : '-' }}
                            -
                            {{ $item->TGL_AKHIR
                                ? \Carbon\Carbon::parse($item->TGL_AKHIR)->format('d/m/Y')
                                : '-' }}
                        </div>
                    </td>
                    <td>
                        {{ $item->rkt->INDIKATOR ?? '-' }}
                    </td>
                    <td>
                        {{ $item->refDana->DESKRIPSI_SUMBER_DANA ?? '-' }}
                    </td>
                    <td class="text-center">
                        {{ $item->QTY ?? 0 }}
                    </td>
                    <td class="text-center">
                        {{ $item->VOLUME ?? 0 }}
                    </td>
                    <td class="text-center">
                        {{ $item->SATUAN ?? '-' }}
                    </td>
                    <td class="text-right">
                        Rp {{ number_format($item->HARGA_SATUAN ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="text-right">
                        Rp {{ number_format($item->NOMINAL ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">
                        Tidak ada data RKA
                    </td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="8" class="text-right">
                    TOTAL
                </td>
                <td class="text-right">
                    Rp {{ number_format($grandTotal, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>
    <div class="footer">
        <div class="signature">
            <div>
                Yogyakarta, {{ date('d F Y') }}
            </div>
            <div style="margin-top: 10px;">
                Bendahara
            </div>
            <div class="signature-space"></div>
            <div>
                ______________________
            </div>
        </div>
    </div>
</body>
</html>