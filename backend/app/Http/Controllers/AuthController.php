<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan;
use App\Models\AccessLog;
use Illuminate\Support\Facades\Crypt;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'nip' => 'required',
            'password' => 'required',
        ]);

        $user = Karyawan::where('NIP_KARYAWAN', $request->nip)->first();

        // Pengecekan Plain Text
        if (!$user || $request->password !== $user->PASSWORD_KARYAWAN) {
            return response()->json(['message' => 'NIP atau Password salah!'], 401);
        }

        $roles = $user->jabatans->pluck('DESKRIPSI_JABATAN'); 
        $userRole = $roles->first() ?? 'User';

        $accessLog = AccessLog::create([
                'START_LOGIN' => now(), 
                'USERNAME'    => $user->NIP_KARYAWAN,
                'ROLE'        => substr($userRole, 0, 10)
            ]);

        $tokenData = [
            'nip'           => $user->NIP_KARYAWAN,
            'role'          => substr($userRole, 0, 10),
            'id_access_log' => $accessLog->ID_ACCESS_LOG, 
            'time'          => now()->timestamp
        ];
        
        $token = Crypt::encryptString(json_encode($tokenData));
        return response()->json([
            'success' => true,
            'message' => 'Login Berhasil',
            'data' => [
                'access_token' => $token,
                'roles' => $roles,
                'user' => $user
            ]
        ], 200);
    }


    public function logout(Request $request)
    {
        // 1. Ambil token dari Header
        $token = $request->bearerToken();

        if ($token) {
            try {
                // 2. Decrypt token buat nyari ID Access Log-nya
                $decrypted = \Illuminate\Support\Facades\Crypt::decryptString($token);
                $tokenData = json_decode($decrypted);
                $accessLogId = $tokenData->id_access_log ?? null;

                // 3. Catat waktu END_LOGIN
                if ($accessLogId) {
                    \App\Models\AccessLog::where('ID_ACCESS_LOG', $accessLogId)
                        ->update(['END_LOGIN' => now()]);
                }
            } catch (\Exception $e) {
                // Token invalid/expired, biarkan saja langsung terlogout di frontend
            }
        }

        return response()->json([
            'success' => true, 
            'message' => 'Berhasil logout dan log dicatat'
        ], 200);
    }
}
