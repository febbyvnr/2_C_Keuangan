<?php

namespace App\Http\Controllers;

use App\Models\FpdAnggaran;
use App\Models\MstProgramKerja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FpdAnggaranController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $data = FpdAnggaran::with([
                'programKerja',
                'detailFpd.detailProgram',
            ])->get();

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

    public function search(Request $request): JsonResponse
    {
        try {
            $keyword = trim((string) $request->query('keyword', ''));

            $query = FpdAnggaran::with([
                'programKerja',
                'detailFpd.detailProgram',
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

            $data = $query->get();

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
                'detailFpd.detailProgram',
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
                'ID_PROGRAM_KERJA' => 'required|integer|exists:mst_program_kerja,ID_PROGRAM_KERJA',
                'TGL_FPD' => 'required|date',
                'NOMINAL_ANGGARAN' => 'required|numeric|min:0',
                'NIP_VALIDATOR_FPD' => 'nullable|string|max:20',
            ]);

            $this->validateProgramKerjaBudget(
                (int) $validated['ID_PROGRAM_KERJA'],
                (float) $validated['NOMINAL_ANGGARAN']
            );

            $validated['NOMINAL_FPD'] = 0;
            $validated['NOMINAL_SISA'] = (float) $validated['NOMINAL_ANGGARAN'];

            $data = FpdAnggaran::create($validated)->load([
                'programKerja',
                'detailFpd.detailProgram',
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
                'ID_PROGRAM_KERJA' => 'required|integer|exists:mst_program_kerja,ID_PROGRAM_KERJA',
                'TGL_FPD' => 'required|date',
                'NOMINAL_ANGGARAN' => 'required|numeric|min:0',
                'NIP_VALIDATOR_FPD' => 'nullable|string|max:20',
            ]);

            $totalDetail = (float) $data->detailFpd()->sum('TOTAL');

            if ($data->detailFpd()->exists() && (int) $validated['ID_PROGRAM_KERJA'] !== (int) $data->ID_PROGRAM_KERJA) {
                throw ValidationException::withMessages([
                    'ID_PROGRAM_KERJA' => ['ID program kerja tidak bisa diubah karena FPD sudah punya detail.'],
                ]);
            }

            if ((float) $validated['NOMINAL_ANGGARAN'] < $totalDetail) {
                throw ValidationException::withMessages([
                    'NOMINAL_ANGGARAN' => ['Nominal anggaran tidak boleh lebih kecil dari total detail FPD.'],
                ]);
            }

            $this->validateProgramKerjaBudget(
                (int) $validated['ID_PROGRAM_KERJA'],
                (float) $validated['NOMINAL_ANGGARAN'],
                $data->ID_FPD
            );

            $validated['NOMINAL_FPD'] = $totalDetail;
            $validated['NOMINAL_SISA'] = (float) $validated['NOMINAL_ANGGARAN'] - $totalDetail;

            $data->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate',
                'data' => $data->load([
                    'programKerja',
                    'detailFpd.detailProgram',
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
                'message' => 'Terjadi kesalahan',
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
    public function export($id): JsonResponse|StreamedResponse
    {
        try {
            $id = (int) $id;

            $fpd = FpdAnggaran::with(['programKerja', 'detailFpd.detailProgram.programKerja'])->find($id);

            if (!$fpd) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data FPD tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            $fileName = 'fpd_' . $fpd->ID_FPD . '_' . now()->format('Ymd_His') . '.csv';

            return response()->streamDownload(function () use ($fpd) {
                $handle = fopen('php://output', 'w');

                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

                // Header informasi FPD
                fputcsv($handle, ['DATA FPD'], ';');
                fputcsv($handle, ['ID FPD', $fpd->ID_FPD], ';');
                fputcsv($handle, ['Tanggal FPD', $fpd->TGL_FPD?->format('Y-m-d H:i:s') ?? $fpd->TGL_FPD], ';');
                fputcsv($handle, ['ID Program Kerja', $fpd->ID_PROGRAM_KERJA], ';');
                fputcsv($handle, ['Program Kerja', optional($fpd->programKerja)->PROGRAM_KERJA ?? '-'], ';');
                fputcsv($handle, ['Nominal Anggaran', $fpd->NOMINAL_ANGGARAN], ';');
                fputcsv($handle, ['Nominal FPD', $fpd->NOMINAL_FPD], ';');
                fputcsv($handle, ['Nominal Sisa', $fpd->NOMINAL_SISA], ';');
                fputcsv($handle, ['Validator FPD', $fpd->NIP_VALIDATOR_FPD ?? '-'], ';');

                fputcsv($handle, [], ';'); // baris kosong

                // Header detail
                fputcsv($handle, [
                    'No',
                    'ID Detail FPD',
                    'ID Detail Program Kerja',
                    'Nama Detail Program',
                    'QTY',
                    'Volume',
                    'Satuan',
                    'Harga Satuan',
                    'Total',
                    'Link Bukti Nota'
                ], ';');

                $grandTotal = 0;
                foreach ($fpd->detailFpd as $index => $detail) {
                    $grandTotal += (float) $detail->TOTAL;

                    fputcsv($handle, [
                        $index + 1,
                        $detail->ID_DT_FPD,
                        $detail->ID_DT_PROGKER,
                        optional(optional($detail->detailProgram)->programKerja)->PROGRAM_KERJA ?? ('Detail #' . $detail->ID_DT_PROGKER),
                        $detail->QTY,
                        $detail->VOLUME,
                        $detail->SATUAN,
                        $detail->HARGA_SATUAN,
                        $detail->TOTAL,
                        $detail->LINK_BUKTI_NOTA_FPD ?? '-',
                    ], ';');
                }

                fputcsv($handle, [], ';');
                fputcsv($handle, ['Grand Total Detail', $grandTotal], ';');

                fclose($handle);
            }, $fileName, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat export data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function validateProgramKerjaBudget(int $idProgramKerja, float $nominalAnggaranBaru, ?int $excludeFpdId = null): void
    {
        $programKerja = MstProgramKerja::query()
            ->active()
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
        $programBudget = (float) $programKerja->NOMINAL;

        if (($allocatedNominal + $nominalAnggaranBaru) > $programBudget) {
            throw ValidationException::withMessages([
                'NOMINAL_ANGGARAN' => [
                    'Total anggaran FPD untuk program kerja ini melebihi anggaran RKA/program kerja.',
                ],
            ]);
        }
    }
}
