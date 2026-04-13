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
        $role = $request->role ?? null; 

        // EXCEL
        if ($type == 'excel') {
            return Excel::download(
                new LaporanPenerimaanExport($start, $end, $sumberDana, $role),
                'Laporan_Penerimaan.xlsx'
            );
        }

        // CSV
        if ($type == 'csv') {
            $queryCsv = DB::table('TR_PENERIMAAN as p')
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
                    $queryCsv->whereBetween('p.TANGGAL_TR_PENERIMAAN', [$start, $end]);
                }

                // FILTER SUMBER DANA
                if ($sumberDana) {
                    $queryCsv->where('p.ID_REF_DANA', $sumberDana);
        }

                $dataCsv = $queryCsv->get();

                $csv = "Tanggal,Jenis,Uraian,Jumlah\n";

             foreach ($dataCsv as $row) {
            $csv .= "{$row->tanggal},\"{$row->jenis}\",\"{$row->uraian}\",{$row->jumlah}\n";
        }

        //  RETURN CSV
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename=Laporan_Penerimaan.csv');
        }

        // QUERY UTAMA (PDF & JSON)
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