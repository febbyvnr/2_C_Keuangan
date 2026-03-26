<?php

namespace App\Http\Controllers;

use App\Models\FpdAnggaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FpdAnggaranController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $data = FpdAnggaran::all();

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

            $query = FpdAnggaran::query();

            if ($keyword !== '') {
                $query->where(function ($q) use ($keyword) {
                    $q->where('TGL_FPD', 'like', "%{$keyword}%")
                      ->orWhere('NIP_VALIDATOR_FPD', 'like', "%{$keyword}%");
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
            $data = FpdAnggaran::with('detailFpd')->find($id);

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
                'ID_PROGRAM_KERJA' => 'required|integer|exists:dtl_program_kerja,ID_PROGRAM_KERJA',
                'TGL_FPD' => 'required|date',
                'NOMINAL_ANGGARAN' => 'required|numeric|min:0',
                'NOMINAL_FPD' => 'required|numeric|min:0',
                'NOMINAL_SISA' => 'required|numeric|min:0',
                'NIP_VALIDATOR_FPD' => 'nullable|string|max:20',
            ]);

            $data = FpdAnggaran::create($validated);

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
                'ID_PROGRAM_KERJA' => 'required|integer|exists:dtl_program_kerja,ID_PROGRAM_KERJA',
                'TGL_FPD' => 'required|date',
                'NOMINAL_ANGGARAN' => 'required|numeric|min:0',
                'NOMINAL_FPD' => 'required|numeric|min:0',
                'NOMINAL_SISA' => 'required|numeric|min:0',
                'NIP_VALIDATOR_FPD' => 'nullable|string|max:20',
            ]);

            $data->update($validated);

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
            $data = FpdAnggaran::find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            $data->delete();

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
}
