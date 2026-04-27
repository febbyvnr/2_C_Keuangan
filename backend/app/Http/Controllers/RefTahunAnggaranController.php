<?php

namespace App\Http\Controllers;

use App\Models\RefTahunAnggaran;
use Illuminate\Http\Request;

class RefTahunAnggaranController extends Controller
{
    /**
     * Menampilkan semua data
     */
    public function index()
    {
        return RefTahunAnggaran::withCount(['programKerja', 'tarif'])
            ->orderBy('ID_TA_ANGGARAN', 'desc')
            ->get()
            ->map(function ($item) {
                $item->is_used = ($item->program_kerja_count > 0 || $item->tarif_count > 0);
                return $item;
            });
    }

    /**
     * Mencari data
     */
    public function search(Request $request)
    {
        $query = RefTahunAnggaran::query();

        if ($request->filled('keyword')) {
            $query->where('DESKRIPSI_TAHUN_ANGGARAN', 'like', '%' . $request->keyword . '%');
        }

        if ($request->filled('is_active')) {
            $query->where('IS_CURRENT', $request->is_active);
        }

        return $query->get();
    }

    /**
     * Menambah Tahun Anggaran
     */
    public function store(Request $request)
    {
        $request->validate([
            'DESKRIPSI_TAHUN_ANGGARAN' => 'required|unique:REF_TAHUN_ANGGARAN,DESKRIPSI_TAHUN_ANGGARAN',
            'IS_CURRENT' => 'required|boolean',
        ]);

        $last = RefTahunAnggaran::orderBy('ID_TA_ANGGARAN', 'desc')->first();

        $newId = $last ? $last->ID_TA_ANGGARAN + 1 : 1;

        if ($request->IS_CURRENT == 1) {
            RefTahunAnggaran::where('IS_CURRENT', 1)->update(['IS_CURRENT' => 0]);
        }

        $data = RefTahunAnggaran::create([
            'ID_TA_ANGGARAN' => $newId,
            'DESKRIPSI_TAHUN_ANGGARAN' => $request->DESKRIPSI_TAHUN_ANGGARAN,
            'IS_CURRENT' => $request->IS_CURRENT,
        ]);

        return response()->json($data->fresh(), 201);
    }

    /**
     * Mengubah Tahun Anggaran
     */
    public function update(Request $request, $id)
    {
        $data = RefTahunAnggaran::findOrFail($id);

        $request->validate([
            'DESKRIPSI_TAHUN_ANGGARAN' => 'required',
            'IS_CURRENT' => 'required|boolean',
        ]);

        // kl klp sblh ttp bs edit walaupun uda dipake tabel lain, asal ga memutus FK tabel lain aja
        // if (
        //     $data->programKerja()->exists() ||
        //     $data->tarif()->exists()
        // ) {
        //     return response()->json([
        //         'message' => 'Tidak boleh mengubah data karena sudah dipakai transaksi'
        //     ], 400);
        // }

        if ($request->IS_CURRENT == 1) {
            RefTahunAnggaran::where('IS_CURRENT', 1)
                ->where('ID_TA_ANGGARAN', '!=', $id)
                ->update(['IS_CURRENT' => 0]);
        }

        $data->update($request->all());

        return $data;
    }
    /**
     * Menghapus Tahun Anggaran
     */
    public function destroy($id)
{
    $data = RefTahunAnggaran::findOrFail($id);

    if ($data->IS_CURRENT == 1) {
        return response()->json([
            'message' => 'Tidak bisa menghapus tahun anggaran yang sedang aktif'
        ], 400);
    }

    if (
        $data->programKerja()->exists() ||
        $data->tarif()->exists()
    ) {
        return response()->json([
            'message' => 'Tidak bisa dihapus, sudah dipakai data lain'
        ], 400);
    }

    $data->delete();

    return response()->json([
        'message' => 'Data berhasil dihapus'
    ]);
}

    /**
     * Detail 1 data
     */
    public function show($id)
    {
        return RefTahunAnggaran::findOrFail($id);
    }
}