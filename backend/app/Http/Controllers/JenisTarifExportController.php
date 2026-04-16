<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JenisTarifExport;
use Illuminate\Support\Facades\Auth;

class JenisTarifExportController extends Controller
{
    public function export(Request $request)
    {
        // Ambil role dari request atau dari user yang login
        $role = $request->role ?? (Auth::user()->role ?? 'Bendahara');

        // VALIDASI ROLE (Hanya Bendahara yang boleh akses)
        if ($role !== 'Bendahara') {
            return response()->json([
                'message' => 'Hanya Bendahara yang boleh generate laporan'
            ], 403);
        }

        // PROSES EXCEL
        try {
            return Excel::download(
                new JenisTarifExport($role),
                'Data_Jenis_Tarif.xlsx'
            );
        } catch (\Exception $e) {
            // Jika masih error "Target class [excel]", kita paksa clear config via code
            \Illuminate\Support\Facades\Artisan::call('config:clear');

            return response()->json([
                'message' => 'Terjadi kesalahan pada layanan Excel. Silakan coba klik export sekali lagi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}