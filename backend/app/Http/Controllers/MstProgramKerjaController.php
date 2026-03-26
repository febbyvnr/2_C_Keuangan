<?php

namespace App\Http\Controllers;

use App\Models\MstProgramKerja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;

class MstProgramKerjaController extends Controller
{
    /**
     * Menampilkan daftar program kerja aktif
     * Search berdasarkan program kerja, indikator, sasaran
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = trim((string) $request->query('search', ''));

            $query = MstProgramKerja::query()
                ->with([
                    'tahunAnggaran',
                    'unit',
                    'tan',
                    'coa',
                    'kegiatan',
                    'detailProgramKerja',
                    'trPm',
                ])
                ->where('IS_DELETE', 0)
                ->orderBy('ID_PROGRAM_KERJA', 'asc');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('PROGRAM_KERJA', 'like', "%{$search}%")
                        ->orWhere('INDIKATOR', 'like', "%{$search}%")
                        ->orWhere('SASARAN', 'like', "%{$search}%")
                        ->orWhere('KELUARAN_PROGKER', 'like', "%{$search}%")
                        ->orWhere('NIP_PENANGGUNG_JAWAB', 'like', "%{$search}%");
                });
            }

            $data = $query->get();

            return response()->json([
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data program kerja tidak ditemukan'
                    : 'Data program kerja berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data program kerja',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan detail program kerja
     */
    public function show(int $id): JsonResponse
    {
        try {
            $data = MstProgramKerja::query()
                ->with([
                    'tahunAnggaran',
                    'unit',
                    'tan',
                    'coa',
                    'kegiatan',
                    'detailProgramKerja',
                    'trPm',
                ])
                ->where('IS_DELETE', 0)
                ->find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data program kerja tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail program kerja berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail program kerja',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menambahkan program kerja baru
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // $validated = $request->validate(
            //     [
            //         'ID_TA_ANGGARAN' => [
            //             'nullable',
            //             'integer',
            //             Rule::exists('ref_tahun_anggaran', 'ID_TA_ANGGARAN'),
            //         ],
            //         'ID_UNIT' => [
            //             'nullable',
            //             'integer',
            //             Rule::exists('mst_unit', 'ID_UNIT'),
            //         ],
            //         'ID_TAN' => [
            //             'nullable',
            //             'integer',
            //             Rule::exists('ref_tan', 'ID_TAN'),
            //         ],
            //         'ID_MASTER_COA' => [
            //             'nullable',
            //             'integer',
            //             Rule::exists('mst_coa', 'ID_MASTER_COA'),
            //         ],
            //         'ID_KEGIATAN' => [
            //             'nullable',
            //             'integer',
            //             Rule::exists('mst_kegiatan', 'ID_KEGIATAN')->where(function ($query) {
            //                 $query->where('IS_DELETE', 0);
            //             }),
            //         ],
            //         'NOMINAL' => [
            //             'nullable',
            //             'numeric',
            //             'min:0',
            //         ],
            //         'INDIKATOR' => [
            //             'nullable',
            //             'string',
            //             'max:100',
            //         ],
            //         'SASARAN' => [
            //             'nullable',
            //             'string',
            //             'max:100',
            //         ],
            //         'WAKTU_AWAL' => [
            //             'nullable',
            //             'date',
            //         ],
            //         'WAKTU_AKHIR' => [
            //             'nullable',
            //             'date',
            //             'after_or_equal:WAKTU_AWAL',
            //         ],
            //         'KELUARAN_PROGKER' => [
            //             'nullable',
            //             'string',
            //             'max:100',
            //         ],
            //         'PROGRAM_KERJA' => [
            //             'required',
            //             'string',
            //             'max:255',
            //         ],
            //         'NIP_PENANGGUNG_JAWAB' => [
            //             'nullable',
            //             'string',
            //             'max:20',
            //         ],
            //     ]
                $validated = $request->validate([
                    'ID_TA_ANGGARAN' => [
                        'required',
                        'integer',
                        Rule::exists('ref_tahun_anggaran', 'ID_TA_ANGGARAN'),
                    ],
                    'ID_UNIT' => [
                        'required',
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
                    'NOMINAL' => [
                        'required',
                        'numeric',
                        'min:0',
                    ],
                    'INDIKATOR' => [
                        'required',
                        'string',
                        'max:100',
                    ],
                    'SASARAN' => [
                        'required',
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
                        'required',
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
                ],
                [
                    'ID_TA_ANGGARAN.exists' => 'Tahun anggaran tidak valid.',
                    'ID_UNIT.exists' => 'Unit tidak valid.',
                    'ID_TAN.exists' => 'TAN tidak valid.',
                    'ID_MASTER_COA.exists' => 'COA tidak valid.',
                    'ID_KEGIATAN.exists' => 'Kegiatan tidak valid.',
                    'NOMINAL.numeric' => 'Nominal harus berupa angka.',
                    'NOMINAL.min' => 'Nominal tidak boleh kurang dari 0.',
                    'INDIKATOR.max' => 'Indikator maksimal 100 karakter.',
                    'SASARAN.max' => 'Sasaran maksimal 100 karakter.',
                    'WAKTU_AWAL.date' => 'Waktu awal harus berupa tanggal yang valid.',
                    'WAKTU_AKHIR.date' => 'Waktu akhir harus berupa tanggal yang valid.',
                    'WAKTU_AKHIR.after_or_equal' => 'Waktu akhir tidak boleh lebih awal dari waktu awal.',
                    'KELUARAN_PROGKER.max' => 'Keluaran program kerja maksimal 100 karakter.',
                    'PROGRAM_KERJA.required' => 'Program kerja wajib diisi.',
                    'PROGRAM_KERJA.max' => 'Program kerja maksimal 255 karakter.',
                    'NIP_PENANGGUNG_JAWAB.max' => 'NIP penanggung jawab maksimal 20 karakter.',
                ]
            );

            $isDuplicate = MstProgramKerja::query()
                ->where('IS_DELETE', 0)
                ->where('PROGRAM_KERJA', $validated['PROGRAM_KERJA'])
                ->where('ID_TA_ANGGARAN', $validated['ID_TA_ANGGARAN'] ?? null)
                ->exists();

            if ($isDuplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => [
                        'PROGRAM_KERJA' => [
                            'Program kerja sudah digunakan pada tahun anggaran tersebut.',
                        ],
                    ],
                ], 422);
            }

            $data = DB::transaction(function () use ($validated) {
                $nextId = ((int) MstProgramKerja::max('ID_PROGRAM_KERJA')) + 1;

                $programKerja = MstProgramKerja::create([
                    'ID_PROGRAM_KERJA' => $nextId,
                    'ID_TA_ANGGARAN' => $validated['ID_TA_ANGGARAN'] ?? null,
                    'ID_UNIT' => $validated['ID_UNIT'] ?? null,
                    'ID_TAN' => $validated['ID_TAN'] ?? null,
                    'ID_MASTER_COA' => $validated['ID_MASTER_COA'] ?? null,
                    'ID_KEGIATAN' => $validated['ID_KEGIATAN'] ?? null,
                    'NOMINAL' => $validated['NOMINAL'] ?? null,
                    'INDIKATOR' => $validated['INDIKATOR'] ?? null,
                    'SASARAN' => $validated['SASARAN'] ?? null,
                    'WAKTU_AWAL' => $validated['WAKTU_AWAL'] ?? null,
                    'WAKTU_AKHIR' => $validated['WAKTU_AKHIR'] ?? null,
                    'KELUARAN_PROGKER' => $validated['KELUARAN_PROGKER'] ?? null,
                    'PROGRAM_KERJA' => $validated['PROGRAM_KERJA'],
                    'NIP_PENANGGUNG_JAWAB' => $validated['NIP_PENANGGUNG_JAWAB'] ?? null,
                    'IS_DELETE' => 0,
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
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada database saat menambahkan program kerja',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan program kerja',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengubah program kerja
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $programKerja = MstProgramKerja::query()
                ->where('IS_DELETE', 0)
                ->find($id);

            if (!$programKerja) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data program kerja tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            // $validated = $request->validate(
            //     [
            //         'ID_TA_ANGGARAN' => [
            //             'nullable',
            //             'integer',
            //             Rule::exists('ref_tahun_anggaran', 'ID_TA_ANGGARAN'),
            //         ],
            //         'ID_UNIT' => [
            //             'nullable',
            //             'integer',
            //             Rule::exists('mst_unit', 'ID_UNIT'),
            //         ],
            //         'ID_TAN' => [
            //             'nullable',
            //             'integer',
            //             Rule::exists('ref_tan', 'ID_TAN'),
            //         ],
            //         'ID_MASTER_COA' => [
            //             'nullable',
            //             'integer',
            //             Rule::exists('mst_coa', 'ID_MASTER_COA'),
            //         ],
            //         'ID_KEGIATAN' => [
            //             'nullable',
            //             'integer',
            //             Rule::exists('mst_kegiatan', 'ID_KEGIATAN')->where(function ($query) {
            //                 $query->where('IS_DELETE', 0);
            //             }),
            //         ],
            //         'NOMINAL' => [
            //             'nullable',
            //             'numeric',
            //             'min:0',
            //         ],
            //         'INDIKATOR' => [
            //             'nullable',
            //             'string',
            //             'max:100',
            //         ],
            //         'SASARAN' => [
            //             'nullable',
            //             'string',
            //             'max:100',
            //         ],
            //         'WAKTU_AWAL' => [
            //             'nullable',
            //             'date',
            //         ],
            //         'WAKTU_AKHIR' => [
            //             'nullable',
            //             'date',
            //             'after_or_equal:WAKTU_AWAL',
            //         ],
            //         'KELUARAN_PROGKER' => [
            //             'nullable',
            //             'string',
            //             'max:100',
            //         ],
            //         'PROGRAM_KERJA' => [
            //             'required',
            //             'string',
            //             'max:255',
            //         ],
            //         'NIP_PENANGGUNG_JAWAB' => [
            //             'nullable',
            //             'string',
            //             'max:20',
            //         ],
            //     ]
                 $validated = $request->validate([
                    'ID_TA_ANGGARAN' => [
                        'required',
                        'integer',
                        Rule::exists('ref_tahun_anggaran', 'ID_TA_ANGGARAN'),
                    ],
                    'ID_UNIT' => [
                        'required',
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
                    'NOMINAL' => [
                        'required',
                        'numeric',
                        'min:0',
                    ],
                    'INDIKATOR' => [
                        'required',
                        'string',
                        'max:100',
                    ],
                    'SASARAN' => [
                        'required',
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
                        'required',
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
                ],
                [
                    'ID_TA_ANGGARAN.exists' => 'Tahun anggaran tidak valid.',
                    'ID_UNIT.exists' => 'Unit tidak valid.',
                    'ID_TAN.exists' => 'TAN tidak valid.',
                    'ID_MASTER_COA.exists' => 'COA tidak valid.',
                    'ID_KEGIATAN.exists' => 'Kegiatan tidak valid.',
                    'NOMINAL.numeric' => 'Nominal harus berupa angka.',
                    'NOMINAL.min' => 'Nominal tidak boleh kurang dari 0.',
                    'INDIKATOR.max' => 'Indikator maksimal 100 karakter.',
                    'SASARAN.max' => 'Sasaran maksimal 100 karakter.',
                    'WAKTU_AWAL.date' => 'Waktu awal harus berupa tanggal yang valid.',
                    'WAKTU_AKHIR.date' => 'Waktu akhir harus berupa tanggal yang valid.',
                    'WAKTU_AKHIR.after_or_equal' => 'Waktu akhir tidak boleh lebih awal dari waktu awal.',
                    'KELUARAN_PROGKER.max' => 'Keluaran program kerja maksimal 100 karakter.',
                    'PROGRAM_KERJA.required' => 'Program kerja wajib diisi.',
                    'PROGRAM_KERJA.max' => 'Program kerja maksimal 255 karakter.',
                    'NIP_PENANGGUNG_JAWAB.max' => 'NIP penanggung jawab maksimal 20 karakter.',
                ]
            );

            $isDuplicate = MstProgramKerja::query()
                ->where('IS_DELETE', 0)
                ->where('PROGRAM_KERJA', $validated['PROGRAM_KERJA'])
                ->where('ID_TA_ANGGARAN', $validated['ID_TA_ANGGARAN'] ?? null)
                ->where('ID_PROGRAM_KERJA', '!=', $id)
                ->exists();

            if ($isDuplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => [
                        'PROGRAM_KERJA' => [
                            'Program kerja sudah digunakan pada tahun anggaran tersebut.',
                        ],
                    ],
                ], 422);
            }

            $programKerja->update([
                'ID_TA_ANGGARAN' => $validated['ID_TA_ANGGARAN'] ?? null,
                'ID_UNIT' => $validated['ID_UNIT'] ?? null,
                'ID_TAN' => $validated['ID_TAN'] ?? null,
                'ID_MASTER_COA' => $validated['ID_MASTER_COA'] ?? null,
                'ID_KEGIATAN' => $validated['ID_KEGIATAN'] ?? null,
                'NOMINAL' => $validated['NOMINAL'] ?? null,
                'INDIKATOR' => $validated['INDIKATOR'] ?? null,
                'SASARAN' => $validated['SASARAN'] ?? null,
                'WAKTU_AWAL' => $validated['WAKTU_AWAL'] ?? null,
                'WAKTU_AKHIR' => $validated['WAKTU_AKHIR'] ?? null,
                'KELUARAN_PROGKER' => $validated['KELUARAN_PROGKER'] ?? null,
                'PROGRAM_KERJA' => $validated['PROGRAM_KERJA'],
                'NIP_PENANGGUNG_JAWAB' => $validated['NIP_PENANGGUNG_JAWAB'] ?? null,
            ]);

            $programKerja->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Program kerja berhasil diperbarui',
                'data' => $programKerja->load([
                    'tahunAnggaran',
                    'unit',
                    'tan',
                    'coa',
                    'kegiatan',
                    'detailProgramKerja',
                    'trPm',
                ]),
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
                'message' => 'Terjadi kesalahan pada database saat mengubah program kerja',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengubah program kerja',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menghapus program kerja (soft delete)
     * Tidak boleh dihapus jika sudah dipakai pada detail program kerja, FPD, atau PM
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $programKerja = MstProgramKerja::query()
                ->with(['detailProgramKerja', 'trPm'])
                ->where('IS_DELETE', 0)
                ->find($id);

            if (!$programKerja) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data program kerja tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            if ($this->isProgramKerjaUsed($programKerja)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program kerja tidak boleh dihapus karena sudah dipakai pada data terkait',
                    'data' => null,
                ], 422);
            }

            $programKerja->update([
                'IS_DELETE' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Program kerja berhasil dihapus',
                'data' => null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus program kerja',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper cek apakah program kerja sudah dipakai
     */
    private function isProgramKerjaUsed(MstProgramKerja $programKerja): bool
    {
        $hasDetail = method_exists($programKerja, 'detailProgramKerja')
            && $programKerja->detailProgramKerja()->exists();

        $hasTrPm = method_exists($programKerja, 'trPm')
            && $programKerja->trPm()->exists();

        $hasFpd = DB::table('fpd_anggaran')
            ->where('ID_PROGRAM_KERJA', $programKerja->ID_PROGRAM_KERJA)
            ->exists();

        return $hasDetail || $hasTrPm || $hasFpd;
    }
}