<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Crypt; // <-- PENTING: Import Crypt

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

        // --- WORKAROUND: BIKIN TOKEN SENDIRI (TANPA DATABASE) ---
        // Kita bungkus NIP-nya, lalu kita enkripsi jadi token super panjang
        $tokenData = [
            'nip' => $user->NIP_KARYAWAN,
            'time' => now()->timestamp
        ];
        $token = Crypt::encryptString(json_encode($tokenData));

        $roles = $user->jabatans->pluck('DESKRIPSI_JABATAN'); 

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