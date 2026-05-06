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

        // 1. IDENTIFIKASI AKTOR & ROLE 
        $nip = $request->nip ?? (Auth::check() ? Auth::user()->nip : null);
        $authRole = Auth::check() ? Auth::user()->role : null;

        $dbRole = DB::table('tr_jabatan as tj')
            ->join('ref_jabatan_str as rj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
            ->where('tj.NIP_KARYAWAN', $nip)
            ->whereNull('tj.TGL_SELESAI_JABATAN')
            ->value('rj.DESKRIPSI_JABATAN');

        $role = $dbRole ?? $authRole;
        $role = trim($role);

        // 2. VALIDASI ROLE (Wajib Bendahara atau Kepala Sekolah)
        // Ditambahkan validasi ketat agar tidak sembarang role bisa akses
        if (!in_array($role, ['Bendahara', 'Kepala Sekolah'])) {
            return response()->json([
                'message' => 'Role tidak diizinkan generate laporan pengeluaran'
            ], 403);
        }

        // 3. QUERY UTAMA (HIRARKI: RKT -> RKA -> Realisasi Pengeluaran)
        // Logika: mst_program_kerja (RKT) -> dtl_program_kerja (RKA) -> tr_pm (Realisasi)
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

        // 4. HANDLING EXCEL
        if ($type == 'excel') {
            return Excel::download(
                new LaporanPengeluaranExport($start, $end, null, $role, $nip), 
                'Laporan_Pengeluaran_RKT.xlsx'
            );
        }

        // 5. HANDLING PDF
        if (strtolower(trim($type)) === 'pdf') {
            $pdf = Pdf::loadView(
                'exports.LaporanPengeluaran_pdf', // Buat view blade baru khusus pengeluaran
                compact('data', 'total', 'start', 'end', 'role', 'nip') 
            );

            return $pdf->download('Laporan_Pengeluaran_RKT.pdf');
        }

        // 6. DEFAULT JSON (Untuk Dashboard)
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'total' => $total,
            'role_akses' => $role
        ]);
    }
}