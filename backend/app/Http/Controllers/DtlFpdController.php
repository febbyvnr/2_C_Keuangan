<?php

namespace App\Http\Controllers;

use App\Models\DtlProgramKerja;
use App\Models\DtlFpd;
use App\Models\FpdAnggaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DtlFpdController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $query = DtlFpd::with([
                'fpd.programKerja',
                'detailProgram.programKerja',
                'detailProgram.sumberDana',
            ]);

            if (request()->filled('id_fpd')) {
                $query->where('ID_FPD', (int) request()->query('id_fpd'));
            }

            $data = $query->orderByDesc('ID_DT_FPD')->get();

            return response()->json([
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data detail FPD tidak ditemukan'
                    : 'Data detail FPD berhasil diambil',
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

            $query = DtlFpd::with([
                'fpd.programKerja',
                'detailProgram.programKerja',
                'detailProgram.sumberDana',
            ]);

            if ($request->filled('id_fpd')) {
                $query->where('ID_FPD', (int) $request->query('id_fpd'));
            }

            if ($keyword !== '') {
                $query->where(function ($q) use ($keyword) {
                    $q->where('SATUAN', 'like', "%{$keyword}%")
                      ->orWhere('LINK_BUKTI_NOTA_FPD', 'like', "%{$keyword}%")
                      ->orWhereHas('detailProgram.programKerja', function ($sub) use ($keyword) {
                          $sub->where('PROGRAM_KERJA', 'like', "%{$keyword}%");
                      });
                });
            }

            $data = $query->orderByDesc('ID_DT_FPD')->get();

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
            $data = DtlFpd::with([
                'fpd.programKerja',
                'detailProgram.programKerja',
                'detailProgram.sumberDana',
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
                'message' => 'Detail FPD berhasil diambil',
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
                'ID_FPD' => 'required|integer|exists:fpd_anggaran,ID_FPD',
                'ID_DT_PROGKER' => 'required|integer|exists:dtl_program_kerja,ID_DT_PROGKER',
                'QTY' => 'required|integer|min:1',
                'HARGA_SATUAN' => 'required|numeric|min:0',
                'VOLUME' => 'required|integer|min:1',
                'SATUAN' => 'required|string|max:10',
                'LINK_BUKTI_NOTA_FPD' => 'nullable|string|max:255',
            ]);

            $fpd = FpdAnggaran::find($validated['ID_FPD']);
            $detailProgram = DtlProgramKerja::find($validated['ID_DT_PROGKER']);

            if (!$fpd || !$detailProgram) {
                throw ValidationException::withMessages([
                    'ID_FPD' => ['Data FPD atau detail program kerja tidak ditemukan.'],
                ]);
            }

            if ((int) $fpd->ID_PROGRAM_KERJA !== (int) $detailProgram->ID_PROGRAM_KERJA) {
                throw ValidationException::withMessages([
                    'ID_DT_PROGKER' => ['Detail program kerja harus sesuai dengan program kerja pada FPD.'],
                ]);
            }

            $validated['TOTAL'] = (float) $validated['QTY'] * (float) $validated['VOLUME'] * (float) $validated['HARGA_SATUAN'];

            $this->validateDetailProgramBudget(
                $detailProgram,
                $validated['TOTAL']
            );

            if (((float) $fpd->detailFpd()->sum('TOTAL') + $validated['TOTAL']) > (float) $fpd->NOMINAL_ANGGARAN) {
                throw ValidationException::withMessages([
                    'TOTAL' => ['Total detail FPD melebihi nominal anggaran.'],
                ]);
            }

            $data = DtlFpd::create($validated);
            $this->syncFpd($validated['ID_FPD']);

            $data->refresh()->load([
                'fpd.programKerja',
                'detailProgram.programKerja',
                'detailProgram.sumberDana',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Detail FPD berhasil ditambahkan',
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
            $data = DtlFpd::find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            $validated = $request->validate([
                'ID_FPD' => 'required|integer|exists:fpd_anggaran,ID_FPD',
                'ID_DT_PROGKER' => 'required|integer|exists:dtl_program_kerja,ID_DT_PROGKER',
                'QTY' => 'required|integer|min:1',
                'HARGA_SATUAN' => 'required|numeric|min:0',
                'VOLUME' => 'required|integer|min:1',
                'SATUAN' => 'required|string|max:10',
                'LINK_BUKTI_NOTA_FPD' => 'nullable|string|max:255',
            ]);

            $oldFpdId = $data->ID_FPD;
            $fpd = FpdAnggaran::find($validated['ID_FPD']);
            $detailProgram = DtlProgramKerja::find($validated['ID_DT_PROGKER']);

            if (!$fpd || !$detailProgram) {
                throw ValidationException::withMessages([
                    'ID_FPD' => ['Data FPD atau detail program kerja tidak ditemukan.'],
                ]);
            }

            if ((int) $fpd->ID_PROGRAM_KERJA !== (int) $detailProgram->ID_PROGRAM_KERJA) {
                throw ValidationException::withMessages([
                    'ID_DT_PROGKER' => ['Detail program kerja harus sesuai dengan program kerja pada FPD.'],
                ]);
            }

            $validated['TOTAL'] = (float) $validated['QTY'] * (float) $validated['VOLUME'] * (float) $validated['HARGA_SATUAN'];

            $this->validateDetailProgramBudget(
                $detailProgram,
                $validated['TOTAL'],
                $data->ID_DT_FPD
            );

            $totalLain = (float) DtlFpd::where('ID_FPD', $validated['ID_FPD'])
                ->where('ID_DT_FPD', '!=', $data->ID_DT_FPD)
                ->sum('TOTAL');

            if (($totalLain + $validated['TOTAL']) > (float) $fpd->NOMINAL_ANGGARAN) {
                throw ValidationException::withMessages([
                    'TOTAL' => ['Total detail FPD melebihi nominal anggaran.'],
                ]);
            }

            $data->update($validated);
            $this->syncFpd($validated['ID_FPD']);

            if ((int) $oldFpdId !== (int) $validated['ID_FPD']) {
                $this->syncFpd($oldFpdId);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate',
                'data' => $data->load([
                    'fpd.programKerja',
                    'detailProgram.programKerja',
                    'detailProgram.sumberDana',
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
            $data = DtlFpd::find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            $fpdId = $data->ID_FPD;

            $data->delete();
            $this->syncFpd($fpdId);

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

    private function syncFpd(int $idFpd): void
    {
        $fpd = FpdAnggaran::find($idFpd);

        if (!$fpd) {
            return;
        }

        $total = (float) $fpd->detailFpd()->sum('TOTAL');

        $fpd->update([
            'NOMINAL_FPD' => $total,
            'NOMINAL_SISA' => (float) $fpd->NOMINAL_ANGGARAN - $total,
        ]);
    }

    private function validateDetailProgramBudget(DtlProgramKerja $detailProgram, float $totalBaru, ?int $excludeDetailFpdId = null): void
    {
        $detailBudget = !is_null($detailProgram->TOTAL_PROGKER)
            ? (float) $detailProgram->TOTAL_PROGKER
            : (float) ($detailProgram->NOMINAL ?? 0);

        $usedQuery = DtlFpd::query()
            ->where('ID_DT_PROGKER', $detailProgram->ID_DT_PROGKER);

        if (!is_null($excludeDetailFpdId)) {
            $usedQuery->where('ID_DT_FPD', '!=', $excludeDetailFpdId);
        }

        $usedTotal = (float) $usedQuery->sum('TOTAL');

        if (($usedTotal + $totalBaru) > $detailBudget) {
            throw ValidationException::withMessages([
                'TOTAL' => [
                    'Total pemakaian FPD melebihi anggaran program kerja.',
                ],
            ]);
        }
    }
}
