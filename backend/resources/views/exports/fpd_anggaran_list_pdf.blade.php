<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan FPD Anggaran</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h2 { text-align: center; margin-bottom: 4px; }
        p.subtitle { text-align: center; margin: 0 0 16px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #2563eb; color: white; padding: 8px; text-align: left; }
        td { padding: 6px 8px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) td { background-color: #f3f4f6; }
        .footer { margin-top: 20px; font-size: 10px; color: #888; text-align: right; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h2>Laporan Form Pengajuan Dana (FPD)</h2>
    <p class="subtitle">Sistem Informasi Keuangan SMK</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID FPD</th>
                <th>Tanggal FPD</th>
                <th>Program Kerja</th>
                <th>Validator</th>
                <th class="text-right">Nominal Anggaran</th>
                <th class="text-right">Nominal FPD</th>
                <th class="text-right">Nominal Sisa</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row->ID_FPD }}</td>
                <td>{{ $row->TGL_FPD?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $row->programKerja?->PROGRAM_KERJA ?? '-' }}</td>
                <td>{{ $row->NIP_VALIDATOR_FPD ?? '-' }}</td>
                <td class="text-right">{{ number_format($row->NOMINAL_ANGGARAN ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($row->NOMINAL_FPD ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($row->NOMINAL_SISA ?? 0, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center; color:#888;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
</body>
</html>
