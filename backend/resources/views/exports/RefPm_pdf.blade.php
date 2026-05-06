<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 1cm; }
        body { font-family: sans-serif; font-size: 8pt; line-height: 1.2; }
        .header-title { text-align: center; margin-bottom: 20px; }
        .header-title h2 { margin: 0; font-size: 16pt; text-transform: uppercase; }
        
        table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 4px; word-wrap: break-word; vertical-align: top; overflow: hidden; }
        
        .bg-orange { background-color: #F79646; font-weight: bold; text-align: center; }
        .bg-blue { background-color: #4F81BD; color: white; font-weight: bold; text-align: center; vertical-align: middle; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <div class="header-title">
        <h2>EVALUASI RKT TAHUN 2026</h2>
        <h2>UNIT SEKOLAH SMK BOPKRI 2 YOGYAKARTA</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th class="bg-orange" width="30%">TUJUAN</th>
                <th class="bg-orange">INDIKATOR</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>I. Meningkatkan lulusan yang kompeten, berjiwa entrepreneur, mandiri dan berkarakter kebopkrian.</td>
                <td>I.1. 70% lulusan dapat mengimplemasikan nilai-nilai ke bopkrian.<br>I.2. 25% peserta didik memiliki jiwa entrepreneur, yang bercirikan kebopkrian.<br>I.3. 80% lulusan kompeten, dapat diterima di industri / dunia kerja dan memiliki karakter ke bopkrian.</td>
            </tr>
            <tr>
                <td>II. Meningkatkan Kualitas SDM.</td>
                <td>II.1. Memiliki guru berpendidikan S1 sebanyak 90%.<br>II.2. Memiliki guru produktif bersertifikat kompetensi di bidang keahlian masing-masing, berlisensi industri dan LSP sebanyak 90%.<br>II.3. Memiliki guru bersertifikat pengajar kebutuhan khusus sebanyak 20% untuk mewujudkan sekolah ramah Inklusi.</td>
            </tr>
            <tr>
                <td>III. Mewujudkan tata Kelola yang berkualitas.</td>
                <td>III.1. Memiliki panduan tata kelola yang berkualitas transparan dan akuntabel.<br>III.2. Meningkatkan kualitas sarana dan prasarana sebanyak 95% sesuai standar industri ramah inklusi.</td>
            </tr>
            <tr>
                <td>IV. Mewujudkan sekolah yang mampu berkompetisi era global.</td>
                <td>IV.1. Meningkatkan kerjasama dengan DUDIKA setingkat internasional 20%.<br>IV.2. Meningkatkan kompetensi siswa dalam berbahasa asing 40%.</td>
            </tr>
        </tbody>
    </table>

    <!-- Tabel Utama Evaluasi (Kolom A-Y) -->
    <table>
        <thead>
            <tr>
                <th class="bg-blue" rowspan="3" width="25">NO</th>
                <th class="bg-blue" rowspan="3" width="60">PROGRAM</th>
                <th class="bg-blue" rowspan="3" width="100">KEGIATAN</th>
                <th class="bg-blue" rowspan="3" width="80">SASARAN</th>
                <th class="bg-blue" rowspan="3" width="120">INDIKATOR</th>
                <th class="bg-blue" rowspan="3" width="70">PJ</th>
                <th class="bg-blue" rowspan="3" width="70">ANGGARAN</th>
                <th class="bg-blue" rowspan="3" width="40">POS</th>
                <th class="bg-blue" rowspan="3" width="80">WAKTU</th>
                <th class="bg-blue" rowspan="3" width="80">KELUARAN</th>
                <th class="bg-blue" colspan="8">RELEVANSI</th>
                <th class="bg-blue" rowspan="3">EFEKTIF</th>
                <th class="bg-blue" rowspan="3">EFISIEN</th>
                <th class="bg-blue" rowspan="3">USULAN</th>
                <th class="bg-blue" rowspan="3">KOREKSI</th>
                <th class="bg-blue" rowspan="3">TANGGAPAN</th>
                <th class="bg-blue" rowspan="3">EVALUASI</th>
                <th class="bg-blue" rowspan="3">REKOMENDASI</th>
            </tr>
            <tr>
                <th class="bg-blue" colspan="2">VISI</th>
                <th class="bg-blue" colspan="2">MISI</th>
                <th class="bg-blue" colspan="2">NILAI</th>
                <th class="bg-blue" colspan="2">TUJUAN</th>
            </tr>
            <tr>
                <th class="bg-blue" width="15">M</th><th class="bg-blue" width="15">K</th>
                <th class="bg-blue" width="15">M</th><th class="bg-blue" width="15">K</th>
                <th class="bg-blue" width="15">M</th><th class="bg-blue" width="15">K</th>
                <th class="bg-blue" width="15">M</th><th class="bg-blue" width="15">K</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $row['program_utama'] }}</td>
                <td>{{ $row['program_kerja'] }}</td>
                <td>{{ $row['sasaran'] }}</td>
                <td>{{ $row['indikator'] }}</td>
                <td>{{ $row['nip_pj'] }}</td>
                <td class="center">{{ number_format($row['anggaran'], 0, ',', '.') }}</td>
                <td class="center">{{ $row['pos_anggaran'] }}</td>
                <td>{{ $row['waktu'] }}</td>
                <td>{{ $row['keluaran'] }}</td>
                <td class="center">{{ $row['v_m'] }}</td><td class="center">{{ $row['v_k'] }}</td>
                <td class="center">{{ $row['m_m'] }}</td><td class="center">{{ $row['m_k'] }}</td>
                <td class="center">{{ $row['n_m'] }}</td><td class="center">{{ $row['n_k'] }}</td>
                <td class="center">{{ $row['t_m'] }}</td><td class="center">{{ $row['t_k'] }}</td>
                <td>{{ $row['efektif'] }}</td>
                <td>{{ $row['efisien'] }}</td>
                <td>{{ $row['usulan'] }}</td>
                <td>{{ $row['koreksi'] }}</td>
                <td>{{ $row['tanggapan'] }}</td>
                <td>{{ $row['evaluasi_total'] }}</td>
                <td>{{ $row['rekomendasi'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>