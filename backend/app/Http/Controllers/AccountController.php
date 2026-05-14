<?php

namespace App\Http\Controllers;

use App\Models\MstKaryawan;
use App\Models\MstSiswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    private function ensureSuperAdmin(Request $request): ?JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->tokenCan('role:super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Jalur eksklusif Super Admin.'
            ], 403);
        }

        return null;
    }

    // =========================================================================
    // KENDALI AKUN SISWA (SUPER ADMIN)
    // =========================================================================

    public function setStudentPassword(Request $request, int $id): JsonResponse
    {
        if ($denied = $this->ensureSuperAdmin($request)) {
            return $denied;
        }

        $request->validate([
            'new_password' => 'required|string|min:6',
        ]);

        $siswa = MstSiswa::where('ID_SISWA_TETAP', $id)->first();

        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan.'], 404);
        }

        $siswa->PASSWORD = Hash::make($request->new_password);
        $siswa->save();

        return response()->json([
            'success' => true,
            'message' => 'Password akun siswa berhasil diatur.',
            'data'    => [
                'ID_SISWA_TETAP'   => $siswa->ID_SISWA_TETAP,
                'NISN_SISWA'       => $siswa->NISN_SISWA,
                'NAMA_SISWA_TETAP' => $siswa->NAMA_SISWA_TETAP
            ]
        ]);
    }

    public function bulkGenerateStudentCredentials(Request $request): JsonResponse
    {
        if ($denied = $this->ensureSuperAdmin($request)) {
            return $denied;
        }

        $siswaList = MstSiswa::whereNull('PASSWORD')->orWhere('PASSWORD', '')->get();
        $count = 0;

        foreach ($siswaList as $siswa) {
            if (!empty($siswa->NISN_SISWA)) {
                $siswa->PASSWORD = Hash::make($siswa->NISN_SISWA);
                $siswa->save();
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "$count akun siswa berhasil digenerate password awalnya menggunakan NISN."
        ]);
    }

    // =========================================================================
    // KENDALI AKUN KARYAWAN (SUPER ADMIN)
    // =========================================================================

    public function storeStaffAccount(Request $request): JsonResponse
    {
        if ($denied = $this->ensureSuperAdmin($request)) {
            return $denied;
        }

        $validated = $request->validate([
            'NIP_KARYAWAN'       => 'required|string|unique:mst_karyawan,NIP_KARYAWAN',
            'NAMA_KARYAWAN'      => 'required|string|max:100',
            'NAMA_LENGKAP_GELAR' => 'required|string|max:150',
            'EMAIL_KARYAWAN'     => 'required|email|max:100',
            'PASSWORD_KARYAWAN'  => 'required|string|min:6',
            'ID_UNIT'            => 'required|integer'
        ]);

        try {
            $karyawan = new MstKaryawan();
            $karyawan->NIP_KARYAWAN       = $validated['NIP_KARYAWAN'];
            $karyawan->NAMA_KARYAWAN      = $validated['NAMA_KARYAWAN'];
            $karyawan->NAMA_LENGKAP_GELAR = $validated['NAMA_LENGKAP_GELAR'];
            $karyawan->EMAIL_KARYAWAN     = $validated['EMAIL_KARYAWAN'];
            $karyawan->PASSWORD_KARYAWAN  = Hash::make($validated['PASSWORD_KARYAWAN']);
            $karyawan->ID_UNIT            = $validated['ID_UNIT'];
            $karyawan->IS_DELETE          = false;
            $karyawan->save();

            return response()->json([
                'success' => true,
                'message' => 'Akun admin/staf baru berhasil didaftarkan.',
                'data'    => $karyawan
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftarkan akun staf.',
                'error'   => config('app.debug') ? $th->getMessage() : null
            ], 500);
        }
    }

    public function resetStaffPassword(Request $request, $nip) // Pastikan variabel ini sama dengan di api.php
    {
        if ($denied = $this->ensureSuperAdmin($request)) {
            return $denied;
        }

        $request->validate(['new_password' => 'required|string|min:6']);

        // Cari pakai NIP_KARYAWAN karena itu PK string-nya
        $karyawan = MstKaryawan::where('NIP_KARYAWAN', $nip)->first();

        if (!$karyawan) {
            return response()->json(['message' => 'User dengan NIP '.$nip.' tidak ditemukan'], 404);
        }

        $karyawan->PASSWORD_KARYAWAN = Hash::make($request->new_password);
        $karyawan->save();

        return response()->json(['success' => true, 'message' => 'Password NIP '.$nip.' berhasil dihash & diupdate']);
    }

    public function listStaffAccounts(Request $request): JsonResponse
    {
        if ($denied = $this->ensureSuperAdmin($request)) {
            return $denied;
        }

        $roleKey = Schema::hasColumn('tr_jabatan', 'ID_JABATAN') ? 'ID_JABATAN' : 'ID_JABATAN_STR';

        $karyawan = DB::table('mst_karyawan as mk')
            ->leftJoin('tr_jabatan as tj', 'tj.NIP_KARYAWAN', '=', 'mk.NIP_KARYAWAN')
            ->leftJoin('ref_jabatan_str as rj', "rj.ID_JABATAN", '=', "tj.$roleKey")
            ->where('mk.IS_DELETE', false)
            ->groupBy('mk.NIP_KARYAWAN', 'mk.NAMA_KARYAWAN', 'mk.NAMA_LENGKAP_GELAR', 'mk.EMAIL_KARYAWAN', 'mk.ID_UNIT')
            ->orderBy('mk.NIP_KARYAWAN')
            ->selectRaw("
                mk.NIP_KARYAWAN,
                mk.NAMA_KARYAWAN,
                mk.NAMA_LENGKAP_GELAR,
                mk.EMAIL_KARYAWAN,
                mk.ID_UNIT,
                COALESCE(GROUP_CONCAT(DISTINCT rj.DESKRIPSI_JABATAN ORDER BY rj.DESKRIPSI_JABATAN SEPARATOR ', '), 'Belum Punya Role') as SYSTEM_ROLE
            ")
            ->get();

        return response()->json([
            'success' => true,
            'data' => $karyawan,
        ]);
    }

    public function listRoleOptions(Request $request): JsonResponse
    {
        if ($denied = $this->ensureSuperAdmin($request)) {
            return $denied;
        }

        $roles = DB::table('ref_jabatan_str')
            ->where('IS_VALID_JABATAN', true)
            ->orderBy('DESKRIPSI_JABATAN')
            ->get(['ID_JABATAN', 'DESKRIPSI_JABATAN']);

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }

    public function assignStaffRole(Request $request, string $nip): JsonResponse
    {
        if ($denied = $this->ensureSuperAdmin($request)) {
            return $denied;
        }

        $validated = $request->validate([
            'ID_JABATAN' => 'nullable|integer',
            'DESKRIPSI_JABATAN' => 'nullable|string|max:255',
        ]);

        if (empty($validated['ID_JABATAN']) && empty($validated['DESKRIPSI_JABATAN'])) {
            return response()->json([
                'success' => false,
                'message' => 'ID_JABATAN atau DESKRIPSI_JABATAN wajib diisi.',
            ], 422);
        }

        $karyawan = MstKaryawan::where('NIP_KARYAWAN', $nip)->first();
        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan.',
            ], 404);
        }

        $roleId = null;
        $roleDesc = isset($validated['DESKRIPSI_JABATAN']) ? trim((string) $validated['DESKRIPSI_JABATAN']) : '';

        if (!empty($validated['ID_JABATAN'])) {
            $roleId = (int) $validated['ID_JABATAN'];
            $existingRef = DB::table('ref_jabatan_str')
                ->where('ID_JABATAN', $roleId)
                ->first();

            if (!$existingRef) {
                if ($roleDesc === '') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Role referensi tidak ditemukan. Kirim DESKRIPSI_JABATAN untuk membuat role baru.',
                    ], 422);
                }

                $roleId = (int) DB::table('ref_jabatan_str')->insertGetId([
                    'DESKRIPSI_JABATAN' => $roleDesc,
                    'IS_VALID_JABATAN' => true,
                ]);
            }
        } else {
            $existingByDesc = DB::table('ref_jabatan_str')
                ->whereRaw('LOWER(TRIM(DESKRIPSI_JABATAN)) = ?', [strtolower($roleDesc)])
                ->first();

            if ($existingByDesc) {
                $roleId = (int) $existingByDesc->ID_JABATAN;
            } else {
                $roleId = (int) DB::table('ref_jabatan_str')->insertGetId([
                    'DESKRIPSI_JABATAN' => $roleDesc,
                    'IS_VALID_JABATAN' => true,
                ]);
            }
        }

        $roleKey = Schema::hasColumn('tr_jabatan', 'ID_JABATAN') ? 'ID_JABATAN' : 'ID_JABATAN_STR';
        $startKey = Schema::hasColumn('tr_jabatan', 'TGL_MULAI_JABATAN') ? 'TGL_MULAI_JABATAN' : 'TGL_MULAI_MENJABAT';

        $exists = DB::table('tr_jabatan')
            ->where('NIP_KARYAWAN', $nip)
            ->where($roleKey, $roleId)
            ->exists();

        if (!$exists) {
            $insertPayload = [
                'NIP_KARYAWAN' => $nip,
                $roleKey => $roleId,
                $startKey => now()->toDateString(),
            ];

            // Isi dua versi kolom role jika keduanya ada (biar kompatibel query lama/baru)
            if (Schema::hasColumn('tr_jabatan', 'ID_JABATAN')) {
                $insertPayload['ID_JABATAN'] = $roleId;
            }
            if (Schema::hasColumn('tr_jabatan', 'ID_JABATAN_STR')) {
                $insertPayload['ID_JABATAN_STR'] = $roleId;
            }

            DB::table('tr_jabatan')->insert($insertPayload);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role karyawan berhasil diperbarui.',
        ]);
    }

    public function revokeStaffRole(Request $request, string $nip): JsonResponse
    {
        if ($denied = $this->ensureSuperAdmin($request)) {
            return $denied;
        }

        $validated = $request->validate([
            'ID_JABATAN' => 'required|integer|exists:ref_jabatan_str,ID_JABATAN',
        ]);

        $roleKey = Schema::hasColumn('tr_jabatan', 'ID_JABATAN') ? 'ID_JABATAN' : 'ID_JABATAN_STR';

        DB::table('tr_jabatan')
            ->where('NIP_KARYAWAN', $nip)
            ->where($roleKey, $validated['ID_JABATAN'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role karyawan berhasil dicabut.',
        ]);
    }
}
