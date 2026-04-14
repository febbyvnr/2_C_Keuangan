<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LaporanBukuKhasUmumExport;

class LaporanBukuKhasUmumController extends Controller
{
    public function bku(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $type = $request->type;
        $role = $request->role ?? 'Bendahara';

        // VALIDASI BENDAHARA
        if ($type == 'excel' && $role && $role !== 'Bendahara') {
            return response()->json([
                'message' => 'Hanya Bendahara yang boleh generate laporan'
            ], 403);
        }

        // =========================
        // 1. PENERIMAAN SISWA
        // =========================
        $pembayaran = DB::table('TR_PEMBAYARAN as p')
            ->join('MST_SISWA as s', 'p.ID_SISWA_TETAP', '=', 's.ID_SISWA_TETAP')
            ->join('REF_JENIS_PEMBAYARAN as jp', 'p.ID_JENIS_PEMBAYARAN', '=', 'jp.ID_JENIS_PEMBAYARAN')
            ->select(
                'p.TGL_BAYAR as tanggal',
                DB::raw("CONCAT('Pembayaran - ', s.NAMA_SISWA_TETAP) as uraian"),
                'p.JUMLAH_BAYAR as debit',
                DB::raw('0 as kredit'),
                'jp.DESKRIPSI_JENIS_PEMBAYARAN as metode'
            );

        // =========================
        // 2. PENERIMAAN LAIN
        // =========================
        $penerimaan = DB::table('TR_PENERIMAAN as p')
            ->join('REF_PENERIMAAN as rp', 'p.ID_REF_PENERIMAAN', '=', 'rp.ID_REF_PENERIMAAN')
            ->select(
                'p.TANGGAL_TR_PENERIMAAN as tanggal',
                'p.DESKRIPSI_TR_PENERIMAAN as uraian',
                'p.JUMLAH_TR_PENERIMAAN as debit',
                DB::raw('0 as kredit'),
                DB::raw("'Transfer' as metode") // asumsi BANK
            );

        // =========================
        // 3. PENGELUARAN
        // =========================
        $pengeluaran = DB::table('DTL_FPD as d')
            ->join('FPD_ANGGARAN as f', 'd.ID_FPD', '=', 'f.ID_FPD')
            ->join('MST_PROGRAM_KERJA as pk', 'f.ID_PROGRAM_KERJA', '=', 'pk.ID_PROGRAM_KERJA')
            ->select(
                'f.TGL_FPD as tanggal',
                DB::raw("CONCAT('Pengeluaran - ', pk.PROGRAM_KERJA) as uraian"),
                DB::raw('0 as debit'),
                'd.TOTAL as kredit',
                DB::raw("'Transfer' as metode") // asumsi BANK
            );

        // =========================
        // FILTER TANGGAL
        // =========================
        if ($start && $end) {
            $pembayaran->whereBetween('p.TGL_BAYAR', [$start, $end]);
            $penerimaan->whereBetween('p.TANGGAL_TR_PENERIMAAN', [$start, $end]);
            $pengeluaran->whereBetween('f.TGL_FPD', [$start, $end]);
        }

        // =========================
        // UNION SEMUA DATA
        // =========================
         $data = DB::query()
            ->fromSub(
                $pembayaran
                    ->unionAll($penerimaan)
                    ->unionAll($pengeluaran),
                'trx'
            )
            ->orderBy('tanggal', 'asc')
            ->get();
            
        // =========================
        // HITUNG SALDO
        // =========================
        $saldo = 0;
        foreach ($data as $item) {
            $saldo += $item->debit - $item->kredit;
            $item->saldo = $saldo;
        }

        // =========================
        // SPLIT P1 & P2
        // =========================
        $p1 = collect($data)->filter(function ($item) {
            return $item->metode === 'Tunai';
        })->values();

        $p2 = collect($data)->filter(function ($item) {
            return $item->metode !== 'Tunai';
        })->values();

        // =========================
        // EXCEL
        // =========================
        if ($type == 'excel') {
            return Excel::download(
                new LaporanBukuKhasUmumExport($data, $p1, $p2, $role),
                'Laporan_BKU.xlsx'
            );
        }

        // =========================
        // PDF
        // =========================
        if ($type == 'pdf') {
            $pdf = Pdf::loadView(
                'exports.LaporanBukuKhasUmum',
                [
                    'bku' => $data,
                    'p1' => $p1,
                    'p2' => $p2,
                    'start' => $start,
                    'end' => $end,
                    'role' => $role
                ]
            );

            return $pdf->download('Laporan_BKU.pdf');
        }

        return response()->json([
            'bku' => $data,
            'p1' => $p1,
            'p2' => $p2
        ]);
    }
}