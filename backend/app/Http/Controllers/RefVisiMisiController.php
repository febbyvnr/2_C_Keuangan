<?php

namespace App\Http\Controllers;

use App\Models\RefVisiMisi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RefVisiMisiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = RefVisiMisi::query();

            $tipe = $request->query('tipe');
            if ($tipe !== null && $tipe !== '') {
                $query->where('TIPE', $tipe);
            }

            $data = $query->get();

            return response()->json([
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data ref visi misi tidak ditemukan'
                    : 'Data ref visi misi berhasil diambil',
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
            $tipe = $request->query('tipe');

            $query = RefVisiMisi::query();

            if ($keyword !== '') {
                $query->where('DESKRIPSI', 'like', "%{$keyword}%");
            }

            if ($tipe !== null && $tipe !== '') {
                $query->where('TIPE', $tipe);
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
            $data = RefVisiMisi::with('trPm')->find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ref visi misi berhasil diambil',
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
                'TIPE' => 'required|in:Visi,Misi',
                'DESKRIPSI' => 'required|string|max:255',
                'IS_ACTIVE' => 'nullable|boolean',
            ]);

            $data = RefVisiMisi::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Ref visi misi berhasil ditambahkan',
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
            $data = RefVisiMisi::find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            $validated = $request->validate([
                'TIPE' => 'required|in:Visi,Misi',
                'DESKRIPSI' => 'required|string|max:255',
                'IS_ACTIVE' => 'nullable|boolean',
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
            $data = RefVisiMisi::find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            $usageCount = $data->trPm()->count();
            if ($usageCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak dapat dihapus karena sudah dipakai dalam evaluasi PM',
                    'data' => [
                        'id' => $data->ID_VISI_MISI,
                        'deskripsi' => $data->DESKRIPSI,
                        'usage_count' => $usageCount,
                    ],
                ], 422);
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
