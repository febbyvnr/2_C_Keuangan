<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\MstProgramKerja;
use App\Models\TrPm;

class DashboardTimPenjaminanMutuController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // 1. Ambil Total Program Kerja RKT
            $totalProgram = MstProgramKerja::where('IS_DELETE', 0)->count();

            $dataMutu = DB::table('tr_pm as tp')
                ->join('ref_pm as rp', 'tp.ID_REF_PM', '=', 'rp.ID_REF_PM')
                ->select('rp.DESKRIPSI_REF_PM as kategori', DB::raw('count(DISTINCT tp.ID_PROGRAM_KERJA) as jumlah'))
                ->whereIn('tp.ID_REF_PM', [25, 26, 28, 29])
                ->groupBy('rp.DESKRIPSI_REF_PM')
                ->get();

            $realisasi = $dataMutu->where('kategori', 'Evaluasi')->first()->jumlah ?? 0;
       
            $deviasi = TrPm::whereIn('ID_REF_PM', [25, 26])
                ->distinct('ID_PROGRAM_KERJA')
                ->count();

            return response()->json([
                'status' => true,
                'message' => 'DashboardTimPenjaminanMutu berhasil dimuat',
                'data' => [
                    'total_rkt' => $totalProgram,
                    'realisasi' => (int)$realisasi,
                    'deviasi' => $deviasi,
                    'persentase_capaian' => $totalProgram > 0 ? round(($realisasi / $totalProgram) * 100, 2) : 0,
                    'rincian_mutu' => $dataMutu
                ]
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memproses DashboardTimPenjaminanMutu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}