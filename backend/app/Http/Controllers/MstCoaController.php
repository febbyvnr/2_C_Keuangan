<?php

namespace App\Http\Controllers;

use App\Models\MstCoa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class MstCoaController extends Controller
{
    /**
     * Menampilkan daftar COA
     * Bisa search berdasarkan kode atau deskripsi/nama akun
     */
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $query = MstCoa::query()
            ->with(['children'])
            ->where('IS_DELETE', 0)
            ->whereNull('MST_ID_MASTER_COA')
            ->orderBy('KODE_COA', 'asc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('KODE_COA', $search)
                ->orWhere('DESKRIPSI_COA', 'like', "%{$search}%");
            });
        }

        $data = $query->get();

        return response()->json([
            'success' => true,
            'message' => $data->isEmpty()
                ? 'Data COA tidak ditemukan'
                : 'Data COA berhasil diambil',
            'data' => $data,
        ]);
    }

    /**
     * Menampilkan detail COA
     */
    public function show(int $id): JsonResponse
    {
        try {
            $data = MstCoa::query()
                ->with(['parent', 'children', 'programKerja'])
                ->where('IS_DELETE', 0)
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

    /**
     * Menambahkan COA baru
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(
                [
                    'MST_ID_MASTER_COA' => [
                        'nullable',
                        'integer',
                        Rule::exists('mst_coa', 'ID_MASTER_COA')->where(function ($query) {
                            $query->where('IS_DELETE', 0);
                        }),
                    ],
                    'KODE_COA' => [
                        'required',
                        'string',
                        'max:10',
                        'unique:mst_coa,KODE_COA',
                    ],
                    'DESKRIPSI_COA' => [
                        'required',
                        'string',
                        'max:100',
                    ],
                ],
                [
                    'MST_ID_MASTER_COA.exists' => 'Parent COA tidak valid.',
                    'KODE_COA.required' => 'Kode COA wajib diisi.',
                    'KODE_COA.unique' => 'Kode COA sudah digunakan.',
                    'KODE_COA.max' => 'Kode COA maksimal 10 karakter.',
                    'DESKRIPSI_COA.required' => 'Deskripsi COA wajib diisi.',
                    'DESKRIPSI_COA.max' => 'Deskripsi COA maksimal 100 karakter.',
                ]
            );

            $data = DB::transaction(function () use ($validated) {
                $nextId = ((int) MstCoa::max('ID_MASTER_COA')) + 1;

                $coa = MstCoa::create([
                    'ID_MASTER_COA' => $nextId,
                    'MST_ID_MASTER_COA' => $validated['MST_ID_MASTER_COA'] ?? null,
                    'KODE_COA' => $validated['KODE_COA'],
                    'DESKRIPSI_COA' => $validated['DESKRIPSI_COA'],
                    'IS_DELETE' => 0,
                ]);

                return $coa->fresh();
            });

            return response()->json([
                'success' => true,
                'message' => 'COA berhasil ditambahkan',
                'data' => $data,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (QueryException $e) {
            if (
                str_contains(strtolower($e->getMessage()), 'unique') ||
                str_contains(strtolower($e->getMessage()), 'duplicate')
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'KODE_COA sudah digunakan',
                    'errors' => [
                        'KODE_COA' => ['Kode COA sudah digunakan.'],
                    ],
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada database saat menambahkan COA',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan COA',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengubah COA
     * Tidak boleh jika COA sudah dipakai pada program kerja
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $coa = MstCoa::query()
                ->where('IS_DELETE', 0)
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
                    'KODE_COA' => [
                        'required',
                        'string',
                        'max:10',
                        Rule::unique('mst_coa', 'KODE_COA')->ignore($id, 'ID_MASTER_COA'),
                    ],
                    'DESKRIPSI_COA' => [
                        'required',
                        'string',
                        'max:100',
                    ],
                ],
                [
                    'MST_ID_MASTER_COA.exists' => 'Parent COA tidak valid.',
                    'KODE_COA.required' => 'Kode COA wajib diisi.',
                    'KODE_COA.unique' => 'Kode COA sudah digunakan.',
                    'KODE_COA.max' => 'Kode COA maksimal 10 karakter.',
                    'DESKRIPSI_COA.required' => 'Deskripsi COA wajib diisi.',
                    'DESKRIPSI_COA.max' => 'Deskripsi COA maksimal 100 karakter.',
                ]
            );

            if (
                isset($validated['MST_ID_MASTER_COA']) &&
                (int) $validated['MST_ID_MASTER_COA'] === (int) $coa->ID_MASTER_COA
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent COA tidak boleh dirinya sendiri',
                    'data' => null,
                ], 422);
            }

            $coa->update([
                'MST_ID_MASTER_COA' => $validated['MST_ID_MASTER_COA'] ?? null,
                'KODE_COA' => $validated['KODE_COA'],
                'DESKRIPSI_COA' => $validated['DESKRIPSI_COA'],
            ]);

            $coa->refresh();

            return response()->json([
                'success' => true,
                'message' => 'COA berhasil diperbarui',
                'data' => $coa,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (QueryException $e) {
            if (
                str_contains(strtolower($e->getMessage()), 'unique') ||
                str_contains(strtolower($e->getMessage()), 'duplicate')
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'KODE_COA sudah digunakan',
                    'errors' => [
                        'KODE_COA' => ['Kode COA sudah digunakan.'],
                    ],
                ], 422);
            }

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

    /**
     * Menghapus COA (soft delete)
     * Hanya boleh jika belum dipakai program kerja dan tidak punya child aktif
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $coa = MstCoa::query()
                ->with('children')
                ->where('IS_DELETE', 0)
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
                ->where('IS_DELETE', 0)
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

    /**
     * Menampilkan data parent COA untuk dropdown
     */
    public function parents(): JsonResponse
    {
        try {
            $data = MstCoa::query()
                ->where('IS_DELETE', 0)
                ->whereNull('MST_ID_MASTER_COA')
                ->orderBy('KODE_COA', 'asc')
                ->get();

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

    /**
     * Helper untuk cek apakah COA sudah dipakai ato belom
     * Saat ini baru cek program kerja, harusnya besok masih ada cek laporan/transaksi
     */
    private function isCoaUsed(MstCoa $coa): bool
    {
        return method_exists($coa, 'programKerja') && $coa->programKerja()->exists();
    }
}