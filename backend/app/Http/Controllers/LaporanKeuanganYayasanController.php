<?php

namespace App\Http\Controllers;

use App\Models\RefTahunAnggaran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanKeuanganYayasanExport;

class LaporanKeuanganYayasanController extends Controller
{
    public function exportExcel(Request $request)
    {
        $idTaAnggaran = $this->resolveTahunAnggaranId($request);

        $filters = [
            'tahun' => $this->resolvePeriodeTahun($request),
            'id_ta_anggaran' => $idTaAnggaran,
            'tahun_angka' => $this->resolveTahunAngka($request, $idTaAnggaran),
            'format' => 'excel',
        ];

        return Excel::download(new LaporanKeuanganYayasanExport($filters), 'Laporan_Keuangan_Yayasan.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $idTaAnggaran = $this->resolveTahunAnggaranId($request);

        $filters = [
            'tahun' => $this->resolvePeriodeTahun($request),
            'id_ta_anggaran' => $idTaAnggaran,
            'tahun_angka' => $this->resolveTahunAngka($request, $idTaAnggaran),
            'format' => 'pdf',
        ];

        return Excel::download(
            new LaporanKeuanganYayasanExport($filters), 
            'Laporan_Keuangan_Yayasan.pdf', 
            \Maatwebsite\Excel\Excel::DOMPDF
        );
    }

    private function resolvePeriodeTahun(Request $request): string
    {
        $raw = $request->input('tahun')
            ?? $request->input('ID_TA_ANGGARAN')
            ?? $request->input('id_ta_anggaran');

        if (is_null($raw) || $raw === '') {
            $active = RefTahunAnggaran::query()
                ->where('IS_CURRENT', 1)
                ->first();

            if ($active && !empty($active->DESKRIPSI_TAHUN_ANGGARAN)) {
                return (string) $active->DESKRIPSI_TAHUN_ANGGARAN;
            }

            return '-';
        }

        if (is_numeric($raw)) {
            $tahun = RefTahunAnggaran::query()
                ->where('ID_TA_ANGGARAN', (int) $raw)
                ->first();

            if ($tahun && !empty($tahun->DESKRIPSI_TAHUN_ANGGARAN)) {
                return (string) $tahun->DESKRIPSI_TAHUN_ANGGARAN;
            }
        }

        return (string) $raw;
    }

    private function resolveTahunAnggaranId(Request $request): ?int
    {
        $raw = $request->input('ID_TA_ANGGARAN')
            ?? $request->input('id_ta_anggaran')
            ?? $request->input('tahun');

        if (is_numeric($raw)) {
            return (int) $raw;
        }

        return null;
    }

    private function resolveTahunAngka(Request $request, ?int $idTaAnggaran): ?int
    {
        $tahun = $request->input('tahun');

        if (is_numeric($tahun) && strlen((string) $tahun) === 4) {
            return (int) $tahun;
        }

        if ($idTaAnggaran) {
            $row = RefTahunAnggaran::query()
                ->where('ID_TA_ANGGARAN', $idTaAnggaran)
                ->first();

            if ($row && !empty($row->DESKRIPSI_TAHUN_ANGGARAN) && preg_match('/\d{4}/', (string) $row->DESKRIPSI_TAHUN_ANGGARAN, $matches)) {
                return (int) $matches[0];
            }
        }

        return null;
    }
}