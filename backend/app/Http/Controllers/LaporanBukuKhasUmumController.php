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
    public function bku(Request $request)
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
        // PENERIMAAN (DEBIT)
        // =========================
        $penerimaan = DB::table('TR_PENERIMAAN as p')
            ->select(
                'p.TANGGAL_TR_PENERIMAAN as tanggal',
                DB::raw("p.DESKRIPSI_TR_PENERIMAAN as uraian"),
                DB::raw("p.JUMLAH_TR_PENERIMAAN as debit"),
                DB::raw("0 as kredit"),
                DB::raw("'Tunai' as metode")
            );

        // =========================
        // PEMBAYARAN 
        // =========================
     $pembayaran = DB::table('TR_PEMBAYARAN as p')
    ->join('REF_METODE_PEMBAYARAN as m', 'p.ID_JENIS_PEMBAYARAN', '=', 'm.ID_METODE_PEMBAYARAN')
    ->join('mst_siswa as ms', 'p.ID_SISWA_TETAP', '=', 'ms.ID_SISWA_TETAP')
    ->select(
        'p.TGL_BAYAR as tanggal',
        DB::raw("CONCAT('Pembayaran - ', ms.NAMA_SISWA_TETAP) as uraian"),
        DB::raw("0 as debit"), 
        DB::raw("p.JUMLAH_BAYAR as kredit"), 
        'm.DESKRIPSI_METODE_PEMBAYARAN as metode'
    );
        // =========================
        // FILTER
        // =========================
        if ($start && $end) {
            $penerimaan->whereBetween('p.TANGGAL_TR_PENERIMAAN', [$start, $end]);
            $pembayaran->whereBetween('p.TGL_BAYAR', [$start, $end]);
        }

        // =========================
        // GABUNG 
        // =========================
        $data = collect()
            ->merge($penerimaan->get())
            ->merge($pembayaran->get())
            ->sortBy('tanggal')
            ->values();

        // =========================
        // HITUNG SALDO (REALTIME)
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
        // PEMISAHAN
        // =========================
        $p1 = collect($bku)
            ->filter(fn($x) => strtolower($x['metode']) == 'tunai')
            ->values();

        $p2 = collect($bku)
            ->filter(fn($x) => strtolower($x['metode']) != 'tunai')
            ->values();

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
        // EXCEL
        // =========================
        if ($type == 'excel') {
            return Excel::download(
                new LaporanBukuKhasUmumExport($bku, $p1, $p2, $role, $nip, $nama, $nip_ttd),
                'Laporan_BKU.xlsx'
            );
        }

        // =========================
        // PDF
        // =========================
        if ($type == 'pdf') {
            $pdf = Pdf::loadView(
                'exports.LaporanBukuKhasUmum',
                compact('bku', 'role', 'nama', 'nip_ttd')
            );

            return $pdf->download('Laporan_BKU.pdf');
        }

        // =========================
        // JSON
        // =========================
        return response()->json([
            'bku' => $bku,
            'tunai' => $p1,
            'bank' => $p2
        ]);
    }
}