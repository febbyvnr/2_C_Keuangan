<?php

namespace App\Http\Controllers;

use App\Models\RefSumberDana;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RefSumberDanaController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $data = RefSumberDana::all();
            return response()->json([
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $keyword = trim((string) $request->query('keyword', ''));
            $query = RefSumberDana::query();
            if ($keyword !== '') {
                $query->where(function ($q) use ($keyword) {
                    $q->where('ID_REF_DANA', 'like', "%{$keyword}%")
                      ->orWhere('REF_ID_REF_DANA', 'like', "%{$keyword}%")
                      ->orWhere('DESKRIPSI_SUMBER_DANA', 'like', "%{$keyword}%");
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
            $data = RefSumberDana::find($id);
            if (!$data) {
                return response()->json([
                    'message' => 'Data tidak ditemukan',
                ], 404);
            }
            return response()->json([
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'REF_ID_REF_DANA' => 'nullable|integer|exists:ref_sumber_dana,ID_REF_DANA',
                'DESKRIPSI_SUMBER_DANA' => 'required|string|max:255',
            ], [
                // 'REF_ID_REF_DANA.required' => 'ID referensi sumber dana wajib diisi.',
                'REF_ID_REF_DANA.integer' => 'ID referensi sumber dana harus berupa angka.',
                'REF_ID_REF_DANA.exists' => 'ID referensi sumber dana tidak ditemukan di database.',
                'DESKRIPSI_SUMBER_DANA.required' => 'Deskripsi wajib diisi.',
                'DESKRIPSI_SUMBER_DANA.string' => 'Deskripsi harus berupa teks.',
            ]);
            $lastId = RefSumberDana::max('ID_REF_DANA');
            $newId = $lastId ? $lastId + 1 : 1;
            $data = RefSumberDana::create([
                'ID_REF_DANA' => $newId,
                'REF_ID_REF_DANA' => $validated['REF_ID_REF_DANA'],
                'DESKRIPSI_SUMBER_DANA' => $validated['DESKRIPSI_SUMBER_DANA'],
            ]);
            return response()->json([
                'data' => $data,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $id = (int) $id;
            $data = RefSumberDana::find($id);
            if (!$data) {
                return response()->json([
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            $validated = $request->validate([
                'REF_ID_REF_DANA' => 'nullable|integer|exists:ref_sumber_dana,ID_REF_DANA',
                'DESKRIPSI_SUMBER_DANA' => 'required|string|max:255',
            ],
            [
                // 'REF_ID_REF_DANA.required' => 'ID referensi sumber dana wajib diisi.',
                'REF_ID_REF_DANA.integer' => 'ID referensi sumber dana harus berupa angka.',
                'REF_ID_REF_DANA.exists' => 'ID referensi sumber dana tidak ditemukan di database.',
                'DESKRIPSI_SUMBER_DANA.required' => 'Deskripsi wajib diisi.',
            ]);
            $data->update($validated);
            return response()->json([
                'data' => $data,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $id = (int) $id;
            $data = RefSumberDana::find($id);
            if (!$data) {
                return response()->json([
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            if ($data->dtlProgramKerja()->exists() || $data->trPenerimaan()->exists()) {
                return response()->json([
                    'message' => 'Data tidak bisa dihapus karena masih digunakan',
                ], 400);
            }
            $data->delete();
            return response()->json([
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}