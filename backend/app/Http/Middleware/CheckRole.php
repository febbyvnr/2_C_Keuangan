<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\Karyawan;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // 1. Ambil token dari Postman (Authorization: Bearer <token>)
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized: Token tidak ditemukan.'], 401);
        }

        // 2. Buka gembok enkripsi tokennya buat nyari NIP
        try {
            $payload = json_decode(Crypt::decryptString($token));
            $user = Karyawan::where('NIP_KARYAWAN', $payload->nip)->first();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized: User tidak valid.'], 401);
            }

            // Daftarkan user ini ke request Laravel biar fungsi $request->user() tetap jalan
            $request->setUserResolver(function () use ($user) {
                return $user;
            });

        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized: Token rusak atau tidak valid.'], 401);
        }

        // 3. Cek Role (Sama kayak sebelumnya)
        $userRoles = $user->jabatans->pluck('DESKRIPSI_JABATAN')->toArray();
        $hasAccess = count(array_intersect($userRoles, $roles)) > 0;

        if (!$hasAccess) {
            return response()->json([
                'message' => 'Forbidden: Akses ditolak. Role Anda tidak sesuai.'
            ], 403);
        }

        return $next($request);
    }
}