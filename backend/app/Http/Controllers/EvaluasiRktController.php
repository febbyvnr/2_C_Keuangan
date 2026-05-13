<?php

namespace App\Http\Controllers;

use App\Exports\EvaluasiRktExport;
use App\Models\EvaluasiRkt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class EvaluasiRktController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = EvaluasiRkt::with(['programKerja', 'refPm']);

            // Filter per kolom (opsional)
            if ($request->filled('ID_PROGRAM_KERJA')) {
                $query->where('ID_PROGRAM_KERJA', (int) $request->ID_PROGRAM_KERJA);
            }
            if ($request->filled('ID_REF_PM')) {
                $query->where('ID_REF_PM', (int) $request->ID_REF_PM);
            }
            if ($request->filled('TGL_PM')) {
                $query->where('TGL_PM', 'like', $request->TGL_PM . '%');
            }
            if ($request->filled('keyword')) {
                $kw = $request->keyword;
                $query->where(function ($q) use ($kw) {
                    $q->where('DESKRIPSI_TR_PM', 'like', "%{$kw}%")
                      ->orWhereHas('refPm', fn($r) => $r->where('NAMA_PM', 'like', "%{$kw}%"))
                      ->orWhereHas('programKerja', fn($p) => $p->where('PROGRAM_KERJA', 'like', "%{$kw}%"));
                });
            }
            $data = EvaluasiRkt::with([
                'programKerja',
                'refPm',
                'validator.jabatan.refJabatan'
            ])->get();

            // Pagination opsional — kalau tidak ada per_page, return semua (backward-compat)
            $perPage = (int) $request->query('per_page', 0);
            if ($perPage > 0) {
                $data = $query->paginate($perPage);
                return response()->json([
                    'success' => true,
                    'message' => 'Data Evaluasi RKT berhasil diambil',
                    'data' => $data->items(),
                    'pagination' => [
                        'total'        => $data->total(),
                        'per_page'     => $data->perPage(),
                        'current_page' => $data->currentPage(),
                        'last_page'    => $data->lastPage(),
                    ],
                ]);
            }

            $data = $query->get();
            return response()->json([
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data Evaluasi RKT tidak ditemukan'
                    : 'Data Evaluasi RKT berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = EvaluasiRkt::with([
                'programKerja',
                'refPm',
                'validator.jabatan.refJabatan'
            ]);

            if ($request->filled('ID_PROGRAM_KERJA')) {
                $query->where('ID_PROGRAM_KERJA', (int) $request->query('ID_PROGRAM_KERJA'));
            }

            if ($request->filled('ID_REF_PM')) {
                $query->where('ID_REF_PM', (int) $request->query('ID_REF_PM'));
            }

            if ($request->filled('TGL_PM')) {
                $query->where('TGL_PM', 'like', $request->query('TGL_PM') . '%');
            }

            if ($request->filled('keyword')) {
                $kw = $request->query('keyword');
                $query->where(function ($q) use ($kw) {
                    $q->where('DESKRIPSI_TR_PM', 'like', "%{$kw}%")
                      ->orWhereHas('refPm', fn($r) => $r->where('NAMA_PM', 'like', "%{$kw}%"))
                      ->orWhereHas('programKerja', fn($p) => $p->where('PROGRAM_KERJA', 'like', "%{$kw}%"));
                });
            }

            $data = $query->get();

            return response()->json([
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data tidak ditemukan'
                    : 'Data berhasil ditemukan',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat search',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $id = (int) $id;
            $data = EvaluasiRkt::with(['programKerja', 'refPm', 'validator.jabatan.refJabatan'])->find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null,
                ], 404);
            }
            return response()->json([
                'success' => true,
                'message' => 'Evaluasi RKT berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ID_PROGRAM_KERJA' => 'required|integer|exists:mst_program_kerja,ID_PROGRAM_KERJA',
                'ID_REF_PM' => 'required|integer|exists:ref_pm,ID_REF_PM',
                'TGL_PM' => 'required|date',
                'DESKRIPSI_TR_PM' => 'nullable|string|max:100',
            ]);

            $data = EvaluasiRkt::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Evaluasi RKT berhasil ditambahkan',
                'data' => $data,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $id = (int) $id;
            $data = EvaluasiRkt::find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            $validated = $request->validate([
                'ID_PROGRAM_KERJA' => 'required|integer|exists:mst_program_kerja,ID_PROGRAM_KERJA',
                'ID_REF_PM' => 'required|integer|exists:ref_pm,ID_REF_PM',
                'TGL_PM' => 'required|date',
                'DESKRIPSI_TR_PM' => 'nullable|string|max:100',
            ]);

            $data->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate',
                'data' => $data,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function approve(Request $request, $id): JsonResponse
    {
        try {
            $data = EvaluasiRkt::find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            $validated = $request->validate([
                'NIP_VALIDATOR_PM' => 'required'
            ]);
            $data->update([
                'NIP_VALIDATOR_PM' => $validated['NIP_VALIDATOR_PM']
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Evaluasi berhasil disetujui',
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function reject($id): JsonResponse
    {
        try {
            $data = EvaluasiRkt::find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            $data->update([
                'NIP_VALIDATOR_PM' => 'Ditolak'
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Evaluasi berhasil ditolak',
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['ID_PROGRAM_KERJA', 'ID_REF_PM', 'TGL_PM', 'keyword']);
        return Excel::download(new EvaluasiRktExport($filters), 'evaluasi_rkt.xlsx');
    }

    public function exportCsv(Request $request)
    {
        $filters = $request->only(['ID_PROGRAM_KERJA', 'ID_REF_PM', 'TGL_PM', 'keyword']);
        return Excel::download(new EvaluasiRktExport($filters), 'evaluasi_rkt.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['ID_PROGRAM_KERJA', 'ID_REF_PM', 'TGL_PM', 'keyword']);

        $query = EvaluasiRkt::with(['programKerja', 'refPm']);

        if (!empty($filters['ID_PROGRAM_KERJA'])) {
            $query->where('ID_PROGRAM_KERJA', $filters['ID_PROGRAM_KERJA']);
        }
        if (!empty($filters['ID_REF_PM'])) {
            $query->where('ID_REF_PM', $filters['ID_REF_PM']);
        }
        if (!empty($filters['TGL_PM'])) {
            $query->where('TGL_PM', 'like', $filters['TGL_PM'] . '%');
        }
        if (!empty($filters['keyword'])) {
            $kw = $filters['keyword'];
            $query->where(function ($q) use ($kw) {
                $q->where('DESKRIPSI_TR_PM', 'like', "%{$kw}%")
                  ->orWhereHas('refPm', fn($r) => $r->where('NAMA_PM', 'like', "%{$kw}%"))
                  ->orWhereHas('programKerja', fn($p) => $p->where('PROGRAM_KERJA', 'like', "%{$kw}%"));
            });
        }

        $data = $query->get();
        $pdf = Pdf::loadView('exports.evaluasi_rkt_pdf', compact('data'))->setPaper('a4', 'landscape');
        return $pdf->download('evaluasi_rkt.pdf');
    }

    public function destroy($id): JsonResponse
    {
        try {
            $id = (int) $id;
            $data = EvaluasiRkt::find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus',
                'data' => null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
