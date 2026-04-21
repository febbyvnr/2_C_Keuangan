<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan;
use App\Models\AccessLog; // <-- UBAH KE ACCESS LOG
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

        // --- PAKAI MODEL ACCESS LOG ---
        $accessLog = AccessLog::create([
            'START_LOGIN' => now(),
            'USERNAME'    => $user->NIP_KARYAWAN,
            'ROLE'        => substr($userRole, 0, 10) 
        ]);

        // --- WORKAROUND: BIKIN TOKEN SENDIRI ---
        $tokenData = [
            'nip'           => $user->NIP_KARYAWAN,
            'role'          => $userRole,
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
}