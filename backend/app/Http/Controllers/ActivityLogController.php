<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index()
    {
        // Narik data activity_log sekaligus bawa data relasi 'actor' (MstKaryawan)
        $logs = ActivityLog::with('actor')
            ->orderBy('EVENT_TIME', 'desc')
            ->get(); // Pakai get() dulu buat ngetes tanpa pagination

        // Mapping ulang datanya biar frontend tinggal pakai tanpa pusing baca nested JSON
        $formattedLogs = $logs->map(function ($log) {
            return [
                'id'        => $log->ID_ACTIVITY_LOG,
                'waktu'     => $log->EVENT_TIME,
                'username'  => $log->actor ? $log->actor->NAMA_KARYAWAN : 'Sistem / Tidak Ditemukan', // Tarik nama dari relasi
                'nip_nis'   => $log->ACTOR_USERNAME,
                'role'      => $log->ACTOR_ROLE,
                'aktivitas' => $log->ACTIVITY_NAME,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $formattedLogs
        ], 200);
    }
}