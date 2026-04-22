<?php

namespace App\Http\Controllers;

use App\Models\MstKaryawan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MstKaryawanController extends Controller
{
    public function show($nip): JsonResponse
    {
        try {
            $karyawan = MstKaryawan::where('NIP_KARYAWAN', $nip)
                ->where('IS_DELETE', false)
                ->first();
            if (!$karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data karyawan tidak ditemukan.'
                ], 404);
            }
            return response()->json([
                'success' => true,
                'data' => [
                    'NIP_KARYAWAN'   => $karyawan->NIP_KARYAWAN,
                    'NAMA_KARYAWAN'  => $karyawan->NAMA_KARYAWAN,
                    'EMAIL_KARYAWAN' => $karyawan->EMAIL_KARYAWAN,
                    'UNIT'           => $karyawan->unit ? $karyawan->unit->NAMA_UNIT : null,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error Fetch Karyawan: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.'
            ], 500);
        }
    }

    public function index(): JsonResponse
    {
        $karyawan = MstKaryawan::where('IS_DELETE', false)->get();
        return response()->json([
            'success' => true,
            'data'    => $karyawan
        ]);
    }
}