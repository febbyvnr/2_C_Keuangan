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
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data ref sumber dana tidak ditemukan'
                    : 'Data ref sumber dana berhasil diambil',
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

            $query = RefSumberDana::query();

            if ($keyword !== '') {
                $query->where(function ($q) use ($keyword) {
                    $q->where('ID_REF_DANA', 'like', "%{$keyword}%")
                      ->orWhere('REF_ID_REF_DANA', 'like', "%{$keyword}%");
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
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ref sumber dana berhasil diambil',
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
                'REF_ID_REF_DANA' => 'required|integer',
            ]);

            $data = RefSumberDana::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Ref sumber dana berhasil ditambahkan',
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
            $data = RefSumberDana::find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            $validated = $request->validate([
                'REF_ID_REF_DANA' => 'required|integer',
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
            $data = RefSumberDana::find($id);

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
