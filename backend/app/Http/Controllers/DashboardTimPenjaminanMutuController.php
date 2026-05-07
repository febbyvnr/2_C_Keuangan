<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\MstProgramKerja;

class DashboardTimPenjaminanMutuController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // Mengambil detail Program Kerja (Target) dan hasil Mutu (Realisasi/Deviasi/Evaluasi)
            $dataRkt = DB::table('mst_program_kerja as mst')
                ->leftJoin('tr_pm as tp', 'mst.ID_PROGRAM_KERJA', '=', 'tp.ID_PROGRAM_KERJA')
                ->select(
                    'mst.ID_PROGRAM_KERJA',
                    'mst.PROGRAM_KERJA',   // Nama Program
                    'mst.INDIKATOR',       // Target Indikator dari Perencanaan
                    'mst.SASARAN',         // Sasaran dari Perencanaan
                    'mst.TOTAL_PROGKER',   // Pagu Anggaran
                    
                    DB::raw('GROUP_CONCAT(tp.ID_REF_PM) as list_id_ref'),
                   
                    DB::raw('MAX(CASE WHEN tp.ID_REF_PM = 29 THEN tp.KETERANGAN ELSE NULL END) as catatan_evaluasi'),
                   
                    DB::raw('MAX(CASE WHEN tp.ID_REF_PM = 28 THEN tp.KETERANGAN ELSE NULL END) as realisasi_indikator')
                )
                ->where('mst.IS_DELETE', 0)
                ->groupBy(
                    'mst.ID_PROGRAM_KERJA', 
                    'mst.PROGRAM_KERJA', 
                    'mst.INDIKATOR', 
                    'mst.SASARAN', 
                    'mst.TOTAL_PROGKER'
                )
                ->get();

            $summary = [
                'total_program' => $dataRkt->count(),
                'mencapai_target' => 0,
                'mengalami_deviasi' => 0,
            ];

            $mappedData = $dataRkt->map(function ($item) use (&$summary) {
                $refIds = explode(',', $item->list_id_ref);

                $hasRealisasi = in_array(28, $refIds);
                
                $hasDeviasi = in_array(25, $refIds) || in_array(26, $refIds);

                if ($hasRealisasi) $summary['mencapai_target']++;
                if ($hasDeviasi) $summary['mengalami_deviasi']++;

                return [
                    'id' => $item->ID_PROGRAM_KERJA,
                    'kegiatan' => $item->PROGRAM_KERJA,
                    'target_indikator' => $item->INDIKATOR,
                    'realisasi_indikator' => $item->realisasi_indikator ?? 'Belum diisi',
                    'pagu' => $item->TOTAL_PROGKER,
                    'status' => [
                        'is_realisasi' => $hasRealisasi,
                        'is_deviasi' => $hasDeviasi,
                    ],
                    'evaluasi' => $item->catatan_evaluasi ?? 'N/A' // Konstrain 3: Evaluasi
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Dashboard RKT untuk Tim Penjaminan Mutu',
                'summary' => [
                    'total_rkt' => $summary['total_program'],
                    'realisasi' => $summary['mencapai_target'],
                    'deviasi' => $summary['mengalami_deviasi'],
                    'persentase_mutu' => $summary['total_program'] > 0 
                        ? round(($summary['mencapai_target'] / $summary['total_program']) * 100, 2) 
                        : 0
                ],
                'detail_evaluasi_tpm' => $mappedData
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}