<?php

namespace App\Http\Controllers;

use App\Models\MstInventaris;
use Illuminate\Http\Request;

class InventarisController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = trim((string) $request->query('search', ''));
            $perPage = (int) $request->query('per_page', 10);

            if ($perPage <= 0) {
                $perPage = 10;
            }

            if ($perPage > 100) {
                $perPage = 100;
            }

            $query = MstInventaris::query()
                ->select([
                    'ID_INVENTARIS',
                    'ID_KAT_BARANG',
                    'KODE_INVENTARIS',
                    'NAMA_INVENTARIS',
                    'NILAI_INVENTARIS',
                    'TGL_HABIS_GARANSI',
                    'LINK_FOTO_BARANG',
                    'MEREK_INV',
                    'NO_SERI_INV',
                    'DIMENSI_INV',
                    'KETERANGAN_INV',
                    'TGL_BELI_INV',
                    'KONDISI_TERAKHIR_INV',
                    'STATUS_INV',
                    'IS_DELETE',
                ])
                ->where('IS_DELETE', 0);

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('KODE_INVENTARIS', 'like', '%' . $search . '%')
                      ->orWhere('NAMA_INVENTARIS', 'like', '%' . $search . '%')
                      ->orWhere('MEREK_INV', 'like', '%' . $search . '%')
                      ->orWhere('NO_SERI_INV', 'like', '%' . $search . '%')
                      ->orWhere('KETERANGAN_INV', 'like', '%' . $search . '%')
                      ->orWhere('KONDISI_TERAKHIR_INV', 'like', '%' . $search . '%');
                });
            }

            $inventaris = $query
                ->orderBy('ID_INVENTARIS', 'asc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data inventaris berhasil diambil.',
                'data' => $inventaris->items(),
                'pagination' => [
                    'current_page' => $inventaris->currentPage(),
                    'last_page' => $inventaris->lastPage(),
                    'per_page' => $inventaris->perPage(),
                    'total' => $inventaris->total(),
                ],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data inventaris.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}