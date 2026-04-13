<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data COA</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }
        h2 {
            text-align: center;
            margin-bottom: 12px;
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
    <h2>Laporan Master COA</h2>
    <p>Tanggal cetak: {{ date('d-m-Y H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Parent ID</th>
                <th>Kode COA</th>
                <th>Deskripsi</th>
                <th>Kode Parent</th>
                <th>Deskripsi Parent</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $item)
                <tr>
                    <td>{{ $item->ID_MASTER_COA }}</td>
                    <td>{{ $item->MST_ID_MASTER_COA ?? '-' }}</td>
                    <td>{{ $item->KODE_COA }}</td>
                    <td>{{ $item->DESKRIPSI_COA }}</td>
                    <td>{{ optional($item->parent)->KODE_COA ?? '-' }}</td>
                    <td>{{ optional($item->parent)->DESKRIPSI_COA ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>