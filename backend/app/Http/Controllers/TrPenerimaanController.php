<?php

namespace App\Http\Controllers;

use App\Models\TrPenerimaan;
use App\Exports\LaporanPenerimaanExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TrPenerimaanController extends Controller
{
    public function index()
    {
        $data = DB::table('tr_penerimaan as tp')
            ->leftJoin('ref_penerimaan as rp', 'tp.ID_REF_PENERIMAAN', '=', 'rp.ID_REF_PENERIMAAN')
            ->select(
                'tp.ID_TR_PENERIMAAN',
                'tp.ID_REF_PENERIMAAN',
                'tp.ID_REF_DANA',
                'tp.DESKRIPSI_TR_PENERIMAAN',
                'tp.TANGGAL_TR_PENERIMAAN',
                'tp.JUMLAH_TR_PENERIMAAN',
                'tp.NIP_PENERIMA',
                'rp.DESKRIPSI_REF_PENERIMAAN'
            )
            ->orderBy('tp.ID_TR_PENERIMAAN', 'asc')
            ->get();

        return response()->json([
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ID_REF_PENERIMAAN' => 'required',
            'ID_REF_DANA' => 'required',
            'DESKRIPSI_TR_PENERIMAAN' => 'required',
            'TANGGAL_TR_PENERIMAAN' => 'required|date',
            'JUMLAH_TR_PENERIMAAN' => 'required|numeric',
            'NIP_PENERIMA' => 'required',
        ]);

        $data = TrPenerimaan::create($validated);

        return response()->json([
            'message' => 'Data penerimaan berhasil disimpan',
            'data' => $data
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'ID_REF_PENERIMAAN' => 'required',
            'ID_REF_DANA' => 'required',
            'DESKRIPSI_TR_PENERIMAAN' => 'required',
            'TANGGAL_TR_PENERIMAAN' => 'required|date',
            'JUMLAH_TR_PENERIMAAN' => 'required|numeric',
            'NIP_PENERIMA' => 'required',
        ]);

        $data = TrPenerimaan::findOrFail($id);
        $data->update($validated);

        return response()->json([
            'message' => 'Data penerimaan berhasil diupdate',
            'data' => $data
        ]);
    }

    public function destroy($id)
    {
        $data = TrPenerimaan::findOrFail($id);
        $data->delete();

        return response()->json([
            'message' => 'Data penerimaan berhasil dihapus'
        ]);
    }

    public function export(Request $request)
    {
        $type = $request->query('type');
        $tanggalAwal = $request->query('tanggal_awal');
        $tanggalAkhir = $request->query('tanggal_akhir');

        $query = DB::table('tr_penerimaan as tp')
            ->leftJoin('ref_penerimaan as rp', 'tp.ID_REF_PENERIMAAN', '=', 'rp.ID_REF_PENERIMAAN')
            ->select(
                'tp.ID_TR_PENERIMAAN',
                'tp.TANGGAL_TR_PENERIMAAN',
                'tp.DESKRIPSI_TR_PENERIMAAN',
                'tp.JUMLAH_TR_PENERIMAAN',
                'tp.ID_REF_DANA',
                'tp.NIP_PENERIMA',
                'rp.DESKRIPSI_REF_PENERIMAAN'
            )
            ->orderBy('tp.TANGGAL_TR_PENERIMAAN', 'asc')
            ->orderBy('tp.ID_TR_PENERIMAAN', 'asc');

        if ($tanggalAwal) {
            $query->whereDate('tp.TANGGAL_TR_PENERIMAAN', '>=', $tanggalAwal);
        }

        if ($tanggalAkhir) {
            $query->whereDate('tp.TANGGAL_TR_PENERIMAAN', '<=', $tanggalAkhir);
        }

        $rawData = $query->get();

        $saldoBerjalan = 0;
        $data = $rawData->map(function ($item, $index) use (&$saldoBerjalan) {
            $debit = (float) $item->JUMLAH_TR_PENERIMAAN;
            $kredit = 0;
            $saldoBerjalan += $debit - $kredit;

            return (object) [
                'no' => $index + 1,
                'tanggal' => $item->TANGGAL_TR_PENERIMAAN,
                'uraian' => $item->DESKRIPSI_TR_PENERIMAAN ?: ($item->DESKRIPSI_REF_PENERIMAAN ?? '-'),
                'debit' => $debit,
                'kredit' => $kredit,
                'saldo' => $saldoBerjalan,
            ];
        });

        $saldoAkhir = $data->last()->saldo ?? 0;
        $periode = ($tanggalAwal || $tanggalAkhir)
            ? (($tanggalAwal ?: '-') . ' s/d ' . ($tanggalAkhir ?: '-'))
            : '- s/d -';

        $tanggalCetak = now()->format('d F Y');

        if ($type === 'excel') {
            return Excel::download(
                new LaporanPenerimaanExport($data, $periode, $saldoAkhir, $tanggalCetak),
                'laporan_penerimaan.xlsx'
            );
        }

        if ($type === 'pdf') {
            $pdf = Pdf::loadView('exports.LaporanPenerimaan_pdf', [
                'data' => $data,
                'saldoAkhir' => $saldoAkhir,
                'periode' => $periode,
                'tanggalCetak' => $tanggalCetak,
            ])->setPaper('a4', 'portrait');

            return $pdf->download('laporan_penerimaan.pdf');
        }

        return response()->json([
            'message' => 'Data laporan penerimaan',
            'data' => $data,
            'saldo_akhir' => $saldoAkhir,
            'periode' => $periode,
        ]);
    }
}