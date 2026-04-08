<?php

namespace App\Http\Controllers;

use App\Models\TagihanSiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Exports\TagihanSiswaExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class TagihanSiswaController extends Controller
{
    private const ALLOWED_STATUS = [
        'Belum Bayar',
        'Sudah Bayar',
    ];

    private const ALLOWED_BULAN = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember',
    ];

    public function index(Request $request): JsonResponse
    {
        $query = TagihanSiswa::with([
            'siswa',
            'jenisPembayaran',
            'pembayaran',
        ]);

        if ($request->filled('ID_SISWA_TETAP')) {
            $query->where('ID_SISWA_TETAP', $request->ID_SISWA_TETAP);
        }

        if ($request->filled('ID_JENIS_PEMBAYARAN')) {
            $query->where('ID_JENIS_PEMBAYARAN', $request->ID_JENIS_PEMBAYARAN);
        }

        if ($request->filled('BULAN_TAGIHAN_SISWA')) {
            $query->where('BULAN_TAGIHAN_SISWA', $request->BULAN_TAGIHAN_SISWA);
        }

        if ($request->filled('TAHUN_TAGIHAN_SISWA')) {
            $query->where('TAHUN_TAGIHAN_SISWA', $request->TAHUN_TAGIHAN_SISWA);
        }

        if ($request->filled('STATUS_TAGIHAN_SISWA')) {
            $query->where('STATUS_TAGIHAN_SISWA', $request->STATUS_TAGIHAN_SISWA);
        }

        // if ($request->filled('KELAS')) {
        //     $kelas = trim($request->KELAS);

        //     $query->whereHas('siswa', function ($q) use ($kelas) {
        //         $q->where('KELAS_SISWA', 'like', '%' . $kelas . '%');
        //     });
        // }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('NAMA_SISWA_TETAP', 'like', '%' . $search . '%')
                  ->orWhere('NISN_SISWA', 'like', '%' . $search . '%');
            });
        }

        $data = $query
            ->orderByDesc('TAHUN_TAGIHAN_SISWA')
            ->orderByDesc('ID_TAGIHAN_SISWA')
            ->get()
            ->map(function ($tagihan) {
                return $this->formatTagihan($tagihan);
            })
            ->values();

        if ($request->filled('tunggakan')) {
            $tunggakan = strtolower((string) $request->tunggakan);

            if ($tunggakan === 'ada') {
                $data = $data->filter(fn ($item) => $item['SISA_TAGIHAN'] > 0)->values();
            }

            if ($tunggakan === 'tidak') {
                $data = $data->filter(fn ($item) => $item['SISA_TAGIHAN'] <= 0)->values();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar tagihan siswa berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request);

        DB::beginTransaction();

        try {
            $this->validateDuplicate(
                $validated['ID_SISWA_TETAP'],
                $validated['ID_JENIS_PEMBAYARAN'],
                $validated['BULAN_TAGIHAN_SISWA'],
                $validated['TAHUN_TAGIHAN_SISWA']
            );

            $nextId = ((int) TagihanSiswa::max('ID_TAGIHAN_SISWA') ?: 0) + 1;

            $tagihan = TagihanSiswa::create([
                'ID_TAGIHAN_SISWA' => $nextId,
                'ID_SISWA_TETAP' => $validated['ID_SISWA_TETAP'],
                'ID_JENIS_PEMBAYARAN' => $validated['ID_JENIS_PEMBAYARAN'],
                'BULAN_TAGIHAN_SISWA' => $validated['BULAN_TAGIHAN_SISWA'],
                'TAHUN_TAGIHAN_SISWA' => $validated['TAHUN_TAGIHAN_SISWA'],
                'JUMLAH_TAGIHAN_SISWA' => $validated['JUMLAH_TAGIHAN_SISWA'],
                'STATUS_TAGIHAN_SISWA' => $validated['STATUS_TAGIHAN_SISWA'],
                'DUEDATE_TAGIHAN_SISWA' => $validated['DUEDATE_TAGIHAN_SISWA'],
            ]);

            $tagihan->load(['siswa', 'jenisPembayaran', 'pembayaran']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tagihan siswa berhasil ditambahkan.',
                'data' => $this->formatTagihan($tagihan, true),
            ], 201);

        } catch (HttpResponseException $e) {
            DB::rollBack();
            throw $e;

        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan tagihan siswa.',
                'error' => config('app.debug') ? $th->getMessage() : null,
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $tagihan = TagihanSiswa::with([
            'siswa',
            'jenisPembayaran',
            'pembayaran',
        ])->find($id);

        if (!$tagihan) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan siswa tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail tagihan siswa berhasil diambil.',
            'data' => $this->formatTagihan($tagihan, true),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $tagihan = TagihanSiswa::with('pembayaran')->find($id);

        if (!$tagihan) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan siswa tidak ditemukan.',
            ], 404);
        }

        $validated = $this->validatePayload($request);

        DB::beginTransaction();

        try {
            $this->validateDuplicate(
                $validated['ID_SISWA_TETAP'],
                $validated['ID_JENIS_PEMBAYARAN'],
                $validated['BULAN_TAGIHAN_SISWA'],
                $validated['TAHUN_TAGIHAN_SISWA'],
                $id
            );

            $jumlahPembayaran = $tagihan->pembayaran->count();
            $totalPembayaran = (float) $tagihan->pembayaran->sum('JUMLAH_BAYAR');

            if ($jumlahPembayaran > 0) {
                if ((int) $validated['ID_SISWA_TETAP'] !== (int) $tagihan->ID_SISWA_TETAP) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tagihan yang sudah memiliki pembayaran tidak boleh dipindahkan ke siswa lain.',
                    ], 422);
                }

                if ((int) $validated['ID_JENIS_PEMBAYARAN'] !== (int) $tagihan->ID_JENIS_PEMBAYARAN) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Jenis pembayaran tidak boleh diubah jika sudah ada pembayaran.',
                    ], 422);
                }

                if ((float) $validated['JUMLAH_TAGIHAN_SISWA'] < $totalPembayaran) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Jumlah tagihan tidak boleh lebih kecil dari total pembayaran yang sudah masuk.',
                    ], 422);
                }
            }

            $statusFinal = $validated['STATUS_TAGIHAN_SISWA'];

            if ($totalPembayaran >= (float) $validated['JUMLAH_TAGIHAN_SISWA'] && (float) $validated['JUMLAH_TAGIHAN_SISWA'] > 0) {
                $statusFinal = 'Sudah Bayar';
            } elseif ($totalPembayaran > 0 && $totalPembayaran < (float) $validated['JUMLAH_TAGIHAN_SISWA']) {
                $statusFinal = 'Belum Bayar';
            }

            $tagihan->update([
                'ID_SISWA_TETAP' => $validated['ID_SISWA_TETAP'],
                'ID_JENIS_PEMBAYARAN' => $validated['ID_JENIS_PEMBAYARAN'],
                'BULAN_TAGIHAN_SISWA' => $validated['BULAN_TAGIHAN_SISWA'],
                'TAHUN_TAGIHAN_SISWA' => $validated['TAHUN_TAGIHAN_SISWA'],
                'JUMLAH_TAGIHAN_SISWA' => $validated['JUMLAH_TAGIHAN_SISWA'],
                'STATUS_TAGIHAN_SISWA' => $statusFinal,
                'DUEDATE_TAGIHAN_SISWA' => $validated['DUEDATE_TAGIHAN_SISWA'],
            ]);

            $tagihan->load(['siswa', 'jenisPembayaran', 'pembayaran']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tagihan siswa berhasil diubah.',
                'data' => $this->formatTagihan($tagihan, true),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah tagihan siswa.',
                'error' => config('app.debug') ? $th->getMessage() : null,
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $tagihan = TagihanSiswa::with('pembayaran')->find($id);

        if (!$tagihan) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan siswa tidak ditemukan.',
            ], 404);
        }

        if ($tagihan->pembayaran->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan tidak bisa dihapus karena sudah memiliki riwayat pembayaran.',
            ], 422);
        }

        $tagihan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tagihan siswa berhasil dihapus.',
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'ID_SISWA_TETAP' => [
                'required',
                'integer',
                'exists:mst_siswa,ID_SISWA_TETAP',
            ],
            'ID_JENIS_PEMBAYARAN' => [
                'required',
                'integer',
                'exists:ref_jenis_pembayaran,ID_JENIS_PEMBAYARAN',
            ],
            'BULAN_TAGIHAN_SISWA' => [
                'required',
                'string',
                Rule::in(self::ALLOWED_BULAN),
            ],
            'TAHUN_TAGIHAN_SISWA' => [
                'required',
                'regex:/^\d{4}$/',
            ],
            'JUMLAH_TAGIHAN_SISWA' => [
                'required',
                'numeric',
                'min:1',
            ],
            'STATUS_TAGIHAN_SISWA' => [
                'required',
                'string',
                Rule::in(self::ALLOWED_STATUS),
            ],
            'DUEDATE_TAGIHAN_SISWA' => [
                'required',
                'date',
            ],
        ]);
    }

    private function validateDuplicate(
        int $idSiswa,
        int $idJenisPembayaran,
        string $bulan,
        string $tahun,
        ?int $exceptId = null
    ): void {
        $query = TagihanSiswa::where('ID_SISWA_TETAP', $idSiswa)
            ->where('ID_JENIS_PEMBAYARAN', $idJenisPembayaran)
            ->where('BULAN_TAGIHAN_SISWA', $bulan)
            ->where('TAHUN_TAGIHAN_SISWA', $tahun);

        if ($exceptId !== null) {
            $query->where('ID_TAGIHAN_SISWA', '!=', $exceptId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'duplicate' => ['Tagihan siswa untuk siswa, jenis pembayaran, bulan, dan tahun tersebut sudah ada.'],
            ]);
        }
    }

    private function formatTagihan(TagihanSiswa $tagihan, bool $includeHistori = false): array
    {
        $totalPembayaran = (float) $tagihan->pembayaran->sum('JUMLAH_BAYAR');
        $sisaTagihan = max(0, (float) $tagihan->JUMLAH_TAGIHAN_SISWA - $totalPembayaran);

        $result = [
            'ID_TAGIHAN_SISWA' => (int) $tagihan->ID_TAGIHAN_SISWA,
            'ID_SISWA_TETAP' => (int) $tagihan->ID_SISWA_TETAP,
            'ID_JENIS_PEMBAYARAN' => (int) $tagihan->ID_JENIS_PEMBAYARAN,
            'BULAN_TAGIHAN_SISWA' => $tagihan->BULAN_TAGIHAN_SISWA,
            'TAHUN_TAGIHAN_SISWA' => $tagihan->TAHUN_TAGIHAN_SISWA,
            'JUMLAH_TAGIHAN_SISWA' => (float) $tagihan->JUMLAH_TAGIHAN_SISWA,
            'STATUS_TAGIHAN_SISWA' => $tagihan->STATUS_TAGIHAN_SISWA,
            'DUEDATE_TAGIHAN_SISWA' => $tagihan->DUEDATE_TAGIHAN_SISWA,

            'SISWA' => [
                'ID_SISWA_TETAP' => optional($tagihan->siswa)->ID_SISWA_TETAP,
                'NAMA_SISWA_TETAP' => optional($tagihan->siswa)->NAMA_SISWA_TETAP,
                'NISN_SISWA' => optional($tagihan->siswa)->NISN_SISWA,
                'KODE_TA' => optional($tagihan->siswa)->KODE_TA,
            ],

            'JENIS_PEMBAYARAN' => [
                'ID_JENIS_PEMBAYARAN' => optional($tagihan->jenisPembayaran)->ID_JENIS_PEMBAYARAN,
                'DESKRIPSI_JENIS_PEMBAYARAN' => optional($tagihan->jenisPembayaran)->DESKRIPSI_JENIS_PEMBAYARAN,
            ],

            'TOTAL_PEMBAYARAN' => $totalPembayaran,
            'SISA_TAGIHAN' => $sisaTagihan,
            'ADA_TUNGGAKAN' => $sisaTagihan > 0,
            'JUMLAH_TRANSAKSI_PEMBAYARAN' => $tagihan->pembayaran->count(),
        ];

        if ($includeHistori) {
            $result['HISTORI_PEMBAYARAN'] = $tagihan->pembayaran->map(function ($pembayaran) {
                return [
                    'ID_PEMBAYARAN' => $pembayaran->ID_PEMBAYARAN ?? null,
                    'TGL_BAYAR' => $pembayaran->TGL_BAYAR ?? null,
                    'JUMLAH_BAYAR' => isset($pembayaran->JUMLAH_BAYAR) ? (float) $pembayaran->JUMLAH_BAYAR : 0,
                    'NIP_VALIDATOR_PEMBAYARAN' => $pembayaran->NIP_VALIDATOR_PEMBAYARAN ?? null,
                ];
            })->values();
        }

        return $result;
    }

    

    public function export(Request $request)
    {
        $query = TagihanSiswa::with([
            'siswa',
            'jenisPembayaran',
            'pembayaran',
        ]);

        if ($request->filled('ID_SISWA_TETAP')) {
            $query->where('ID_SISWA_TETAP', $request->ID_SISWA_TETAP);
        }

        if ($request->filled('ID_JENIS_PEMBAYARAN')) {
            $query->where('ID_JENIS_PEMBAYARAN', $request->ID_JENIS_PEMBAYARAN);
        }

        if ($request->filled('BULAN_TAGIHAN_SISWA')) {
            $query->where('BULAN_TAGIHAN_SISWA', $request->BULAN_TAGIHAN_SISWA);
        }

        if ($request->filled('TAHUN_TAGIHAN_SISWA')) {
            $query->where('TAHUN_TAGIHAN_SISWA', $request->TAHUN_TAGIHAN_SISWA);
        }

        if ($request->filled('STATUS_TAGIHAN_SISWA')) {
            $query->where('STATUS_TAGIHAN_SISWA', $request->STATUS_TAGIHAN_SISWA);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('NAMA_SISWA_TETAP', 'like', '%' . $search . '%')
                ->orWhere('NISN_SISWA', 'like', '%' . $search . '%');
            });
        }

        $data = $query
            ->orderByDesc('TAHUN_TAGIHAN_SISWA')
            ->orderByDesc('ID_TAGIHAN_SISWA')
            ->get()
            ->map(function ($tagihan) {
                return $this->formatTagihan($tagihan, true);
            })
            ->values();

        if ($request->filled('tunggakan')) {
            $tunggakan = strtolower((string) $request->tunggakan);

            if ($tunggakan === 'ada') {
                $data = $data->filter(fn ($item) => $item['SISA_TAGIHAN'] > 0)->values();
            }

            if ($tunggakan === 'tidak') {
                $data = $data->filter(fn ($item) => $item['SISA_TAGIHAN'] <= 0)->values();
            }
        }

        $filename = 'tagihan_siswa_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($data) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 agar Excel membaca karakter dengan benar
            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'ID_TAGIHAN_SISWA',
                'ID_SISWA_TETAP',
                'NAMA_SISWA_TETAP',
                'NISN_SISWA',
                'ID_JENIS_PEMBAYARAN',
                'JENIS_PEMBAYARAN',
                'BULAN_TAGIHAN_SISWA',
                'TAHUN_TAGIHAN_SISWA',
                'JUMLAH_TAGIHAN_SISWA',
                'TOTAL_PEMBAYARAN',
                'SISA_TAGIHAN',
                'STATUS_TAGIHAN_SISWA',
                'ADA_TUNGGAKAN',
                'DUEDATE_TAGIHAN_SISWA',
                'JUMLAH_TRANSAKSI_PEMBAYARAN',
            ]);

            foreach ($data as $item) {
                fputcsv($handle, [
                    $item['ID_TAGIHAN_SISWA'],
                    $item['ID_SISWA_TETAP'],
                    $item['SISWA']['NAMA_SISWA_TETAP'] ?? null,
                    $item['SISWA']['NISN_SISWA'] ?? null,
                    $item['ID_JENIS_PEMBAYARAN'],
                    $item['JENIS_PEMBAYARAN']['DESKRIPSI_JENIS_PEMBAYARAN'] ?? null,
                    $item['BULAN_TAGIHAN_SISWA'],
                    $item['TAHUN_TAGIHAN_SISWA'],
                    $item['JUMLAH_TAGIHAN_SISWA'],
                    $item['TOTAL_PEMBAYARAN'],
                    $item['SISA_TAGIHAN'],
                    $item['STATUS_TAGIHAN_SISWA'],
                    $item['ADA_TUNGGAKAN'] ? 'Ya' : 'Tidak',
                    $item['DUEDATE_TAGIHAN_SISWA'],
                    $item['JUMLAH_TRANSAKSI_PEMBAYARAN'],
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only([
            'ID_SISWA_TETAP',
            'ID_JENIS_PEMBAYARAN',
            'BULAN_TAGIHAN_SISWA',
            'TAHUN_TAGIHAN_SISWA',
            'STATUS_TAGIHAN_SISWA',
            'search',
            'tunggakan',
        ]);

        return Excel::download(new TagihanSiswaExport($filters), 'tagihan_siswa.xlsx');
    }

    public function exportCsv(Request $request)
    {
        $filters = $request->only([
            'ID_SISWA_TETAP',
            'ID_JENIS_PEMBAYARAN',
            'BULAN_TAGIHAN_SISWA',
            'TAHUN_TAGIHAN_SISWA',
            'STATUS_TAGIHAN_SISWA',
            'search',
            'tunggakan',
        ]);

        return Excel::download(
            new TagihanSiswaExport($filters),
            'tagihan_siswa.csv'
        );
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only([
            'ID_SISWA_TETAP',
            'ID_JENIS_PEMBAYARAN',
            'BULAN_TAGIHAN_SISWA',
            'TAHUN_TAGIHAN_SISWA',
            'STATUS_TAGIHAN_SISWA',
            'search',
            'tunggakan',
        ]);

        $query = TagihanSiswa::with([
            'siswa',
            'jenisPembayaran',
            'pembayaran',
        ]);

        if (!empty($filters['ID_SISWA_TETAP'])) {
            $query->where('ID_SISWA_TETAP', $filters['ID_SISWA_TETAP']);
        }

        if (!empty($filters['ID_JENIS_PEMBAYARAN'])) {
            $query->where('ID_JENIS_PEMBAYARAN', $filters['ID_JENIS_PEMBAYARAN']);
        }

        if (!empty($filters['BULAN_TAGIHAN_SISWA'])) {
            $query->where('BULAN_TAGIHAN_SISWA', $filters['BULAN_TAGIHAN_SISWA']);
        }

        if (!empty($filters['TAHUN_TAGIHAN_SISWA'])) {
            $query->where('TAHUN_TAGIHAN_SISWA', $filters['TAHUN_TAGIHAN_SISWA']);
        }

        if (!empty($filters['STATUS_TAGIHAN_SISWA'])) {
            $query->where('STATUS_TAGIHAN_SISWA', $filters['STATUS_TAGIHAN_SISWA']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('NAMA_SISWA_TETAP', 'like', '%' . $search . '%')
                ->orWhere('NISN_SISWA', 'like', '%' . $search . '%');
            });
        }

        $data = $query
            ->orderByDesc('TAHUN_TAGIHAN_SISWA')
            ->orderByDesc('ID_TAGIHAN_SISWA')
            ->get()
            ->map(function ($tagihan) {
                return $this->formatTagihan($tagihan, true);
            })
            ->values();

        if (!empty($filters['tunggakan'])) {
            $tunggakan = strtolower((string) $filters['tunggakan']);

            if ($tunggakan === 'ada') {
                $data = $data->filter(fn ($item) => $item['SISA_TAGIHAN'] > 0)->values();
            }

            if ($tunggakan === 'tidak') {
                $data = $data->filter(fn ($item) => $item['SISA_TAGIHAN'] <= 0)->values();
            }
        }

        $pdf = Pdf::loadView('exports.tagihan_siswa_pdf', compact('data'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('tagihan_siswa.pdf');
    }
}