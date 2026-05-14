<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized: User tidak terautentikasi.'], 401);
        }

        $userRoles = method_exists($user, 'jabatans')
            ? $user->jabatans()->pluck('DESKRIPSI_JABATAN')->toArray()
            : [];

        $normalizedUserRoles = array_map(fn ($r) => strtolower(trim((string) $r)), $userRoles);
        $normalizedExpectedRoles = array_map(fn ($r) => strtolower(trim((string) $r)), $roles);
        $hasAccess = count(array_intersect($normalizedUserRoles, $normalizedExpectedRoles)) > 0;

        if (!$hasAccess) {
            return response()->json([
                'message' => 'Forbidden: Akses ditolak. Role Anda tidak sesuai.'
            ], 403);
        }

        return $next($request);
    }
}
