<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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

        if ($type == 'excel' && $role && !in_array($role, ['Bendahara', 'Kepala Sekolah'])) {
            return response()->json([
                'message' => 'Hanya Bendahara atau Kepala Sekolah yang boleh generate laporan'
            ], 403);
        }

        if ($type == 'excel') {
            return Excel::download(
                new LaporanPengeluaranExport($start, $end, $sumberDana, $role),
                'Laporan_Pengeluaran.xlsx'
            );
        }

        $query = DB::table('tr_pm as tp')
            ->join('fpd_anggaran as fa', 'tp.ID_PROGRAM_KERJA', '=', 'fa.ID_PROGRAM_KERJA')
            ->join('dtl_fpd as df', 'fa.ID_FPD', '=', 'df.ID_FPD')
            ->join('dtl_program_kerja as dpk', 'fa.ID_PROGRAM_KERJA', '=', 'dpk.ID_PROGRAM_KERJA')
            ->join('mst_program_kerja as mpk', 'dpk.ID_PROGRAM_KERJA', '=', 'mpk.ID_PROGRAM_KERJA')
            ->join('ref_sumber_dana as rsd', 'dpk.ID_REF_DANA', '=', 'rsd.ID_REF_DANA')
            ->select(
                'tp.TGL_PM as tanggal',
                'mpk.PROGRAM_KERJA as program',
                'rsd.DESKRIPSI_SUMBER_DANA as sumber_dana',
                'tp.DESKRIPSI_TR_PM as uraian',
                DB::raw('(df.QTY * df.HARGA_SATUAN) as nominal')
            );

        if ($start && $end) {
            $query->whereBetween('tp.TGL_PM', [$start, $end]);
        }

        if ($sumberDana) {
            $query->where('dpk.ID_REF_DANA', $sumberDana);
        }

        $data = $query->orderBy('tp.TGL_PM', 'asc')->get();
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