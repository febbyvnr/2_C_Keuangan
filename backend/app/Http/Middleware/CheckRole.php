<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Ambil data user dari request (dapat dari Token Sanctum)
        $karyawan = $request->user();
        
        // 2. Kalau nggak ada user (artinya gak bawa token / token salah)
        if (!$karyawan) {
            return response()->json([
                'success' => false, 
                'message' => 'Akses ditolak. Silakan bawa Bearer Token.'
            ], 401); // 401: Unauthorized
        }

        // 3. Kalau route mensyaratkan role tertentu (misal 'Bendahara'), 
        //    cek apakah karyawan punya role tersebut.
        if (!empty($roles) && !$karyawan->hasAnyRole($roles)) {
            return response()->json([
                'success' => false, 
                'message' => 'Akses ditolak. Jabatan Anda tidak memiliki izin untuk fitur ini.'
            ], 403); // 403: Forbidden
        }

        // Lanjut ke Controller kalau lolos semua cek
        return $next($request);
    }
}