<?php

namespace App\Http\Controllers;

use App\Models\MstKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi input: cuma butuh NIP, dan pastikan NIP itu ada di database
        $request->validate([
            'nip' => 'required|string|exists:mst_karyawan,NIP_KARYAWAN'
        ]);

        // 2. Cari karyawan berdasarkan NIP
        $karyawan = MstKaryawan::find($request->nip);

        // 3. Cek apakah akunnya sudah dihapus/dinonaktifkan
        if ($karyawan->IS_DELETE == 1) {
            return response()->json([
                'success' => false, 
                'message' => 'Akun Karyawan dinonaktifkan.'
            ], 403);
        }

        // 4. Bersihkan token lama (biar nggak numpuk kalau login berkali-kali)
        $karyawan->tokens()->delete();

        // 5. Buatkan token baru pakai Sanctum
        $token = $karyawan->createToken('subsistem-keuangan')->plainTextToken;

        // 6. Tarik info jabatan buat diliatin di response (opsional tapi bagus buat testing)
        $jabatan = $karyawan->trJabatans()->with('refJabatan')->get()
            ->pluck('refJabatan.DESKRIPSI_JABATAN')->filter()->values();

        // 7. Kembalikan response sukses beserta Token-nya
        return response()->json([
            'success' => true,
            'message' => 'Login Berhasil',
            'data'    => [
                'nip'     => $karyawan->NIP_KARYAWAN,
                'nama'    => $karyawan->NAMA_KARYAWAN,
                'jabatan' => $jabatan
            ],
            'token'   => $token
        ]);
    }
}