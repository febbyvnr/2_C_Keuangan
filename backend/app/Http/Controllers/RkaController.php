<?php

namespace App\Http\Controllers;

use App\Models\Rka;
use App\Models\RkaDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RkaController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $data = Rka::with('details')
                ->where(function ($query) {
                    $query->whereNull('IS_DELETE')
                          ->orWhere('IS_DELETE', 0);
                })
                ->get();

            return response()->json([
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data RKA tidak ditemukan'
                    : 'Data RKA berhasil diambil',
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

            $query = Rka::with('details')
                ->where(function ($q) {
                    $q->whereNull('IS_DELETE')
                      ->orWhere('IS_DELETE', 0);
                });

            if ($keyword !== '') {
                $query->where(function ($q) use ($keyword) {
                    $q->where('PROGRAM_KERJA', 'like', "%{$keyword}%")
                        ->orWhere('INDIKATOR', 'like', "%{$keyword}%")
                        ->orWhere('SASARAN', 'like', "%{$keyword}%")
                        ->orWhere('KELUARAN_PROGKER', 'like', "%{$keyword}%")
                        ->orWhere('NIP_PENANGGUNG_JAWAB', 'like', "%{$keyword}%")
                        ->orWhere('ID_KEGIATAN', 'like', "%{$keyword}%")
                        ->orWhere('ID_MASTER_COA', 'like', "%{$keyword}%")
                        ->orWhere('ID_TA_ANGGARAN', 'like', "%{$keyword}%");
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

            $data = Rka::with('details')
                ->where(function ($query) {
                    $query->whereNull('IS_DELETE')
                          ->orWhere('IS_DELETE', 0);
                })
                ->find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail RKA berhasil diambil',
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
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'ID_TA_ANGGARAN' => 'required|integer',
                'ID_UNIT' => 'nullable|integer',
                'ID_TAN' => 'nullable|integer',
                'ID_MASTER_COA' => 'required|integer',
                'ID_KEGIATAN' => 'required|integer',
                'INDIKATOR' => 'nullable|string|max:100',
                'SASARAN' => 'nullable|string|max:100',
                'WAKTU_AWAL' => 'nullable|date',
                'WAKTU_AKHIR' => 'nullable|date',
                'KELUARAN_PROGKER' => 'nullable|string|max:100',
                'PROGRAM_KERJA' => 'required|string|max:255',
                'NIP_PENANGGUNG_JAWAB' => 'required|string|max:20',
                'details' => 'required|array|min:1',
                'details.*.ID_REF_DANA' => 'required|integer',
                'details.*.NOMINAL' => 'nullable|numeric|min:0',
                'details.*.TGL_AWAL' => 'nullable|date',
                'details.*.TGL_AKHIR' => 'nullable|date',
                'details.*.QTY' => 'nullable|integer|min:0',
                'details.*.HARGA_SATUAN' => 'required|numeric|min:0',
                'details.*.VOLUME' => 'nullable|integer|min:0',
                'details.*.SATUAN' => 'nullable|string|max:10',
            ]);

            $lastId = Rka::max('ID_PROGRAM_KERJA');
            $newId = $lastId ? ((int) $lastId + 1) : 1;

            $rka = Rka::create([
                'ID_PROGRAM_KERJA' => $newId,
                'ID_TA_ANGGARAN' => $validated['ID_TA_ANGGARAN'],
                'ID_UNIT' => $validated['ID_UNIT'] ?? null,
                'ID_TAN' => $validated['ID_TAN'] ?? null,
                'ID_MASTER_COA' => $validated['ID_MASTER_COA'],
                'ID_KEGIATAN' => $validated['ID_KEGIATAN'],
                'NOMINAL' => 0,
                'INDIKATOR' => $validated['INDIKATOR'] ?? null,
                'SASARAN' => $validated['SASARAN'] ?? null,
                'WAKTU_AWAL' => $validated['WAKTU_AWAL'] ?? null,
                'WAKTU_AKHIR' => $validated['WAKTU_AKHIR'] ?? null,
                'KELUARAN_PROGKER' => $validated['KELUARAN_PROGKER'] ?? null,
                'PROGRAM_KERJA' => $validated['PROGRAM_KERJA'],
                'NIP_PENANGGUNG_JAWAB' => $validated['NIP_PENANGGUNG_JAWAB'],
                'IS_DELETE' => 0,
            ]);

            $total = 0;
            $lastDetailId = RkaDetail::max('ID_DT_PROGKER');
            $nextDetailId = $lastDetailId ? ((int) $lastDetailId + 1) : 1;

            foreach ($validated['details'] as $detail) {
                $qty = (int) ($detail['QTY'] ?? 0);
                $volume = (int) ($detail['VOLUME'] ?? 0);
                $hargaSatuan = (float) $detail['HARGA_SATUAN'];

                $pengali = $volume > 0 ? $volume : ($qty > 0 ? $qty : 1);
                $subtotal = $pengali * $hargaSatuan;

                $savedDetail = RkaDetail::create([
                    'ID_DT_PROGKER' => $nextDetailId++,
                    'ID_PROGRAM_KERJA' => $rka->ID_PROGRAM_KERJA,
                    'ID_REF_DANA' => $detail['ID_REF_DANA'],
                    'NOMINAL' => $detail['NOMINAL'] ?? $subtotal,
                    'TGL_AWAL' => $detail['TGL_AWAL'] ?? null,
                    'TGL_AKHIR' => $detail['TGL_AKHIR'] ?? null,
                    'QTY' => $qty > 0 ? $qty : null,
                    'HARGA_SATUAN' => $hargaSatuan,
                    'VOLUME' => $volume > 0 ? $volume : null,
                    'SATUAN' => $detail['SATUAN'] ?? null,
                    'TOTAL_PROGKER' => $subtotal,
                ]);

                $total += (float) $savedDetail->TOTAL_PROGKER;
            }

            $rka->update([
                'NOMINAL' => $total,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'RKA berhasil ditambahkan',
                'data' => $rka->load('details'),
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $id = (int) $id;
            $rka = Rka::find($id);

            if (!$rka) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            $validated = $request->validate([
                'ID_TA_ANGGARAN' => 'required|integer',
                'ID_UNIT' => 'nullable|integer',
                'ID_TAN' => 'nullable|integer',
                'ID_MASTER_COA' => 'required|integer',
                'ID_KEGIATAN' => 'required|integer',
                'INDIKATOR' => 'nullable|string|max:100',
                'SASARAN' => 'nullable|string|max:100',
                'WAKTU_AWAL' => 'nullable|date',
                'WAKTU_AKHIR' => 'nullable|date',
                'KELUARAN_PROGKER' => 'nullable|string|max:100',
                'PROGRAM_KERJA' => 'required|string|max:255',
                'NIP_PENANGGUNG_JAWAB' => 'required|string|max:20',
                'details' => 'required|array|min:1',
                'details.*.ID_REF_DANA' => 'required|integer',
                'details.*.NOMINAL' => 'nullable|numeric|min:0',
                'details.*.TGL_AWAL' => 'nullable|date',
                'details.*.TGL_AKHIR' => 'nullable|date',
                'details.*.QTY' => 'nullable|integer|min:0',
                'details.*.HARGA_SATUAN' => 'required|numeric|min:0',
                'details.*.VOLUME' => 'nullable|integer|min:0',
                'details.*.SATUAN' => 'nullable|string|max:10',
            ]);

            $rka->update([
                'ID_TA_ANGGARAN' => $validated['ID_TA_ANGGARAN'],
                'ID_UNIT' => $validated['ID_UNIT'] ?? null,
                'ID_TAN' => $validated['ID_TAN'] ?? null,
                'ID_MASTER_COA' => $validated['ID_MASTER_COA'],
                'ID_KEGIATAN' => $validated['ID_KEGIATAN'],
                'INDIKATOR' => $validated['INDIKATOR'] ?? null,
                'SASARAN' => $validated['SASARAN'] ?? null,
                'WAKTU_AWAL' => $validated['WAKTU_AWAL'] ?? null,
                'WAKTU_AKHIR' => $validated['WAKTU_AKHIR'] ?? null,
                'KELUARAN_PROGKER' => $validated['KELUARAN_PROGKER'] ?? null,
                'PROGRAM_KERJA' => $validated['PROGRAM_KERJA'],
                'NIP_PENANGGUNG_JAWAB' => $validated['NIP_PENANGGUNG_JAWAB'],
            ]);

            RkaDetail::where('ID_PROGRAM_KERJA', $rka->ID_PROGRAM_KERJA)->delete();

            $total = 0;
            $lastDetailId = RkaDetail::max('ID_DT_PROGKER');
            $nextDetailId = $lastDetailId ? ((int) $lastDetailId + 1) : 1;

            foreach ($validated['details'] as $detail) {
                $qty = (int) ($detail['QTY'] ?? 0);
                $volume = (int) ($detail['VOLUME'] ?? 0);
                $hargaSatuan = (float) $detail['HARGA_SATUAN'];

                $pengali = $volume > 0 ? $volume : ($qty > 0 ? $qty : 1);
                $subtotal = $pengali * $hargaSatuan;

                $savedDetail = RkaDetail::create([
                    'ID_DT_PROGKER' => $nextDetailId++,
                    'ID_PROGRAM_KERJA' => $rka->ID_PROGRAM_KERJA,
                    'ID_REF_DANA' => $detail['ID_REF_DANA'],
                    'NOMINAL' => $detail['NOMINAL'] ?? $subtotal,
                    'TGL_AWAL' => $detail['TGL_AWAL'] ?? null,
                    'TGL_AKHIR' => $detail['TGL_AKHIR'] ?? null,
                    'QTY' => $qty > 0 ? $qty : null,
                    'HARGA_SATUAN' => $hargaSatuan,
                    'VOLUME' => $volume > 0 ? $volume : null,
                    'SATUAN' => $detail['SATUAN'] ?? null,
                    'TOTAL_PROGKER' => $subtotal,
                ]);

                $total += (float) $savedDetail->TOTAL_PROGKER;
            }

            $rka->update([
                'NOMINAL' => $total,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate',
                'data' => $rka->load('details'),
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();

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
            $rka = Rka::find($id);

            if (!$rka) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            $rka->update([
                'IS_DELETE' => 1,
            ]);

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