<?php

namespace App\Http\Controllers;

use App\Models\MstSiswa;
use App\Models\TagihanSiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MstSiswaController extends Controller
{
    /**
     * Menampilkan profil siswa yang sedang login (Otomatis dari Token)
     */
    public function myProfile(Request $request): JsonResponse
    {
        $siswaSession = $request->user();

        $siswa = MstSiswa::where('ID_SISWA_TETAP', $siswaSession->ID_SISWA_TETAP)
            ->first();

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil siswa berhasil diambil.',
            'data' => $siswa,
        ]);
    }

    /**
     * Mengupdate data kontak/orang tua mandiri
     */
    public function updateMyProfile(Request $request): JsonResponse
    {
        try {
            $siswaSession = $request->user();

            $siswa = MstSiswa::where('ID_SISWA_TETAP', $siswaSession->ID_SISWA_TETAP)
                ->first();

            if (!$siswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data profil tidak ditemukan.',
                ], 404);
            }

            $validated = $request->validate([
                'NO_HP_SISWA'          => ['nullable', 'string', 'max:20'],
                'PEKERJAAN_AYAH_SISWA' => ['nullable', 'string', 'max:255'],
                'PEKERJAAN_IBU_SISWA'  => ['nullable', 'string', 'max:255'],
                'NAMA_WALI_SISWA'      => ['nullable', 'string', 'max:255'],
            ]);

            $siswa->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Profil Anda berhasil diperbarui.',
                'data'    => $siswa,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui profil.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Menampilkan daftar tagihan milik siswa yang sedang login (URL Bersih)
     * Mengadopsi format mapping yang sama persis dengan TagihanSiswaController
     */
    public function myTagihan(Request $request): JsonResponse
    {
        $siswaSession = $request->user();

        $tagihan = TagihanSiswa::with([
            'jenisTagihan',
            'pembayaran.metodePembayaran',
        ])
        ->where('ID_SISWA_TETAP', $siswaSession->ID_SISWA_TETAP)
        ->orderByDesc('TAHUN_TAGIHAN_SISWA')
        ->orderByDesc('ID_TAGIHAN_SISWA')
        ->get()
        ->map(function ($item) {
            $totalPembayaran = (float) $item->pembayaran->sum('JUMLAH_BAYAR');
            $sisaTagihan = max(0, (float) $item->JUMLAH_TAGIHAN_SISWA - $totalPembayaran);

            return [
                'ID_TAGIHAN_SISWA'     => (int) $item->ID_TAGIHAN_SISWA,
                'JENIS_TAGIHAN'        => [
                    'ID_JENIS_TAGIHAN'        => optional($item->jenisTagihan)->ID_JENIS_TAGIHAN,
                    'DESKRIPSI_JENIS_TAGIHAN' => optional($item->jenisTagihan)->DESKRIPSI_JENIS_TAGIHAN,
                ],
                'BULAN_TAGIHAN_SISWA'       => $item->BULAN_TAGIHAN_SISWA,
                'TAHUN_TAGIHAN_SISWA'       => $item->TAHUN_TAGIHAN_SISWA,
                'JUMLAH_TAGIHAN_SISWA'      => (float) $item->JUMLAH_TAGIHAN_SISWA,
                'TOTAL_PEMBAYARAN'          => $totalPembayaran,
                'SISA_TAGIHAN'              => $sisaTagihan,
                'STATUS_TAGIHAN_SISWA'      => $item->STATUS_TAGIHAN_SISWA,
                'DUEDATETIME_TAGIHAN_SISWA' => $item->DUEDATETIME_TAGIHAN_SISWA,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar tagihan Anda berhasil diambil.',
            'data'    => $tagihan,
        ]);
    }
}