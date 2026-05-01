<?php

namespace App\Http\Controllers;

use App\Models\RefPm;
use App\Models\TrPm; 
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
            $data = RefPm::with(['trPm.programKerja'])
                ->orderBy('ID_REF_PM', 'asc')
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

    public function updateEvaluasi(Request $request, $id): JsonResponse
    {
        try {
            $trPm = TrPm::findOrFail($id);
            $user = Auth::user();

            // Proteksi: PIC tidak bisa edit jika sudah divalidasi NIP_VALIDATOR oleh Kepsek
            if ($user->role === 'PIC' && !empty($trPm->NIP_VALIDATOR)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Akses Terkunci. Sudah divalidasi Kepala Sekolah.'
                ], 403);
            }

            $data = $request->all();
            if ($user->role === 'Kepala Sekolah') {
                $data['NIP_VALIDATOR'] = $user->nip;
            }

            $trPm->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Data evaluasi berhasil diperbarui',
                'data' => $trPm,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function exportExcel()
    {
        // Membersihkan buffer agar file .xlsx tidak corrupt/rusak
        if (ob_get_contents()) ob_end_clean();

        return Excel::download(new class implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithCustomStartCell, WithEvents {
            
            private $rowNumber = 1;

            public function collection()
            {
                return RefPm::with(['trPm.programKerja'])
                    ->orderBy('ID_REF_PM', 'asc')
                    ->get();
            }

            public function startCell(): string { return 'A12'; }

            public function headings(): array
            {
                return [
                    'NO', 'PROGRAM', 'KEGIATAN', 'SASARAN', 'INDIKATOR KEBERHASILAN', 
                    'PENANGGUNG JAWAB', 'ANGGARAN', 'POS ANGGARAN/SUMBER PEMBIAYAAN', 
                    'WAKTU PELAKSANAAN', 'KELUARAN', 
                    'VISI (M)', 'VISI (K)', 'MISI (M)', 'MISI (K)', 'NILAI (M)', 'NILAI (K)', 'TUJUAN (M)', 'TUJUAN (K)',
                    'EFEKTIF', 'EFISIEN', 'USULAN PERUBAHAN', 'KOREKSI DARI YAYASAN', 'TANGGAPAN JAWABAN', 'EVALUASI', 'REKOMENDASI'
                ];
            }

            public function map($refPm): array
            {
                $rows = [];
                if ($refPm->trPm->isEmpty()) {
                    $rows[] = [
                        $this->rowNumber++, '-', $refPm->NAMA_PM, '-', '-', '-', 0, '-', '-', '-',
                        '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''
                    ];
                } else {
                    foreach ($refPm->trPm as $tr) {
                        $mst = $tr->programKerja; 
                        $rows[] = [
                            $this->rowNumber++,
                            'PROGRAM UTAMA',
                            $mst->{'PROGRAM KERJA'} ?? $refPm->NAMA_PM, // Handle kolom ber-spasi
                            $mst->SASARAN ?? '-',
                            $mst->INDIKATOR ?? '-',
                            $mst->NIP_PENANGGUNG_JAWAB ?? '-',
                            $mst->{'TOTAL PROGKER'} ?? 0, // Handle kolom ber-spasi
                            $tr->DESKRIPSII_TR_PM ?? '-', 
                            ($mst->{'WAKTU AWAL'} ?? '') . ' s/d ' . ($mst->{'WAKTU AKHIR'} ?? ''),
                            $mst->{'KELUARAN PROGKER'} ?? '-',
                            $tr->RELEVANSI_VISI_M ?? '', $tr->RELEVANSI_VISI_K ?? '',
                            $tr->RELEVANSI_MISI_M ?? '', $tr->RELEVANSI_MISI_K ?? '',
                            $tr->RELEVANSI_NILAI_M ?? '', $tr->RELEVANSI_NILAI_K ?? '',
                            $tr->RELEVANSI_TUJUAN_M ?? '', $tr->RELEVANSI_TUJUAN_K ?? '',
                            $tr->EFEKTIF ?? '', $tr->EFISIEN ?? '',
                            $tr->USULAN_PERUBAHAN ?? '', $tr->KOREKSI_YAYASAN ?? '',
                            $tr->TANGGAPAN_JAWABAN ?? '', $tr->EVALUASI ?? '', $tr->REKOMENDASI ?? '',
                        ];
                    }
                }
                return $rows;
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

                // Header Oranye (Tujuan & Indikator)
                $sheet->mergeCells('A4:B4'); $sheet->setCellValue('A4', 'TUJUAN');
                $sheet->mergeCells('C4:Y4'); $sheet->setCellValue('C4', 'INDIKATOR');
                $sheet->getStyle('A4:Y4')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F79646']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);

                // Blok Tujuan I-IV
                $tujuanData = [
                    ["I. Meningkatkan lulusan yang kompeten, berjiwa entrepreneur, mandiri dan berkarakter kebopkrian.", "I.1. 70% lulusan dapat mengimplemasikan nilai-nilai ke bopkrian.\nI.2.  25% peserta didik memiliki jiwa entrepreneur, yang bercirikan kebopkrian.\nI.3. 80% lulusan kompeten, dapat diterima di industri / dunia kerja dan memiliki karakter ke bopkrian."],
                    ["II. Meningkatkan Kualitas SDM.", "II.1. Memiliki guru berpendidikan S1 sebanyak 90%.\nII.2. Memiliki guru produktif bersertifikat kompetensi di bidang keahlian masing-masing, berlisensi industri dan LSP sebanyak 90%.\nII.3.Memiliki guru bersertifikat pengajar kebutuhan khususs sebanyak 20% untuk mewujudkan sekolah ramah Inklusi."],
                    ["III. Mewujudkan tata Kelola yang berkualitas.", "III.1. Memiliki panduan tata kelola yang berkualitas transparan dan akuntabel\nIII.2. Meningkatkan kualitas sarana dan prasarana sebanyak 95& sesuai standar industri ramah inklusi"],
                    ["IV. Mewujudkan sekolah yang mampu berkompetisi era global.", "IV.1. Meningkatkan kerjasama dengan DUDIKA setingkat internasional 20%.\nIV.2.Meningkatkan kompetensi siwa dalam berbahasa asing 40%."]
                ];
                $row = 5;
                foreach ($tujuanData as $dt) {
                    $sheet->mergeCells("A$row:B$row"); $sheet->setCellValue("A$row", $dt[0]);
                    $sheet->mergeCells("C$row:Y$row"); $sheet->setCellValue("C$row", $dt[1]);
                    $sheet->getStyle("A$row:Y$row")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("A$row:Y$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $sheet->getRowDimension($row)->setRowHeight(80);
                    $row++;
                }

                // Header Biru Utama
                $sheet->getStyle('A12:Y14')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);

                // Header Relevansi
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
                        // Menutup border secara dinamis mengikuti data terakhir
                        $sheet->getStyle("A12:Y$highestRow")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                        $sheet->getStyle("A15:Y$highestRow")->getAlignment()->setWrapText(true);
                    },
                ];
            }
        }, 'evaluasi-rkt-2026.xlsx');
    }
}