<?php

namespace App\Http\Controllers;

use App\Models\RefTarif;
use Illuminate\Http\Request;

class RefTarifController extends Controller
{
    public function index()
    {
        return RefTarif::with(['jenisTarif', 'tahunAnggaran'])
            ->orderBy('TGL_PENETAPAN', 'desc')
            ->get();
    }

    /**
     * Mencari data
     */
    public function search(Request $request)
    {
        $keyword = $request->query('keyword');

        $query = RefTarif::with(['jenisTarif', 'tahunAnggaran'])
            ->join('ref_jenis_tarif', 'ref_tarif.ID_JENIS_TARIF', '=', 'ref_jenis_tarif.ID_JENIS_TARIF');

        if ($keyword) {
            $query->where('ref_jenis_tarif.DESKRIPSI_JENIS_TARIF', 'like', "%{$keyword}%")
                  ->orWhere('ref_tarif.DESKRIPSI_TARIF', 'like', "%{$keyword}%"); // Bisa cari dari deskripsi baru
        }

        return $query->select('ref_tarif.*')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'ID_JENIS_TARIF' => 'required|exists:ref_jenis_tarif,ID_JENIS_TARIF',
            'ID_TA_ANGGARAN' => 'required|exists:ref_tahun_anggaran,ID_TA_ANGGARAN',
            'DESKRIPSI_TARIF' => 'nullable|string|max:100', // Kolom baru
            'NOMINAL' => 'required|numeric|min:0',
            'TGL_PENETAPAN' => 'required|date',
        ]);

        $data = RefTarif::create($request->all());
        return response()->json($data, 201);
    }

    /**
     * Mengubah Tarif
     */
    public function update(Request $request, $idJenisTarif, $idTaAnggaran)
    {
        $data = RefTarif::findOrFail($id);

        $request->validate([
            'DESKRIPSI_TARIF' => 'nullable|string|max:100',
            'NOMINAL' => 'required|numeric|min:0',
            'TGL_PENETAPAN' => 'required|date',
        ]);

        $data->update($request->only(['DESKRIPSI_TARIF', 'NOMINAL', 'TGL_PENETAPAN']));

        return response()->json($data);
    }

    /**
     * Menghapus Tarif
     */
    public function destroy($idJenisTarif, $idTaAnggaran)
    {
        $data = RefTarif::findOrFail($id);
        $data->delete();

        return response()->json(['message' => 'Data berhasil dihapus dengan aman']);
    }

    /**
     * Detail 1 data
     */
    public function show($idJenisTarif, $idTaAnggaran)
    {
        return RefTarif::with(['jenisTarif', 'tahunAnggaran'])
            ->where('ID_JENIS_TARIF', $idJenisTarif)
            ->where('ID_TA_ANGGARAN', $idTaAnggaran)
            ->firstOrFail();
    }

    public function showById($id)
    {
        $data = RefTarif::with(['jenisTarif', 'tahunAnggaran'])
            ->where('ID_JENIS_TARIF', $id)
            ->first();

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json($data);
    }

    public function byJenis($idJenis)
    {
        return RefTarif::with(['jenisTarif', 'tahunAnggaran'])
            ->where('ID_JENIS_TARIF', $idJenis)
            ->get();
    }

    public function byTahun($idTahun)
    {
        return RefTarif::with(['jenisTarif', 'tahunAnggaran'])
            ->where('ID_TA_ANGGARAN', $idTahun)
            ->get();
    }
}