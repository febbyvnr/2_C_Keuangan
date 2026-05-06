<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pengeluaran</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; }
        .header h3 { margin: 5px 0; padding: 0; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid black; }
        th { background-color: #2e75b6; color: white; padding: 8px; }
        td { padding: 6px; vertical-align: top; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .total-row { background-color: #ffe699; font-weight: bold; }
        .footer-table { width: 100%; border: none; margin-top: 30px; }
        .footer-table td { border: none; }
        .underline { text-decoration: underline; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>SMK BOPKRI 2 YOGYAKARTA</h2>
        <h3>LAPORAN PENGELUARAN (KK)</h3>
        <p>Periode: {{ $start ?? 'AWAL' }} s/d {{ $end ?? 'AKHIR' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="15%">TANGGAL</th>
                <th width="30%">PROGRAM KERJA</th>
                <th width="15%">SUMBER DANA</th>
                <th width="20%">URAIAN</th>
                <th width="15%">NOMINAL</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($data as $item)
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                <td>{{ $item->program }}</td>
                <td class="text-center">{{ $item->sumber_dana }}</td>
                <td>{{ $item->uraian }}</td>
                <td class="text-right">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Data tidak ditemukan</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL PENGELUARAN</td>
                <td class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="footer-table">
        <tr>
            <td width="65%"></td>
            <td class="text-center">
                Yogyakarta, {{ date('d F Y') }}<br>
                {{ ucfirst($role) }},<br><br><br><br>
                <span class="underline">{{ $nama_ttd }}</span><br>
                NIP: {{ $nip_ttd }}
            </td>
        </tr>
    </table>
</body>
</html>