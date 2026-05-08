<?php

namespace App\Http\Controllers;
use App\Models\RefJenisTarif;
use Illuminate\Http\Request;

class RefJenisTarifController extends Controller
{
    public function index()
    {
        $data = RefJenisTarif::orderBy('ID_JENIS_TARIF', 'asc')->get();
        $data->transform(function ($item) {
            $item->is_used = $item->tarif()->exists();
            return $item;
        });
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function search(Request $request)
    {
        $query = RefJenisTarif::query();
        if ($request->filled('keyword')) {
            $query->where('DESKRIPSI_JENIS_TARIF', 'like', '%' . $request->keyword . '%');
        }
        $data = $query->get()->transform(function ($item) {
            $item->is_used = $item->tarif()->exists();
            return $item;
        });
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'DESKRIPSI_JENIS_TARIF' => 'nullable|unique:ref_jenis_tarif,DESKRIPSI_JENIS_TARIF',
        ]);

        $data = RefJenisTarif::create([
            'DESKRIPSI_JENIS_TARIF' => $request->DESKRIPSI_JENIS_TARIF,
        ]);

        return response()->json([
            'success' => true,
            'data' => $data
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $data = RefJenisTarif::findOrFail($id);
        $request->validate([
            'DESKRIPSI_JENIS_TARIF' =>
                'nullable|unique:ref_jenis_tarif,DESKRIPSI_JENIS_TARIF,' . $id . ',ID_JENIS_TARIF',
        ]);
        $data->update([
            'DESKRIPSI_JENIS_TARIF' => $request->DESKRIPSI_JENIS_TARIF
        ]);
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function destroy($id)
    {
        $data = RefJenisTarif::findOrFail($id);
        if ($data->tarif()->exists()) {
            return response()->json([
                'message' => 'Tidak bisa dihapus, sudah dipakai pada data tarif'
            ], 400);
        }
        $data->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus'
        ]);
    }

    public function show($id)
    {
        $data = RefJenisTarif::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}