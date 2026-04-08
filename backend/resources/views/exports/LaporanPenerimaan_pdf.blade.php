<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 6px; }
        th { background: #eee; }
    </style>
</head>
<body>

<img src="{{ public_path('logo.png') }}" height="60">

<h3>LAPORAN PENERIMAAN KAS (BKM)</h3>
<p>Periode: {{ $start }} s/d {{ $end }}</p>

<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Jenis</th>
            <th>Uraian</th>
            <th>Jumlah</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
        <tr>
            <td>{{ $row->tanggal }}</td>
            <td>{{ $row->jenis }}</td>
            <td>{{ $row->uraian }}</td>
            <td>{{ number_format($row->jumlah, 0, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="3"><b>TOTAL</b></td>
            <td><b>{{ number_format($total, 0, ',', '.') }}</b></td>
        </tr>
    </tbody>
</table>

</body>
</html>