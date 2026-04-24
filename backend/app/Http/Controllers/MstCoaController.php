<?php

namespace App\Http\Controllers;

use App\Models\MstCoa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

use App\Exports\MstCoaExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class MstCoaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $search = trim((string) $request->query('search', ''));

            $query = MstCoa::query()
                ->with([
                    'children' => function ($q) {
                        $q->active()->orderBy('ID_MASTER_COA', 'desc');
                    }
                ])
                ->active()
                // ->whereNull('MST_ID_MASTER_COA')
                ->orderBy('ID_MASTER_COA', 'desc');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('KODE_COA', 'like', "%{$search}%")
                      ->orWhere('DESKRIPSI_COA', 'like', "%{$search}%");
                });
            }

            $data = $query->get()->map(function ($item) {
                return [
                    'ID_MASTER_COA' => $item->ID_MASTER_COA,
                    'MST_ID_MASTER_COA' => $item->MST_ID_MASTER_COA,
                    'KODE_COA' => $item->KODE_COA,
                    'DESKRIPSI_COA' => $item->DESKRIPSI_COA,
                    'is_used' => $this->isCoaUsed($item),
                    'has_child' => $item->children()->active()->exists(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data COA tidak ditemukan'
                    : 'Data COA berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data COA',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = MstCoa::query()
                ->with([
                    'parent',
                    'children' => function ($q) {
                        $q->active()->orderBy('ID_MASTER_COA', 'desc');
                    },
                    'programKerja',
                ])
                ->active()
                ->find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data COA tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail COA berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail COA',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'MST_ID_MASTER_COA' => [
                    'nullable',
                    'integer',
                    Rule::exists('mst_coa', 'ID_MASTER_COA')
                        ->where(fn ($q) => $q->where('IS_DELETE', 0)),
                ],
                'DESKRIPSI_COA' => ['nullable', 'string', 'max:100'],
            ]);

            DB::beginTransaction();

            $coa = new MstCoa();
            $coa->MST_ID_MASTER_COA = $validated['MST_ID_MASTER_COA'] ?? null;
            $coa->DESKRIPSI_COA = $validated['DESKRIPSI_COA'];
            $coa->IS_DELETE = 0;
            $coa->save();

            $id = $coa->ID_MASTER_COA;

            $coa->KODE_COA = 'COA' . str_pad($id, 3, '0', STR_PAD_LEFT);
            $coa->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'COA berhasil ditambahkan',
                'data' => $coa->fresh(['parent', 'children']),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $coa = MstCoa::query()
                ->active()
                ->find($id);

            if (!$coa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data COA tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            if ($this->isCoaUsed($coa)) {
                return response()->json([
                    'success' => false,
                    'message' => 'COA tidak boleh diubah karena sudah dipakai pada program kerja',
                    'data' => null,
                ], 422);
            }
            $validated = $request->validate(
                [
                    'MST_ID_MASTER_COA' => [
                        'nullable',
                        'integer',
                        Rule::exists('mst_coa', 'ID_MASTER_COA')->where(function ($query) {
                            $query->where('IS_DELETE', 0);
                        }),
                    ],
                    'DESKRIPSI_COA' => [
                        'nullable',
                        'string',
                        'max:100',
                    ],
                ],
                [
                    'MST_ID_MASTER_COA.exists' => 'Parent COA tidak valid.',
                    'DESKRIPSI_COA.max' => 'Deskripsi COA maksimal 100 karakter.',
                ]
            );
            $newParentId = array_key_exists('MST_ID_MASTER_COA', $validated)
                ? $validated['MST_ID_MASTER_COA']
                : $coa->MST_ID_MASTER_COA;
            if (!is_null($newParentId) && (int) $newParentId === (int) $coa->ID_MASTER_COA) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent COA tidak boleh dirinya sendiri',
                    'data' => null,
                ], 422);
            }
            if (
                !is_null($newParentId) &&
                $this->isDescendant((int) $newParentId, (int) $coa->ID_MASTER_COA)
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent COA tidak valid karena menyebabkan struktur melingkar',
                    'data' => null,
                ], 422);
            }
            $parentChanged = (int) ($coa->MST_ID_MASTER_COA ?? 0) !== (int) ($newParentId ?? 0);
            // if ($parentChanged) {
            //     $hasActiveChildren = $coa->children()
            //         ->active()
            //         ->exists();
            //     if ($hasActiveChildren) {
            //         return response()->json([
            //             'success' => false,
            //             'message' => 'COA tidak boleh dipindah parent karena masih memiliki sub COA aktif',
            //             'data' => null,
            //         ], 422);
            //     }
            // }
            $updateData = [
                'MST_ID_MASTER_COA' => $newParentId,
                'DESKRIPSI_COA' => $validated['DESKRIPSI_COA'],
            ];
            $coa->update($updateData);
            return response()->json([
                'success' => true,
                'message' => 'COA berhasil diperbarui',
                'data' => $coa->fresh(['parent', 'children']),
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
                'message' => 'Terjadi kesalahan pada database saat mengubah COA',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengubah COA',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $coa = MstCoa::query()
                ->with('children')
                ->active()
                ->find($id);

            if (!$coa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data COA tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            if ($this->isCoaUsed($coa)) {
                return response()->json([
                    'success' => false,
                    'message' => 'COA tidak boleh dihapus karena sudah dipakai pada program kerja',
                    'data' => null,
                ], 422);
            }

            $hasActiveChildren = $coa->children()
                ->active()
                ->exists();

            if ($hasActiveChildren) {
                return response()->json([
                    'success' => false,
                    'message' => 'COA tidak boleh dihapus karena masih memiliki sub COA aktif',
                    'data' => null,
                ], 422);
            }

            $coa->update([
                'IS_DELETE' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'COA berhasil dihapus',
                'data' => null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus COA',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function parents(): JsonResponse
    {
        try {
            $data = MstCoa::query()
                ->active()
                ->orderBy('ID_MASTER_COA', 'desc')
                ->get([
                    'ID_MASTER_COA',
                    'KODE_COA',
                    'DESKRIPSI_COA',
                ])
                ->map(function ($item) {
                    return [
                        'value' => $item->ID_MASTER_COA,
                        'label' => $item->KODE_COA . ' - ' . $item->DESKRIPSI_COA,
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Data parent COA berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil parent COA',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = MstCoa::query()
            ->with(['parent'])
            ->active()
            ->orderBy('ID_MASTER_COA', 'desc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('KODE_COA', 'like', "%{$search}%")
                  ->orWhere('DESKRIPSI_COA', 'like', "%{$search}%");
            });
        }

        $data = $query->get();

        $filename = 'mst_coa_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($data) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'ID_MASTER_COA',
                'MST_ID_MASTER_COA',
                'KODE_COA',
                'DESKRIPSI_COA',
                'PARENT_KODE_COA',
                'PARENT_DESKRIPSI_COA',
                'IS_DELETE',
            ]);

            foreach ($data as $item) {
                fputcsv($handle, [
                    $item->ID_MASTER_COA,
                    $item->MST_ID_MASTER_COA,
                    $item->KODE_COA,
                    $item->DESKRIPSI_COA,
                    optional($item->parent)->KODE_COA,
                    optional($item->parent)->DESKRIPSI_COA,
                    $item->IS_DELETE,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['search']);

        return Excel::download(new MstCoaExport($filters), 'mst_coa.xlsx');
    }


    public function exportPdf(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = MstCoa::query()
            ->with(['parent'])
            ->active()
            ->orderBy('ID_MASTER_COA', 'desc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('KODE_COA', 'like', "%{$search}%")
                ->orWhere('DESKRIPSI_COA', 'like', "%{$search}%");
            });
        }

        $data = $query->get();

        $pdf = Pdf::loadView('exports.mst_coa_pdf', compact('data'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('mst_coa.pdf');
    }

    private function generateNextCoaCode(?int $parentId = null, ?int $excludeId = null): string
    {
        if (is_null($parentId)) {
            $query = MstCoa::query()
                ->active()
                ->whereNull('MST_ID_MASTER_COA');

            if (!is_null($excludeId)) {
                $query->where('ID_MASTER_COA', '!=', $excludeId);
            }

            $lastRoot = $query->orderByDesc('ID_MASTER_COA')->first();

            if (!$lastRoot) {
                return '1';
            }

            return (string) (((int) $lastRoot->KODE_COA) + 1);
        }

        $parent = MstCoa::query()->active()->findOrFail($parentId);

        $childrenQuery = MstCoa::query()
            ->active()
            ->where('MST_ID_MASTER_COA', $parentId);

        if (!is_null($excludeId)) {
            $childrenQuery->where('ID_MASTER_COA', '!=', $excludeId);
        }

        $children = $childrenQuery->orderBy('KODE_COA', 'asc')->get();

        if ($children->isEmpty()) {
            return $parent->KODE_COA . '.1';
        }

        $lastNumbers = $children->map(function ($item) {
            $parts = explode('.', $item->KODE_COA);
            return (int) end($parts);
        });

        $nextNumber = $lastNumbers->max() + 1;

        return $parent->KODE_COA . '.' . $nextNumber;
    }

    private function isDescendant(int $parentCandidateId, int $currentNodeId): bool
    {
        $current = MstCoa::query()->find($parentCandidateId);

        while ($current) {
            if ((int) $current->ID_MASTER_COA === $currentNodeId) {
                return true;
            }

            if (is_null($current->MST_ID_MASTER_COA)) {
                return false;
            }

            $current = MstCoa::query()->find($current->MST_ID_MASTER_COA);
        }

        return false;
    }

    private function isCoaUsed(MstCoa $coa): bool
    {
        return $coa->programKerja()->exists();
    }
}