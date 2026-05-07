<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
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
     * Export RKAS Report to Excel (alias)
     */
    public function exportExcel(Request $request)
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
     * Get RKAS report data and support export types
     */
    public function rkas(Request $request)
    {
        $filters = $request->only(['ID_TA_ANGGARAN', 'ID_REF_DANA']);
        $metadata = $this->buildRkasMetadata($request, $filters);

        $export = new RkasExport($filters, $metadata['role'], $metadata['nip'], $metadata['nama'], $metadata['filterText']);
        $data = collect($export->getData());
        $total = $data->sum('anggaran_disetujui');

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'total' => $total,
            'filterText' => $metadata['filterText'],
            'info_ttd' => [
                'nama' => $metadata['nama'],
                'nip' => $metadata['nip'],
                'role' => $metadata['role'],
            ],
        ]);
    }

    private function buildRkasMetadata(Request $request, array $filters = []): array
    {
        $role = 'Bendahara';
        $nama = '-';
        $nip = '-';

        $jabatanId = DB::table('ref_jabatan_str')
            ->whereRaw('LOWER(DESKRIPSI_JABATAN) = ?', ['bendahara'])
            ->value('ID_JABATAN');

        if ($jabatanId) {
            $ttdNip = DB::table('tr_jabatan')
                ->where('ID_JABATAN', $jabatanId)
                ->value('NIP_KARYAWAN');

            if ($ttdNip) {
                $emp = DB::table('mst_karyawan')
                    ->where('IS_DELETE', 0)
                    ->where('NIP_KARYAWAN', $ttdNip)
                    ->select('NIP_KARYAWAN', 'NAMA_KARYAWAN', 'NAMA_LENGKAP_GELAR')
                    ->first();

                if ($emp) {
                    $nip = $emp->NIP_KARYAWAN;
                    $nama = $emp->NAMA_LENGKAP_GELAR ?: $emp->NAMA_KARYAWAN;
                }
            }
        }

        $filterDisplay = [];
        if (!empty($filters['ID_TA_ANGGARAN'])) {
            $tahun = DB::table('ref_tahun_anggaran')
                ->where('ID_TA_ANGGARAN', $filters['ID_TA_ANGGARAN'])
                ->value('DESKRIPSI_TAHUN_ANGGARAN');
            if ($tahun) {
                $filterDisplay[] = $tahun;
            }
        }

        if (!empty($filters['ID_REF_DANA'])) {
            $dana = DB::table('ref_sumber_dana')
                ->where('ID_REF_DANA', $filters['ID_REF_DANA'])
                ->value('DESKRIPSI_SUMBER_DANA');
            if ($dana) {
                $filterDisplay[] = 'Sumber Dana: ' . $dana;
            }
        }

        $filterText = !empty($filterDisplay) ? implode(' | ', $filterDisplay) : 'Semua Data';

        return compact('role', 'nama', 'nip', 'filterText');
    }

    /**
     * Helper untuk memproses export (Excel/PDF)
     */
    private function processExport(Request $request, $type)
    {
        try {
            $requestNip = $request->input('nip')
                ?? $request->input('NIP_KARYAWAN')
                ?? (Auth::check() ? (Auth::user()->nip ?? Auth::user()->NIP_KARYAWAN ?? null) : null);

            // Ambil filters (jika kosong, akan ditangani dengan default/semua data)
            $filters = $request->only(['ID_TA_ANGGARAN', 'ID_REF_DANA']);

            $role = 'Bendahara';
            $nama = '-';
            $nip = '-';

            // TTD only: resolve Bendahara via ref_jabatan_str -> tr_jabatan -> mst_karyawan
            $jabatanId = DB::table('ref_jabatan_str')
                ->whereRaw('LOWER(DESKRIPSI_JABATAN) = ?', ['bendahara'])
                ->value('ID_JABATAN');

            if ($jabatanId) {
                $ttdNip = DB::table('tr_jabatan')
                    ->where('ID_JABATAN', $jabatanId)
                    ->value('NIP_KARYAWAN');

                if ($ttdNip) {
                    $emp = DB::table('mst_karyawan')
                        ->where('IS_DELETE', 0)
                        ->where('NIP_KARYAWAN', $ttdNip)
                        ->select('NIP_KARYAWAN', 'NAMA_KARYAWAN', 'NAMA_LENGKAP_GELAR')
                        ->first();

                    if ($emp) {
                        $nip = $emp->NIP_KARYAWAN;
                        $nama = $emp->NAMA_LENGKAP_GELAR ?: $emp->NAMA_KARYAWAN;
                    }
                }
            }

            $fileName = 'Laporan_RKAS_' . date('Y-m-d');

            // Get filter descriptions for display
            $filterDisplay = [];
            if (!empty($filters['ID_TA_ANGGARAN'])) {
                $tahun = DB::table('ref_tahun_anggaran')
                    ->where('ID_TA_ANGGARAN', $filters['ID_TA_ANGGARAN'])
                    ->value('DESKRIPSI_TAHUN_ANGGARAN');
                if ($tahun) {
                    $filterDisplay[] = $tahun;
                }
            }
            if (!empty($filters['ID_REF_DANA'])) {
                $dana = DB::table('ref_sumber_dana')
                    ->where('ID_REF_DANA', $filters['ID_REF_DANA'])
                    ->value('DESKRIPSI_SUMBER_DANA');
                if ($dana) {
                    $filterDisplay[] = 'Sumber Dana: ' . $dana;
                }
            }
            $filterText = !empty($filterDisplay) ? implode(' | ', $filterDisplay) : 'Semua Data';

            if ($type === 'pdf') {
                $export = new RkasExport($filters, $role, $nip, $nama, $filterText);
                $data = collect($export->getData());
                $total = $data->sum('anggaran_disetujui');

                return Pdf::loadView('exports.rkas_pdf', compact('data', 'total', 'role', 'nama', 'nip', 'filterText'))
                    ->download($fileName . '.pdf');
            }

            // Proses Download Excel
            return Excel::download(
                new RkasExport($filters, $role, $nip, $nama, $filterText),
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
