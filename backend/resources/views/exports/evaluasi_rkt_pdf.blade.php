<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Evaluasi RKT</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 4px; }
        p.subtitle { text-align: center; margin: 0 0 16px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #2563eb; color: white; padding: 8px; text-align: left; }
        td { padding: 6px 8px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) td { background-color: #f3f4f6; }
        .footer { margin-top: 20px; font-size: 10px; color: #888; text-align: right; }
    </style>
</head>
<body>
    <h2>Laporan Evaluasi RKT</h2>
    <p class="subtitle">Sistem Informasi Keuangan SMK</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Program Kerja (RKT)</th>
                <th>Jenis PM</th>
                <th>Tanggal Evaluasi</th>
                <th>Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $row)
            <tr>
                <td>{{ $row->ID_PM }}</td>
                <td>{{ $row->programKerja?->PROGRAM_KERJA ?? $row->ID_PROGRAM_KERJA }}</td>
                <td>{{ $row->refPm?->NAMA_PM ?? $row->ID_REF_PM }}</td>
                <td>{{ $row->TGL_PM?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $row->DESKRIPSI_TR_PM ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; color:#888;">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Digenerate pada: {{ now()->format('d/m/Y H:i:s') }}</div>
</body>
</html>
