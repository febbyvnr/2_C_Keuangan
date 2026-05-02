<?php

namespace App\Http\Controllers;

use App\Models\MstProgramKerja;
use App\Models\TrPm;
use App\Models\RefPm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RefPmController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // Mengambil Master Program Kerja beserta transaksinya untuk ditampilkan di aplikasi
            $data = MstProgramKerja::with(['trPm.refPm'])
                ->where('IS_DELETE', 0)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateEvaluasi(Request $request, $id_pm): JsonResponse
    {
        try {
            $trPm = TrPm::findOrFail($id_pm);
            $user = Auth::user();

            // Proteksi: PIC tidak bisa edit jika NIP_VALIDATOR_PM sudah diisi Kepala Sekolah
            if ($user->role === 'PIC' && !empty($trPm->NIP_VALIDATOR_PM)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Akses Terkunci. Sudah divalidasi Kepala Sekolah.'
                ], 403);
            }

            $payload = $request->only(['DESKRIPSI_TR_PM', 'ID_REF_PM']);
            
            // Jika yang login Kepsek, otomatis isi NIP_VALIDATOR_PM
            if ($user->role === 'Kepala Sekolah') {
                $payload['NIP_VALIDATOR_PM'] = $user->nip;
            }

            $trPm->update($payload);

            return response()->json([
                'success' => true, 
                'message' => 'Berhasil diperbarui'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false, 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportExcel()
    {
        if (ob_get_contents()) ob_end_clean();

        return Excel::download(new class implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithCustomStartCell, WithEvents {
            
            private $rowNumber = 1;

            public function collection()
            {
                // Mengambil Master Program Kerja sebagai baris utama Excel
                return MstProgramKerja::with(['trPm'])
                    ->where('IS_DELETE', 0)
                    ->orderBy('ID_PROGRAM_KERJA', 'asc')
                    ->get();
            }

            public function startCell(): string { return 'A12'; }

            public function headings(): array
            {
                return [
                    'NO', 'PROGRAM', 'KEGIATAN', 'SASARAN', 'INDIKATOR KEBERHASILAN', 
                    'PENANGGUNG JAWAB', 'ANGGARAN', 'POS ANGGARAN', 
                    'WAKTU PELAKSANAAN', 'KELUARAN', 
                    'VISI (M)', 'VISI (K)', 'MISI (M)', 'MISI (K)', 'NILAI (M)', 'NILAI (K)', 'TUJUAN (M)', 'TUJUAN (K)',
                    'EFEKTIF', 'EFISIEN', 'USULAN PERUBAHAN', 'KOREKSI DARI YAYASAN', 'TANGGAPAN JAWABAN', 'EVALUASI', 'REKOMENDASI'
                ];
            }

            public function map($mst): array
            {
                $transaksi = $mst->trPm;
                
                // Fungsi pencari data di tabel tr_pm berdasarkan ID_REF_PM
                $getTrVal = function($idRef) use ($transaksi) {
                    $item = $transaksi->where('ID_REF_PM', $idRef)->first();
                    return $item ? $item->DESKRIPSI_TR_PM : '';
                };

                return [
                    $this->rowNumber++,
                    'PROGRAM UTAMA',
                    $mst->PROGRAM_KERJA ?? '-',
                    $mst->SASARAN ?? '-',
                    $mst->INDIKATOR ?? '-',
                    $mst->NIP_PENANGGUNG_JAWAB ?? '-',
                    $mst->TOTAL_PROGKER ?? 0,
                    '-',
                    ($mst->WAKTU_AWAL ?? '') . ' s/d ' . ($mst->WAKTU_AKHIR ?? ''),
                    $mst->{'KELUARAN PROGKER'} ?? '-',
                    
                    // Relevansi (ID 1-8 di ref_pm)
                    $getTrVal(1), $getTrVal(2), 
                    $getTrVal(3), $getTrVal(4), 
                    $getTrVal(5), $getTrVal(6), 
                    $getTrVal(7), $getTrVal(8), 
                    
                    // Evaluasi Tambahan (ID 9-15 di ref_pm)
                    $getTrVal(9), $getTrVal(10), $getTrVal(11), 
                    $getTrVal(12), $getTrVal(13), $getTrVal(14), $getTrVal(15)
                ];
            }

            public function styles(Worksheet $sheet)
            {
                // Judul
                $sheet->mergeCells('A1:Y1');
                $sheet->setCellValue('A1', 'EVALUASI RKT TAHUN 2026');
                $sheet->mergeCells('A2:Y2');
                $sheet->setCellValue('A2', 'UNIT SEKOLAH SMK BOPKRI 2 YOGYAKARTA');
                $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Tujuan & Indikator (Orange)
                $sheet->mergeCells('A4:B4'); $sheet->setCellValue('A4', 'TUJUAN');
                $sheet->mergeCells('C4:Y4'); $sheet->setCellValue('C4', 'INDIKATOR');
                $sheet->getStyle('A4:Y4')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F79646']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);

                $tujuanData = [
                    ["I. Meningkatkan lulusan yang kompeten, berjiwa entrepreneur, mandiri dan berkarakter kebopkrian.", "I.1. 70% lulusan dapat mengimplemasikan nilai-nilai ke bopkrian.\nI.2. 25% peserta didik memiliki jiwa entrepreneur, yang bercirikan kebopkrian.\nI.3. 80% lulusan kompeten, dapat diterima di industri / dunia kerja dan memiliki karakter ke bopkrian."],
                    ["II. Meningkatkan Kualitas SDM.", "II.1. Memiliki guru berpendidikan S1 sebanyak 90%.\nII.2. Memiliki guru produktif bersertifikat kompetensi di bidang keahlian masing-masing, berlisensi industri dan LSP sebanyak 90%.\nII.3. Memiliki guru bersertifikat pengajar kebutuhan khusus sebanyak 20% untuk mewujudkan sekolah ramah Inklusi."],
                    ["III. Mewujudkan tata Kelola yang berkualitas.", "III.1. Memiliki panduan tata kelola yang berkualitas transparan dan akuntabel.\nIII.2. Meningkatkan kualitas sarana dan prasarana sebanyak 95% sesuai standar industri ramah inklusi."],
                    ["IV. Mewujudkan sekolah yang mampu berkompetisi era global.", "IV.1. Meningkatkan kerjasama dengan DUDIKA setingkat internasional 20%.\nIV.2. Meningkatkan kompetensi siswa dalam berbahasa asing 40%."]
                ];

                $row = 5;
                foreach ($tujuanData as $dt) {
                    $sheet->mergeCells("A$row:B$row"); $sheet->setCellValue("A$row", $dt[0]);
                    $sheet->mergeCells("C$row:Y$row"); $sheet->setCellValue("C$row", $dt[1]);
                    $sheet->getStyle("A$row:Y$row")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("A$row:Y$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $sheet->getRowDimension($row)->setRowHeight(110); 
                    $row++;
                }

                // Header Biru
                $sheet->getStyle('A12:Y14')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);

                $sheet->mergeCells('K12:R12'); $sheet->setCellValue('K12', 'RELEVANSI');
                $sheet->mergeCells('K13:L13'); $sheet->setCellValue('K13', 'VISI');
                $sheet->mergeCells('M13:N13'); $sheet->setCellValue('M13', 'MISI');
                $sheet->mergeCells('O13:P13'); $sheet->setCellValue('O13', 'NILAI');
                $sheet->mergeCells('Q13:R13'); $sheet->setCellValue('Q13', 'TUJUAN');

                $labels = ['K14'=>'M','L14'=>'K','M14'=>'M','N14'=>'K','O14'=>'M','P14'=>'K','Q14'=>'M','R14'=>'K'];
                foreach($labels as $cell => $val) { $sheet->setCellValue($cell, $val); }

                $vMerge = ['A','B','C','D','E','F','G','H','I','J','S','T','U','V','W','X','Y'];
                foreach ($vMerge as $col) { $sheet->mergeCells($col.'12:'.$col.'14'); }

                return [];
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function(AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $highestRow = $sheet->getHighestRow();
                        $sheet->getStyle("A12:Y$highestRow")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                        $sheet->getStyle("A15:Y$highestRow")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                    },
                ];
            }
        }, 'evaluasi-rkt-2026.xlsx');
    }
}