<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardTimPenjaminanMutuController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $tahunFilter = $request->query('tahun', date('Y'));
            $tahunSekarang = (int) date('Y');

            $dataRkt = DB::table('mst_program_kerja as mst')
                ->leftJoin('tr_pm as tp', 'mst.ID_PROGRAM_KERJA', '=', 'tp.ID_PROGRAM_KERJA')
                ->select(
                    'mst.ID_PROGRAM_KERJA',
                    'mst.PROGRAM_KERJA',   
                    'mst.INDIKATOR',       
                    'mst.SASARAN',         
                    'mst.TOTAL_PROGKER',   
                    'mst.ID_TA_ANGGARAN', 
                    DB::raw('GROUP_CONCAT(tp.ID_REF_PM) as list_id_ref'),
                    DB::raw('MAX(CASE WHEN tp.ID_REF_PM = 28 THEN tp.DESKRIPSI_TR_PM ELSE NULL END) as realisasi_indikator'),
                    DB::raw('MAX(CASE WHEN tp.ID_REF_PM = 29 THEN tp.DESKRIPSI_TR_PM ELSE NULL END) as catatan_evaluasi')
                )
                ->where('mst.IS_DELETE', 0)
                // Filter berdasarkan ID_TA_ANGGARAN
                ->where('mst.ID_TA_ANGGARAN', $tahunFilter) 
                ->groupBy(
                    'mst.ID_PROGRAM_KERJA', 
                    'mst.PROGRAM_KERJA', 
                    'mst.INDIKATOR', 
                    'mst.SASARAN', 
                    'mst.TOTAL_PROGKER',
                    'mst.ID_TA_ANGGARAN'
                )
                ->get();

            $totalProgram = $dataRkt->count();
            $countRealisasi = 0;
            $countDeviasi = 0;

            $detailEvaluasi = $dataRkt->map(function ($item) use (&$countRealisasi, &$countDeviasi, $tahunSekarang) {
                $refIds = $item->list_id_ref ? explode(',', $item->list_id_ref) : [];
                $isRealisasi = in_array(28, $refIds);
                $isDeviasi = in_array(25, $refIds) || in_array(26, $refIds);

                if ($isRealisasi) $countRealisasi++;
                if ($isDeviasi) $countDeviasi++;

                // LOGIC STATUS SELESAI
                $statusTeks = 'Aktif';
                if ((int)$item->ID_TA_ANGGARAN < $tahunSekarang) {
                    $statusTeks = 'Selesai';
                } elseif ($isRealisasi) {
                    $statusTeks = 'Terealisasi';
                }

                return [
                    'id_program' => $item->ID_PROGRAM_KERJA,
                    'program_kerja' => $item->PROGRAM_KERJA,
                    'id_ta' => $item->ID_TA_ANGGARAN,
                    'target_indikator' => $item->INDIKATOR,
                    'sasaran' => $item->SASARAN,
                    'pagu_anggaran' => (float) $item->TOTAL_PROGKER,
                    'realisasi_teks' => $item->realisasi_indikator ?? 'Belum ada input realisasi',
                    'evaluasi_teks' => $item->catatan_evaluasi ?? 'Belum ada input evaluasi',
                    'status_label' => $statusTeks,
                    'status_detail' => [
                        'sudah_realisasi' => $isRealisasi,
                        'ada_deviasi' => $isDeviasi
                    ]
                ];
            });

            return response()->json([
                'status' => true,
                'message' => "Dashboard Berhasil Dimuat",
                'summary' => [
                    'total_rkt' => $totalProgram,
                    'total_realisasi' => $countRealisasi,
                    'total_deviasi' => $countDeviasi,
                    'persentase_capaian' => $totalProgram > 0 
                        ? round(($countRealisasi / $totalProgram) * 100, 2) 
                        : 0,
                ],
                'data' => $detailEvaluasi
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memproses Dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}