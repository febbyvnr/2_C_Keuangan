<?php

namespace App\Http\Controllers;

use App\Models\MstKaryawan;
use App\Models\MstSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validasi input dasar
        $request->validate([
            'identifier' => 'required|string', // Frontend mengirim NIP atau NISN di field ini
            'password' => 'required|string',
        ]);

        $identifier = $request->identifier;
        $password = $request->password;

        // --- SKENARIO 1: CEK SEBAGAI KARYAWAN ---
        $karyawan = MstKaryawan::where('NIP_KARYAWAN', $identifier)->first();

        if ($karyawan && Hash::check($password, $karyawan->PASSWORD)) {
            // Terbitkan token Sanctum dengan penanda kemampuan/role
            $token = $karyawan->createToken('karyawan-token', ['role:karyawan'])->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil sebagai Karyawan',
                'data' => [
                    'token' => $token,
                    'role'  => 'karyawan',
                    'user'  => $karyawan
                ]
            ], 200);
        }

        // --- SKENARIO 2: CEK SEBAGAI SISWA ---
        // Jika skenario 1 gagal, kita cari berdasarkan NISN di mst_siswa
        $siswa = MstSiswa::where('nisn', $identifier)->first();

        if ($siswa && Hash::check($password, $siswa->PASSWORD)) {
            // Terbitkan token Sanctum untuk siswa
            $token = $siswa->createToken('siswa-token', ['role:siswa'])->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil sebagai Siswa',
                'data' => [
                    'token' => $token,
                    'role'  => 'siswa',
                    'user'  => $siswa
                ]
            ], 200);
        }

        // --- SKENARIO 3: GAGAL TOTAL ---
        return response()->json([
            'success' => false,
            'message' => 'NIP/NISN atau Password salah.'
        ], 401);
    }

    public function logout(Request $request)
    {
        // Menghapus token yang sedang digunakan untuk login saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ], 200);
    }
}