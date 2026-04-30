<?php

namespace App\Http\Controllers;

use App\Models\RefPm;
use App\Models\TrPm; 
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles; 
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell; 
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RefPmController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $data = RefPm::with(['trPm.programKerja'])
                ->orderBy('REF_ID_REF_PM', 'asc')
                ->orderBy('NAMA_PM', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => $data->isEmpty() ? 'Data tidak ditemukan' : 'Data berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function exportExcel()
    {
        return Excel::download(new class implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithCustomStartCell {
            
            public function collection()
            {
                return TrPm::with(['refPm', 'programKerja'])->get();
            }

            public function startCell(): string
            {
                return 'A12'; 
            }

            public function headings(): array
            {
                // Baris 14: Header level 3 (Label paling bawah)
                return [
                    'ID Master', 'Tahun Ajaran', 'Unit Kerja', 'Kode Kegiatan', 
                    'Nama Kegiatan', 'Sasaran', 'Target', 'Penanggung Jawab', 'Prioritas',
                    'ID Transaksi', 'Anggaran', 'Sumber Dana', 'Waktu Pelaksanaan', 
                    'Capaian/Output', 'Hambatan', 'Solusi',
                    'MASIH', 'KURANG', // VISI (Q-R)
                    'MASIH', 'KURANG', // MISI (S-T)
                    'MASIH', 'KURANG', // NILAI (U-V)
                    'MASIH', 'KURANG', // TUJUAN (W-X)
                    'Efektivitas', 'Efisiensi', 'Rekomendasi', 'Evaluasi Kegiatan', 'Tindak Lanjut', 'Status'
                ];
            }

            public function map($trPm): array
            {
                return [
                    $trPm->programKerja->ID_MASTER ?? '',
                    $trPm->programKerja->TAHUN_AJARAN ?? '',
                    $trPm->programKerja->UNIT_KERJA ?? 'SMK BOPKRI 2 YOGYAKARTA',
                    $trPm->programKerja->KODE_KEGIATAN ?? '',
                    $trPm->programKerja->PROGRAM_KERJA ?? '', 
                    $trPm->programKerja->SASARAN ?? '',
                    $trPm->programKerja->TARGET ?? '',
                    $trPm->programKerja->PENANGGUNG_JAWAB ?? '',
                    $trPm->programKerja->PRIORITAS ?? '',
                    $trPm->ID_TR_PM,
                    $trPm->ANGGARAN ?? 0,
                    $trPm->SUMBER_DANA ?? '',
                    $trPm->WAKTU_PELAKSANAAN ?? '',
                    $trPm->CAPAIAN_OUTPUT ?? '',
                    $trPm->HAMBATAN ?? '',
                    $trPm->SOLUSI ?? '',
                    // Mapping Relevansi: Data masuk ke kolom MASIH, kolom KURANG kosong
                    $trPm->refPm->RELEVANSI_VISI ?? '', '', 
                    $trPm->refPm->RELEVANSI_MISI ?? '', '',
                    $trPm->refPm->RELEVANSI_NILAI ?? '', '',
                    $trPm->refPm->RELEVANSI_TUJUAN ?? '', '',
                    $trPm->refPm->EFEKTIVITAS ?? '',
                    $trPm->refPm->EFISIENSI ?? '',
                    $trPm->refPm->REKOMENDASI ?? '',
                    $trPm->refPm->EVALUASI_KEGIATAN ?? '',
                    $trPm->refPm->TINDAK_LANJUT ?? '',
                    $trPm->refPm->STATUS ?? '',
                ];
            }

            public function styles(Worksheet $sheet)
            {
                // 1. JUDUL ATAS
                $sheet->mergeCells('A1:AD1');
                $sheet->setCellValue('A1', 'EVALUASI RKT TAHUN 2026');
                $sheet->mergeCells('A2:AD2');
                $sheet->setCellValue('A2', 'UNIT SEKOLAH SMK BOPKRI 2 YOGYAKARTA');
                $sheet->getStyle('A1:A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                // 2. TABEL TUJUAN & INDIKATOR (ORANYE) - SESUAI GAMBAR image_0c4892.png
                $sheet->mergeCells('A4:B4'); $sheet->setCellValue('A4', 'TUJUAN');
                $sheet->mergeCells('C4:AD4'); $sheet->setCellValue('C4', 'INDIKATOR');
                $sheet->getStyle('A4:AD4')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F79646']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);

                $tujuanData = [
                    [
                        'I. Meningkatkan lulusan yang kompeten, berjiwa entrepreneur, mandiri dan berkarakter kebopkrian.', 
                        "I.1. 70 % lulusan dapat mengimplementasikan nilai-nilai Kebopkrian.\nI.2. 25% peserta didik memiliki jiwa entrepreneur,yang bercirikan kebopkrian\nI.3. 80% lulusan kompeten , dapat diterima di industri /dunia kerja dan memiliki karakter kebopkrian"
                    ],
                    [
                        'II. Meningkatkan Kualitas SDM.', 
                        "II.1. Memiliki guru berpendidikan S1 sebanyak 90%\nII.2. Memiliki guru produktif bersertifikat kompetensi di bidang keahlian masing- masing, berlisensi industri dan LSP sebanyak 90%.\nII.3. Memiliki guru bersertifikat pengajar berkebutuhan khusus sebanyak 20% untuk mewujudkan sekolah ramah Inklusi"
                    ],
                    [
                        'III. Mewujudkan Tata Kelola yang Berkualitas.', 
                        "III.1. Memiliki panduan tata Kelola yang berkualitas transparan dan akuntabel\nIII.2. Meningkatkan kualitas Sarana dan Prasarana sebanyak 95% sesuai standar industri ramah inklusi"
                    ],
                    [
                        'IV. Mewujudkan sekolah yang mampu berkompetisi era global', 
                        "IV.1. Meningkatkan kerjasama dengan DUDIKA setingkat internasional 20%\nIV.2. Meningkatakan kompetensi siswa dalam berbahasa asing 40%"
                    ]
                ];

                $row = 5;
                foreach ($tujuanData as $data) {
                    $sheet->mergeCells("A$row:B$row");
                    $sheet->setCellValue("A$row", $data[0]);
                    $sheet->mergeCells("C$row:AD$row");
                    $sheet->setCellValue("C$row", $data[1]);
                    $sheet->getStyle("A$row:AD$row")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                    $sheet->getStyle("A$row:AD$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $row++;
                }

                // --- 3. HEADER BERLAPIS RELEVANSI (WARNA BIRU) ---
                
                // Baris 12: Group Utama RELEVANSI
                $sheet->mergeCells('Q12:X12');
                $sheet->setCellValue('Q12', 'RELEVANSI');
                
                // Baris 13: Sub-Header (Visi, Misi, Nilai, Tujuan)
                $sheet->mergeCells('Q13:R13'); $sheet->setCellValue('Q13', 'VISI');
                $sheet->mergeCells('S13:T13'); $sheet->setCellValue('S13', 'MISI');
                $sheet->mergeCells('U13:V13'); $sheet->setCellValue('U13', 'NILAI');
                $sheet->mergeCells('W13:X13'); $sheet->setCellValue('W13', 'TUJUAN');

                // Baris 14: Isi manual MASIH & KURANG
                $sheet->setCellValue('Q14', 'MASIH'); $sheet->setCellValue('R14', 'KURANG');
                $sheet->setCellValue('S14', 'MASIH'); $sheet->setCellValue('T14', 'KURANG');
                $sheet->setCellValue('U14', 'MASIH'); $sheet->setCellValue('V14', 'KURANG');
                $sheet->setCellValue('W14', 'MASIH'); $sheet->setCellValue('X14', 'KURANG');

                // Styling Biru untuk seluruh Header (Baris 12-14)
                $sheet->getStyle('A12:AD14')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);

                // 4. MERGE VERTIKAL UNTUK KOLOM SELAIN RELEVANSI (Baris 12 s/d 14)
                $otherCols = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Y','Z','AA','AB','AC','AD'];
                foreach ($otherCols as $col) {
                    $sheet->mergeCells($col . '12:' . $col . '14');
                }

                // Border otomatis untuk semua baris data di bawah header
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A12:AD' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                return [];
            }
        }, 'laporan-evaluasi-rkt-2026.xlsx');
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $keyword = trim((string) $request->query('keyword', ''));
            $query = RefPm::query();

            if ($keyword !== '') {
                $query->where(function ($q) use ($keyword) {
                    $q->where('NAMA_PM', 'like', "%{$keyword}%")
                      ->orWhereHas('trPm.programKerja', function ($q) use ($keyword) {
                          $q->where('PROGRAM_KERJA', 'like', "%{$keyword}%");
                      });
                });
            }

            $data = $query->with(['trPm.programKerja'])->orderBy('REF_ID_REF_PM', 'asc')->get();
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function updateEvaluasi(Request $request, $id): JsonResponse
    {
        try {
            $trPm = TrPm::find($id);
            if (!$trPm) return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);

            $trPm->update($request->all());
            return response()->json(['success' => true, 'data' => $trPm]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse { return response()->json([]); }
    public function store(Request $request): JsonResponse { return response()->json([]); }
    public function update(Request $request, $id): JsonResponse { return response()->json([]); }
    public function destroy($id): JsonResponse { return response()->json([]); }
}