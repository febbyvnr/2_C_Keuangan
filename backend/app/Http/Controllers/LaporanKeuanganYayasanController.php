<?php

namespace App\Http\Controllers;

use App\Models\RefTahunAnggaran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LaporanKeuanganYayasanExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanKeuanganYayasanController extends Controller
{
    public function exportExcel(Request $request)
    {
        $idTaAnggaran = $this->resolveTahunAnggaranId($request);
        $signer = $this->resolveSigner($request);

        $fileName = 'Laporan Keuangan Yayasan ' . date('Y-m-d');

        $filters = [
            'tahun' => $this->resolvePeriodeTahun($request),
            'id_ta_anggaran' => $idTaAnggaran,
            'tahun_angka' => $this->resolveTahunAngka($request, $idTaAnggaran),
            'format' => 'excel',
            'role' => $signer['role'],
            'nama' => $signer['nama'],
            'nip' => $signer['nip'],
        ];

        return Excel::download(new LaporanKeuanganYayasanExport($filters), $fileName . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $idTaAnggaran = $this->resolveTahunAnggaranId($request);
        $signer = $this->resolveSigner($request);

        $fileName = 'Laporan Keuangan Yayasan ' . date('Y-m-d');

        $filters = [
            'tahun' => $this->resolvePeriodeTahun($request),
            'id_ta_anggaran' => $idTaAnggaran,
            'tahun_angka' => $this->resolveTahunAngka($request, $idTaAnggaran),
            'format' => 'pdf',
            'role' => $signer['role'],
            'nama' => $signer['nama'],
            'nip' => $signer['nip'],
        ];

        $export = new LaporanKeuanganYayasanExport($filters);
        $data = $export->getData();
        $totalMasuk = $data->sum('TOTAL_MASUK');
        $totalKeluar = $data->sum('TOTAL_KELUAR');

        return Pdf::loadView('exports.yayasan_pdf', [
            'data' => $data,
            'tahun' => $filters['tahun'] ?? '-',
            'role' => $signer['role'] ?? 'Bendahara',
            'nama' => $signer['nama'] ?? '-',
            'nip' => $signer['nip'] ?? '-',
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
        ])->download($fileName . '.pdf');
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
                $periode = (string) $active->DESKRIPSI_TAHUN_ANGGARAN;
                return trim((string) preg_replace('/^Tahun Anggaran\s*/i', '', $periode));
            }

            return '-';
        }

        if (is_numeric($raw)) {
            $tahun = RefTahunAnggaran::query()
                ->where('ID_TA_ANGGARAN', (int) $raw)
                ->first();

            if ($tahun && !empty($tahun->DESKRIPSI_TAHUN_ANGGARAN)) {
                $periode = (string) $tahun->DESKRIPSI_TAHUN_ANGGARAN;
                return trim((string) preg_replace('/^Tahun Anggaran\s*/i', '', $periode));
            }
        }

        $periode = (string) $raw;
        return trim((string) preg_replace('/^Tahun Anggaran\s*/i', '', $periode));
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

    private function resolveSigner(Request $request): array
    {
        $nip = $request->input('nip')
            ?? $request->input('NIP_KARYAWAN')
            ?? (Auth::check() ? Auth::user()->nip : null);

        $nama = $request->input('nama')
            ?? (Auth::check() ? Auth::user()->name : null);

        $authRole = Auth::check() ? Auth::user()->role : null;
        $dbRole = null;

        if ($nip) {
            $dbRole = DB::table('tr_jabatan as tj')
                ->join('ref_jabatan_str as rj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
                ->where('tj.NIP_KARYAWAN', $nip)
                ->whereNull('tj.TGL_SELESAI_JABATAN')
                ->value('rj.DESKRIPSI_JABATAN');
        }

        $role = trim($dbRole ?? $request->input('role') ?? $authRole ?? 'Bendahara');

        if (!$nip) {
            $nip = DB::table('tr_jabatan as tj')
                ->join('ref_jabatan_str as rj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
                ->whereNull('tj.TGL_SELESAI_JABATAN')
                ->where('rj.DESKRIPSI_JABATAN', $role)
                ->value('tj.NIP_KARYAWAN');
        }

        if ($nip) {
            $emp = DB::table('mst_karyawan')
                ->where('IS_DELETE', 0)
                ->where('NIP_KARYAWAN', $nip)
                ->select('NAMA_LENGKAP_GELAR', 'NAMA_KARYAWAN')
                ->first();

            if ($emp) {
                $nama = $emp->NAMA_LENGKAP_GELAR ?: $emp->NAMA_KARYAWAN;
            }
        }

        if (!$nama) {
            $nama = '-';
        }

        return [
            'role' => $role,
            'nama' => $nama,
            'nip' => $nip,
        ];
    }
}