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
            $dataRkt = DB::table('mst_program_kerja as mst')
                ->leftJoin('tr_pm as tp', 'mst.ID_PROGRAM_KERJA', '=', 'tp.ID_PROGRAM_KERJA')
                ->select(
                    'mst.ID_PROGRAM_KERJA',
                    'mst.PROGRAM_KERJA',   
                    'mst.INDIKATOR',       
                    'mst.SASARAN',         
                    'mst.TOTAL_PROGKER',   
                    DB::raw('GROUP_CONCAT(tp.ID_REF_PM) as list_id_ref'),
                    // Mengambil teks Realisasi dari kolom DESKRIPSI_TR_PM
                    DB::raw('MAX(CASE WHEN tp.ID_REF_PM = 28 THEN tp.DESKRIPSI_TR_PM ELSE NULL END) as realisasi_indikator'),
                    // Mengambil teks Evaluasi dari kolom DESKRIPSI_TR_PM
                    DB::raw('MAX(CASE WHEN tp.ID_REF_PM = 29 THEN tp.DESKRIPSI_TR_PM ELSE NULL END) as catatan_evaluasi')
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

            // Inisialisasi Counter untuk Summary Dashboard
            $totalProgram = $dataRkt->count();
            $countRealisasi = 0;
            $countDeviasi = 0;

            // Mapping data untuk detail tabel
            $detailEvaluasi = $dataRkt->map(function ($item) use (&$countRealisasi, &$countDeviasi) {
                $refIds = $item->list_id_ref ? explode(',', $item->list_id_ref) : [];

                // Pengecekan status berdasarkan konstrain tugas TPM
                $isRealisasi = in_array(28, $refIds);
                $isDeviasi = in_array(25, $refIds) || in_array(26, $refIds);

                if ($isRealisasi) $countRealisasi++;
                if ($isDeviasi) $countDeviasi++;

                return [
                    'id_program' => $item->ID_PROGRAM_KERJA,
                    'program_kerja' => $item->PROGRAM_KERJA,
                    'target_indikator' => $item->INDIKATOR,
                    'sasaran' => $item->SASARAN,
                    'pagu_anggaran' => (float) $item->TOTAL_PROGKER,
                    'realisasi_teks' => $item->realisasi_indikator ?? 'Belum ada input realisasi',
                    'evaluasi_teks' => $item->catatan_evaluasi ?? 'Belum ada input evaluasi',
                    'status' => [
                        'sudah_realisasi' => $isRealisasi,
                        'ada_deviasi' => $isDeviasi
                    ]
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Dashboard Tim Penjaminan Mutu berhasil dimuat',
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
                'message' => 'Gagal memproses Dashboard Tim Penjaminan Mutu',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString() // Opsional: untuk debug lebih dalam
            ], 500);
        }
    }
}