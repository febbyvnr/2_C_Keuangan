<!DOCTYPE html>
<html>
<head>
    <title>Laporan RKA</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; }
        .bg-master { background-color: #e6f7ff; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

    <h2>LAPORAN RENCANA KEGIATAN DAN ANGGARAN (RKA)</h2>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kegiatan / Rincian</th>
                <th>Target/Keluaran</th>
                <th>Qty</th>
                <th>Harga Satuan</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $rka)
                <tr class="bg-master">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>PROGRAM: {{ $rka->PROGRAM_KERJA }}</td>
                    <td>{{ $rka->KELUARAN_PROGKER }}</td>
                    <td></td>
                    <td></td>
                    <td class="text-right">Rp {{ number_format($rka->NOMINAL, 0, ',', '.') }}</td>
                </tr>

                @foreach($rka->details as $detail)
                <tr>
                    <td></td>
                    <td>- Sumber Dana ID: {{ $detail->ID_REF_DANA }}</td>
                    <td></td>
                    <td class="text-center">{{ $detail->QTY }}</td>
                    <td class="text-right">Rp {{ number_format($detail->HARGA_SATUAN, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($detail->TOTAL_PROGKER, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

</body>
</html>