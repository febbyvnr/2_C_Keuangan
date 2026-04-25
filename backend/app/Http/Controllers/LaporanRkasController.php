<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RkasExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanRkasController extends Controller
{
    /**
     * Export RKAS Report to Excel
     */
    public function export(Request $request)
    {
        return $this->processExport($request, 'xlsx');
    }

    /**
     * Export RKAS Report to PDF
     */
    public function exportPdf(Request $request)
    {
        return $this->processExport($request, 'pdf');
    }

    /**
     * Helper untuk memproses export (Excel/PDF)
     */
    private function processExport(Request $request, $type)
    {
        try {
            $nip = $request->nip
                ?? $request->NIP_KARYAWAN
                ?? (Auth::check() ? Auth::user()->nip : null);
            $nama = $request->nama ?? (Auth::check() ? Auth::user()->name : null);
            $authRole = Auth::check() ? Auth::user()->role : null;

            $dbRole = null;
            if ($nip) {
                $dbRole = DB::table('tr_jabatan as tj')
                    ->join('ref_jabatan_str as rj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
                    ->where('tj.NIP_KARYAWAN', $nip)
                    ->whereNull('tj.TGL_SELESAI_JABATAN')
                    ->value('rj.DESKRIPSI_JABATAN');
            }

            $role = trim($dbRole ?? $request->role ?? $authRole ?? 'Bendahara');

            if (!$nip) {
                $nip = DB::table('tr_jabatan as tj')
                    ->join('ref_jabatan_str as rj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
                    ->whereNull('tj.TGL_SELESAI_JABATAN')
                    ->where('rj.DESKRIPSI_JABATAN', $role)
                    ->value('tj.NIP_KARYAWAN');
            }

            // VALIDASI ROLE
            if (!in_array($role, ['Bendahara', 'Kepala Sekolah'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role tidak diizinkan generate laporan RKAS'
                ], 403);
            }

            // Ambil filters
            $filters = $request->only(['ID_TA_ANGGARAN', 'ID_REF_DANA']);

            // Validasi filter
            if (empty($filters['ID_TA_ANGGARAN']) && empty($filters['ID_REF_DANA'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimal harus memilih salah satu filter (Tahun Anggaran atau Sumber Dana)'
                ], 400);
            }

            $fileName = 'Laporan_RKAS_' . date('Y-m-d');

            if ($type === 'pdf') {
                // Proses Download PDF
                return Excel::download(
                    new RkasExport($filters, $role, $nip, $nama),
                    $fileName . '.pdf',
                    \Maatwebsite\Excel\Excel::DOMPDF
                );
            }

            // Proses Download Excel
            return Excel::download(
                new RkasExport($filters, $role, $nip, $nama),
                $fileName . '.xlsx'
            );

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Artisan::call('config:clear');

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada layanan export. Silakan coba klik sekali lagi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}