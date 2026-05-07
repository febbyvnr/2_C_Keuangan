<?php

namespace App\Http\Controllers;

use App\Models\RefSumberDana;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RefSumberDanaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $search = trim((string) $request->query('search', ''));
            $allData = RefSumberDana::query()
                ->withCount([
                    'children as has_child', 
                    'dtlProgramKerja',
                    'trPenerimaan'
                ])
                ->orderBy('REF_ID_REF_DANA', 'asc') 
                ->orderBy('ID_REF_DANA', 'asc')
                ->get();
            $allData->map(function ($item) {
                $item->is_used = ($item->dtl_program_kerja_count > 0 || 
                                $item->tr_penerimaan_count > 0 || 
                                $item->has_child > 0);
                return $item;
            });
            $formattedData = $this->generateHierarchy($allData);
            if ($search !== '') {
                $formattedData = collect($formattedData)->filter(function ($item) use ($search) {
                    return str_contains(strtolower($item['DESKRIPSI_SUMBER_DANA']), strtolower($search)) ||
                        str_contains((string)$item['nomor_urut'], $search);
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
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function generateHierarchy($nodes, $parentId = null, $prefix = "")
    {
        $result = [];
        $index = 1;
        $children = $nodes->where('REF_ID_REF_DANA', $parentId);
        foreach ($children as $child) {
            $currentNumber = $prefix ? $prefix . "." . $index : (string)$index;
            $result[] = [
                'ID_REF_DANA'           => $child->ID_REF_DANA,
                'REF_ID_REF_DANA'       => $child->REF_ID_REF_DANA,
                'DESKRIPSI_SUMBER_DANA' => $child->DESKRIPSI_SUMBER_DANA,
                'nomor_urut'            => $currentNumber,
                'has_child'             => $child->has_child > 0,
                'is_used'               => $child->is_used,
            ];
            $subResults = $this->generateHierarchy($nodes, $child->ID_REF_DANA, $currentNumber);
            $result = array_merge($result, $subResults);
            $index++;
        }
        return $result;
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
            if (
                $data->dtlProgramKerja()->exists() ||
                $data->trPenerimaan()->exists() ||
                $data->children()->exists()
            ) {
                return response()->json([
                    'message' => 'Tidak bisa dihapus karena masih digunakan atau punya child'
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