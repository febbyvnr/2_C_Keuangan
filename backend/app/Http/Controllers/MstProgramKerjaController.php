<?php

namespace App\Http\Controllers;

use App\Models\MstProgramKerja;
use App\Models\FpdAnggaran;
use App\Models\TrPm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Exports\MstProgramKerjaExport;
use Maatwebsite\Excel\Facades\Excel;

class MstProgramKerjaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = MstProgramKerja::with([
                'tahunAnggaran',
                'unit',
                'tan',
                'coa',
                'kegiatan',
                'trPm',
                'detailProgramKerja',
            ])
            ->leftJoin(
                'mst_karyawan as validator',
                'mst_program_kerja.NIP_VALIDATOR_PROGKER',
                '=',
                'validator.NIP_KARYAWAN'
            )
            ->select(
                'mst_program_kerja.*',
                'validator.NAMA_KARYAWAN as NAMA_VALIDATOR',
                'validator.JABATAN_FUNGSIONAL as JABATAN_VALIDATOR'
            )
            ->where('mst_program_kerja.IS_DELETE', 0);

            if ($request->filled('ID_TA_ANGGARAN')) {
                $query->where('mst_program_kerja.ID_TA_ANGGARAN', $request->ID_TA_ANGGARAN);
            }

            if ($request->filled('ID_UNIT')) {
               $query->where('mst_program_kerja.ID_UNIT', $request->ID_UNIT);
            }

            if ($request->filled('ID_TAN')) {
                $query->where('mst_program_kerja.ID_TAN', $request->ID_TAN);
            }

            if ($request->filled('ID_MASTER_COA')) {
                $query->where('mst_program_kerja.ID_MASTER_COA', $request->ID_MASTER_COA);
            }

            if ($request->filled('ID_KEGIATAN')) {
                $query->where('mst_program_kerja.ID_KEGIATAN', $request->ID_KEGIATAN);
            }

            if ($request->filled('NIP_PENANGGUNG_JAWAB')) {
                $query->where('mst_program_kerja.NIP_PENANGGUNG_JAWAB', $request->NIP_PENANGGUNG_JAWAB);
            }

            if ($request->filled('search')) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('mst_program_kerja.PROGRAM_KERJA', 'like', '%' . $search . '%')
                        ->orWhere('mst_program_kerja.INDIKATOR', 'like', '%' . $search . '%')
                        ->orWhere('mst_program_kerja.SASARAN', 'like', '%' . $search . '%')
                        ->orWhere('mst_program_kerja.KELUARAN_PROGKER', 'like', '%' . $search . '%');
                });
            }

            $data = $query
                ->orderByDesc('mst_program_kerja.ID_PROGRAM_KERJA')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data program kerja berhasil diambil',
                'data' => $data,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data program kerja',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = MstProgramKerja::with([
                'tahunAnggaran',
                'unit',
                'tan',
                'coa',
                'kegiatan',
                'detailProgramKerja',
                'trPm',
            ])->active()->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail program kerja berhasil diambil',
                'data' => $data,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Program kerja tidak ditemukan',
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail program kerja',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ID_TA_ANGGARAN' => [
                'required',
                'integer',
                Rule::exists('ref_tahun_anggaran', 'ID_TA_ANGGARAN'),
            ],
            'ID_UNIT' => [
                'nullable',
                'integer',
                Rule::exists('mst_unit', 'ID_UNIT'),
            ],
            'ID_TAN' => [
                'nullable',
                'integer',
                Rule::exists('ref_tan', 'ID_TAN'),
            ],
            'ID_MASTER_COA' => [
                'required',
                'integer',
                Rule::exists('mst_coa', 'ID_MASTER_COA'),
            ],
            'ID_KEGIATAN' => [
                'required',
                'integer',
                Rule::exists('mst_kegiatan', 'ID_KEGIATAN'),
            ],
            'TOTAL_PROGKER' => [
                'required',
                'numeric',
                'min:0',
            ],
            'INDIKATOR' => [
                'nullable',
                'string',
                'max:100',
            ],
            'SASARAN' => [
                'nullable',
                'string',
                'max:100',
            ],
            'WAKTU_AWAL' => [
                'required',
                'date',
            ],
            'WAKTU_AKHIR' => [
                'required',
                'date',
                'after_or_equal:WAKTU_AWAL',
            ],
            'KELUARAN_PROGKER' => [
                'nullable',
                'string',
                'max:100',
            ],
            'PROGRAM_KERJA' => [
                'required',
                'string',
                'max:255',
            ],
            'NIP_PENANGGUNG_JAWAB' => [
                'required',
                'string',
                'max:20',
            ],
            'AKSI' => ['required', 'in:DRAFT,AJUKAN'],
        ]);

        $aksi = $validated['AKSI'];
        unset($validated['AKSI']);

        if ($aksi === 'AJUKAN') {
            return response()->json([
                'success' => false,
                'message' => 'RKT belum bisa langsung diajukan. Simpan draft lalu lengkapi RKA terlebih dahulu.',
            ], 422);
        }

        try {
            $data = DB::transaction(function () use ($validated, $aksi) {
                $programKerja = MstProgramKerja::create([
                    'ID_TA_ANGGARAN' => $validated['ID_TA_ANGGARAN'],
                    'ID_UNIT' => $validated['ID_UNIT'] ?? null,
                    'ID_TAN' => $validated['ID_TAN'] ?? null,
                    'ID_MASTER_COA' => $validated['ID_MASTER_COA'],
                    'ID_KEGIATAN' => $validated['ID_KEGIATAN'],
                    'TOTAL_PROGKER' => $validated['TOTAL_PROGKER'],
                    'INDIKATOR' => $validated['INDIKATOR'] ?? null,
                    'SASARAN' => $validated['SASARAN'] ?? null,
                    'WAKTU_AWAL' => $validated['WAKTU_AWAL'],
                    'WAKTU_AKHIR' => $validated['WAKTU_AKHIR'],
                    'KELUARAN_PROGKER' => $validated['KELUARAN_PROGKER'] ?? null,
                    'PROGRAM_KERJA' => $validated['PROGRAM_KERJA'],
                    'NIP_PENANGGUNG_JAWAB' => $validated['NIP_PENANGGUNG_JAWAB'],
                    'NIP_VALIDATOR_PROGKER' => null,
                    'IS_DELETE' => 0,
                ]);

               $deskripsi = $aksi === 'DRAFT'
                    ? 'Draft: RKT disimpan sebagai draft'
                    : 'Diajukan: RKT diajukan untuk review Kepala Sekolah';

                TrPm::create([
                    'ID_PROGRAM_KERJA' => $programKerja->ID_PROGRAM_KERJA,
                    'NIP_VALIDATOR_PM' => null,
                    'DESKRIPSI_TR_PM' => $deskripsi,
                ]);

                return $programKerja->fresh([
                    'tahunAnggaran',
                    'unit',
                    'tan',
                    'coa',
                    'kegiatan',
                    'detailProgramKerja',
                    'trPm',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Program kerja berhasil ditambahkan',
                'data' => $data,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan program kerja',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'ID_TA_ANGGARAN' => [
                'required',
                'integer',
                Rule::exists('ref_tahun_anggaran', 'ID_TA_ANGGARAN'),
            ],
            'ID_UNIT' => [
                'nullable',
                'integer',
                Rule::exists('mst_unit', 'ID_UNIT'),
            ],
            'ID_TAN' => [
                'nullable',
                'integer',
                Rule::exists('ref_tan', 'ID_TAN'),
            ],
            'ID_MASTER_COA' => [
                'required',
                'integer',
                Rule::exists('mst_coa', 'ID_MASTER_COA'),
            ],
            'ID_KEGIATAN' => [
                'required',
                'integer',
                Rule::exists('mst_kegiatan', 'ID_KEGIATAN'),
            ],
            'TOTAL_PROGKER' => [
                'required',
                'numeric',
                'min:0',
            ],
            'INDIKATOR' => [
                'nullable',
                'string',
                'max:100',
            ],
            'SASARAN' => [
                'nullable',
                'string',
                'max:100',
            ],
            'WAKTU_AWAL' => [
                'required',
                'date',
            ],
            'WAKTU_AKHIR' => [
                'required',
                'date',
                'after_or_equal:WAKTU_AWAL',
            ],
            'KELUARAN_PROGKER' => [
                'nullable',
                'string',
                'max:100',
            ],
            'PROGRAM_KERJA' => [
                'required',
                'string',
                'max:255',
            ],
            'NIP_PENANGGUNG_JAWAB' => [
                'required',
                'string',
                'max:20',
            ],
        ]);

        try {
            $data = DB::transaction(function () use ($validated, $id, $request) {
                $programKerja = MstProgramKerja::with(['trPm'])
                    ->active()
                    ->findOrFail($id);

                $ownershipError = $this->ensureOwnedByUser($programKerja, $request);
                if ($ownershipError) {
                    return $ownershipError;
                }

                $lastPm = $programKerja->trPm
                    ->sortByDesc('ID_PM')
                    ->first();

                $lastNote = strtolower($lastPm->DESKRIPSI_TR_PM ?? '');

                if ($programKerja->NIP_VALIDATOR_PROGKER) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Program kerja sudah disetujui, tidak bisa diubah.'
                    ], 422);
                }

                if (str_contains($lastNote, 'ditolak') || str_contains($lastNote, 'tolak')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Program kerja sudah ditolak, tidak bisa diubah.'
                    ], 422);
                }

                $programKerja->update($validated);

                if (str_contains($lastNote, 'revisi')) {
                    TrPm::create([
                        'ID_PROGRAM_KERJA' => $programKerja->ID_PROGRAM_KERJA,
                        'NIP_VALIDATOR_PM' => null,
                        'DESKRIPSI_TR_PM' => 'Draft: Perbaikan RKT disimpan, lengkapi/cek RKA sebelum diajukan ulang',
                    ]);
                }

                return $programKerja->fresh([
                    'tahunAnggaran',
                    'unit',
                    'tan',
                    'coa',
                    'kegiatan',
                    'detailProgramKerja',
                    'trPm',
                ]);
            });

            if ($data instanceof \Illuminate\Http\JsonResponse) {
                return $data;
            }

            return response()->json([
                'success' => true,
                'message' => 'Berhasil update',
                'data' => $data
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $data = MstProgramKerja::with(['trPm'])
                ->active()
                ->findOrFail($id);

            $ownershipError = $this->ensureOwnedByUser($data, $request);
            if ($ownershipError) {
                return $ownershipError;
            }

            $lastPm = $data->trPm
                ->sortByDesc('ID_PM')
                ->first();

            $lastNote = strtolower($lastPm->DESKRIPSI_TR_PM ?? '');

            if ($data->NIP_VALIDATOR_PROGKER) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program kerja sudah disetujui, tidak bisa dihapus.'
                ], 422);
            }

            if (str_starts_with($lastNote, 'ditolak')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program kerja sudah ditolak, tidak bisa dihapus.'
                ], 422);
            }

            if (str_starts_with($lastNote, 'revisi')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program kerja masih revisi, tidak bisa dihapus.'
                ], 422);
            }

            if ($this->isProgramKerjaUsedForDelete($id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sudah digunakan, tidak bisa dihapus.'
                ], 422);
            }

            $data->update([
                'IS_DELETE' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil hapus'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal hapus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'NIP_VALIDATOR_PROGKER' => [
                'required',
                'string',
                'max:20',
            ],
        ]);

        try {
            $data = DB::transaction(function () use ($validated, $id) {
                $programKerja = MstProgramKerja::with(['trPm'])
                    ->active()
                    ->findOrFail($id);

                $bendaharaValidator = false;

                if ($programKerja->NIP_VALIDATOR_PROGKER) {
                    $bendahara = DB::table('mst_karyawan as mk')
                        ->join('tr_jabatan as tj', 'mk.NIP_KARYAWAN', '=', 'tj.NIP_KARYAWAN')
                        ->join('ref_jabatan_str as rj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
                        ->where('mk.NIP_KARYAWAN', $programKerja->NIP_VALIDATOR_PROGKER)
                        ->whereNull('tj.TGL_SELESAI_JABATAN')
                        ->where('rj.DESKRIPSI_JABATAN', 'like', '%Bendahara%')
                        ->exists();

                    $bendaharaValidator = $bendahara;
                }

                $validator = DB::table('mst_karyawan as mk')
                    ->join('tr_jabatan as tj', 'mk.NIP_KARYAWAN', '=', 'tj.NIP_KARYAWAN')
                    ->join('ref_jabatan_str as rj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
                    ->where('mk.NIP_KARYAWAN', $validated['NIP_VALIDATOR_PROGKER'])
                    ->whereNull('tj.TGL_SELESAI_JABATAN')
                    ->where('rj.DESKRIPSI_JABATAN', 'Kepala Sekolah')
                    ->select(
                        'mk.NIP_KARYAWAN',
                        'mk.NAMA_LENGKAP_GELAR',
                        'mk.JABATAN_FUNGSIONAL',
                        'rj.DESKRIPSI_JABATAN as ROLE_AKSES'
                    )
                    ->first();

                if (!$validator) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Hanya Kepala Sekolah yang boleh menyetujui RKT.',
                    ], 422);
                }

                if ($programKerja->NIP_VALIDATOR_PROGKER && !$bendaharaValidator) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Program kerja sudah disetujui Kepala Sekolah.',
                    ], 422);
                }
                $lastPm = $programKerja->trPm
                    ->sortByDesc('ID_PM')
                    ->first();

                $lastNote = strtolower($lastPm->DESKRIPSI_TR_PM ?? '');

                $isSubmitted =
                    str_contains($lastNote, 'diajukan') ||
                    str_contains($lastNote, 'pengajuan') ||
                    str_contains($lastNote, 'disetujui') ||
                    !empty($programKerja->NIP_VALIDATOR_PROGKER);

                if (!$isSubmitted) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Hanya RKT yang sudah diajukan yang bisa disetujui.',
                    ], 422);
                }

                if (str_starts_with($lastNote, 'ditolak')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Program kerja sudah ditolak, tidak bisa di-approve.',
                    ], 422);
                }

                if (str_starts_with($lastNote, 'revisi')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Program kerja masih revisi, tidak bisa di-approve.',
                    ], 422);
                }

                $programKerja->update([
                    'NIP_VALIDATOR_PROGKER' => $validated['NIP_VALIDATOR_PROGKER'],
                ]);

                TrPm::create([
                    'ID_PROGRAM_KERJA' => $programKerja->ID_PROGRAM_KERJA,
                    'NIP_VALIDATOR_PM' => $validated['NIP_VALIDATOR_PROGKER'],
                    'DESKRIPSI_TR_PM' => 'Disetujui: RKT disetujui Kepala Sekolah',
                ]);

                return $programKerja->fresh([
                    'tahunAnggaran',
                    'unit',
                    'tan',
                    'coa',
                    'kegiatan',
                    'detailProgramKerja',
                    'trPm',
                ]);
            });

            if ($data instanceof \Illuminate\Http\JsonResponse) {
                return $data;
            }

            return response()->json([
                'success' => true,
                'message' => 'RKT berhasil disetujui Kepala Sekolah',
                'data' => $data,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal approve',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'NIP_VALIDATOR_PM' => 'required|string',
            'DESKRIPSI' => 'required|string',
        ]);

        try {
            $program = MstProgramKerja::findOrFail($id);

            $validator = DB::table('mst_karyawan as mk')
                ->join('tr_jabatan as tj', 'mk.NIP_KARYAWAN', '=', 'tj.NIP_KARYAWAN')
                ->join('ref_jabatan_str as rj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
                ->where('mk.NIP_KARYAWAN', $request->NIP_VALIDATOR_PM)
                ->whereNull('tj.TGL_SELESAI_JABATAN')
                ->where('rj.DESKRIPSI_JABATAN', 'Kepala Sekolah')
                ->select(
                    'mk.NIP_KARYAWAN',
                    'mk.NAMA_LENGKAP_GELAR',
                    'mk.JABATAN_FUNGSIONAL',
                    'rj.DESKRIPSI_JABATAN as ROLE_AKSES'
                )
                ->first();

            if (!$validator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Kepala Sekolah yang boleh menolak RKT.',
                ], 422);
            }

            TrPm::create([
                'ID_PROGRAM_KERJA' => $id,
                'NIP_VALIDATOR_PM' => $request->NIP_VALIDATOR_PM,
                'DESKRIPSI_TR_PM' => 'Ditolak: ' . $request->DESKRIPSI,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Program kerja berhasil ditolak',
                'data' => $program->fresh([
                    'tahunAnggaran',
                    'unit',
                    'tan',
                    'coa',
                    'kegiatan',
                    'detailProgramKerja',
                    'trPm',
                ]),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak program kerja',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function revisi(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'NIP_VALIDATOR_PM' => 'required|string',
            'DESKRIPSI' => 'required|string',
        ]);

        try {
            $program = MstProgramKerja::with(['trPm'])
                ->where('IS_DELETE', 0)
                ->findOrFail($id);

            $validator = DB::table('mst_karyawan as mk')
                ->join('tr_jabatan as tj', 'mk.NIP_KARYAWAN', '=', 'tj.NIP_KARYAWAN')
                ->join('ref_jabatan_str as rj', 'tj.ID_JABATAN', '=', 'rj.ID_JABATAN')
                ->where('mk.NIP_KARYAWAN', $request->NIP_VALIDATOR_PM)
                ->whereNull('tj.TGL_SELESAI_JABATAN')
                ->where('rj.DESKRIPSI_JABATAN', 'Kepala Sekolah')
                ->select(
                    'mk.NIP_KARYAWAN',
                    'mk.NAMA_LENGKAP_GELAR',
                    'mk.JABATAN_FUNGSIONAL',
                    'rj.DESKRIPSI_JABATAN as ROLE_AKSES'
                )
                ->first();

            if (!$validator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Kepala Sekolah yang boleh mengajukan revisi RKT.',
                ], 422);
            }

            if ($program->NIP_VALIDATOR_PROGKER) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program kerja sudah disetujui Kepala Sekolah, tidak bisa direvisi.',
                ], 422);
            }

            $lastPm = $program->trPm
                ->sortByDesc('ID_PM')
                ->first();

            $lastNote = strtolower(trim($lastPm->DESKRIPSI_TR_PM ?? ''));

            if (!str_starts_with($lastNote, 'diajukan')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya RKT yang sedang diajukan yang bisa diminta revisi.',
                ], 422);
            }

            TrPm::create([
                'ID_PROGRAM_KERJA' => $id,
                'NIP_VALIDATOR_PM' => $request->NIP_VALIDATOR_PM,
                'DESKRIPSI_TR_PM' => 'Revisi: ' . $request->DESKRIPSI,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Program kerja masuk tahap revisi',
                'data' => $program->fresh([
                    'tahunAnggaran',
                    'unit',
                    'tan',
                    'coa',
                    'kegiatan',
                    'detailProgramKerja',
                    'trPm',
                ]),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengajukan revisi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only([
            'search',
            'ID_TAN',
            'ID_TA_ANGGARAN',
        ]);

        return Excel::download(
            new MstProgramKerjaExport($filters),
            'rkt.xlsx'
        );
    }

    private function isProgramKerjaUsed(int $id): bool
    {
        $data = MstProgramKerja::withCount([
            'detailProgramKerja',
            'trPm',
        ])->find($id);

        if (!$data) {
            return false;
        }

        $hasFpd = FpdAnggaran::where('ID_PROGRAM_KERJA', $id)->exists();

        return $data->detail_program_kerja_count > 0
            || $data->tr_pm_count > 0
            || $hasFpd;
    }

    private function isProgramKerjaUsedForUpdate(int $id): bool
    {
        $data = MstProgramKerja::withCount([
            'detailProgramKerja',
        ])->find($id);

        if (!$data) {
            return false;
        }

        $hasFpd = FpdAnggaran::where('ID_PROGRAM_KERJA', $id)->exists();

        return $data->detail_program_kerja_count > 0 || $hasFpd;
    }

    private function isProgramKerjaUsedForDelete(int $id): bool
    {
        $data = MstProgramKerja::withCount([
            'detailProgramKerja',
        ])->find($id);

        if (!$data) {
            return false;
        }

        $hasFpd = FpdAnggaran::where('ID_PROGRAM_KERJA', $id)->exists();

        return $data->detail_program_kerja_count > 0 || $hasFpd;
    }


    public function ajukan(Request $request, $id): JsonResponse
    {
        try {
            $programKerja = MstProgramKerja::with(['trPm', 'detailProgramKerja'])
                ->where('ID_PROGRAM_KERJA', $id)
                ->where('IS_DELETE', 0)
                ->firstOrFail();

            $ownershipError = $this->ensureOwnedByUser($programKerja, $request);
            if ($ownershipError) {
                return $ownershipError;
            }

            if ($programKerja->NIP_VALIDATOR_PROGKER) {
                return response()->json([
                    'success' => false,
                    'message' => 'RKT sudah disetujui dan terkunci.',
                ], 422);
            }

            $lastPm = $programKerja->trPm
                ->sortByDesc('ID_PM')
                ->first();

            $lastNote = strtolower(trim($lastPm->DESKRIPSI_TR_PM ?? ''));

            if (!str_starts_with($lastNote, 'draft')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya RKT berstatus draft yang bisa diajukan.',
                ], 422);
            }

            $paguRkt = (float) $programKerja->TOTAL_PROGKER;

            $totalRka = $programKerja->detailProgramKerja
                ->sum(function ($detail) {
                    return (float) $detail->NOMINAL;
                });

            $minimalRka = $paguRkt * 0.95;

            if ($totalRka <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'RKT belum bisa diajukan. Lengkapi RKA terlebih dahulu.',
                ], 422);
            }

            if ($totalRka > $paguRkt) {
                return response()->json([
                    'success' => false,
                    'message' => 'RKT belum bisa diajukan. Total RKA melebihi pagu RKT.',
                ], 422);
            }

            if ($totalRka < $minimalRka) {
                return response()->json([
                    'success' => false,
                    'message' => 'RKT belum bisa diajukan. Total RKA minimal harus 95% dari pagu RKT.',
                ], 422);
            }

            TrPm::create([
                'ID_PROGRAM_KERJA' => $programKerja->ID_PROGRAM_KERJA,
                'NIP_VALIDATOR_PM' => null,
                'DESKRIPSI_TR_PM' => 'Diajukan: RKT dan RKA diajukan untuk review Kepala Sekolah',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'RKT dan RKA berhasil diajukan ke Kepala Sekolah.',
                'data' => $programKerja->fresh(['trPm', 'detailProgramKerja']),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengajukan RKT.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    private function ensureOwnedByUser($programKerja, Request $request): ?JsonResponse
    {
        $nipLogin = $request->input('NIP_LOGIN');

        if (!$nipLogin) {
            return response()->json([
                'success' => false,
                'message' => 'NIP login wajib dikirim.',
            ], 401);
        }

        if ((string) $programKerja->NIP_PENANGGUNG_JAWAB !== (string) $nipLogin) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke RKT ini.',
            ], 403);
        }

        return null;
    }

    public function readyForApproval(Request $request): JsonResponse
    {
        try {
            $data = MstProgramKerja::with([
                'tahunAnggaran',
                'unit',
                'tan',
                'coa',
                'kegiatan',
                'trPm',
                'detailProgramKerja',
            ])
            ->where('IS_DELETE', 0)
            ->get()
            ->filter(function ($item) {
                $totalRkt = (float) $item->TOTAL_PROGKER;
                $totalRka = collect($item->detailProgramKerja)->sum(function ($detail) {
                    $qty = $detail->QTY
                        ?? $detail->KUANTITAS
                        ?? $detail->JUMLAH
                        ?? 0;
                    $harga = $detail->HARGA
                        ?? $detail->HARGA_SATUAN
                        ?? $detail->NOMINAL
                        ?? 0;
                    return $detail->TOTAL
                        ?? $detail->TOTAL_RINCIAN
                        ?? $detail->SUBTOTAL
                        ?? ($qty * $harga);
                });
                $minimalRka = $totalRkt * 0.95;
                return $totalRka >= $minimalRka
                    && $totalRka <= $totalRkt;
            })
            ->values();
            return response()->json([
                'success' => true,
                'message' => 'Data RKT siap approval berhasil diambil',
                'data' => $data,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}