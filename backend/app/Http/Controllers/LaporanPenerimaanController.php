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
                'laporan_bkm.xlsx'
            );
        }

        // CSV
        if ($type == 'csv') {
            return Excel::download(
                new LaporanPenerimaanExport($start, $end, $sumberDana),
                'laporan_bkm.csv'
            );
        }

        // PDF
        if ($type == 'pdf') {

            $data = DB::table('TR_PENERIMAAN as p')
                ->join('REF_PENERIMAAN as rp', 'p.ID_REF_PENERIMAAN', '=', 'rp.ID_REF_PENERIMAAN')
                ->select(
                    'p.TANGGAL_TR_PENERIMAAN as tanggal',
                    'rp.DESKRIPSI_REF_PENERIMAAN as jenis',
                    'p.DESKRIPSI_TR_PENERIMAAN as uraian',
                    'p.JUMLAH_TR_PENERIMAAN as jumlah'
                )
                ->whereNotNull('p.NIP_PENERIMA')
                ->whereBetween('p.TANGGAL_TR_PENERIMAAN', [$start, $end])
                ->when($sumberDana, function ($query) use ($sumberDana) {
                    $query->where('p.ID_REF_DANA', $sumberDana);
                })
                ->get();

            $total = $data->sum('jumlah');

            $pdf = Pdf::loadView('laporan.penerimaan_pdf', compact('data', 'total', 'start', 'end'));

            return $pdf->download('laporan_bkm.pdf');
        }
    }
}