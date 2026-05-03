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
            $totalAnggaran = (float) MstProgramKerja::sum('TOTAL_PROGKER');

            $totalRealisasi = (float) DtlFpd::sum('TOTAL');

            $totalPembayaranSiswa = (float) TrPembayaran::sum('JUMLAH_BAYAR');

            $persentase = $totalAnggaran > 0
                ? ($totalRealisasi / $totalAnggaran) * 100
                : 0;
                
            return response()->json([
                'status' => true,
                'message' => 'Dashboard bendahara berhasil diambil',
                'data' => [
                    'total_anggaran' => $totalAnggaran,
                    'total_realisasi' => $totalRealisasi,
                    'persentase_serapan' => round($persentase, 2),
                    'total_pembayaran_siswa' => $totalPembayaranSiswa,
                    'sisa_anggaran' => $totalAnggaran - $totalRealisasi
                ]
            ], 200);

        } catch (\Throwable $e) {

            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}