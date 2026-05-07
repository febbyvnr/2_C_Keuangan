<?php

namespace App\Http\Controllers;

use App\Models\RefJenisTagihan;
use Illuminate\Http\JsonResponse;

class RefJenisTagihanController extends Controller
{
    public function index(): JsonResponse
    {
        $data = RefJenisTagihan::orderBy('DESKRIPSI_JENIS_TAGIHAN', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar jenis tagihan berhasil diambil.',
            'data' => $data,
        ]);
    }
}