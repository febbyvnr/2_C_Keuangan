<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LaporanPenerimaanExport;
use Illuminate\Support\Facades\Auth;

class LaporanPenerimaanController extends Controller
{
    public function penerimaan(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $sumberDana = $request->sumber_dana;
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
        // 🔥 FIX: AMBIL DATA TTD DULU (WAJIB SEBELUM EXCEL)
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
            ->whereIn('rj.DESKRIPSI_JABATAN', ['Bendahara', 'Kepala Sekolah'])
            ->get();

        $penandatangan = $ttd->first(function ($item) use ($role) {
            return strtolower(trim($item->role)) === strtolower(trim($role));
        });

        // fallback aman
        $nama = $penandatangan->nama ?? '-';
        $nip_ttd = $penandatangan->nip ?? '-';

        // =========================
        // EXPORT EXCEL
        // =========================
        if ($type == 'excel') {
            return Excel::download(
                new LaporanPenerimaanExport($start, $end, $sumberDana, $role, $nip, $nama, $nip_ttd),
                'Laporan_Penerimaan.xlsx'
            );
        }

        // =========================
        // QUERY DATA
        // =========================
        $query = DB::table('TR_PENERIMAAN as p')
            ->join('REF_PENERIMAAN as rp', 'p.ID_REF_PENERIMAAN', '=', 'rp.ID_REF_PENERIMAAN')
            ->join('REF_SUMBER_DANA as rd', 'p.ID_REF_DANA', '=', 'rd.ID_REF_DANA')
            ->select(
                'p.TANGGAL_TR_PENERIMAAN as tanggal',
                'rp.DESKRIPSI_REF_PENERIMAAN as jenis',
                'rd.DESKRIPSI_SUMBER_DANA as sumber_dana',
                'p.DESKRIPSI_TR_PENERIMAAN as uraian',
                'p.JUMLAH_TR_PENERIMAAN as jumlah'
            )
            ->whereNotNull('p.NIP_PENERIMA');

        if ($start && $end) {
            $query->whereBetween('p.TANGGAL_TR_PENERIMAAN', [$start, $end]);
        }

        if ($sumberDana) {
            $query->where('p.ID_REF_DANA', $sumberDana);
        }

        $data = $query->get();
        $total = $data->sum('jumlah');

        // =========================
        // EXPORT PDF
        // =========================
        if (strtolower(trim($type)) === 'pdf') {

            $pdf = Pdf::loadView(
                'exports.LaporanPenerimaan_pdf',
                compact('data', 'total', 'start', 'end', 'role', 'nama', 'nip_ttd')
            );

            return $pdf->download('Laporan_Penerimaan.pdf');
        }

        // =========================
        // RESPONSE JSON
        // =========================
        return response()->json([
            'data' => $data,
            'total' => $total
        ]);
    }
}