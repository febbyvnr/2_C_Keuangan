<?php

namespace App\Http\Controllers;

use App\Models\MstKaryawan;
use App\Models\MstSiswa;
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

        $karyawan = MstKaryawan::query()
            ->where('IS_DELETE', false)
            ->orderBy('NAMA_KARYAWAN')
            ->get([
                'NIP_KARYAWAN',
                'NAMA_KARYAWAN',
                'NAMA_LENGKAP_GELAR',
                'EMAIL_KARYAWAN',
                'ID_UNIT',
            ]);

        return response()->json([
            'success' => true,
            'data' => $karyawan,
        ]);
    }
}
