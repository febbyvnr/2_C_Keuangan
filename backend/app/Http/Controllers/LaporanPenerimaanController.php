<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LaporanPenerimaanExport;

class LaporanPenerimaanController extends Controller
{
    public function penerimaan(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $sumberDana = $request->sumber_dana;
        $type = $request->type;

        // EXCEL
        if ($type == 'excel') {
            return Excel::download(
                new LaporanPenerimaanExport($start, $end, $sumberDana),
                'Laporan_Penerimaan.xlsx'
            );
        }

        // CSV
        if ($type == 'csv') {
            return Excel::download(
                new LaporanPenerimaanExport($start, $end, $sumberDana),
                'Laporan_Penerimaan.csv'
            );
        }

        // QUERY UTAMA (dipakai PDF & JSON)
        $query = DB::table('TR_PENERIMAAN as p')
            ->join('REF_PENERIMAAN as rp', 'p.ID_REF_PENERIMAAN', '=', 'rp.ID_REF_PENERIMAAN')
            ->select(
                'p.TANGGAL_TR_PENERIMAAN as tanggal',
                'rp.DESKRIPSI_REF_PENERIMAAN as jenis',
                'p.DESKRIPSI_TR_PENERIMAAN as uraian',
                'p.JUMLAH_TR_PENERIMAAN as jumlah'
            )
            ->whereNotNull('p.NIP_PENERIMA');

        // FILTER PERIODE 
        if ($start && $end) {
            $query->whereBetween('p.TANGGAL_TR_PENERIMAAN', [$start, $end]);
        }

        // FILTER SUMBER DANA
        if ($sumberDana) {
            $query->where('p.ID_REF_DANA', $sumberDana);
        }

        $data = $query->get();
        $total = $data->sum('jumlah');

        // PDF
        if ($type == 'pdf') {

            $pdf = Pdf::loadView(
                'exports.LaporanPenerimaan_pdf',
                compact('data', 'total', 'start', 'end')
            );

            return $pdf->download('Laporan_Penerimaan.pdf');
        }

        return response()->json([
            'data' => $data,
            'total' => $total
        ]);
    }
}