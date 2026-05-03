<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MstProgramKerja;
use App\Models\DtlFpd;

class DashboardBendaharaController extends Controller
{
    public function index()
    {
        try {
            // =========================
            // 1. TOTAL ANGGARAN
            // =========================
            $totalAnggaran = MstProgramKerja::sum('TOTAL_PROGKER');

            // =========================
            // 2. TOTAL REALISASI
            // =========================
            $totalRealisasi = DtlFpd::sum('NOMINAL');

            // =========================
            // 3. PERSENTASE SERAPAN
            // =========================
            $persentase = $totalAnggaran > 0
                ? ($totalRealisasi / $totalAnggaran) * 100
                : 0;

            // =========================
            // 4. RESPONSE
            // =========================
            return response()->json([
                'status' => true,
                'message' => 'Dashboard bendahara berhasil diambil',
                'data' => [
                    'total_anggaran' => $totalAnggaran ?? 0,
                    'total_realisasi' => $totalRealisasi ?? 0,
                    'persentase_serapan' => round($persentase, 2)
                ]
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}