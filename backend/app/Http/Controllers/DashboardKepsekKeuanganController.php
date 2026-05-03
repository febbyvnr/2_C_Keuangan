<?php

namespace App\Http\Controllers;

use App\Models\MstProgramKerja;
use App\Models\DtlFpd;
use App\Models\TrPembayaran;

class DashboardKepsekKeuanganController extends Controller
{
    public function index()
    {
        try {
            $totalAnggaran = (float) MstProgramKerja::sum('TOTAL_PROGKER');
            $totalRealisasi = (float) DtlFpd::sum('TOTAL');
            $totalPembayaran = (float) TrPembayaran::sum('JUMLAH_BAYAR');

            $persentase = $totalAnggaran > 0
                ? ($totalRealisasi / $totalAnggaran) * 100
                : 0;

            if ($persentase >= 80) {
                $status = "Baik";
            } elseif ($persentase >= 50) {
                $status = "Cukup";
            } else {
                $status = "Belum Optimal";
            }

            return response()->json([
                'status' => true,
                'message' => 'Dashboard Kepala Sekolah',
                'data' => [
                    'total_anggaran' => $totalAnggaran,
                    'realisasi' => $totalRealisasi,
                    'serapan' => round($persentase, 2),
                    'pembayaran_siswa' => $totalPembayaran,
                    'status_keuangan' => $status
                ]
            ], 200);

        } catch (\Throwable $e) {

            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil dashboard kepsek',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}