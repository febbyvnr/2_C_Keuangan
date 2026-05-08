<?php

namespace App\Http\Controllers;

use App\Models\RefPenerimaan;
use App\Models\TrPenerimaan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class RefPenerimaanController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        try {
            $search = trim((string) $request->query('search', ''));
            $allData = RefPenerimaan::query()
                ->withCount([
                    'children as has_child', 
                    'trPenerimaan'
                ])
                ->orderBy('REF_ID_REF_PENERIMAAN', 'asc') 
                ->orderBy('ID_REF_PENERIMAAN', 'asc')
                ->get();
            $allData->map(function ($item) {
                $item->is_used = ($item->tr_penerimaan_count > 0 || $item->has_child > 0);
                return $item;
            });
            $formattedData = $this->generateHierarchy($allData);
            if ($search !== '') {
                $formattedData = collect($formattedData)->filter(function ($item) use ($search) {
                    return str_contains(strtolower($item['DESKRIPSI_REF_PENERIMAAN']), strtolower($search)) ||
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
        $children = $nodes->where('REF_ID_REF_PENERIMAAN', $parentId);
        foreach ($children as $child) {
            $currentNumber = $prefix ? $prefix . "." . $index : (string)$index;
            $result[] = [
                'ID_REF_PENERIMAAN'     => $child->ID_REF_PENERIMAAN,
                'REF_ID_REF_PENERIMAAN' => $child->REF_ID_REF_PENERIMAAN,
                'DESKRIPSI_REF_PENERIMAAN'  => $child->DESKRIPSI_REF_PENERIMAAN,
                'nomor_urut'            => $currentNumber,
                'has_child'             => $child->has_child > 0,
                'is_used'               => $child->is_used,
            ];
            $subResults = $this->generateHierarchy($nodes, $child->ID_REF_PENERIMAAN, $currentNumber);
            $result = array_merge($result, $subResults);
            $index++;
        }
        return $result;
    }

    public function show($id): JsonResponse
    {
        try {
            $data = RefPenerimaan::find($id);
            if(!$data){
                return response()->json([
                    'message' => 'Data referensi penerimaan tidak ditemukan'
                ],404);
            }
            return response()->json([
                'data' => $data
            ]);
        } catch (\Throwable $e){
            return response()->json([
                'message'=>'Terjadi kesalahan',
                'error'=>$e->getMessage()
            ],500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {

            $query = RefPenerimaan::query();
            if ($request->filled('REF_ID_REF_PENERIMAAN')) {
                $query->where('REF_ID_REF_PENERIMAAN', $request->REF_ID_REF_PENERIMAAN);
            }
            if ($request->filled('DESKRIPSI_REF_PENERIMAAN')) {
                $query->where(
                    'DESKRIPSI_REF_PENERIMAAN',
                    'like',
                    '%' . $request->DESKRIPSI_REF_PENERIMAAN . '%'
                );
            }
            $data = $query->get();
            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            return response()->json($data, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat search',
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'REF_ID_REF_PENERIMAAN' => 'nullable|exists:ref_penerimaan,ID_REF_PENERIMAAN',
                'DESKRIPSI_REF_PENERIMAAN' => 'nullable|unique:ref_penerimaan,DESKRIPSI_REF_PENERIMAAN'
            ],[
                'REF_ID_REF_PENERIMAAN.exists' => 'Referensi induk tidak valid',
                'DESKRIPSI_REF_PENERIMAAN.unique' => 'Deskripsi penerimaan sudah ada'
            ]);
            $data = RefPenerimaan::create($validated);
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (ValidationException $e){
            return response()->json([
                'errors'=>$e->errors()
            ],422);
        } catch (\Throwable $e){
            return response()->json([
                'message'=>'Terjadi kesalahan',
                'error'=>$e->getMessage()
            ],500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $data = RefPenerimaan::find($id);
            if (!$data) {
                return response()->json([
                    'message' => 'Data referensi penerimaan tidak ditemukan'
                ], 404);
            }
            $validated = $request->validate([
                'REF_ID_REF_PENERIMAAN' => 'nullable|exists:ref_penerimaan,ID_REF_PENERIMAAN|different:ID_REF_PENERIMAAN',
                'DESKRIPSI_REF_PENERIMAAN' => 'nullable|unique:ref_penerimaan,DESKRIPSI_REF_PENERIMAAN,' . $id . ',ID_REF_PENERIMAAN'
            ]);
            $data->update($validated);
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $data = RefPenerimaan::find($id);
            if (!$data) {
                return response()->json(['message' => 'Data tidak ditemukan'], 404);
            }
            $isUsedInTransaction = TrPenerimaan::where('ID_REF_PENERIMAAN', $id)->exists();
            $hasChild = RefPenerimaan::where('REF_ID_REF_PENERIMAAN', $id)->exists();
            if ($isUsedInTransaction || $hasChild) {
                return response()->json([
                    'message' => 'Data tidak bisa dihapus karena sudah digunakan di transaksi atau memiliki data child'
                ], 409);
            }
            $data->delete();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal menghapus data', 'error' => $e->getMessage()], 500);
        }
    }
}