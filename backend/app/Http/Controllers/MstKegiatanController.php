<?php

namespace App\Http\Controllers;

use App\Models\MstKegiatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;

class MstKegiatanController extends Controller
{
    /**
     * Menampilkan daftar kegiatan aktif
     * Search berdasarkan deskripsi kegiatan
     * List utama hanya parent, child ada di dalam children
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = trim((string) $request->query('search', ''));

            $query = MstKegiatan::query()
                ->with(['children'])
                ->where('IS_DELETE', 0)
                ->whereNull('MST_ID_KEGIATAN')
                ->orderBy('DESKRIPSI_KEGIATAN', 'asc');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('DESKRIPSI_KEGIATAN', 'like', "%{$search}%")
                      ->orWhereHas('children', function ($sub) use ($search) {
                          $sub->where('DESKRIPSI_KEGIATAN', 'like', "%{$search}%");
                      });
                });
            }

            $data = $query->get();

            return response()->json([
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data kegiatan tidak ditemukan'
                    : 'Data kegiatan berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data kegiatan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan detail kegiatan
     */
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

    /**
     * Menambahkan kegiatan baru
     * Untuk kondisi sekarang: deskripsi kegiatan dibuat unik pada data aktif
     */
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

    /**
     * Mengubah kegiatan
     * Tidak boleh jika sudah dipakai pada program kerja
     */
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

            if ($this->isKegiatanUsed($kegiatan)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kegiatan tidak boleh diubah karena sudah dipakai pada program kerja',
                    'data' => null,
                ], 422);
            }

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

    /**
     * Menghapus kegiatan (soft delete)
     * Hanya boleh jika belum dipakai program kerja dan tidak punya child aktif
     */
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

    /**
     * Menampilkan parent kegiatan untuk dropdown
     */
    public function parents(): JsonResponse
    {
        try {
            $data = MstKegiatan::query()
                ->where('IS_DELETE', 0)
                ->whereNull('MST_ID_KEGIATAN')
                ->orderBy('DESKRIPSI_KEGIATAN', 'asc')
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

    /**
     * Helper cek apakah kegiatan sudah dipakai
     * Saat ini relasi yang tersedia baru programKerja
     */
    private function isKegiatanUsed(MstKegiatan $kegiatan): bool
    {
        return method_exists($kegiatan, 'programKerja') && $kegiatan->programKerja()->exists();
    }
}