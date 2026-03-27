<?php

namespace App\Http\Controllers;

use App\Models\TrCicilan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TrCicilanController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $data = TrCicilan::all();
            return response()->json([
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data cicilan tidak ditemukan'
                    : 'Data cicilan berhasil diambil',
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
            $query = TrCicilan::query();
            if ($request->filled('ID_TR_CICILAN')) {
                $query->where('ID_TR_CICILAN', $request->ID_TR_CICILAN);
            }
            if ($request->filled('ID_PEMBAYARAN')) {
                $query->where('ID_PEMBAYARAN', $request->ID_PEMBAYARAN);
            }
            if ($request->filled('TGL_CICILAN')) {
                $query->where('TGL_CICILAN', $request->TGL_CICILAN);
            }
            if ($request->filled('JUMLAH_CICILAN')) {
                $query->where('JUMLAH_CICILAN', $request->JUMLAH_CICILAN);
            }
            if ($request->filled('CICILAN_KE')) {
                $query->where('CICILAN_KE', $request->CICILAN_KE);
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
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $id = (int) $id;
            $data = TrCicilan::find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data cicilan tidak ditemukan',
                    'data' => null,
                ], 404);
            }
            return response()->json([
                'success' => true,
                'message' => 'Data cicilan berhasil diambil',
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
                'ID_PEMBAYARAN' => 'required|integer|exists:tr_pembayaran,ID_PEMBAYARAN',
                'TGL_CICILAN' => 'required|date',
                'JUMLAH_CICILAN' => 'required|numeric',
                'CICILAN_KE' => 'required|integer'
            ],[
                'ID_PEMBAYARAN.required' => 'ID pembayaran wajib diisi',
                'ID_PEMBAYARAN.exists' => 'ID pembayaran tidak ditemukan',
                'TGL_CICILAN.required' => 'Tanggal cicilan wajib diisi',
                'JUMLAH_CICILAN.required' => 'Jumlah cicilan wajib diisi',
                'CICILAN_KE.required' => 'Cicilan ke wajib diisi'
            ]);
            $lastId = TrCicilan::max('ID_TR_CICILAN');
            $newId = $lastId ? $lastId + 1 : 1;
            $data = TrCicilan::create([
                'ID_TR_CICILAN' => $newId,
                'ID_PEMBAYARAN' => $validated['ID_PEMBAYARAN'],
                'TGL_CICILAN' => $validated['TGL_CICILAN'],
                'JUMLAH_CICILAN' => $validated['JUMLAH_CICILAN'],
                'CICILAN_KE' => $validated['CICILAN_KE']
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Data cicilan berhasil ditambahkan',
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
            $data = TrCicilan::find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data cicilan tidak ditemukan',
                    'data' => null,
                ], 404);
            }
            $validated = $request->validate([
                'ID_PEMBAYARAN' => 'required|integer|exists:tr_pembayaran,ID_PEMBAYARAN',
                'TGL_CICILAN' => 'required|date',
                'JUMLAH_CICILAN' => 'required|numeric',
                'CICILAN_KE' => 'required|integer'
            ]);
            $data->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'Data cicilan berhasil diupdate',
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
            $data = TrCicilan::find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data cicilan tidak ditemukan',
                    'data' => null,
                ], 404);
            }
            $data->delete();
            return response()->json([
                'success' => true,
                'message' => 'Data cicilan berhasil dihapus',
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