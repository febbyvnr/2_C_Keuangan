<?php

namespace App\Http\Controllers;

use App\Models\RefPm;
use App\Models\TrPm; 
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
                return 'A12'; // Tabel biru mulai di baris 12 supaya atasnya kosong buat tabel oranye
            }

            public function headings(): array
            {
                return [
                    'ID', 
                    'Standar Mutu', 
                    'Evaluasi PIC', 
                    'Capaian Realisasi', 
                    'Evaluasi Kepsek'
                ];
            }

            public function map($trPm): array
            {
                return [
                    $trPm->ID_TR_PM,
                    $trPm->refPm->NAMA_PM ?? '-',
                    $trPm->EVALUASI_PIC ?? 'Belum Diisi',
                    $trPm->CAPAIAN_REALISASI ?? '0%',
                    $trPm->EVALUASI_KEPSEK ?? 'Menunggu Review',
                ];
            }

            public function styles(Worksheet $sheet)
            {
                // 1. HEADER JUDUL (DITENGAHKAN)
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'EVALUASI RKT TAHUN 2026');
                $sheet->mergeCells('A2:E2');
                $sheet->setCellValue('A2', 'UNIT SEKOLAH SMK BOPKRI 2 YOGYAKARTA');
                
                $sheet->getStyle('A1:A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                // 2. TABEL TUJUAN & INDIKATOR (ORANYE)
                $sheet->mergeCells('A4:B4');
                $sheet->setCellValue('A4', 'TUJUAN');
                $sheet->mergeCells('C4:E4');
                $sheet->setCellValue('C4', 'INDIKATOR');
                
                $sheet->getStyle('A4:E4')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F79646']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);

                $tujuanData = [
                    ['I. Meningkatkan lulusan yang kompeten, berjiwa entrepreneur, mandiri dan berkarakter kebopkrian.', "I.1. 70 % lulusan dapat mengimplementasikan nilai-nilai Kebopkrian.\nI.2. 25% peserta didik memiliki jiwa entrepreneur,yang bercirikan kebopkrian\nI.3. 80% lulusan kompeten , dapat diterima di industri /dunia kerja dan memiliki karakter kebopkrian"],
                    ['II. Meningkatkan Kualitas SDM.', "II.1. Memiliki guru berpendidikan S1 sebanyak 90%\nII.2. Memiliki guru produktif bersertifikat kompetensi di bidang keahlian masing- masing, berlisensi industri dan LSP sebanyak 90%.\nII.3. Memiliki guru bersertifikat pengajar berkebutuhan khusus sebanyak 20% untuk mewujudkan sekolah ramah Inklusi"],
                    ['III. Mewujudkan Tata Kelola yang Berkualitas.', "III.1. Memiliki panduan tata Kelola yang berkualitas transparan dan akuntabel\nIII.2. Meningkatkan kualitas Sarana dan Prasarana sebanyak 95% sesuai standar industri ramah inklusi"],
                    ['IV. Mewujudkan sekolah yang mampu berkompetisi di era global', "IV.1. Meningkatkan kerjasama dengan DUDIKA setingkat internasional 20%\nIV.2. Meningkatakan kompetensi siswa dalam berbahasa asing 40%"]
                ];

                $row = 5;
                foreach ($tujuanData as $data) {
                    $sheet->mergeCells("A$row:B$row");
                    $sheet->setCellValue("A$row", $data[0]);
                    $sheet->mergeCells("C$row:E$row");
                    $sheet->setCellValue("C$row", $data[1]);
                    $sheet->getStyle("A$row:E$row")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                    $sheet->getStyle("A$row:E$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $row++;
                }

                // 3. STYLING TABEL BIRU (START CELL A12)
                $sheet->getStyle('A12:E12')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A12:E' . $highestRow)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
                ]);

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
                      ->orWhere('DESKRIPSI_PM', 'like', "%{$keyword}%")
                      ->orWhereHas('trPm.programKerja', function ($q) use ($keyword) {
                          $q->where('PROGRAM_KERJA', 'like', "%{$keyword}%")
                            ->orWhere('INDIKATOR', 'like', "%{$keyword}%");
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

            $role = $request->user()->role; 
            if ($role === 'PIC') {
                if (!empty($trPm->EVALUASI_KEPSEK)) return response()->json(['success' => false, 'message' => 'Akses ditutup.'], 403);
                $trPm->update($request->validate(['EVALUASI_PIC' => 'required|string', 'CAPAIAN_REALISASI' => 'nullable|string']));
            } elseif ($role === 'KEPALA_SEKOLAH') {
                $trPm->update($request->validate(['EVALUASI_KEPSEK' => 'required|string']));
            }

            return response()->json(['success' => true, 'data' => $trPm]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse { /* Kode Show */ return response()->json([]); }
    public function store(Request $request): JsonResponse { /* Kode Store */ return response()->json([]); }
    public function update(Request $request, $id): JsonResponse { /* Kode Update */ return response()->json([]); }
    public function destroy($id): JsonResponse { /* Kode Destroy */ return response()->json([]); }
}