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
        $hasFilter = $this->hasTahunFilter($request);
        $idTaAnggaran = $hasFilter ? $this->resolveTahunAnggaranId($request) : null;
        $signer = $this->resolveSigner(); // Hapus parameter $request karena tidak digunakan lagi

        $fileName = 'Laporan Keuangan Yayasan ' . date('Y-m-d');

        $filters = [
            'tahun' => $hasFilter ? $this->resolvePeriodeTahun($request) : 'Semua Data',
            'id_ta_anggaran' => $idTaAnggaran,
            'tahun_angka' => $hasFilter ? $this->resolveTahunAngka($request, $idTaAnggaran) : null,
            'format' => 'excel',
            'role' => $signer['role'],
            'nama' => $signer['nama'],
            'nip' => $signer['nip'],
        ];

        return Excel::download(new LaporanKeuanganYayasanExport($filters), $fileName . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $hasFilter = $this->hasTahunFilter($request);
        $idTaAnggaran = $hasFilter ? $this->resolveTahunAnggaranId($request) : null;
        $signer = $this->resolveSigner();

        $fileName = 'Laporan Keuangan Yayasan ' . date('Y-m-d');

        $filters = [
            'tahun' => $hasFilter ? $this->resolvePeriodeTahun($request) : 'Semua Data',
            'id_ta_anggaran' => $idTaAnggaran,
            'tahun_angka' => $hasFilter ? $this->resolveTahunAngka($request, $idTaAnggaran) : null,
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

    public function yayasan(Request $request)
    {
        $hasFilter = $this->hasTahunFilter($request);
        $idTaAnggaran = $hasFilter ? $this->resolveTahunAnggaranId($request) : null;
        $signer = $this->resolveSigner();

        $filters = [
            'tahun' => $hasFilter ? $this->resolvePeriodeTahun($request) : 'Semua Data',
            'id_ta_anggaran' => $idTaAnggaran,
            'tahun_angka' => $hasFilter ? $this->resolveTahunAngka($request, $idTaAnggaran) : null,
            'format' => 'json',
            'role' => $signer['role'],
            'nama' => $signer['nama'],
            'nip' => $signer['nip'],
        ];

        $export = new LaporanKeuanganYayasanExport($filters);
        $data = collect($export->getData());
        $totalMasuk = $data->sum('TOTAL_MASUK');
        $totalKeluar = $data->sum('TOTAL_KELUAR');

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'tahun' => $filters['tahun'] ?? '-',
            'info_ttd' => [
                'role' => $signer['role'],
                'nama' => $signer['nama'],
                'nip' => $signer['nip'],
            ],
        ]);
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

    private function hasTahunFilter(Request $request): bool
    {
        $rawId = $request->input('ID_TA_ANGGARAN') ?? $request->input('id_ta_anggaran');
        if (is_numeric($rawId) && (int) $rawId > 0) {
            return true;
        }

        $rawTahun = $request->input('tahun');
        if (is_numeric($rawTahun)) {
            return (int) $rawTahun > 0;
        }

        if (is_string($rawTahun)) {
            $normalized = trim(strtolower($rawTahun));
            if ($normalized !== '' && !in_array($normalized, ['0', 'semua', 'semua data', '-'], true)) {
                return true;
            }
        }

        return false;
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

    /**
     * Resolve signer (penandatangan) berdasarkan jabatan Bendahara
     * Logika ini diambil dari LaporanRkasController dan disederhanakan
     * 
     * @return array ['role', 'nama', 'nip']
     */
    private function resolveSigner(): array
    {
        $role = 'Bendahara';
        $nama = '-';
        $nip = '-';

        // Cari ID Jabatan dari ref_jabatan_str dengan deskripsi 'bendahara' (case insensitive)
        $jabatanId = DB::table('ref_jabatan_str')
            ->whereRaw('LOWER(DESKRIPSI_JABATAN) = ?', ['bendahara'])
            ->value('ID_JABATAN');

        if ($jabatanId) {
            // Cari NIP karyawan dari tr_jabatan berdasarkan ID_JABATAN
            $ttdNip = DB::table('tr_jabatan')
                ->where('ID_JABATAN', $jabatanId)
                ->value('NIP_KARYAWAN');

            if ($ttdNip) {
                // Ambil data karyawan dari mst_karyawan
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

        return [
            'role' => $role,
            'nama' => $nama,
            'nip' => $nip,
        ];
    }
}