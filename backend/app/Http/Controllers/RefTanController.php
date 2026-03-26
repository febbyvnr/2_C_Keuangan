<?php

namespace App\Http\Controllers;

use App\Models\RefTan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class RefTanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $search = trim((string) $request->query('search', ''));

            $query = RefTan::query()->orderBy('TAHUN', 'desc');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('TAHUN', 'like', "%{$search}%")
                      ->orWhere('DESKRIPSI_TAN', 'like', "%{$search}%");
                });
            }

            $data = $query->get();

            return response()->json([
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data TAN tidak ditemukan'
                    : 'Data TAN berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data TAN',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function current(): JsonResponse
    {
        try {
            $data = RefTan::query()
                ->where('IS_CURRENT', 1)
                ->orderBy('TAHUN', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data TAN aktif berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data TAN aktif',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = RefTan::find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data TAN tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail TAN berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail TAN',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(
                [
                    'TAHUN' => ['required', 'string', 'size:4'],
                    'IS_CURRENT' => ['nullable', 'boolean'],
                    'DESKRIPSI_TAN' => ['nullable', 'string', 'max:100'],
                ],
                [
                    'TAHUN.required' => 'Tahun wajib diisi.',
                    'TAHUN.size' => 'Tahun harus terdiri dari 4 karakter.',
                    'DESKRIPSI_TAN.max' => 'Deskripsi TAN maksimal 100 karakter.',
                ]
            );

            $isDuplicate = RefTan::query()
                ->where('TAHUN', $validated['TAHUN'])
                ->exists();

            if ($isDuplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => [
                        'TAHUN' => [
                            'Tahun sudah digunakan.',
                        ],
                    ],
                ], 422);
            }

            $data = DB::transaction(function () use ($validated) {
                $nextId = ((int) RefTan::max('ID_TAN')) + 1;

                if (($validated['IS_CURRENT'] ?? false) == true) {
                    RefTan::query()->update([
                        'IS_CURRENT' => 0,
                    ]);
                }

                $tan = RefTan::create([
                    'ID_TAN' => $nextId,
                    'TAHUN' => $validated['TAHUN'],
                    'IS_CURRENT' => $validated['IS_CURRENT'] ?? 0,
                    'DESKRIPSI_TAN' => $validated['DESKRIPSI_TAN'] ?? null,
                ]);

                return $tan->fresh();
            });

            return response()->json([
                'success' => true,
                'message' => 'Data TAN berhasil ditambahkan',
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
                'message' => 'Terjadi kesalahan pada database saat menambahkan data TAN',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan data TAN',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $tan = RefTan::find($id);

            if (!$tan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data TAN tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            $validated = $request->validate(
                [
                    'TAHUN' => ['required', 'string', 'size:4'],
                    'IS_CURRENT' => ['nullable', 'boolean'],
                    'DESKRIPSI_TAN' => ['nullable', 'string', 'max:100'],
                ],
                [
                    'TAHUN.required' => 'Tahun wajib diisi.',
                    'TAHUN.size' => 'Tahun harus terdiri dari 4 karakter.',
                    'DESKRIPSI_TAN.max' => 'Deskripsi TAN maksimal 100 karakter.',
                ]
            );

            $isDuplicate = RefTan::query()
                ->where('TAHUN', $validated['TAHUN'])
                ->where('ID_TAN', '!=', $id)
                ->exists();

            if ($isDuplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => [
                        'TAHUN' => [
                            'Tahun sudah digunakan.',
                        ],
                    ],
                ], 422);
            }

            DB::transaction(function () use ($validated, $tan) {
                if (($validated['IS_CURRENT'] ?? false) == true) {
                    RefTan::query()
                        ->where('ID_TAN', '!=', $tan->ID_TAN)
                        ->update([
                            'IS_CURRENT' => 0,
                        ]);
                }

                $tan->update([
                    'TAHUN' => $validated['TAHUN'],
                    'IS_CURRENT' => $validated['IS_CURRENT'] ?? 0,
                    'DESKRIPSI_TAN' => $validated['DESKRIPSI_TAN'] ?? null,
                ]);
            });

            $tan->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Data TAN berhasil diperbarui',
                'data' => $tan,
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
                'message' => 'Terjadi kesalahan pada database saat mengubah data TAN',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengubah data TAN',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $tan = RefTan::find($id);

            if (!$tan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data TAN tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            if ($tan->IS_CURRENT) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data TAN aktif tidak boleh dihapus',
                    'data' => null,
                ], 422);
            }

            $isUsed = DB::table('mst_program_kerja')
                ->where('ID_TAN', $tan->ID_TAN)
                ->exists();

            if ($isUsed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data TAN tidak dapat dihapus karena sudah digunakan pada program kerja',
                    'data' => null,
                ], 422);
            }

            $tan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data TAN berhasil dihapus',
                'data' => null,
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada database saat menghapus data TAN',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data TAN',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}