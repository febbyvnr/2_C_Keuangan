<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LaporanPengeluaranExport;

class LaporanPengeluaranController extends Controller
{
    public function pengeluaran(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $sumberDana = $request->sumber_dana;
        $type = $request->type;
        $role = $request->role ?? null; 

        if ($type == 'excel' && $role && $role !== 'Bendahara') {
            return response()->json([
                'message' => 'Hanya Bendahara yang boleh generate laporan'
            ], 403);
        }

        if ($type == 'excel') {
            return Excel::download(
                new LaporanPengeluaranExport($start, $end, $sumberDana, $role),
                'Laporan_Pengeluaran.xlsx'
            );
        }

        $query = DB::table('tr_pm as tp')
            ->join('dtl_fpd as df', 'tp.ID_TR_PM', '=', 'df.ID_TR_PM')
            ->join('fpd_anggaran as fa', 'df.ID_FPD_ANGGARAN', '=', 'fa.ID_FPD_ANGGARAN')
            ->join('dtl_program_kerja as dpk', 'fa.ID_ID_DT_PROGKER', '=', 'dpk.ID_ID_DT_PROGKER')
            ->join('mst_program_kerja as mpk', 'dpk.ID_MST_PROGRAM_KERJA', '=', 'mpk.ID_MST_PROGRAM_KERJA')
            ->join('ref_sumber_dana as rsd', 'dpk.ID_REF_SUMBER_DANA', '=', 'rsd.ID_REF_SUMBER_DANA')
            ->select(
                'tp.TANGGAL_TR_PM as tanggal',
                'mpk.NAMA_MST_PROGRAM_KERJA as program',
                'rsd.NAMA_REF_SUMBER_DANA as sumber_dana',
                'tp.DESKRIPSI_TR_PM as uraian',
                'df.JUMLAH_DTL_FPD as nominal'
            );

        if ($start && $end) {
            $query->whereBetween('tp.TGL_PM', [$start, $end]);
        }

        if ($sumberDana) {
            $query->where('dpk.ID_REF_SUMBER_DANA', $sumberDana);
        }

        $data = $query->get();
        $total = $data->sum('nominal');

        if ($type == 'pdf') {
            $pdf = Pdf::loadView(
                'exports.LaporanPengeluaran_pdf',
                compact('data', 'total', 'start', 'end')
            );

            return $pdf->download('Laporan_Pengeluaran.pdf');
        }

        return response()->json([
            'data' => $data,
            'total' => $total
        ]);
    }
}