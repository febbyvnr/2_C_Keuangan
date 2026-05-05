<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LaporanBukuKhasUmumExport;
use Illuminate\Support\Facades\Auth;

class LaporanBukuKhasUmumController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $type = $request->type;

        $nip = $request->nip ?? (Auth::check() ? Auth::user()->nip : null);

        $authRole = Auth::check() ? Auth::user()->role : null;

        $dbRole = DB::table('tr_jabatan as tj')
            ->join('ref_jabatan_str as rj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
            ->where('tj.NIP_KARYAWAN', $nip)
            ->whereNull('tj.TGL_SELESAI_JABATAN')
            ->value('rj.DESKRIPSI_JABATAN');

        $role = $dbRole ?? $authRole;
        $role = strtolower(trim($role));

        if (!in_array($role, ['bendahara', 'kepala sekolah'])) {
            return response()->json([
                'message' => 'Role tidak diizinkan mengakses laporan'
            ], 403);
        }

        // =========================
        // DATA PENERIMAAN
        // =========================
        $penerimaan = DB::table('TR_PENERIMAAN')
            ->select(
                'TANGGAL_TR_PENERIMAAN as tanggal',
                DB::raw("DESKRIPSI_TR_PENERIMAAN as uraian"),
                DB::raw("JUMLAH_TR_PENERIMAAN as debit"),
                DB::raw("0 as kredit"),
                DB::raw("'Bank' as metode")
            );

        // =========================
        // DATA PEMBAYARAN (JOIN METODE)
        // =========================
        $pembayaran = DB::table('TR_PEMBAYARAN as p')
            ->join('REF_METODE_PEMBAYARAN as m', 'p.ID_JENIS_PEMBAYARAN', '=', 'm.ID_METODE_PEMBAYARAN')
            ->join('mst_karyawan as mk', 'p.NIP_VALIDATOR_PEMBAYARAN', '=', 'mk.NIP_KARYAWAN')
            ->select(
                'p.TGL_BAYAR as tanggal',
                DB::raw("CONCAT('Pembayaran - ', mk.NAMA_KARYAWAN) as uraian"),
                DB::raw("p.JUMLAH_BAYAR as debit"),
                DB::raw("0 as kredit"),
                'm.DESKRIPSI_METODE_PEMBAYARAN as metode'
            );

        // =========================
        // FILTER
        // =========================
        if ($start && $end) {
            $penerimaan->whereBetween('TANGGAL_TR_PENERIMAAN', [$start, $end]);
            $pembayaran->whereBetween('p.TGL_BAYAR', [$start, $end]);
        }

        // =========================
        // GABUNG DATA
        // =========================
        $data = $penerimaan->unionAll($pembayaran)
            ->orderBy('tanggal')
            ->get();

        // =========================
        // HITUNG SALDO
        // =========================
        $saldo = 0;
        $bku = [];

        foreach ($data as $row) {
            $saldo += ($row->debit - $row->kredit);

            $bku[] = [
                'tanggal' => $row->tanggal,
                'uraian' => $row->uraian,
                'debit' => $row->debit,
                'kredit' => $row->kredit,
                'metode' => $row->metode,
                'saldo' => $saldo
            ];
        }

        // =========================
        // PEMISAHAN TUNAI & BANK
        // =========================
        $p1 = collect($bku)->filter(fn($x) => strtolower($x['metode']) == 'tunai')->values();
        $p2 = collect($bku)->filter(fn($x) => strtolower($x['metode']) != 'tunai')->values();

        // =========================
        // TTD 
        // =========================
        $ttd = DB::table('tr_jabatan as tj')
            ->join('ref_jabatan_str as rj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
            ->join('mst_karyawan as mk', 'tj.NIP_KARYAWAN', '=', 'mk.NIP_KARYAWAN')
            ->select(
                'rj.DESKRIPSI_JABATAN as role',
                'mk.NAMA_LENGKAP_GELAR as nama',
                'mk.NIP_KARYAWAN as nip'
            )
            ->whereNull('tj.TGL_SELESAI_JABATAN')
            ->get();

        $penandatangan = $ttd->first(function ($item) use ($role) {
            return strtolower(trim($item->role)) === strtolower(trim($role));
        });

        $nama = $penandatangan->nama ?? '-';
        $nip_ttd = $penandatangan->nip ?? '-';

        // =========================
        // EXPORT EXCEL
        // =========================
        if ($type == 'excel') {
            return Excel::download(
                new LaporanBukuKhasUmumExport($bku, $p1, $p2, $role, $nip, $nama, $nip_ttd),
                'Laporan_BKU.xlsx'
            );
        }

        // =========================
        // EXPORT PDF
        // =========================
        if ($type == 'pdf') {
            $pdf = Pdf::loadView(
                'exports.LaporanBukuKhasUmum',
                compact('bku', 'role', 'nama', 'nip_ttd')
            );

            return $pdf->download('Laporan_BKU.pdf');
        }

        // =========================
        // RESPONSE JSON
        // =========================
        return response()->json([
            'bku' => $bku,
            'tunai' => $p1,
            'bank' => $p2
        ]);
    }
}