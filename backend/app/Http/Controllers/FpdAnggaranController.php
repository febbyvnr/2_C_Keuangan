<?php

namespace App\Http\Controllers;

use App\Exports\FpdAnggaranListExport;
use App\Models\DtlProgramKerja;
use App\Models\FpdAnggaran;
use App\Models\MstProgramKerja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FpdAnggaranController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = FpdAnggaran::with([
                'programKerja',
                'validator.jabatan.refJabatan',
                'programKerja.detailProgramKerja.sumberDana',
                'detailFpd.detailProgram',
                'detailFpd.detailProgram.sumberDana',
            ])->orderByDesc('ID_FPD');

            // Filter per kolom (opsional)
            if ($request->filled('TGL_FPD')) {
                $query->where('TGL_FPD', 'like', $request->TGL_FPD . '%');
            }
            if ($request->filled('NIP_VALIDATOR_FPD')) {
                $query->where('NIP_VALIDATOR_FPD', 'like', '%' . $request->NIP_VALIDATOR_FPD . '%');
            }
            if ($request->filled('PROGRAM_KERJA')) {
                $query->whereHas('programKerja', fn($q) => $q->where('PROGRAM_KERJA', 'like', '%' . $request->PROGRAM_KERJA . '%'));
            }

            // Pagination opsional — kalau tidak ada page param, return semua (backward-compat)
            $perPage = (int) $request->query('per_page', 0);
            if ($perPage > 0) {
                $data = $query->paginate($perPage);
                return response()->json([
                    'success' => true,
                    'message' => 'Data FPD anggaran berhasil diambil',
                    'data' => $data->items(),
                    'pagination' => [
                        'total'        => $data->total(),
                        'per_page'     => $data->perPage(),
                        'current_page' => $data->currentPage(),
                        'last_page'    => $data->lastPage(),
                    ],
                ]);
            }

            $data = $query->get();
            return response()->json([
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data FPD anggaran tidak ditemukan'
                    : 'Data FPD anggaran berhasil diambil',
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

    public function exportListExcel(Request $request)
    {
        $query = FpdAnggaran::with(['programKerja'])
            ->orderByDesc('ID_FPD');

        if ($request->filled('TGL_FPD')) {
            $query->where('TGL_FPD', 'like', $request->TGL_FPD . '%');
        }
        if ($request->filled('NIP_VALIDATOR_FPD')) {
            $query->where('NIP_VALIDATOR_FPD', 'like', '%' . $request->NIP_VALIDATOR_FPD . '%');
        }
        if ($request->filled('PROGRAM_KERJA')) {
            $query->whereHas('programKerja', fn($q) => $q->where('PROGRAM_KERJA', 'like', '%' . $request->PROGRAM_KERJA . '%'));
        }

        $data = $query->get();
        $filename = 'FPD_Anggaran_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new FpdAnggaranListExport($data), $filename);
    }

    public function exportListPdf(Request $request)
    {
        $query = FpdAnggaran::with(['programKerja', 'validator.jabatan.refJabatan'])
            ->orderByDesc('ID_FPD');

        if ($request->filled('TGL_FPD')) {
            $query->where('TGL_FPD', 'like', $request->TGL_FPD . '%');
        }
        if ($request->filled('NIP_VALIDATOR_FPD')) {
            $query->where('NIP_VALIDATOR_FPD', 'like', '%' . $request->NIP_VALIDATOR_FPD . '%');
        }
        if ($request->filled('PROGRAM_KERJA')) {
            $query->whereHas('programKerja', fn($q) => $q->where('PROGRAM_KERJA', 'like', '%' . $request->PROGRAM_KERJA . '%'));
        }

        $data = $query->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.fpd_anggaran_list_pdf', compact('data'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('FPD_Anggaran_' . now()->format('Ymd_His') . '.pdf');
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $keyword = trim((string) $request->query('keyword', ''));

            $query = FpdAnggaran::with([
                'programKerja',
                'validator.jabatan.refJabatan',
                'programKerja.detailProgramKerja.sumberDana',
                'detailFpd.detailProgram',
                'detailFpd.detailProgram.sumberDana',
            ]);

            if ($keyword !== '') {
                $query->where(function ($q) use ($keyword) {
                    $q->where('TGL_FPD', 'like', "%{$keyword}%")
                      ->orWhere('NIP_VALIDATOR_FPD', 'like', "%{$keyword}%")
                      ->orWhereHas('programKerja', function ($sub) use ($keyword) {
                          $sub->where('PROGRAM_KERJA', 'like', "%{$keyword}%");
                      });
                });
            }

            $data = $query->orderByDesc('ID_FPD')->get();

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
            $data = FpdAnggaran::with([
                'programKerja',
                'validator.jabatan.refJabatan',
                'programKerja.detailProgramKerja.sumberDana',
                'detailFpd.detailProgram',
                'detailFpd.detailProgram.sumberDana',
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
                'message' => 'FPD anggaran berhasil diambil',
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
                'ID_DT_PROGKER' => 'required_without:ID_PROGRAM_KERJA|integer|exists:dtl_program_kerja,ID_DT_PROGKER',
                'ID_PROGRAM_KERJA' => 'required_without:ID_DT_PROGKER|integer|exists:mst_program_kerja,ID_PROGRAM_KERJA',
                'TGL_FPD' => 'required|date',
                'NOMINAL_ANGGARAN' => 'required_without:ID_DT_PROGKER|numeric|min:0',
                'NIP_VALIDATOR_FPD' => 'nullable|string|max:20',
            ]);

            $payload = $this->buildFpdPayload($validated);

            $this->validateProgramKerjaBudget(
                (int) $payload['ID_PROGRAM_KERJA'],
                (float) $payload['NOMINAL_ANGGARAN']
            );

            $payload['NOMINAL_FPD'] = 0;
            $payload['NOMINAL_SISA'] = (float) $payload['NOMINAL_ANGGARAN'];

            $data = FpdAnggaran::create($payload)->load([
                'programKerja',
                'programKerja.detailProgramKerja.sumberDana',
                'detailFpd.detailProgram',
                'detailFpd.detailProgram.sumberDana',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FPD anggaran berhasil ditambahkan',
                'data' => $data,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //ini gbs verif by waka
    // public function update(Request $request, $id): JsonResponse
    // {
    //     try {
    //         $id = (int) $id;
    //         $data = FpdAnggaran::find($id);

    //         if (!$data) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Data tidak ditemukan',
    //                 'data' => null,
    //             ], 404);
    //         }

    //         $validated = $request->validate([
    //             'ID_PROGRAM_KERJA' => 'required|integer|exists:mst_program_kerja,ID_PROGRAM_KERJA',
    //             'TGL_FPD' => 'required|date',
    //             'NOMINAL_ANGGARAN' => 'required|numeric|min:0',
    //             'NIP_VALIDATOR_FPD' => 'nullable|string|max:20',
    //         ]);

    //         $totalDetail = (float) $data->detailFpd()->sum('TOTAL');

    //         if ($data->detailFpd()->exists() && (int) $validated['ID_PROGRAM_KERJA'] !== (int) $data->ID_PROGRAM_KERJA) {
    //             throw ValidationException::withMessages([
    //                 'ID_PROGRAM_KERJA' => ['ID program kerja tidak bisa diubah karena FPD sudah punya detail.'],
    //             ]);
    //         }

    //         if ((float) $validated['NOMINAL_ANGGARAN'] < $totalDetail) {
    //             throw ValidationException::withMessages([
    //                 'NOMINAL_ANGGARAN' => ['Nominal anggaran tidak boleh lebih kecil dari total detail FPD.'],
    //             ]);
    //         }

    //         $this->validateProgramKerjaBudget(
    //             (int) $validated['ID_PROGRAM_KERJA'],
    //             (float) $validated['NOMINAL_ANGGARAN'],
    //             $data->ID_FPD
    //         );

    //         $validated['NOMINAL_FPD'] = $totalDetail;
    //         $validated['NOMINAL_SISA'] = (float) $validated['NOMINAL_ANGGARAN'] - $totalDetail;

    //         $data->update($validated);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Data berhasil diupdate',
    //             'data' => $data->load([
    //                 'programKerja',
    //                 'programKerja.detailProgramKerja.sumberDana',
    //                 'detailFpd.detailProgram',
    //                 'detailFpd.detailProgram.sumberDana',
    //             ]),
    //         ]);
    //     } catch (ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validasi gagal',
    //             'errors' => $e->errors(),
    //         ], 422);
    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Terjadi kesalahan',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    //ini bisa verifikasi by waka
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $id = (int) $id;
            $data = FpdAnggaran::find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null,
                ], 404);
            }
            $validated = $request->validate([
                'ID_DT_PROGKER' => 'required_without:ID_PROGRAM_KERJA|integer|exists:dtl_program_kerja,ID_DT_PROGKER',
                'ID_PROGRAM_KERJA' => 'required_without:ID_DT_PROGKER|integer|exists:mst_program_kerja,ID_PROGRAM_KERJA',
                'TGL_FPD' => 'required|date',
                'NOMINAL_ANGGARAN' => 'required_without:ID_DT_PROGKER|numeric|min:0',
                'NIP_VALIDATOR_FPD' => 'nullable|string|max:20',
            ]);
            $payload = $this->buildFpdPayload($validated);
            $totalDetail = (float) $data->detailFpd()->sum('TOTAL');
            if ($data->detailFpd()->exists() && (int) $payload['ID_PROGRAM_KERJA'] !== (int) $data->ID_PROGRAM_KERJA) {
                throw ValidationException::withMessages([
                    'ID_PROGRAM_KERJA' => ['ID program kerja tidak bisa diubah karena FPD sudah punya rincian detail.'],
                ]);
            }
            if ((float) $payload['NOMINAL_ANGGARAN'] < $totalDetail) {
                throw ValidationException::withMessages([
                    'NOMINAL_ANGGARAN' => ['Nominal anggaran tidak boleh lebih kecil dari total rincian (Rp ' . number_format($totalDetail, 0, ',', '.') . ').'],
                ]);
            }
            $isNominalChanged = (float)$payload['NOMINAL_ANGGARAN'] !== (float)$data->NOMINAL_ANGGARAN;
            $isProgramChanged = (int)$payload['ID_PROGRAM_KERJA'] !== (int)$data->ID_PROGRAM_KERJA;
            if ($isNominalChanged || $isProgramChanged) {
                $this->validateProgramKerjaBudget(
                    (int) $payload['ID_PROGRAM_KERJA'],
                    (float) $payload['NOMINAL_ANGGARAN'],
                    $data->ID_FPD
                );
            }
            $payload['NOMINAL_FPD'] = $totalDetail;
            $payload['NOMINAL_SISA'] = (float) $payload['NOMINAL_ANGGARAN'] - $totalDetail;
            $data->update($payload);
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui',
                'data' => $data->load([
                    'programKerja',
                    'validator.jabatan.refJabatan',
                    'programKerja.detailProgramKerja.sumberDana',
                    'detailFpd.detailProgram',
                    'detailFpd.detailProgram.sumberDana',
                ]),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $id = (int) $id;
            $data = FpdAnggaran::find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            DB::transaction(function () use ($data) {
                $data->detailFpd()->delete();
                $data->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus',
                'data' => null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        try {
            $fpd = FpdAnggaran::find($id);
            if (!$fpd) {
                return response()->json([
                    'success' => false,
                    'message' => 'FPD tidak ditemukan'
                ], 404);
            }
            if ($fpd->NIP_VALIDATOR_FPD === "Ditolak") {
                return response()->json([
                    'success' => false,
                    'message' => 'Sudah ditolak sebelumnya'
                ], 400);
            }
            $fpd->update([
                'NIP_VALIDATOR_FPD' => "Ditolak"
            ]);
            return response()->json([
                'success' => true,
                'message' => 'FPD berhasil ditolak'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal reject FPD',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function export(Request $request, $id = null): JsonResponse|StreamedResponse
    {
        try {
            $relations = [
                'programKerja',
                'detailFpd.detailProgram.programKerja',
                'detailFpd.detailProgram.sumberDana',
            ];

            if (!is_null($id)) {
                $id = (int) $id;

                $fpd = FpdAnggaran::with($relations)->find($id);

                if (!$fpd) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data FPD tidak ditemukan',
                        'data' => null,
                    ], 404);
                }

                $fileName = 'laporan_FPD_' . $id . '.xlsx';

                return response()->streamDownload(function () use ($fpd) {
                    echo $this->buildFpdXlsx($fpd);
                }, $fileName, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]);
            }

            $query = FpdAnggaran::with($relations)->orderByDesc('ID_FPD');

            if ($request->filled('TGL_FPD')) {
                $query->where('TGL_FPD', 'like', $request->TGL_FPD . '%');
            }
            if ($request->filled('NIP_VALIDATOR_FPD')) {
                $query->where('NIP_VALIDATOR_FPD', 'like', '%' . $request->NIP_VALIDATOR_FPD . '%');
            }
            if ($request->filled('PROGRAM_KERJA')) {
                $query->whereHas('programKerja', fn($q) => $q->where('PROGRAM_KERJA', 'like', '%' . $request->PROGRAM_KERJA . '%'));
            }

            $fpds = $query->get();
            $fileName = 'laporan_FPD_semua_' . now()->format('Ymd_His') . '.xlsx';

            return response()->streamDownload(function () use ($fpds) {
                echo $this->buildFpdXlsx($fpds);
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat export data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function buildFpdXlsx(FpdAnggaran|Collection $fpdData): string
    {
        $fpds = $fpdData instanceof FpdAnggaran
            ? collect([$fpdData])
            : $fpdData->values();
        $reportDate = now();
        $sheetXml = $this->buildFpdWorksheetXml($fpds, $reportDate);

        $entries = [
            '[Content_Types].xml' => $this->buildContentTypesXml(),
            '_rels/.rels' => $this->buildRootRelsXml(),
            'docProps/app.xml' => $this->buildAppPropsXml(),
            'docProps/core.xml' => $this->buildCorePropsXml($reportDate),
            'xl/workbook.xml' => $this->buildWorkbookXml(),
            'xl/_rels/workbook.xml.rels' => $this->buildWorkbookRelsXml(),
            'xl/styles.xml' => $this->buildStylesXml(),
            'xl/sharedStrings.xml' => $sheetXml['sharedStrings'],
            'xl/worksheets/sheet1.xml' => $sheetXml['worksheet'],
        ];

        return $this->buildZipArchive($entries);
    }

    private function buildFpdWorksheetXml(Collection $fpds, $reportDate): array
    {
        $sharedStrings = [];
        $sharedStringMap = [];
        $sheetRows = [];
        $lastColumn = 'R';
        $summary = $fpds->count() === 1
            ? 'ID FPD: ' . $fpds->first()->ID_FPD
                . ' | Tanggal FPD: ' . ($fpds->first()->TGL_FPD?->format('d/m/Y') ?? '-')
                . ' | Program Kerja: ' . (optional($fpds->first()->programKerja)->PROGRAM_KERJA ?? '-')
            : 'Semua ID FPD | Total FPD: ' . $fpds->count();

        $sheetRows[] = $this->buildRowXml(2, [
            $this->sharedStringCell('A2', 2, 'SMK BOPKRI 2 YOGYAKARTA', $sharedStrings, $sharedStringMap),
        ]);
        $sheetRows[] = $this->buildRowXml(3, [
            $this->sharedStringCell('A3', 3, 'LAPORAN FORM PENGAJUAN DANA (FPD)', $sharedStrings, $sharedStringMap),
        ]);
        $sheetRows[] = $this->buildRowXml(4, [
            $this->sharedStringCell(
                'A4',
                1,
                $summary,
                $sharedStrings,
                $sharedStringMap
            ),
        ]);

        $headings = [
            'No',
            'ID FPD',
            'Tanggal FPD',
            'Program Kerja',
            'Validator FPD',
            'Nominal Anggaran',
            'Nominal Terpakai',
            'Nominal Sisa',
            'ID Detail FPD',
            'ID Detail Program',
            'Detail Program Kerja',
            'Sumber Dana',
            'QTY',
            'Volume',
            'Satuan',
            'Harga Satuan',
            'Total',
            'Link Bukti Nota',
        ];

        $headerCells = [];
        foreach ($headings as $index => $heading) {
            $cellRef = $this->columnLetter($index + 1) . '6';
            $headerCells[] = $this->sharedStringCell($cellRef, 4, $heading, $sharedStrings, $sharedStringMap);
        }
        $sheetRows[] = $this->buildRowXml(6, $headerCells);

        $currentRow = 7;
        $rowNumber = 1;
        $detailTotal = 0.0;
        $totalAnggaran = 0.0;
        $totalSisa = 0.0;

        if ($fpds->isEmpty()) {
            $sheetRows[] = $this->buildRowXml($currentRow, [
                $this->numberCell("A{$currentRow}", 5, 1),
                $this->sharedStringCell("B{$currentRow}", 5, '-', $sharedStrings, $sharedStringMap),
                $this->sharedStringCell("C{$currentRow}", 5, '-', $sharedStrings, $sharedStringMap),
                $this->sharedStringCell("D{$currentRow}", 6, 'Tidak ada data FPD', $sharedStrings, $sharedStringMap),
                $this->sharedStringCell("E{$currentRow}", 5, '-', $sharedStrings, $sharedStringMap),
                $this->numberCell("F{$currentRow}", 7, 0),
                $this->numberCell("G{$currentRow}", 7, 0),
                $this->numberCell("H{$currentRow}", 7, 0),
                $this->sharedStringCell("I{$currentRow}", 5, '-', $sharedStrings, $sharedStringMap),
                $this->sharedStringCell("J{$currentRow}", 5, '-', $sharedStrings, $sharedStringMap),
                $this->sharedStringCell("K{$currentRow}", 6, '-', $sharedStrings, $sharedStringMap),
                $this->sharedStringCell("L{$currentRow}", 6, '-', $sharedStrings, $sharedStringMap),
                $this->sharedStringCell("M{$currentRow}", 5, '-', $sharedStrings, $sharedStringMap),
                $this->sharedStringCell("N{$currentRow}", 5, '-', $sharedStrings, $sharedStringMap),
                $this->sharedStringCell("O{$currentRow}", 5, '-', $sharedStrings, $sharedStringMap),
                $this->numberCell("P{$currentRow}", 7, 0),
                $this->numberCell("Q{$currentRow}", 7, 0),
                $this->sharedStringCell("R{$currentRow}", 6, '-', $sharedStrings, $sharedStringMap),
            ]);
            $currentRow++;
        } else {
            foreach ($fpds as $fpd) {
                $programKerjaName = optional($fpd->programKerja)->PROGRAM_KERJA ?? '-';
                $tanggalFpd = $fpd->TGL_FPD?->format('d/m/Y') ?? '-';
                $validator = $fpd->NIP_VALIDATOR_FPD ?: '-';
                $rows = $fpd->detailFpd->sortBy('ID_DT_FPD')->values();

                $totalAnggaran += (float) $fpd->NOMINAL_ANGGARAN;
                $totalSisa += (float) $fpd->NOMINAL_SISA;

                if ($rows->isEmpty()) {
                    $sheetRows[] = $this->buildRowXml($currentRow, [
                        $this->numberCell("A{$currentRow}", 5, $rowNumber++),
                        $this->numberCell("B{$currentRow}", 5, (float) $fpd->ID_FPD),
                        $this->sharedStringCell("C{$currentRow}", 5, $tanggalFpd, $sharedStrings, $sharedStringMap),
                        $this->sharedStringCell("D{$currentRow}", 6, $programKerjaName, $sharedStrings, $sharedStringMap),
                        $this->sharedStringCell("E{$currentRow}", 5, $validator, $sharedStrings, $sharedStringMap),
                        $this->numberCell("F{$currentRow}", 7, (float) $fpd->NOMINAL_ANGGARAN),
                        $this->numberCell("G{$currentRow}", 7, (float) $fpd->NOMINAL_FPD),
                        $this->numberCell("H{$currentRow}", 7, (float) $fpd->NOMINAL_SISA),
                        $this->sharedStringCell("I{$currentRow}", 5, '-', $sharedStrings, $sharedStringMap),
                        $this->sharedStringCell("J{$currentRow}", 5, '-', $sharedStrings, $sharedStringMap),
                        $this->sharedStringCell("K{$currentRow}", 6, '-', $sharedStrings, $sharedStringMap),
                        $this->sharedStringCell("L{$currentRow}", 6, '-', $sharedStrings, $sharedStringMap),
                        $this->sharedStringCell("M{$currentRow}", 5, '-', $sharedStrings, $sharedStringMap),
                        $this->sharedStringCell("N{$currentRow}", 5, '-', $sharedStrings, $sharedStringMap),
                        $this->sharedStringCell("O{$currentRow}", 5, '-', $sharedStrings, $sharedStringMap),
                        $this->numberCell("P{$currentRow}", 7, 0),
                        $this->numberCell("Q{$currentRow}", 7, 0),
                        $this->sharedStringCell("R{$currentRow}", 6, '-', $sharedStrings, $sharedStringMap),
                    ]);

                    $currentRow++;
                    continue;
                }

                foreach ($rows as $detail) {
                    $detailProgram = $detail->detailProgram;
                    $sumberDana = optional($detailProgram)->sumberDana;
                    $rowProgramKerja = optional(optional($detailProgram)->programKerja)->PROGRAM_KERJA ?? $programKerjaName;
                    $sumberDanaName = optional($sumberDana)->SUMBER_DANA
                        ?? optional($sumberDana)->DESKRIPSI_SUMBER_DANA
                        ?? optional($sumberDana)->NAMA_SUMBER_DANA
                        ?? '-';
                    $detailLabel = $rowProgramKerja . ($sumberDanaName !== '-' ? ' - ' . $sumberDanaName : '');
                    $detailTotal += (float) $detail->TOTAL;

                    $sheetRows[] = $this->buildRowXml($currentRow, [
                        $this->numberCell("A{$currentRow}", 5, $rowNumber++),
                        $this->numberCell("B{$currentRow}", 5, (float) $fpd->ID_FPD),
                        $this->sharedStringCell("C{$currentRow}", 5, $tanggalFpd, $sharedStrings, $sharedStringMap),
                        $this->sharedStringCell("D{$currentRow}", 6, $programKerjaName, $sharedStrings, $sharedStringMap),
                        $this->sharedStringCell("E{$currentRow}", 5, $validator, $sharedStrings, $sharedStringMap),
                        $this->numberCell("F{$currentRow}", 7, (float) $fpd->NOMINAL_ANGGARAN),
                        $this->numberCell("G{$currentRow}", 7, (float) $fpd->NOMINAL_FPD),
                        $this->numberCell("H{$currentRow}", 7, (float) $fpd->NOMINAL_SISA),
                        $this->numberCell("I{$currentRow}", 5, (float) $detail->ID_DT_FPD),
                        $this->numberCell("J{$currentRow}", 5, (float) $detail->ID_DT_PROGKER),
                        $this->sharedStringCell("K{$currentRow}", 6, $detailLabel, $sharedStrings, $sharedStringMap),
                        $this->sharedStringCell("L{$currentRow}", 6, $sumberDanaName, $sharedStrings, $sharedStringMap),
                        $this->numberCell("M{$currentRow}", 5, (float) $detail->QTY),
                        $this->numberCell("N{$currentRow}", 5, (float) $detail->VOLUME),
                        $this->sharedStringCell("O{$currentRow}", 5, $detail->SATUAN ?? '-', $sharedStrings, $sharedStringMap),
                        $this->numberCell("P{$currentRow}", 7, (float) $detail->HARGA_SATUAN),
                        $this->numberCell("Q{$currentRow}", 7, (float) $detail->TOTAL),
                        $this->sharedStringCell("R{$currentRow}", 6, $detail->LINK_BUKTI_NOTA_FPD ?? '-', $sharedStrings, $sharedStringMap),
                    ]);

                    $currentRow++;
                }
            }
        }

        $totalRow = $currentRow + 1;
        $sheetRows[] = $this->buildRowXml($totalRow, [
            $this->sharedStringCell("Q{$totalRow}", 8, 'TOTAL DETAIL', $sharedStrings, $sharedStringMap),
            $this->numberCell("R{$totalRow}", 9, $detailTotal),
        ]);

        $sheetRows[] = $this->buildRowXml($totalRow + 1, [
            $this->sharedStringCell('Q' . ($totalRow + 1), 8, 'NOMINAL ANGGARAN', $sharedStrings, $sharedStringMap),
            $this->numberCell('R' . ($totalRow + 1), 9, $totalAnggaran),
        ]);

        $sheetRows[] = $this->buildRowXml($totalRow + 2, [
            $this->sharedStringCell('Q' . ($totalRow + 2), 8, 'NOMINAL SISA', $sharedStrings, $sharedStringMap),
            $this->numberCell('R' . ($totalRow + 2), 9, $totalSisa),
        ]);

        $footerRow = $totalRow + 5;
        $sheetRows[] = $this->buildRowXml($footerRow, [
            $this->sharedStringCell(
                "A{$footerRow}",
                10,
                'Diunduh pada ' . $reportDate->timezone(config('app.timezone'))->format('d/m/Y H:i') . ' WIB',
                $sharedStrings,
                $sharedStringMap
            ),
        ]);

        $lastDataRow = $footerRow;
        $dimension = 'A1:' . $lastColumn . $lastDataRow;
        $columnWidths = [6, 10, 16, 34, 20, 18, 18, 18, 14, 16, 36, 22, 10, 10, 12, 16, 16, 28];
        $cols = '';
        foreach ($columnWidths as $index => $width) {
            $columnNumber = $index + 1;
            $cols .= '<col min="' . $columnNumber . '" max="' . $columnNumber . '" width="' . $width . '" customWidth="true"/>';
        }

        $worksheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<dimension ref="' . $dimension . '"/>'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="6" topLeftCell="A7" activePane="bottomLeft" state="frozen"/>'
            . '<selection pane="bottomLeft" activeCell="A7" sqref="A7"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="15"/>'
            . '<cols>' . $cols . '</cols>'
            . '<sheetData>' . implode('', $sheetRows) . '</sheetData>'
            . '<autoFilter ref="A6:R6"/>'
            . '<mergeCells count="3"><mergeCell ref="A2:R2"/><mergeCell ref="A3:R3"/><mergeCell ref="A4:R4"/></mergeCells>'
            . '<pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>'
            . '<pageSetup paperSize="9" orientation="landscape" scale="90" fitToWidth="1" fitToHeight="0"/>'
            . '</worksheet>';

        return [
            'worksheet' => $worksheet,
            'sharedStrings' => $this->buildSharedStringsXml($sharedStrings),
        ];
    }

    private function buildSharedStringsXml(array $strings): string
    {
        $items = [];

        foreach ($strings as $string) {
            $value = str_replace(["\r\n", "\r", "\n"], '&#10;', $this->escapeXml($string));
            $items[] = '<si><t xml:space="preserve">' . $value . '</t></si>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($strings) . '" uniqueCount="' . count($strings) . '">'
            . implode('', $items)
            . '</sst>';
    }

    private function buildContentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            . '</Types>';
    }

    private function buildRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private function buildAppPropsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>Microsoft Excel</Application>'
            . '<DocSecurity>0</DocSecurity>'
            . '<ScaleCrop>false</ScaleCrop>'
            . '<HeadingPairs><vt:vector size="2" baseType="variant"><vt:variant><vt:lpstr>Worksheets</vt:lpstr></vt:variant><vt:variant><vt:i4>1</vt:i4></vt:variant></vt:vector></HeadingPairs>'
            . '<TitlesOfParts><vt:vector size="1" baseType="lpstr"><vt:lpstr>FPD</vt:lpstr></vt:vector></TitlesOfParts>'
            . '<Company>SMK BOPKRI 2 Yogyakarta</Company>'
            . '<LinksUpToDate>false</LinksUpToDate>'
            . '<SharedDoc>false</SharedDoc>'
            . '<HyperlinksChanged>false</HyperlinksChanged>'
            . '<AppVersion>16.0300</AppVersion>'
            . '</Properties>';
    }

    private function buildCorePropsXml($reportDate): string
    {
        $created = $reportDate->copy()->utc()->format('Y-m-d\TH:i:s\Z');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:creator>Codex</dc:creator>'
            . '<cp:lastModifiedBy>Codex</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $created . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $created . '</dcterms:modified>'
            . '<dc:title>Laporan FPD</dc:title>'
            . '<dc:description>Laporan Form Pengajuan Dana</dc:description>'
            . '</cp:coreProperties>';
    }

    private function buildWorkbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<bookViews><workbookView activeTab="0" firstSheet="0"/></bookViews>'
            . '<sheets><sheet name="FPD" sheetId="1" r:id="rId1"/></sheets>'
            . '<calcPr calcId="999999"/></workbook>';
    }

    private function buildWorkbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
            . '</Relationships>';
    }

    private function buildStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="6">'
            . '<font><sz val="11"/><color rgb="FF000000"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FF000000"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="16"/><color rgb="FF000000"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="14"/><color rgb="FF000000"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '<font><sz val="10"/><color rgb="FF6B7280"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="4">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF2E75B6"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFFFE699"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border><left style="thin"><color rgb="FF000000"/></left><right style="thin"><color rgb="FF000000"/></right><top style="thin"><color rgb="FF000000"/></top><bottom style="thin"><color rgb="FF000000"/></bottom><diagonal/></border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="11">'
            . '<xf xfId="0" fontId="0" fillId="0" borderId="0" numFmtId="0"/>'
            . '<xf xfId="0" fontId="0" fillId="0" borderId="0" numFmtId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf xfId="0" fontId="2" fillId="0" borderId="0" numFmtId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf xfId="0" fontId="3" fillId="0" borderId="0" numFmtId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf xfId="0" fontId="4" fillId="2" borderId="1" numFmtId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            . '<xf xfId="0" fontId="0" fillId="0" borderId="1" numFmtId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf xfId="0" fontId="0" fillId="0" borderId="1" numFmtId="0" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            . '<xf xfId="0" fontId="0" fillId="0" borderId="1" numFmtId="3" applyBorder="1" applyNumberFormat="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf xfId="0" fontId="1" fillId="3" borderId="0" numFmtId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf xfId="0" fontId="1" fillId="3" borderId="0" numFmtId="3" applyFont="1" applyFill="1" applyNumberFormat="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf xfId="0" fontId="5" fillId="0" borderId="0" numFmtId="0" applyFont="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function buildZipArchive(array $entries): string
    {
        $data = '';
        $centralDirectory = '';
        $offset = 0;
        [$dosTime, $dosDate] = $this->dosDateTime(now());

        foreach ($entries as $name => $content) {
            $compressed = gzcompress($content, 9);
            $compressed = substr($compressed, 2, -4);
            $crc = (int) sprintf('%u', crc32($content));
            $compressedLength = strlen($compressed);
            $uncompressedLength = strlen($content);
            $nameLength = strlen($name);

            $localHeader = pack(
                'VvvvvvVVVvv',
                0x04034b50,
                20,
                0,
                8,
                $dosTime,
                $dosDate,
                $crc,
                $compressedLength,
                $uncompressedLength,
                $nameLength,
                0
            );

            $data .= $localHeader . $name . $compressed;

            $centralDirectory .= pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014b50,
                20,
                20,
                0,
                8,
                $dosTime,
                $dosDate,
                $crc,
                $compressedLength,
                $uncompressedLength,
                $nameLength,
                0,
                0,
                0,
                0,
                32,
                $offset
            ) . $name;

            $offset += strlen($localHeader) + $nameLength + $compressedLength;
        }

        $centralDirectorySize = strlen($centralDirectory);
        $entryCount = count($entries);

        $endOfCentralDirectory = pack(
            'VvvvvVVv',
            0x06054b50,
            0,
            0,
            $entryCount,
            $entryCount,
            $centralDirectorySize,
            $offset,
            0
        );

        return $data . $centralDirectory . $endOfCentralDirectory;
    }

    private function buildRowXml(int $rowNumber, array $cells): string
    {
        return '<row r="' . $rowNumber . '">' . implode('', $cells) . '</row>';
    }

    private function sharedStringCell(string $ref, int $style, $value, array &$strings, array &$stringMap): string
    {
        $text = (string) $value;

        if (!array_key_exists($text, $stringMap)) {
            $stringMap[$text] = count($strings);
            $strings[] = $text;
        }

        return '<c r="' . $ref . '" s="' . $style . '" t="s"><v>' . $stringMap[$text] . '</v></c>';
    }

    private function numberCell(string $ref, int $style, float|int $value): string
    {
        return '<c r="' . $ref . '" s="' . $style . '"><v>' . $this->formatNumberValue($value) . '</v></c>';
    }

    private function formatNumberValue(float|int $value): string
    {
        if (is_int($value) || floor((float) $value) === (float) $value) {
            return (string) (int) $value;
        }

        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }

    private function columnLetter(int $columnNumber): string
    {
        $column = '';

        while ($columnNumber > 0) {
            $modulo = ($columnNumber - 1) % 26;
            $column = chr(65 + $modulo) . $column;
            $columnNumber = (int) floor(($columnNumber - $modulo) / 26);
        }

        return $column;
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function dosDateTime($dateTime): array
    {
        $dateTime = $dateTime->copy();

        $dosTime = ($dateTime->hour << 11) | ($dateTime->minute << 5) | (int) floor($dateTime->second / 2);
        $dosDate = (($dateTime->year - 1980) << 9) | ($dateTime->month << 5) | $dateTime->day;

        return [$dosTime, $dosDate];
    }

    private function buildFpdPayload(array $validated): array
    {
        $payload = [
            'TGL_FPD' => $validated['TGL_FPD'],
        ];

        if (array_key_exists('NIP_VALIDATOR_FPD', $validated)) {
            $payload['NIP_VALIDATOR_FPD'] = $validated['NIP_VALIDATOR_FPD'];
        }

        if (!empty($validated['ID_DT_PROGKER'])) {
            $rka = DtlProgramKerja::with('programKerja')
                ->find((int) $validated['ID_DT_PROGKER']);
            $programKerja = $rka?->programKerja;

            if (!$rka || !$programKerja || (int) ($programKerja->IS_DELETE ?? 0) === 1) {
                throw ValidationException::withMessages([
                    'ID_DT_PROGKER' => ['RKA tidak ditemukan atau RKT sudah tidak aktif.'],
                ]);
            }

            if (blank($programKerja->NIP_VALIDATOR_PROGKER)) {
                throw ValidationException::withMessages([
                    'ID_DT_PROGKER' => ['RKA belum bisa dipakai karena RKT belum disetujui.'],
                ]);
            }

            $payload['ID_PROGRAM_KERJA'] = (int) $rka->ID_PROGRAM_KERJA;
            $payload['NOMINAL_ANGGARAN'] = $this->getRkaNominal($rka);

            return $payload;
        }

        $payload['ID_PROGRAM_KERJA'] = (int) $validated['ID_PROGRAM_KERJA'];
        $payload['NOMINAL_ANGGARAN'] = (float) $validated['NOMINAL_ANGGARAN'];

        return $payload;
    }

    private function getRkaNominal(DtlProgramKerja $rka): float
    {
        $nominal = $rka->NOMINAL;

        if (!is_null($nominal)) {
            return (float) $nominal;
        }

        return (float) $rka->QTY * (float) $rka->HARGA_SATUAN * (float) $rka->VOLUME;
    }

    private function validateProgramKerjaBudget(int $idProgramKerja, float $nominalAnggaranBaru, ?int $excludeFpdId = null): void
    {
        $programKerja = MstProgramKerja::query()
            ->where(function ($query) {
                $query->where('IS_DELETE', '!=', 1)
                    ->orWhereNull('IS_DELETE');
            })
            ->find($idProgramKerja);

        if (!$programKerja) {
            throw ValidationException::withMessages([
                'ID_PROGRAM_KERJA' => ['Program kerja tidak ditemukan atau sudah tidak aktif.'],
            ]);
        }

        $allocatedQuery = FpdAnggaran::query()
            ->where('ID_PROGRAM_KERJA', $idProgramKerja);

        if (!is_null($excludeFpdId)) {
            $allocatedQuery->where('ID_FPD', '!=', $excludeFpdId);
        }

        $allocatedNominal = (float) $allocatedQuery->sum('NOMINAL_ANGGARAN');
        $programBudget = (float) ($programKerja->TOTAL_PROGKER ?? $programKerja->NOMINAL ?? 0);

        if (($allocatedNominal + $nominalAnggaranBaru) > $programBudget) {
            throw ValidationException::withMessages([
                'NOMINAL_ANGGARAN' => [
                    'Total anggaran FPD untuk program kerja ini melebihi anggaran RKA/program kerja.',
                ],
            ]);
        }
    }
}
