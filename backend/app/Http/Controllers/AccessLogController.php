<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessLogController extends Controller
{
    public function index()
    {
        // Narik data access_log sekalian join ke mst_karyawan pake Query Builder
        $logs = DB::table('access_log')
            ->leftJoin('mst_karyawan', 'access_log.USERNAME', '=', 'mst_karyawan.NIP_KARYAWAN')
            ->select(
                'access_log.ID_ACCESS_LOG as id',
                'access_log.START_LOGIN as start_login',
                'access_log.END_LOGIN as end_login',
                'mst_karyawan.NAMA_KARYAWAN as nama_asli',
                'access_log.USERNAME as nip_nis',
                'access_log.ROLE as role'
            )
            ->orderBy('access_log.START_LOGIN', 'desc')
            ->get();

        $formatted = $logs->map(function ($log) {
            return [
                'id'          => $log->id,
                'start_login' => $log->start_login,
                'end_login'   => $log->end_login ?? 'Belum Logout',
                'username'    => $log->nama_asli ?? 'Sistem / Tester', // Fallback kalo isinya bukan NIP valid
                'nip_nis'     => $log->nip_nis,
                'role'        => $log->role,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $formatted
        ], 200);
    }
}