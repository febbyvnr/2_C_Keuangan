<?php

namespace App\Http\Controllers;

use App\Models\DtlProgramKerja;
use App\Models\DtlFpd;
use App\Models\FpdAnggaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DtlFpdController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $data = DtlFpd::with(['fpd.programKerja', 'detailProgram.programKerja'])->get();

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

            $query = DtlFpd::with(['fpd.programKerja', 'detailProgram.programKerja']);

            if ($keyword !== '') {
                $query->where(function ($q) use ($keyword) {
                    $q->where('SATUAN', 'like', "%{$keyword}%")
                      ->orWhere('LINK_BUKTI_NOTA_FPD', 'like', "%{$keyword}%")
                      ->orWhere('TOTAL_FPD', 'like', "%{$keyword}%");
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
            $data = DtlFpd::with(['fpd.programKerja', 'detailProgram.programKerja'])->find($id);

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

            $data = DB::transaction(function () use ($validated) {
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

                $validated['TOTAL_FPD'] = (float) $validated['QTY'] * (float) $validated['VOLUME'] * (float) $validated['HARGA_SATUAN'];

                if (((float) $fpd->detailFpd()->sum('TOTAL_FPD') + $validated['TOTAL_FPD']) > (float) $fpd->NOMINAL_ANGGARAN) {
                    throw ValidationException::withMessages([
                        'TOTAL_FPD' => ['Total detail FPD melebihi nominal anggaran.'],
                    ]);
                }

                $data = DtlFpd::create($validated);
                $this->syncFpd($validated['ID_FPD']);

                return $data->load(['fpd.programKerja', 'detailProgram.programKerja']);
            });

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

            $data = DB::transaction(function () use ($data, $validated) {
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

                $validated['TOTAL_FPD'] = (float) $validated['QTY'] * (float) $validated['VOLUME'] * (float) $validated['HARGA_SATUAN'];

                $totalLain = (float) DtlFpd::where('ID_FPD', $validated['ID_FPD'])
                    ->where('ID_DT_FPD', '!=', $data->ID_DT_FPD)
                    ->sum('TOTAL_FPD');

                if (($totalLain + $validated['TOTAL_FPD']) > (float) $fpd->NOMINAL_ANGGARAN) {
                    throw ValidationException::withMessages([
                        'TOTAL_FPD' => ['Total detail FPD melebihi nominal anggaran.'],
                    ]);
                }

                $data->update($validated);
                $this->syncFpd($validated['ID_FPD']);

                if ((int) $oldFpdId !== (int) $validated['ID_FPD']) {
                    $this->syncFpd($oldFpdId);
                }

                return $data->load(['fpd.programKerja', 'detailProgram.programKerja']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate',
                'data' => $data,
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

            DB::transaction(function () use ($data, $fpdId) {
                $data->delete();
                $this->syncFpd($fpdId);
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

    private function syncFpd(int $idFpd): void
    {
        $fpd = FpdAnggaran::find($idFpd);

        if (!$fpd) {
            return;
        }

        $total = (float) $fpd->detailFpd()->sum('TOTAL_FPD');

        $fpd->update([
            'NOMINAL_FPD' => $total,
            'NOMINAL_SISA' => (float) $fpd->NOMINAL_ANGGARAN - $total,
        ]);
    }
}
