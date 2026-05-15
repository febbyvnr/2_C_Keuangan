<?php

namespace App\Http\Controllers;

use App\Models\FpdAnggaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventarisAPI extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = FpdAnggaran::with([
                'programKerja',
                'detailFpd.detailProgram.sumberDana',
            ])
                ->whereNotNull('NIP_VALIDATOR_FPD')
                ->where('NIP_VALIDATOR_FPD', '!=', 'Ditolak')
                ->orderByDesc('ID_FPD');

            if ($request->filled('tanggal')) {
                $query->where('TGL_FPD', 'like', $request->query('tanggal') . '%');
            }

            if ($request->filled('program_kerja')) {
                $keyword = trim((string) $request->query('program_kerja'));
                $query->whereHas('programKerja', function ($subQuery) use ($keyword) {
                    $subQuery->where('PROGRAM_KERJA', 'like', '%' . $keyword . '%');
                });
            }

            $data = $query->get()
                ->map(fn (FpdAnggaran $fpd) => $this->formatForInventaris($fpd))
                ->values();

            return response()->json([
                'success' => true,
                'message' => $data->isEmpty()
                    ? 'Data FPD approved untuk inventaris tidak ditemukan.'
                    : 'Data FPD approved untuk inventaris berhasil diambil.',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data FPD untuk inventaris.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $fpd = FpdAnggaran::with([
                'programKerja',
                'detailFpd.detailProgram.sumberDana',
            ])
                ->whereNotNull('NIP_VALIDATOR_FPD')
                ->where('NIP_VALIDATOR_FPD', '!=', 'Ditolak')
                ->find($id);

            if (!$fpd) {
                return response()->json([
                    'success' => false,
                    'message' => 'FPD approved untuk inventaris tidak ditemukan.',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail FPD approved untuk inventaris berhasil diambil.',
                'data' => $this->formatForInventaris($fpd),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail FPD untuk inventaris.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function formatForInventaris(FpdAnggaran $fpd): array
    {
        return [
            'ID_FPD' => $fpd->ID_FPD,
            'TGL_FPD' => $fpd->TGL_FPD?->format('Y-m-d'),
            'PROGRAM_KERJA' => $fpd->programKerja?->PROGRAM_KERJA,
            'NIP_VALIDATOR_FPD' => $fpd->NIP_VALIDATOR_FPD,
            'NOMINAL_FPD' => (float) ($fpd->NOMINAL_FPD ?? 0),
            'DETAIL' => $fpd->detailFpd
                ->sortBy('ID_DT_FPD')
                ->map(function ($detail) {
                    $rka = $detail->detailProgram;
                    $sumberDana = $rka?->sumberDana;

                    return [
                        'ID_RKA' => $detail->ID_DT_PROGKER,
                        'ID_DT_PROGKER' => $detail->ID_DT_PROGKER,
                        'QTY' => (int) ($detail->QTY ?? 0),
                        'VOLUME' => (int) ($detail->VOLUME ?? 0),
                        'SATUAN' => $detail->SATUAN,
                        'HARGA_SATUAN' => (float) ($detail->HARGA_SATUAN ?? 0),
                        'TOTAL' => (float) ($detail->TOTAL ?? 0),
                        'SUMBER_DANA' => $sumberDana?->SUMBER_DANA
                            ?? $sumberDana?->DESKRIPSI_SUMBER_DANA
                            ?? $sumberDana?->NAMA_SUMBER_DANA,
                        'LINK_BUKTI_NOTA_FPD' => $detail->LINK_BUKTI_NOTA_FPD,
                    ];
                })
                ->values(),
        ];
    }
}
