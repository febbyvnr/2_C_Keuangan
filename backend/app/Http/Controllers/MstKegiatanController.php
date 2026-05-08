<?php

namespace App\Http\Controllers;

use App\Models\MstKegiatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;

use App\Exports\MstKegiatanExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class MstKegiatanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $search = trim((string) $request->query('search', ''));
            $allData = MstKegiatan::query()
                ->withCount([
                    'children as has_child' => function ($q) {
                        $q->where('IS_DELETE', 0);
                    },
                    'programKerja as is_used'
                ])
                ->where('IS_DELETE', 0)
                ->orderBy('MST_ID_KEGIATAN', 'asc') 
                ->orderBy('ID_KEGIATAN', 'asc')
                ->get();
            $formattedData = $this->generateHierarchy($allData);
            if ($search !== '') {
                $formattedData = collect($formattedData)->filter(function ($item) use ($search) {
                    return str_contains(strtolower($item['DESKRIPSI_KEGIATAN']), strtolower($search)) ||
                        str_contains($item['nomor_urut'], $search);
                })->values();
            }
            return response()->json([
                'success' => true,
                'message' => count($formattedData) === 0 ? 'Data tidak ditemukan' : 'Data berhasil diambil',
                'data' => $formattedData,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function generateHierarchy($nodes, $parentId = null, $prefix = "")
    {
        $result = [];
        $index = 1;
        $children = $nodes->where('MST_ID_KEGIATAN', $parentId);
        foreach ($children as $child) {
            $currentNumber = $prefix ? $prefix . "." . $index : (string)$index;
            $result[] = [
                'ID_KEGIATAN' => $child->ID_KEGIATAN,
                'MST_ID_KEGIATAN' => $child->MST_ID_KEGIATAN,
                'DESKRIPSI_KEGIATAN' => $child->DESKRIPSI_KEGIATAN,
                'nomor_urut' => $currentNumber,
                'has_child' => $child->has_child,
                'is_used' => $child->is_used,
            ];
            $subResults = $this->generateHierarchy($nodes, $child->ID_KEGIATAN, $currentNumber);
            $result = array_merge($result, $subResults);
        
            $index++;
        }
        return $result;
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = MstKegiatan::query()
                ->with(['parent', 'children', 'programKerja'])
                ->where('IS_DELETE', 0)
                ->find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kegiatan tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail kegiatan berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail kegiatan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(
                [
                    'MST_ID_KEGIATAN' => [
                        'nullable',
                        'integer',
                        Rule::exists('mst_kegiatan', 'ID_KEGIATAN')->where(function ($query) {
                            $query->where('IS_DELETE', 0);
                        }),
                    ],
                    'DESKRIPSI_KEGIATAN' => [
                        'required',
                        'string',
                        'max:100',
                    ],
                ],
                [
                    'MST_ID_KEGIATAN.exists' => 'Parent kegiatan tidak valid.',
                    'DESKRIPSI_KEGIATAN.required' => 'Deskripsi kegiatan wajib diisi.',
                    'DESKRIPSI_KEGIATAN.max' => 'Deskripsi kegiatan maksimal 100 karakter.',
                ]
            );

            $isDuplicate = MstKegiatan::query()
                ->where('IS_DELETE', 0)
                ->where('DESKRIPSI_KEGIATAN', $validated['DESKRIPSI_KEGIATAN'])
                ->exists();

            if ($isDuplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => [
                        'DESKRIPSI_KEGIATAN' => [
                            'Nama kegiatan sudah digunakan.',
                        ],
                    ],
                ], 422);
            }

            $data = DB::transaction(function () use ($validated) {
                $nextId = ((int) MstKegiatan::max('ID_KEGIATAN')) + 1;

                $kegiatan = MstKegiatan::create([
                    'ID_KEGIATAN' => $nextId,
                    'MST_ID_KEGIATAN' => $validated['MST_ID_KEGIATAN'] ?? null,
                    'DESKRIPSI_KEGIATAN' => $validated['DESKRIPSI_KEGIATAN'],
                    'IS_DELETE' => 0,
                ]);

                return $kegiatan->fresh();
            });

            return response()->json([
                'success' => true,
                'message' => 'Kegiatan berhasil ditambahkan',
                'data' => $data,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada database saat menambahkan kegiatan',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan kegiatan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $kegiatan = MstKegiatan::query()
                ->where('IS_DELETE', 0)
                ->find($id);

            if (!$kegiatan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kegiatan tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            // klp sblh bs edit walaupun uda dipake tabel lain, asal ga memutus FK aja
            // if ($this->isKegiatanUsed($kegiatan)) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Kegiatan tidak boleh diubah karena sudah dipakai pada program kerja',
            //         'data' => null,
            //     ], 422);
            // }

            $validated = $request->validate(
                [
                    'MST_ID_KEGIATAN' => [
                        'nullable',
                        'integer',
                        Rule::exists('mst_kegiatan', 'ID_KEGIATAN')->where(function ($query) {
                            $query->where('IS_DELETE', 0);
                        }),
                    ],
                    'DESKRIPSI_KEGIATAN' => [
                        'required',
                        'string',
                        'max:100',
                    ],
                ],
                [
                    'MST_ID_KEGIATAN.exists' => 'Parent kegiatan tidak valid.',
                    'DESKRIPSI_KEGIATAN.required' => 'Deskripsi kegiatan wajib diisi.',
                    'DESKRIPSI_KEGIATAN.max' => 'Deskripsi kegiatan maksimal 100 karakter.',
                ]
            );

            if (
                isset($validated['MST_ID_KEGIATAN']) &&
                (int) $validated['MST_ID_KEGIATAN'] === (int) $kegiatan->ID_KEGIATAN
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent kegiatan tidak boleh dirinya sendiri',
                    'data' => null,
                ], 422);
            }

            $isDuplicate = MstKegiatan::query()
                ->where('IS_DELETE', 0)
                ->where('DESKRIPSI_KEGIATAN', $validated['DESKRIPSI_KEGIATAN'])
                ->where('ID_KEGIATAN', '!=', $id)
                ->exists();

            if ($isDuplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => [
                        'DESKRIPSI_KEGIATAN' => [
                            'Nama kegiatan sudah digunakan.',
                        ],
                    ],
                ], 422);
            }

            $kegiatan->update([
                'MST_ID_KEGIATAN' => $validated['MST_ID_KEGIATAN'] ?? null,
                'DESKRIPSI_KEGIATAN' => $validated['DESKRIPSI_KEGIATAN'],
            ]);

            $kegiatan->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Kegiatan berhasil diperbarui',
                'data' => $kegiatan,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada database saat mengubah kegiatan',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengubah kegiatan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $kegiatan = MstKegiatan::query()
                ->with('children')
                ->where('IS_DELETE', 0)
                ->find($id);

            if (!$kegiatan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kegiatan tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            if ($this->isKegiatanUsed($kegiatan)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kegiatan tidak boleh dihapus karena sudah dipakai pada program kerja',
                    'data' => null,
                ], 422);
            }

            $hasActiveChildren = $kegiatan->children()
                ->where('IS_DELETE', 0)
                ->exists();

            if ($hasActiveChildren) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kegiatan tidak boleh dihapus karena masih memiliki sub kegiatan aktif',
                    'data' => null,
                ], 422);
            }

            $kegiatan->update([
                'IS_DELETE' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kegiatan berhasil dihapus',
                'data' => null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus kegiatan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function parents(): JsonResponse
    {
        try {
            $data = MstKegiatan::query()
                ->where('IS_DELETE', 0)
                ->whereNull('MST_ID_KEGIATAN')
                ->orderBy('ID_KEGIATAN', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data parent kegiatan tidak ditemukan'
                    : 'Data parent kegiatan berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil parent kegiatan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = MstKegiatan::query()
            ->with(['parent'])
            ->where('IS_DELETE', 0)
            ->orderBy('DESKRIPSI_KEGIATAN', 'asc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('DESKRIPSI_KEGIATAN', 'like', "%{$search}%");
            });
        }

        $data = $query->get();

        $filename = 'mst_kegiatan_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($data) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'ID_KEGIATAN',
                'MST_ID_KEGIATAN',
                'DESKRIPSI_KEGIATAN',
                'PARENT_DESKRIPSI_KEGIATAN',
                'IS_DELETE',
            ]);

            foreach ($data as $item) {
                fputcsv($handle, [
                    $item->ID_KEGIATAN,
                    $item->MST_ID_KEGIATAN,
                    $item->DESKRIPSI_KEGIATAN,
                    optional($item->parent)->DESKRIPSI_KEGIATAN,
                    $item->IS_DELETE,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only([
            'search',
        ]);

        return Excel::download(new MstKegiatanExport($filters), 'mst_kegiatan.xlsx');
    }

    public function exportCsv(Request $request)
    {
        $filters = $request->only([
            'search',
        ]);

        return Excel::download(new MstKegiatanExport($filters), 'mst_kegiatan.csv');
    }

    public function exportPdf(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = MstKegiatan::query()
            ->with(['parent'])
            ->where('IS_DELETE', 0)
            ->orderBy('DESKRIPSI_KEGIATAN', 'asc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('DESKRIPSI_KEGIATAN', 'like', "%{$search}%");
            });
        }

        $data = $query->get();

        $pdf = Pdf::loadView('exports.mst_kegiatan_pdf', compact('data'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('mst_kegiatan.pdf');
    }

    private function isKegiatanUsed(MstKegiatan $kegiatan): bool
    {
        return method_exists($kegiatan, 'programKerja') && $kegiatan->programKerja()->exists();
    }
}