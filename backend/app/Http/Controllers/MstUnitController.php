<?php

namespace App\Http\Controllers;

use App\Models\MstUnit;
use Illuminate\Http\JsonResponse;

class MstUnitController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $data = MstUnit::query()
                ->orderBy('ID_UNIT', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data unit tidak ditemukan'
                    : 'Data unit berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data unit',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
