<?php

namespace App\Http\Controllers;

use App\Models\MstCoa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MstCoaController extends Controller
{
    /**
     * Menampilkan daftar COA
     * Bisa search berdasarkan kode atau deskripsi
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $query = MstCoa::with(['parent', 'children'])
            ->active()
            ->orderBy('KODE_COA', 'asc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('KODE_COA', 'like', '%' . $search . '%')
                  ->orWhere('DESKRIPSI_COA', 'like', '%' . $search . '%');
            });
        }

        $data = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Data COA berhasil diambil',
            'data' => $data,
        ]);
    }

    /**
     * Menampilkan detail COA
     */
    public function show(int $id): JsonResponse
    {
        $data = MstCoa::with(['parent', 'children', 'programKerja'])
            ->active()
            ->find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data COA tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail COA berhasil diambil',
            'data' => $data,
        ]);
    }

    /**
     * Menambahkan COA baru
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'MST_ID_MASTER_COA' => 'nullable|integer|exists:mst_coa,ID_MASTER_COA',
            'KODE_COA' => 'required|string|max:10|unique:mst_coa,KODE_COA',
            'DESKRIPSI_COA' => 'required|string|max:100',
            'IS_DELETE' => 'nullable|boolean',
        ]);

        $validated['ID_MASTER_COA'] = (MstCoa::max('ID_MASTER_COA') ?? 0) + 1;
        $validated['IS_DELETE'] = $validated['IS_DELETE'] ?? 0;

        $data = MstCoa::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'COA berhasil ditambahkan',
            'data' => $data,
        ], 201);
    }

    /**
     * Mengubah COA
     * Tidak boleh jika COA sudah dipakai di mst_program_kerja
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $coa = MstCoa::find($id);

        if (!$coa || $coa->IS_DELETE) {
            return response()->json([
                'success' => false,
                'message' => 'Data COA tidak ditemukan',
            ], 404);
        }

        $isUsed = $coa->programKerja()->exists();

        if ($isUsed) {
            return response()->json([
                'success' => false,
                'message' => 'COA tidak boleh diubah karena sudah dipakai pada transaksi/program kerja',
            ], 422);
        }

        $validated = $request->validate([
            'MST_ID_MASTER_COA' => 'nullable|integer|exists:mst_coa,ID_MASTER_COA',
            'KODE_COA' => [
                'required',
                'string',
                'max:10',
                Rule::unique('mst_coa', 'KODE_COA')->ignore($id, 'ID_MASTER_COA'),
            ],
            'DESKRIPSI_COA' => 'required|string|max:100',
            'IS_DELETE' => 'nullable|boolean',
        ]);

        $coa->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'COA berhasil diperbarui',
            'data' => $coa,
        ]);
    }

    /**
     * Menghapus COA (soft delete)
     * Hanya boleh jika belum dipakai program kerja dan tidak punya child aktif
     */
    public function destroy(int $id): JsonResponse
    {
        $coa = MstCoa::with('children')->find($id);

        if (!$coa || $coa->IS_DELETE) {
            return response()->json([
                'success' => false,
                'message' => 'Data COA tidak ditemukan',
            ], 404);
        }

        $isUsed = $coa->programKerja()->exists();

        if ($isUsed) {
            return response()->json([
                'success' => false,
                'message' => 'COA tidak boleh dihapus karena sudah dipakai pada program kerja/transaksi',
            ], 422);
        }

        $hasActiveChildren = $coa->children()->where('IS_DELETE', 0)->exists();

        if ($hasActiveChildren) {
            return response()->json([
                'success' => false,
                'message' => 'COA tidak boleh dihapus karena masih memiliki sub COA aktif',
            ], 422);
        }

        $coa->update([
            'IS_DELETE' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'COA berhasil dihapus',
        ]);
    }

    /**
     * Menampilkan data parent COA untuk dropdown
     */
    public function parents(): JsonResponse
    {
        $data = MstCoa::active()
            ->whereNull('MST_ID_MASTER_COA')
            ->orderBy('KODE_COA', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data parent COA berhasil diambil',
            'data' => $data,
        ]);
    }
}