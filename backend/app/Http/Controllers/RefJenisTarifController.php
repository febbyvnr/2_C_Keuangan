<?php

namespace App\Http\Controllers;

use App\Models\RefJenisTarif;
use Illuminate\Http\Request;

class RefJenisTarifController extends Controller
{
    /**
     * Menampilkan semua data
     */
    public function index()
    {
        return RefJenisTarif::orderBy('ID_JENIS_TARIF', 'asc')->get();
    }

    /**
     * Mencari data
     */
    public function search(Request $request)
    {
        $query = RefJenisTarif::query();

        if ($request->filled('keyword')) {
            $query->where('DESKRIPSI_JENIS_TARIF', 'like', '%' . $request->keyword . '%');
        }

        return $query->get();
    }

    /**
     * Menambah Jenis Tarif
     */
    public function store(Request $request)
{
    $request->validate([
        'DESKRIPSI_JENIS_TARIF' => 'required|unique:REF_JENIS_TARIF,DESKRIPSI_JENIS_TARIF',
    ]);

    $lastId = RefJenisTarif::max('ID_JENIS_TARIF');

    $newId = $lastId ? $lastId + 1 : 1;

    $data = RefJenisTarif::create([
        'ID_JENIS_TARIF' => $newId,
        'DESKRIPSI_JENIS_TARIF' => $request->DESKRIPSI_JENIS_TARIF,
    ]);

    return response()->json($data, 201);
}

    /**
     * Mengubah Jenis Tarif
     */
    public function update(Request $request, $id)
    {
        $data = RefJenisTarif::findOrFail($id);

        $request->validate([
            'DESKRIPSI_JENIS_TARIF' => 'required',
        ]);

        // kalau nanti ada relasi ke tarif, bisa dikunci di sini
        // contoh:
        // if ($data->tarif()->exists()) { ... }

        $data->update([
            'DESKRIPSI_JENIS_TARIF' => $request->DESKRIPSI_JENIS_TARIF
        ]);

        return response()->json($data);
    }

    /**
     * Menghapus Jenis Tarif
     */
    public function destroy($id)
    {
        $data = RefJenisTarif::findOrFail($id);

        // kalau sudah dipakai di tabel tarif
        if ($data->tarif()->exists()) {
            return response()->json([
                'message' => 'Tidak bisa dihapus, sudah dipakai pada data tarif'
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
        return RefJenisTarif::findOrFail($id);
    }
}