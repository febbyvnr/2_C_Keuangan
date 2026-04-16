<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Tagihan Siswa</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }
        h2 {
            text-align: center;
            margin-bottom: 15px;
        }
        p {
            margin: 0 0 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Laporan Tagihan Siswa</h2>
    <p>Tanggal cetak: {{ date('d-m-Y H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                <th>ID Tagihan</th>
                <th>ID Siswa</th>
                <th>Jenis Pembayaran</th>
                <th>Bulan</th>
                <th>Tahun</th>
                <th>Jumlah Tagihan</th>
                <th>Status</th>
                <th>Due Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $item)
                <tr>
                    <td>{{ $item['ID_TAGIHAN_SISWA'] }}</td>
                    <td>{{ $item['ID_SISWA_TETAP'] }}</td>
                    <td>{{ $item['JENIS_PEMBAYARAN']['DESKRIPSI_JENIS_PEMBAYARAN'] ?? '-' }}</td>
                    <td>{{ $item['BULAN_TAGIHAN_SISWA'] }}</td>
                    <td>{{ $item['TAHUN_TAGIHAN_SISWA'] }}</td>
                    <td>Rp {{ number_format($item['JUMLAH_TAGIHAN_SISWA'], 0, ',', '.') }}</td>
                    <td>{{ $item['STATUS_TAGIHAN_SISWA'] }}</td>
                    <td>{{ $item['DUEDATETIME_TAGIHAN_SISWA'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>