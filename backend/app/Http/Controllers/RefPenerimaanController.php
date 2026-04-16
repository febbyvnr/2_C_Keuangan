<?php

namespace App\Http\Controllers;

use App\Models\RefPenerimaan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class RefPenerimaanController extends Controller
{

    public function index(): JsonResponse
    {
        try {
            $data = RefPenerimaan::all();
            return response()->json([
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ],500);
        }
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

    public function update(Request $request,$id): JsonResponse
    {
        try {
            $data = RefPenerimaan::find($id);
            if(!$data){
                return response()->json([
                    'message'=>'Data referensi penerimaan tidak ditemukan'
                ],404);
            }
            $validated = $request->validate([
                'REF_ID_REF_PENERIMAAN' => 'nullable|exists:ref_penerimaan,ID_REF_PENERIMAAN',
                'DESKRIPSI_REF_PENERIMAAN' => 'nullable|unique:ref_penerimaan,DESKRIPSI_REF_PENERIMAAN,' . $id . ',ID_REF_PENERIMAAN'
            ]);
            $data->update($validated);
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (ValidationException $e){
            return response()->json([
                'message'=>'Validasi gagal',
                'errors'=>$e->errors()
            ],422);
        } catch (\Throwable $e){
            return response()->json([
                'message'=>'Terjadi kesalahan',
                'error'=>$e->getMessage()
            ],500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $data = RefPenerimaan::find($id);
            if(!$data){
                return response()->json([
                    'message'=>'Data referensi penerimaan tidak ditemukan'
                ],404);
            }
            $data->delete();
            return response()->json([
                'success' => true,
                'message'=>'Data referensi penerimaan berhasil dihapus'
            ]);
        } catch (\Throwable $e){
            return response()->json([
                'message'=>'Terjadi kesalahan saat menghapus data',
                'error'=>$e->getMessage()
            ],500);
        }
    }
}