<?php

namespace App\Http\Controllers;

use App\Models\MstProgramKerja;
use App\Models\DtlFpd;
use App\Models\TrPembayaran;

class DashboardBendaharaController extends Controller
{
    public function index()
    {
        try {
            $totalAnggaran = MstProgramKerja::sum('TOTAL_PROGKER');
            $totalRealisasi = DtlFpd::sum('NOMINAL');
            $totalPembayaranSiswa = TrPembayaran::sum('NOMINAL');

            $persentase = $totalAnggaran > 0
                ? ($totalRealisasi / $totalAnggaran) * 100
                : 0;

            return response()->json([
                'status' => true,
                'message' => 'Dashboard bendahara berhasil diambil',
                'data' => [
                    'total_anggaran' => $totalAnggaran ?? 0,
                    'total_realisasi' => $totalRealisasi ?? 0,
                    'persentase_serapan' => round($persentase, 2),
                    'total_pembayaran_siswa' => $totalPembayaranSiswa ?? 0
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