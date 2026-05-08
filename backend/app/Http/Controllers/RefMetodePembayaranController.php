<?php

namespace App\Http\Controllers;

use App\Models\RefMetodePembayaran;
use Illuminate\Http\JsonResponse;

class RefMetodePembayaranController extends Controller
{
    public function index(): JsonResponse
    {
        $data = RefMetodePembayaran::orderBy('DESKRIPSI_METODE_PEMBAYARAN', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar metode pembayaran berhasil diambil.',
            'data' => $data,
        ]);
    }
}