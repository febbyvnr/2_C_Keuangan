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
        $sumberDana = $request->sumber_dana;
        $type = $request->type;

        $nip = $request->nip
            ?? (Auth::check() ? Auth::user()->nip : null)
            ?? DB::table('mst_karyawan')->value('NIP_KARYAWAN'); 

        $authRole = Auth::check() ? Auth::user()->role : null;

        $dbRole = DB::table('tr_jabatan as tj')
            ->join('ref_jabatan_str as rj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
            ->where('tj.NIP_KARYAWAN', $nip)
            ->whereNull('tj.TGL_SELESAI_JABATAN')
            ->value('rj.DESKRIPSI_JABATAN');

        $role = $dbRole ?? $authRole ?? 'bendahara'; 
        $role = strtolower(trim($role));

        if (!in_array($role, ['bendahara', 'kepala sekolah'])) {
            return response()->json([
                'message' => 'Role tidak diizinkan mengakses laporan'
            ], 403);
        }

        $ttd = DB::table('tr_jabatan as tj')
            ->join('ref_jabatan_str as rj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
            ->join('mst_karyawan as mk', 'tj.NIP_KARYAWAN', '=', 'mk.NIP_KARYAWAN')
            ->select(
                'rj.DESKRIPSI_JABATAN as role_ttd',
                'mk.NAMA_LENGKAP_GELAR as nama',
                'mk.NIP_KARYAWAN as nip'
            )
            ->whereNull('tj.TGL_SELESAI_JABATAN')
            ->whereIn('rj.DESKRIPSI_JABATAN', ['Bendahara', 'Kepala Sekolah'])
            ->get();

        $penandatangan = $ttd->first(function ($item) use ($role) {
            return strtolower(trim($item->role_ttd)) === strtolower(trim($role));
        });

        $nama = $penandatangan->nama ?? '-';
        $nip_ttd = $penandatangan->nip ?? '-';

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

        if ($start && $end) $query->whereBetween('tp.TGL_PM', [$start, $end]);
        if ($sumberDana) $query->where('dpk.ID_REF_DANA', $sumberDana);

        $data = $query->orderBy('tp.TGL_PM', 'asc')->get();
        $total = $data->sum('nominal');

        if ($type === 'excel') {
            return Excel::download(
                new LaporanPengeluaranExport($start, $end, $sumberDana, $role, $nip, $nama, $nip_ttd), 
                'Laporan_Pengeluaran.xlsx'
            );
        } 
        
        if ($type === 'pdf') {
            $pdf = Pdf::loadView('exports.LaporanPengeluaran_pdf', compact(
                'data', 'total', 'start', 'end', 'role', 'nama', 'nip_ttd'
            ));
            return $pdf->download('Laporan_Pengeluaran.pdf');
        }

        return response()->json([
            'data' => $data,
            'total' => $total
        ]);
    }
}