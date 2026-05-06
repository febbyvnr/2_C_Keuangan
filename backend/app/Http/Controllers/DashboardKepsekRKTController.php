<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\MstProgramKerja;
use App\Models\TrPm;

class DashboardKepsekRKTController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // 1. Ambil Total Program Kerja RKT (Baseline)
            $totalProgram = MstProgramKerja::where('IS_DELETE', 0)->count();

            // 2. Realisasi Indikator (Berdasarkan ID_REF_PM 28 - Evaluasi Total)
            // Mengasumsikan program yang sudah ada data evaluasinya berarti indikatornya sudah terealisasi/terukur
            $realisasi = TrPm::where('ID_REF_PM', 28)
                ->whereNotNull('DESKRIPSI_TR_PM')
                ->distinct('ID_PROGRAM_KERJA')
                ->count();

            // 3. Menghitung Deviasi (Hambatan atau Perubahan)
            // Mengambil data dari ID 25 (Usulan Perubahan) dan 26 (Koreksi Yayasan)
            $deviasi = TrPm::whereIn('ID_REF_PM', [25, 26])
                ->whereNotNull('DESKRIPSI_TR_PM')
                ->distinct('ID_PROGRAM_KERJA')
                ->count();

            // 4. Detail Kategori Mutu khusus untuk Kepsek
            // Mengelompokkan data berdasarkan jenis evaluasi agar Kepsek bisa melihat peta hambatan
            $rincianKepsek = DB::table('tr_pm as tp')
                ->join('ref_pm as rp', 'tp.ID_REF_PM', '=', 'rp.ID_REF_PM')
                ->select('rp.DESKRIPSI_REF_PM as label', DB::raw('count(DISTINCT tp.ID_PROGRAM_KERJA) as nilai'))
                ->whereIn('tp.ID_REF_PM', [25, 26, 28, 29])
                ->groupBy('rp.DESKRIPSI_REF_PM')
                ->get();

            // 5. Hitung Persentase Capaian RKT
            $persentaseCapaian = $totalProgram > 0 ? round(($realisasi / $totalProgram) * 100, 2) : 0;

            return response()->json([
                'status' => true,
                'message' => 'Dashboard Kepsek RKT berhasil dimuat',
                'data' => [
                    'total_rkt' => $totalProgram,
                    'realisasi_indikator' => (int)$realisasi,
                    'total_deviasi' => $deviasi,
                    'persentase_capaian' => $persentaseCapaian,
                    'analisis_mutu' => $rincianKepsek,
                    'keterangan_status' => [
                        'tercapai' => $realisasi,
                        'perlu_perbaikan' => $deviasi,
                        'belum_berjalan' => $totalProgram - $realisasi
                    ]
                ]
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memproses Dashboard Kepsek RKT',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}