<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\TrPembayaran;
use Illuminate\Validation\ValidationException;

class TrPembayaranController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $data = TrPembayaran::all();
            return response()->json([
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data pembayaran tidak ditemukan'
                    : 'Data pembayaran berhasil diambil',
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $data = TrPembayaran::find($id);
            if(!$data){
                return response()->json([
                    'success' => false,
                    'message' => 'Data pembayaran tidak ditemukan'
                ],404);
            }
            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil diambil',
                'data' => $data
            ]);
        } catch (\Throwable $e){
            return response()->json([
                'success'=>false,
                'message'=>'Terjadi kesalahan',
                'error'=>$e->getMessage()
            ],500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {

            $validated = $request->validate([
                'ID_SISWA_TETAP' => 'nullable|integer',
                'KODE_TA' => 'nullable|integer',
                'ID_JENIS_PEMBAYARAN' => 'nullable|integer',
                'ID_TAGIHAN_SISWA' => 'nullable|integer',
                'REF_ID_JENIS_PEMBAYARAN' => 'nullable|integer',
                'TGL_BAYAR' => 'nullable|date',
                'JUMLAH_BAYAR' => 'nullable|numeric',
                'LINK_BUKTI_BAYAR' => 'string|max:255',
                'NIP_VALIDATOR_PEMBAYARAN' => 'nullable|string|max:20'
            ]);
            $lastId = TrPembayaran::max('ID_PEMBAYARAN');
            $newId = $lastId ? $lastId + 1 : 1;
            $validated['ID_PEMBAYARAN'] = $newId;
            $data = TrPembayaran::create($validated);
            return response()->json([
                'success'=>true,
                'message'=>'Data pembayaran berhasil ditambahkan',
                'data'=>$data
            ],201);
        } catch (ValidationException $e){
            return response()->json([
                'success'=>false,
                'message'=>'Validasi gagal',
                'errors'=>$e->errors()
            ],422);
        } catch (\Throwable $e){
            return response()->json([
                'success'=>false,
                'message'=>'Terjadi kesalahan',
                'error'=>$e->getMessage()
            ],500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = TrPembayaran::query();
            if ($request->filled('ID_SISWA_TETAP')) {
                $query->where('ID_SISWA_TETAP', $request->ID_SISWA_TETAP);
            }
            if ($request->filled('KODE_TA')) {
                $query->where('KODE_TA', $request->KODE_TA);
            }
            if ($request->filled('ID_JENIS_PEMBAYARAN')) {
                $query->where('ID_JENIS_PEMBAYARAN', $request->ID_JENIS_PEMBAYARAN);
            }
            if ($request->filled('ID_TAGIHAN_SISWA')) {
                $query->where('ID_TAGIHAN_SISWA', $request->ID_TAGIHAN_SISWA);
            }
            if ($request->filled('REF_ID_JENIS_PEMBAYARAN')) {
                $query->where('REF_ID_JENIS_PEMBAYARAN', $request->REF_ID_JENIS_PEMBAYARAN);
            }
            if ($request->filled('TGL_BAYAR')) {
                $query->whereDate('TGL_BAYAR', $request->TGL_BAYAR);
            }
            if ($request->filled('JUMLAH_BAYAR')) {
                $query->where('JUMLAH_BAYAR', $request->JUMLAH_BAYAR);
            }
            if ($request->filled('NIP_VALIDATOR_PEMBAYARAN')) {
                $query->where('NIP_VALIDATOR_PEMBAYARAN', 'like', '%' . $request->NIP_VALIDATOR_PEMBAYARAN . '%');
            }
            $data = $query->get();
            return response()->json([
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data tidak ditemukan'
                    : 'Data berhasil ditemukan',
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat search',
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $data = TrPembayaran::find($id);
            if(!$data){
                return response()->json([
                    'success'=>false,
                    'message'=>'Data pembayaran tidak ditemukan'
                ],404);
            }
            $validated = $request->validate([
                'ID_SISWA_TETAP' => 'nullable|integer',
                'KODE_TA' => 'nullable|integer',
                'ID_JENIS_PEMBAYARAN' => 'nullable|integer',
                'ID_TAGIHAN_SISWA' => 'nullable|integer',
                'REF_ID_JENIS_PEMBAYARAN' => 'nullable|integer',
                'TGL_BAYAR' => 'nullable|date',
                'JUMLAH_BAYAR' => 'nullable|numeric',
                'LINK_BUKTI_BAYAR' => 'string|max:255',
                'NIP_VALIDATOR_PEMBAYARAN' => 'nullable|string|max:20'
            ]);
            $data->update($validated);
            return response()->json([
                'success'=>true,
                'message'=>'Data pembayaran berhasil diupdate',
                'data'=>$data
            ]);
        } catch (ValidationException $e){
            return response()->json([
                'success'=>false,
                'message'=>'Validasi gagal',
                'errors'=>$e->errors()
            ],422);
        } catch (\Throwable $e){
            return response()->json([
                'success'=>false,
                'message'=>'Terjadi kesalahan',
                'error'=>$e->getMessage()
            ],500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $data = TrPembayaran::find($id);
            if(!$data){
                return response()->json([
                    'success'=>false,
                    'message'=>'Data pembayaran tidak ditemukan'
                ],404);
            }
            $data->delete();
            return response()->json([
                'success'=>true,
                'message'=>'Data pembayaran berhasil dihapus'
            ]);
        } catch (\Throwable $e){
            return response()->json([
                'success'=>false,
                'message'=>'Terjadi kesalahan saat menghapus data',
                'error'=>$e->getMessage()
            ],500);
        }
    }
}