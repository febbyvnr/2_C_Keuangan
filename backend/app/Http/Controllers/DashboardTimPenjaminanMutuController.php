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

            // 2. Ambil Rincian Mutu (Gunakan NAMA_PM sebagai pengganti DESKRIPSI_REF_PM)
            $dataMutu = DB::table('tr_pm as tp')
                ->join('ref_pm as rp', 'tp.ID_REF_PM', '=', 'rp.ID_REF_PM')
                ->select('rp.NAMA_PM as kategori', DB::raw('count(DISTINCT tp.ID_PROGRAM_KERJA) as jumlah'))
                ->whereIn('tp.ID_REF_PM', [25, 26, 28, 29])
                ->groupBy('rp.NAMA_PM')
                ->get();

            // 3. Ambil nilai Realisasi dari kategori 'Evaluasi'
            // Catatan: Pastikan di database NAMA_PM untuk ID 28 adalah 'Evaluasi'
            $realisasi = $dataMutu->where('kategori', 'EVALUASI TOTAL')->first()->jumlah ?? 0;
       
            // 4. Hitung Deviasi (ID 25 & 26)
            $deviasi = TrPm::whereIn('ID_REF_PM', [25, 26])
                ->distinct('ID_PROGRAM_KERJA')
                ->count();

            return response()->json([
                'status' => true,
                'message' => 'Dashboard Tim Penjaminan Mutu berhasil dimuat',
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
                'message' => 'Gagal memproses Dashboard Tim Penjaminan Mutu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}