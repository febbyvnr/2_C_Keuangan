<?php

namespace App\Http\Controllers;

use App\Models\TrPm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
class TrPmController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $data = TrPm::all();

            return response()->json([
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data TR PM tidak ditemukan'
                    : 'Data TR PM berhasil diambil',
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

            $query = TrPm::query();

            if ($keyword !== '') {
                $query->where(function ($q) use ($keyword) {
                    $q->where('DESKRIPSI_TR_PM', 'like', "%{$keyword}%")
                      ->orWhere('TGL_PM', 'like', "%{$keyword}%");
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
            $data = TrPm::with(['programKerja', 'refPm', 'refVisiMisi'])->find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'TR PM berhasil diambil',
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
                'ID_PROGRAM_KERJA' => 'required|integer',
                'ID_REF_PM' => 'required|integer|exists:ref_pm,ID_REF_PM',
                'TGL_PM' => 'required|date',
                'DESKRIPSI_TR_PM' => 'nullable|string|max:500',
                'NIP_VALIDATOR_PM' => 'required|string|max:20',
                'ID_VISI_MISI' => 'nullable|integer|exists:ref_visi_misi,ID_VISI_MISI',
                'TINGKAT_KESESUAIAN' => 'nullable|in:Sesuai,Kurang Sesuai,Tidak Sesuai',
            ]);
            $programKerja = \App\Models\MstProgramKerja::where('ID_PROGRAM_KERJA', $validated['ID_PROGRAM_KERJA'])
                ->where('IS_DELETE', 0)
                ->first();

            if (!$programKerja) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program kerja tidak ditemukan atau sudah dihapus.',
                ], 404);
            }

            $validatorPm = DB::table('mst_karyawan')
                ->where('NIP_KARYAWAN', $validated['NIP_VALIDATOR_PM'])
                ->first();

            if (!$validatorPm) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validator catatan tidak ditemukan.',
                ], 404);
            }

            $jabatanPm = strtolower($validatorPm->JABATAN_FUNGSIONAL ?? '');

            if (!str_contains($jabatanPm, 'waka') && !str_contains($jabatanPm, 'kepala sekolah')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Wakil Kepala Sekolah atau Kepala Sekolah yang boleh memberi revisi/tolak.',
                ], 422);
            }

            $currentValidator = null;

            if ($programKerja->NIP_VALIDATOR_PROGKER) {
                $currentValidator = \Illuminate\Support\Facades\DB::table('mst_karyawan')
                    ->where('NIP_KARYAWAN', $programKerja->NIP_VALIDATOR_PROGKER)
                    ->first();
            }

            $currentJabatan = strtolower($currentValidator->JABATAN_FUNGSIONAL ?? '');

            if (!$programKerja->NIP_VALIDATOR_PROGKER) {
                if (!str_contains($jabatanPm, 'waka')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Revisi/tolak pertama harus dilakukan oleh Wakil Kepala Sekolah.',
                    ], 422);
                }
            } else {
                if (str_contains($currentJabatan, 'kepala sekolah')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Program kerja sudah final disetujui Kepala Sekolah.',
                    ], 422);
                }

                if (!str_contains($currentJabatan, 'waka')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Status approval sebelumnya tidak valid.',
                    ], 422);
                }

                if (!str_contains($jabatanPm, 'kepala sekolah')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Revisi/tolak lanjutan harus dilakukan oleh Kepala Sekolah.',
                    ], 422);
                }
            }

            $lastPm = TrPm::where('ID_PROGRAM_KERJA', $validated['ID_PROGRAM_KERJA'])
                ->orderByDesc('ID_PM')
                ->first();

            $lastNote = strtolower($lastPm->DESKRIPSI_TR_PM ?? '');

            if (str_starts_with($lastNote, 'ditolak')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program kerja sudah ditolak, tidak bisa diproses lagi.',
                ], 422);
            }


            $data = TrPm::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'TR PM berhasil ditambahkan',
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
            $data = TrPm::find($id);

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
                'DESKRIPSI_TR_PM' => 'nullable|string|max:500',
                'ID_VISI_MISI' => 'nullable|integer|exists:ref_visi_misi,ID_VISI_MISI',
                'TINGKAT_KESESUAIAN' => 'nullable|in:Sesuai,Kurang Sesuai,Tidak Sesuai',
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
            $id = (int) $id;
            $data = TrPm::find($id);

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
