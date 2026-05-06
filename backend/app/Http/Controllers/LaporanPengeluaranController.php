<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LaporanPengeluaranExport; 
use Illuminate\Support\Facades\Auth; 

class LaporanPengeluaranController extends Controller
{
    public function pengeluaran(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $type = $request->type;

        // 1. IDENTIFIKASI AKTOR (Untuk Izin Akses)
        $nip_user = $request->nip ?? (Auth::check() ? Auth::user()->nip : null);
        $authRole = Auth::check() ? Auth::user()->role : null;

        $dbRole = DB::table('tr_jabatan as tj')
            ->join('ref_jabatan_str as rj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
            ->where('tj.NIP_KARYAWAN', $nip_user)
            ->whereNull('tj.TGL_SELESAI_JABATAN')
            ->value('rj.DESKRIPSI_JABATAN');

        $role = $dbRole ?? $authRole;
        $role = strtolower(trim($role)); 

        if (!in_array($role, ['bendahara', 'kepala sekolah'])) {
            return response()->json([
                'message' => 'Role tidak diizinkan generate laporan pengeluaran'
            ], 403);
        }

        $ttd = DB::table('tr_jabatan as tj')
            ->join('ref_jabatan_str as rj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
            ->join('mst_karyawan as mk', 'tj.NIP_KARYAWAN', '=', 'mk.NIP_KARYAWAN')
            ->select(
                'rj.DESKRIPSI_JABATAN as jabatan',
                'mk.NAMA_LENGKAP_GELAR as nama',
                'mk.NIP_KARYAWAN as nip'
            )
            ->whereNull('tj.TGL_SELESAI_JABATAN')
            ->where('rj.DESKRIPSI_JABATAN', 'LIKE', '%Bendahara%') 
            ->first();

        $nama_ttd = $ttd->nama ?? '-';
        $nip_ttd = $ttd->nip ?? '-';

        $query = DB::table('TR_PM as p')
            ->join('MST_PROGRAM_KERJA as mst', 'p.ID_PROGRAM_KERJA', '=', 'mst.ID_PROGRAM_KERJA')
            ->join('DTL_PROGRAM_KERJA as dtl', 'p.ID_PROGRAM_KERJA', '=', 'dtl.ID_PROGRAM_KERJA')
            ->select(
                'p.TGL_PM as tanggal',
                'mst.PROGRAM_KERJA as program',
                'mst.INDIKATOR as indikator',
                'p.DESKRIPSI_TR_PM as uraian',
                'dtl.NOMINAL as nominal',
                'dtl.VOLUME',
                'dtl.SATUAN',
                'p.NIP_VALIDATOR_PM as validator'
            )
            ->where('mst.IS_DELETE', 0); 

        if ($start && $end) {
            $query->whereBetween('p.TGL_PM', [$start, $end]);
        }

        $data = $query->get();
        $total = $data->sum('nominal');

        // 5. HANDLING EXCEL
        if ($type == 'excel') {
            return Excel::download(
                new LaporanPengeluaranExport($start, $end, null, $role, $nip_user, $nama_ttd, $nip_ttd), 
                'Laporan_Pengeluaran_RKT.xlsx'
            );
        }

        // 6. HANDLING PDF
        if (strtolower(trim($type)) === 'pdf') {
            $pdf = Pdf::loadView(
                'exports.LaporanPengeluaran_pdf', 
                compact('data', 'total', 'start', 'end', 'role', 'nama_ttd', 'nip_ttd') 
            );

            return $pdf->download('Laporan_Pengeluaran_RKT.pdf');
        }

        // 7. DEFAULT JSON (Untuk Dashboard)
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'total' => $total,
            'role_akses' => $role,
            'info_ttd' => [
                'nama' => $nama_ttd,
                'nip' => $nip_ttd
            ]
        ]);
    }
}