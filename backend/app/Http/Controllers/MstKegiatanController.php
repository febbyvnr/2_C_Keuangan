<?php

namespace App\Http\Controllers;

use App\Models\MstKegiatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MstKegiatanController extends Controller
{
    /**
     * Menampilkan semua kegiatan aktif
     * Bisa search berdasarkan deskripsi kegiatan
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $query = MstKegiatan::with(['parent', 'children'])
            ->active()
            ->orderBy('DESKRIPSI_KEGIATAN', 'asc');

        if ($search) {
            $query->where('DESKRIPSI_KEGIATAN', 'like', '%' . $search . '%');
        }

        $data = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Data kegiatan berhasil diambil',
            'data' => $data,
        ]);
    }

    /**
     * Menampilkan detail kegiatan
     */
    public function show(int $id): JsonResponse
    {
        $data = MstKegiatan::with(['parent', 'children', 'programKerja'])
            ->active()
            ->find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data kegiatan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail kegiatan berhasil diambil',
            'data' => $data,
        ]);
    }

    /**
     * Menambahkan kegiatan baru
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'MST_ID_KEGIATAN' => 'nullable|integer|exists:mst_kegiatan,ID_KEGIATAN',
            'DESKRIPSI_KEGIATAN' => 'required|string|max:100',
            'IS_DELETE' => 'nullable|boolean',
        ]);

        $validated['ID_KEGIATAN'] = (MstKegiatan::max('ID_KEGIATAN') ?? 0) + 1;
        $validated['IS_DELETE'] = $validated['IS_DELETE'] ?? 0;

        $data = MstKegiatan::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kegiatan berhasil ditambahkan',
            'data' => $data,
        ], 201);
    }

    /**
     * Mengubah kegiatan
     * Tidak boleh jika sudah dipakai program kerja,=>  masih main di logikanyaa, masih bingung sihh jhujhurr T_T
     */
   public function update(Request $request, int $id): JsonResponse
    {
        $kegiatan = MstKegiatan::find($id);

        if (!$kegiatan || $kegiatan->IS_DELETE) {
            return response()->json([
                'success' => false,
                'message' => 'Data kegiatan tidak ditemukan',
            ], 404);
        }

        if ($kegiatan->programKerja()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kegiatan tidak boleh diubah karena sudah dipakai pada program kerja',
            ], 422);
        }

        $validated = $request->validate([
            'MST_ID_KEGIATAN' => 'nullable|integer|exists:mst_kegiatan,ID_KEGIATAN',
            'DESKRIPSI_KEGIATAN' => 'required|string|max:100',
        ]);

        if (!empty($validated['MST_ID_KEGIATAN']) && $validated['MST_ID_KEGIATAN'] == $id) {
            return response()->json([
                'success' => false,
                'message' => 'Parent kegiatan tidak boleh dirinya sendiri',
            ], 422);
        }

        $kegiatan->update([
            'MST_ID_KEGIATAN' => $validated['MST_ID_KEGIATAN'] ?? null,
            'DESKRIPSI_KEGIATAN' => $validated['DESKRIPSI_KEGIATAN'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kegiatan berhasil diperbarui',
            'data' => $kegiatan->fresh(),
        ]);
    }

    /**
     * Menghapus kegiatan (soft delete)
     * Hanya boleh jika belum dipakai program kerja dan tidak punya child aktif
     */
    public function destroy(int $id): JsonResponse
    {
        $kegiatan = MstKegiatan::with('children')->find($id);

        if (!$kegiatan || $kegiatan->IS_DELETE) {
            return response()->json([
                'success' => false,
                'message' => 'Data kegiatan tidak ditemukan',
            ], 404);
        }

        if ($kegiatan->programKerja()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kegiatan tidak boleh dihapus karena sudah dipakai pada program kerja',
            ], 422);
        }

        if ($kegiatan->children()->where('IS_DELETE', 0)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kegiatan tidak boleh dihapus karena masih memiliki sub kegiatan aktif',
            ], 422);
        }

        $kegiatan->update([
            'IS_DELETE' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kegiatan berhasil dihapus',
        ]);
    }

    /**
     * Menampilkan parent kegiatan untuk dropdown
     */
    public function parents(): JsonResponse
    {
        $data = MstKegiatan::active()
            ->whereNull('MST_ID_KEGIATAN')
            ->orderBy('DESKRIPSI_KEGIATAN', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data parent kegiatan berhasil diambil',
            'data' => $data,
        ]);
    }
}