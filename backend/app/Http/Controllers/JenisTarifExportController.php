<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JenisTarifExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JenisTarifExportController extends Controller
{
    public function export(Request $request)
    {
        return $this->processExport($request, 'xlsx');
    }

    public function exportPdf(Request $request)
    {
        return $this->processExport($request, 'pdf');
    }

    private function processExport(Request $request, string $type)
    {
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

        // Fallback: jika NIP belum ada, ambil dari jabatan aktif berdasarkan role.
        if (!$nip) {
            $nip = DB::table('tr_jabatan as tj')
                ->join('ref_jabatan_str as rj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
                ->whereNull('tj.TGL_SELESAI_JABATAN')
                ->where('rj.DESKRIPSI_JABATAN', $role)
                ->value('tj.NIP_KARYAWAN');
        }

        if (!in_array($role, ['Bendahara', 'Kepala Sekolah'])) {
            return response()->json([
                'message' => 'Role tidak diizinkan generate laporan'
            ], 403);
        }

        try {
            if ($type === 'pdf') {
                return Excel::download(
                    new JenisTarifExport($role, $nip, $nama),
                    'Data_Jenis_Tarif.pdf',
                    \Maatwebsite\Excel\Excel::DOMPDF
                );
            }

            return Excel::download(
                new JenisTarifExport($role, $nip, $nama),
                'Data_Jenis_Tarif.xlsx'
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Artisan::call('config:clear');

            return response()->json([
                'message' => 'Terjadi kesalahan pada layanan export. Silakan coba klik export sekali lagi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}