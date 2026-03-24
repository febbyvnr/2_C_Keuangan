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
        return RefTahunAnggaran::latest()->get();
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

    return response()->json($data, 201);
}

    /**
     * Mengubah Tahun Anggaran
     */
    public function update(Request $request, $id)
    {
        $data = RefTahunAnggaran::findOrFail($id);

        if ($data->programKerja()->exists()) {
            return response()->json([
                'message' => 'Tidak boleh mengubah, sudah dipakai program kerja'
            ], 400);
        }

        $request->validate([
            'DESKRIPSI_TAHUN_ANGGARAN' => 'required',
            'IS_CURRENT' => 'required|boolean',
        ]);

        // hanya 1 aktif
        if ($request->IS_CURRENT == 1) {
            RefTahunAnggaran::where('IS_CURRENT', 1)->update(['IS_CURRENT' => 0]);
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

        if ($data->programKerja()->exists()) {
            return response()->json([
                'message' => 'Tidak bisa dihapus, sudah dipakai program kerja'
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