<?php

namespace App\Http\Controllers;

use App\Models\RefPm;
use App\Models\MstProgramKerja;
use App\Models\TrPm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
use Barryvdh\DomPDF\Facade\Pdf;

class RefPmController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $search = trim((string) $request->query('search', ''));
            $allData = RefPm::query()
                ->withCount(['children as has_child', 'trPm'])
                ->orderBy('REF_ID_REF_PM', 'asc') 
                ->orderBy('ID_REF_PM', 'asc')
                ->get();
            $allData->map(function ($item) {
                $item->is_used = ($item->tr_pm_count > 0 || $item->has_child > 0);
                return $item;
            });
            $formattedData = $this->generatePmHierarchy($allData);
            if ($search !== '') {
                $formattedData = collect($formattedData)->filter(function ($item) use ($search) {
                    $s = strtolower($search);
                    return str_contains(strtolower($item['NAMA_PM'] ?? ''), $s) ||
                        str_contains(strtolower($item['nomor_urut'] ?? ''), $s) ||
                        str_contains(strtolower($item['DESKRIPSI_PM'] ?? ''), $s);
                })->values();
            }
            return response()->json([
                'success' => true,
                'message' => count($formattedData) === 0 ? 'Data tidak ditemukan' : 'Data berhasil diambil',
                'data' => $formattedData,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function generatePmHierarchy($nodes, $parentId = null, $prefix = "")
    {
        $result = [];
        $index = 1;
        $children = $nodes->where('REF_ID_REF_PM', $parentId);
        foreach ($children as $child) {
            $currentNumber = $prefix ? $prefix . "." . $index : (string)$index;
            $result[] = [
                'ID_REF_PM'     => $child->ID_REF_PM,
                'REF_ID_REF_PM' => $child->REF_ID_REF_PM,
                'NAMA_PM'       => $child->NAMA_PM,
                'DESKRIPSI_PM'       => $child->DESKRIPSI_PM,
                'nomor_urut'    => $currentNumber,
                'has_child'     => $child->has_child > 0,
                'is_used'       => $child->is_used,
                'tr_pm'         => $child->trPm 
            ];
            $subResults = $this->generatePmHierarchy($nodes, $child->ID_REF_PM, $currentNumber);
            $result = array_merge($result, $subResults);
            $index++;
        }
        return $result;
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

            $data = $query->with(['trPm.programKerja'])
                ->orderBy('REF_ID_REF_PM', 'asc')
                ->orderBy('NAMA_PM', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data tidak ditemukan'
                    : 'Data berhasil ditemukan',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat search',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $id = (int) $id;
            $data = RefPm::with([
                'parent' => function ($query) {
                    $query->select('ID_REF_PM', 'NAMA_PM', 'DESKRIPSI_PM');
                },
                'children' => function ($query) {
                    $query->select('ID_REF_PM', 'REF_ID_REF_PM', 'NAMA_PM', 'DESKRIPSI_PM');
                },
                'trPm.programKerja' => function ($query) {
                    $query->select('ID_PROGRAM_KERJA', 'PROGRAM_KERJA', 'INDIKATOR');
                }
            ])->find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ref PM berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'REF_ID_REF_PM' => 'nullable|integer|exists:ref_pm,ID_REF_PM',
                'NAMA_PM' => 'required|string|max:255',
                'DESKRIPSI_PM' => 'nullable|string|max:500',
            ], [
                'NAMA_PM.required' => 'Nama PM tidak boleh kosong',
                'NAMA_PM.max' => 'Nama PM maksimal 255 karakter',
                'DESKRIPSI_PM.max' => 'Deskripsi maksimal 500 karakter',
            ]);
            $data = RefPm::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'Ref PM berhasil ditambahkan',
                'data' => $data,
            ], 201);
        }catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $id = (int) $id;
            $data = RefPm::find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            $validated = $request->validate([
                'REF_ID_REF_PM' => 'nullable|integer|exists:ref_pm,ID_REF_PM',
                'NAMA_PM' => 'required|string|max:100',
                'DESKRIPSI_PM' => 'nullable|string|max:500',
            ], [
                'NAMA_PM.required' => 'Nama PM tidak boleh kosong',
                'NAMA_PM.max' => 'Nama PM maksimal 100 karakter',
                'DESKRIPSI_PM.max' => 'Deskripsi maksimal 500 karakter',
            ]);
            if ($request->REF_ID_REF_PM == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Referensi Parent tidak boleh menunjuk ke diri sendiri.',
                ], 422);
            }
            $data->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate',
                'data' => $data,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $data = RefPm::find((int)$id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            $isUsedInPm = $data->trPm()->exists();
            if ($isUsedInPm) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak dapat dihapus karena sudah digunakan'
                ], 422);
            }
            $data->delete();
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateEvaluasi(Request $request, $id_pm): JsonResponse
    {
        try {
            $trPm = TrPm::findOrFail($id_pm);
            $user = Auth::user();

            if ($user->role === 'PIC' && !empty($trPm->NIP_VALIDATOR_PM)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Akses Terkunci. Sudah divalidasi Kepala Sekolah.'
                ], 403);
            }

            $payload = $request->only(['DESKRIPSI_TR_PM', 'ID_REF_PM']);
            
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
            
            private $rowNum = 0;

            public function collection()
            {
                return MstProgramKerja::where('IS_DELETE', 0)
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
                $this->rowNum++;

                $transaksi = TrPm::where('ID_PROGRAM_KERJA', $mst->ID_PROGRAM_KERJA)->get();

                $getTrVal = function($idRef) use ($transaksi) {
                    $item = $transaksi->firstWhere('ID_REF_PM', $idRef);
                    return $item ? $item->DESKRIPSI_TR_PM : '';
                };

                return [
                    $this->rowNum, 
                    'PROGRAM UTAMA',
                    $mst->PROGRAM_KERJA ?? '-',
                    $mst->SASARAN ?? '-',
                    $mst->INDIKATOR ?? '-',
                    $mst->NIP_PENANGGUNG_JAWAB ?? '-',
                    (int)($mst->TOTAL_PROGKER ?? 0), 
                    '-',
                    ($mst->WAKTU_AWAL ?? '') . ' s/d ' . ($mst->WAKTU_AKHIR ?? ''),
                    $mst->KELUARAN_PROGKER ?? '-', 
 
                    $getTrVal(15), $getTrVal(16),   // VISI (M) & (K)
                    $getTrVal(17), $getTrVal(18),   // MISI (M) & (K)
                    $getTrVal(19), $getTrVal(20),   // NILAI (M) & (K)
                    $getTrVal(21), $getTrVal(22),   // TUJUAN (M) & (K)

                    $getTrVal(23), // EFEKTIF
                    $getTrVal(24), // EFISIEN
                    $getTrVal(25), // USULAN PERUBAHAN
                    $getTrVal(26), // KOREKSI DARI YAYASAN
                    $getTrVal(27), // TANGGAPAN JAWABAN
                    $getTrVal(28), // EVALUASI TOTAL
                    $getTrVal(29)  // REKOMENDASI
                ];
            }

            public function styles(Worksheet $sheet)
            {

                $sheet->mergeCells('A1:Y1');
                $sheet->setCellValue('A1', 'EVALUASI RKT TAHUN 2026');
                $sheet->mergeCells('A2:Y2');
                $sheet->setCellValue('A2', 'UNIT SEKOLAH SMK BOPKRI 2 YOGYAKARTA');
                $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

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

   public function exportPdf()
    {
        try {
            if (ob_get_contents()) ob_end_clean();

            $mstData = MstProgramKerja::where('IS_DELETE', 0)
                ->orderBy('ID_PROGRAM_KERJA', 'asc')
                ->get();

            $reportData = $mstData->map(function ($mst) {
                $transaksi = TrPm::where('ID_PROGRAM_KERJA', $mst->ID_PROGRAM_KERJA)->get();
                
                $getTrVal = function($idRef) use ($transaksi) {
                    $item = $transaksi->firstWhere('ID_REF_PM', $idRef);
                    return $item ? $item->DESKRIPSI_TR_PM : '';
                };

                return [
                    'program_utama'     => 'PROGRAM UTAMA',
                    'program_kerja'     => $mst->PROGRAM_KERJA ?? '-',
                    'sasaran'           => $mst->SASARAN ?? '-',
                    'indikator'         => $mst->INDIKATOR ?? '-',
                    'nip_pj'            => $mst->NIP_PENANGGUNG_JAWAB ?? '-',
                    'anggaran'          => (int)($mst->TOTAL_PROGKER ?? 0),
                    'pos_anggaran'      => '-',
                    'waktu'             => ($mst->WAKTU_AWAL ?? '') . ' s/d ' . ($mst->WAKTU_AKHIR ?? ''),
                    'keluaran'          => $mst->KELUARAN_PROGKER ?? '-',
                    
                    'v_m' => $getTrVal(15), 'v_k' => $getTrVal(16),
                    'm_m' => $getTrVal(17), 'm_k' => $getTrVal(18),
                    'n_m' => $getTrVal(19), 'n_k' => $getTrVal(20),
                    't_m' => $getTrVal(21), 't_k' => $getTrVal(22),
                    
                    'efektif'           => $getTrVal(23),
                    'efisien'           => $getTrVal(24),
                    'usulan'            => $getTrVal(25),
                    'koreksi'           => $getTrVal(26),
                    'tanggapan'         => $getTrVal(27),
                    'evaluasi_total'    => $getTrVal(28),
                    'rekomendasi'       => $getTrVal(29),
                ];
            });

            $pdf = Pdf::loadView('exports.RefPm_pdf', [
                'data'  => $reportData,
                'title' => 'EVALUASI RKT (REF PM)'
            ]);

            $pdf->setPaper('a3', 'landscape');

            return $pdf->stream('Laporan-Evaluasi-RKT-2026.pdf');

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate PDF',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}