<?php

namespace App\Http\Controllers;

use App\Models\RefPm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RefPmController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $data = RefPm::with(['trPm.programKerja'])
                ->orderBy('REF_ID_REF_PM', 'asc')
                ->orderBy('NAMA_PM', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data ref PM tidak ditemukan'
                    : 'Data ref PM berhasil diambil',
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
            $keyword = trim((string) $request->query('keyword', ''));

            $query = RefPm::query();

            if ($keyword !== '') {
                $query->where(function ($q) use ($keyword) {
                    $q->where('NAMA_PM', 'like', "%{$keyword}%")
                      ->orWhere('DESKRIPSI_PM', 'like', "%{$keyword}%")
                      ->orWhereHas('trPm.programKerja', function ($q) use ($keyword) {
                          $q->where('PROGRAM_KERJA', 'like', "%{$keyword}%")
                            ->orWhere('INDIKATOR', 'like', "%{$keyword}%");
                      });
                });
            }

            $data = $query->with(['trPm.programKerja'])
                ->orderBy('REF_ID_REF_PM', 'asc')
                ->orderBy('NAMA_PM', 'asc')
                ->get();

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
            $data = RefPm::with([
                'parent' => function ($query) {
                    $query->select('ID_REF_PM', 'NAMA_PM', 'DESKRIPSI_PM');
                },
                'children' => function ($query) {
                    $query->select('ID_REF_PM', 'REF_ID_REF_PM', 'NAMA_PM', 'DESKRIPSI_PM');
                },
                'trPm.programKerja' => function ($query) {
                    $query->select('ID_PROGRAM_KERJA', 'PROGRAM_KERJA', 'INDIKATOR');
                }
            ])->find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ref PM berhasil diambil',
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
                'REF_ID_REF_PM' => 'nullable|integer|exists:ref_pm,ID_REF_PM',
                'NAMA_PM' => 'required|string|max:100',
                'DESKRIPSI_PM' => 'nullable|string|max:500',
            ]);
            $data = RefPm::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'Ref PM berhasil ditambahkan',
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
            $data = RefPm::find($id);
            if (!$data) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }
           $validated = $request->validate([
                'REF_ID_REF_PM' => "nullable|integer|exists:ref_pm,ID_REF_PM|different:ID_REF_PM",
                'NAMA_PM' => 'required|string|max:100',
                'DESKRIPSI_PM' => 'nullable|string|max:500',
            ], [
                'REF_ID_REF_PM.different' => 'Referensi Parent tidak boleh menunjuk ke diri sendiri.'
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

    public function destroy($id): JsonResponse
    {
        try {
            $data = RefPm::find((int)$id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            $isUsedInPm = $data->trPm()->exists();
            if ($isUsedInPm) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak dapat dihapus karena sudah digunakan'
                ], 422);
            }
            $data->delete();
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
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
