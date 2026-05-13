<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\RefJenisPembayaranExport;
use App\Models\RefJenisPembayaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RefJenisPembayaranExportController extends Controller
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

        $nama = $request->nama
            ?? (Auth::check() ? Auth::user()->name : null);

        $authRole = Auth::check()
            ? Auth::user()->role
            : null;

        $dbRole = null;

        if ($nip) {
            $dbRole = DB::table('tr_jabatan as tj')
                ->join('ref_jabatan_str as rj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
                ->where('tj.NIP_KARYAWAN', $nip)
                ->whereNull('tj.TGL_SELESAI_JABATAN')
                ->value('rj.DESKRIPSI_JABATAN');
        }

        $role = trim(
            $dbRole
            ?? $request->role
            ?? $authRole
            ?? 'Bendahara'
        );

        // Fallback: jika NIP belum ada
        if (!$nip || !$nama) {

            $bendahara = DB::table('ref_jabatan_str as rj')
                ->join('tr_jabatan as tj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
                ->join('mst_karyawan as mk', 'mk.NIP_KARYAWAN', '=', 'tj.NIP_KARYAWAN')
                ->whereRaw('LOWER(rj.DESKRIPSI_JABATAN) = ?', ['bendahara'])
                ->whereNull('tj.TGL_SELESAI_JABATAN')
                ->where('mk.IS_DELETE', 0)
                ->select(
                    'mk.NIP_KARYAWAN',
                    'mk.NAMA_KARYAWAN',
                    'mk.NAMA_LENGKAP_GELAR'
                )
                ->first();

            if ($bendahara) {
                $nip = $bendahara->NIP_KARYAWAN;
                $nama = $bendahara->NAMA_LENGKAP_GELAR
                    ?: $bendahara->NAMA_KARYAWAN;

                $role = 'Bendahara';
            }
        }

        // Validasi role
        if (!in_array($role, ['Bendahara', 'Kepala Sekolah'])) {
            return response()->json([
                'message' => 'Role tidak diizinkan generate laporan'
            ], 403);
        }

        try {

            $fileName = 'Laporan_MetodePembayaran_' . date('Y-m-d');

            $data = RefJenisPembayaran::select(
                'DESKRIPSI_METODE_PEMBAYARAN'
            )
            ->orderBy('DESKRIPSI_METODE_PEMBAYARAN')
            ->get();

            // PDF
            if ($type === 'pdf') {

                return Pdf::loadView(
                    'exports.metode_pembayaran_pdf',
                    [
                        'data' => $data,
                        'role' => $role,
                        'nama' => $nama,
                        'nip' => $nip,
                    ]
                )->download($fileName . '.pdf');
            }

            // EXCEL
            return Excel::download(
                new RefJenisPembayaranExport(
                    $role,
                    $nip,
                    $nama
                ),
                $fileName . '.xlsx'
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