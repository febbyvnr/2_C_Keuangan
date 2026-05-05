<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Models\TrPembayaran;
use App\Models\TagihanSiswa;
use Illuminate\Support\Facades\DB;

class TrPembayaranController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = TrPembayaran::with([
                'tahunAnggaran',
                'metodePembayaran',
                'siswa',
                'tagihan.jenisTagihan',
                'cicilan',
            ]);

            if ($request->filled('ID_SISWA_TETAP')) {
                $query->where('ID_SISWA_TETAP', $request->ID_SISWA_TETAP);
            }

            if ($request->filled('ID_TAGIHAN_SISWA')) {
                $query->where('ID_TAGIHAN_SISWA', $request->ID_TAGIHAN_SISWA);
            }

            if ($request->filled('ID_METODE_PEMBAYARAN')) {
                $query->where('ID_JENIS_PEMBAYARAN', $request->ID_METODE_PEMBAYARAN);
            }

            $data = $query->get();

            return response()->json([
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $data = TrPembayaran::with([
                'tahunAnggaran',
                'metodePembayaran',
                'siswa',
                'tagihan.jenisTagihan',
                'cicilan',
            ])->find($id);

            if (!$data) {
                return response()->json([
                    'message' => 'Data pembayaran tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
{
    DB::beginTransaction();

    try {
        $validated = $request->validate([
            'ID_TAGIHAN_SISWA' => 'required|integer|exists:tagihan_siswa,ID_TAGIHAN_SISWA',
            'ID_METODE_PEMBAYARAN' => 'nullable|integer|exists:ref_metode_pembayaran,ID_METODE_PEMBAYARAN',
            'ID_JENIS_PEMBAYARAN' => 'nullable|integer|exists:ref_metode_pembayaran,ID_METODE_PEMBAYARAN',
            'KODE_TA' => 'nullable|integer',
            'REF_ID_JENIS_PEMBAYARAN' => 'nullable|integer',
            'TGL_BAYAR' => 'nullable|date',
            'JUMLAH_BAYAR' => 'required|numeric|min:1',
            'LINK_BUKTI_BAYAR' => 'required|string|max:255',
            'NIP_VALIDATOR_PEMBAYARAN' => 'nullable|string|max:20',
        ]);

        $idMetodePembayaran = $validated['ID_METODE_PEMBAYARAN']
            ?? $validated['ID_JENIS_PEMBAYARAN']
            ?? null;

        if (!$idMetodePembayaran) {
            DB::rollBack();

            return response()->json([
                'message' => 'Metode pembayaran wajib diisi.',
                'errors' => [
                    'ID_METODE_PEMBAYARAN' => ['ID_METODE_PEMBAYARAN wajib diisi.']
                ]
            ], 422);
        }

        $tagihan = TagihanSiswa::with('siswa')->find($validated['ID_TAGIHAN_SISWA']);

        if (!$tagihan) {
            DB::rollBack();

            return response()->json([
                'message' => 'Tagihan tidak ditemukan'
            ], 404);
        }

        $totalTagihan = (float) $tagihan->JUMLAH_TAGIHAN_SISWA;

        $totalSudahBayar = (float) TrPembayaran::where(
            'ID_TAGIHAN_SISWA',
            $validated['ID_TAGIHAN_SISWA']
        )->sum('JUMLAH_BAYAR');

        $jumlahBayarBaru = (float) $validated['JUMLAH_BAYAR'];
        $sisaTagihan = $totalTagihan - $totalSudahBayar;

        if ($jumlahBayarBaru > $sisaTagihan) {
            DB::rollBack();

            return response()->json([
                'message' => 'Pembayaran melebihi sisa tagihan!',
                'data' => [
                    'total_tagihan' => $totalTagihan,
                    'sudah_bayar' => $totalSudahBayar,
                    'sisa_tagihan' => max(0, $sisaTagihan),
                ]
            ], 422);
        }

        $data = TrPembayaran::create([
            'ID_SISWA_TETAP' => $tagihan->ID_SISWA_TETAP,
            'KODE_TA' => optional($tagihan->siswa)->KODE_TA ?? ($validated['KODE_TA'] ?? null),
            'ID_JENIS_PEMBAYARAN' => $idMetodePembayaran,
            'ID_TAGIHAN_SISWA' => $validated['ID_TAGIHAN_SISWA'],
            'REF_ID_JENIS_PEMBAYARAN' => $validated['REF_ID_JENIS_PEMBAYARAN'] ?? $idMetodePembayaran,
            'TGL_BAYAR' => $validated['TGL_BAYAR'] ?? now(),
            'JUMLAH_BAYAR' => $validated['JUMLAH_BAYAR'],
            'LINK_BUKTI_BAYAR' => $validated['LINK_BUKTI_BAYAR'],
            'NIP_VALIDATOR_PEMBAYARAN' => $validated['NIP_VALIDATOR_PEMBAYARAN'] ?? null,
        ]);

        $this->updateStatusTagihan($validated['ID_TAGIHAN_SISWA']);

        $data->load([
            'tahunAnggaran',
            'metodePembayaran',
            'siswa',
            'tagihan.jenisTagihan',
            'cicilan',
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Pembayaran berhasil disimpan',
            'data' => $data
        ], 201);

    } catch (ValidationException $e) {
        DB::rollBack();

        return response()->json([
            'errors' => $e->errors()
        ], 422);
    } catch (\Throwable $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'Terjadi kesalahan',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function search(Request $request): JsonResponse
    {
        try {
            $query = TrPembayaran::with([
                'tahunAnggaran',
                'metodePembayaran',
                'siswa',
                'tagihan.jenisTagihan',
                'cicilan',
            ]);

            if ($request->filled('ID_SISWA_TETAP')) {
                $query->where('ID_SISWA_TETAP', $request->ID_SISWA_TETAP);
            }

            if ($request->filled('KODE_TA')) {
                $query->where('KODE_TA', $request->KODE_TA);
            }

            if ($request->filled('ID_METODE_PEMBAYARAN')) {
                $query->where('ID_JENIS_PEMBAYARAN', $request->ID_METODE_PEMBAYARAN);
            }

            if ($request->filled('ID_TAGIHAN_SISWA')) {
                $query->where('ID_TAGIHAN_SISWA', $request->ID_TAGIHAN_SISWA);
            }

            if ($request->filled('TGL_BAYAR')) {
                $query->whereDate('TGL_BAYAR', $request->TGL_BAYAR);
            }

            if ($request->filled('JUMLAH_BAYAR')) {
                $query->where('JUMLAH_BAYAR', $request->JUMLAH_BAYAR);
            }

            if ($request->filled('NIP_VALIDATOR_PEMBAYARAN')) {
                $query->where(
                    'NIP_VALIDATOR_PEMBAYARAN',
                    'like',
                    '%' . $request->NIP_VALIDATOR_PEMBAYARAN . '%'
                );
            }

            $data = $query->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'data' => $data
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat search',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = TrPembayaran::find($id);

            if (!$data) {
                DB::rollBack();

                return response()->json([
                    'message' => 'Data pembayaran tidak ditemukan'
                ], 404);
            }

            $validated = $request->validate([
                'ID_METODE_PEMBAYARAN' => 'nullable|integer|exists:ref_metode_pembayaran,ID_METODE_PEMBAYARAN',
                'TGL_BAYAR' => 'nullable|date',
                'JUMLAH_BAYAR' => 'nullable|numeric|min:1',
                'LINK_BUKTI_BAYAR' => 'nullable|string|max:255',
                'NIP_VALIDATOR_PEMBAYARAN' => 'nullable|string|max:20',
            ]);

            $updateData = [];

            if (array_key_exists('ID_METODE_PEMBAYARAN', $validated)) {
                $updateData['ID_JENIS_PEMBAYARAN'] = $validated['ID_METODE_PEMBAYARAN'];
                $updateData['REF_ID_JENIS_PEMBAYARAN'] = $validated['ID_METODE_PEMBAYARAN'];
            }

            foreach ([
                'TGL_BAYAR',
                'JUMLAH_BAYAR',
                'LINK_BUKTI_BAYAR',
                'NIP_VALIDATOR_PEMBAYARAN'
            ] as $field) {
                if (array_key_exists($field, $validated)) {
                    $updateData[$field] = $validated[$field];
                }
            }

            $data->update($updateData);

            $this->updateStatusTagihan($data->ID_TAGIHAN_SISWA);

            $data->load([
                'tahunAnggaran',
                'metodePembayaran',
                'siswa',
                'tagihan.jenisTagihan',
                'cicilan',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pembayaran berhasil diperbarui',
                'data' => $data
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = TrPembayaran::with('cicilan')->find($id);

            if (!$data) {
                DB::rollBack();

                return response()->json([
                    'message' => 'Data pembayaran tidak ditemukan'
                ], 404);
            }

            if ($data->cicilan()->count() > 0) {
                DB::rollBack();

                return response()->json([
                    'message' => 'Pembayaran tidak bisa dihapus karena masih memiliki data cicilan.'
                ], 422);
            }

            $idTagihanSiswa = $data->ID_TAGIHAN_SISWA;

            $data->delete();

            $this->updateStatusTagihan($idTagihanSiswa);

            DB::commit();

            return response()->json([
                'message' => 'Data pembayaran berhasil dihapus'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function updateStatusTagihan($idTagihanSiswa): void
    {
        $tagihan = TagihanSiswa::find($idTagihanSiswa);

        if (!$tagihan) {
            return;
        }

        $totalTagihan = (float) $tagihan->JUMLAH_TAGIHAN_SISWA;

        $totalBayar = (float) TrPembayaran::where(
            'ID_TAGIHAN_SISWA',
            $idTagihanSiswa
        )->sum('JUMLAH_BAYAR');

        if ($totalBayar >= $totalTagihan && $totalTagihan > 0) {
            $status = 'Sudah Bayar';
        } elseif ($totalBayar > 0) {
            $status = 'Cicilan';
        } else {
            $status = 'Belum Bayar';
        }

        $tagihan->update([
            'STATUS_TAGIHAN_SISWA' => $status
        ]);
    }
}