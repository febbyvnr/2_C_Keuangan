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

        $nip = $request->nip ?? (Auth::check() ? Auth::user()->nip : null);
        $authRole = Auth::check() ? Auth::user()->role : null;

        // Mengambil Role dari database berdasarkan NIP_KARYAWAN
        $dbRole = DB::table('tr_jabatan as tj')
            ->join('ref_jabatan_str as rj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
            ->where('tj.NIP_KARYAWAN', $nip)
            ->whereNull('tj.TGL_SELESAI_JABATAN')
            ->value('rj.DESKRIPSI_JABATAN');

        $role = $dbRole ?? $authRole;
        $role = trim($role);

        // VALIDASI ROLE
        if ($type == 'excel' && !in_array($role, ['Bendahara', 'Kepala Sekolah'])) {
            return response()->json([
                'message' => 'Role tidak diizinkan generate laporan'
            ], 403);
        }

        // EXCEL
        if ($type == 'excel') {
            return Excel::download(
                new LaporanPengeluaranExport($start, $end, $sumberDana, $role, $nip),
                'Laporan_Pengeluaran.xlsx'
            );
        }

        // QUERY UTAMA
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

        // PDF
        if (strtolower(trim($type)) === 'pdf') {
            $pdf = Pdf::loadView(
                'exports.LaporanPengeluaran_pdf',
                compact('data', 'total', 'start', 'end', 'role', 'nip')
            );

            return $pdf->download('Laporan_Pengeluaran.pdf');
        }

        return response()->json([
            'data' => $data,
            'total' => $total
        ]);
    }
}