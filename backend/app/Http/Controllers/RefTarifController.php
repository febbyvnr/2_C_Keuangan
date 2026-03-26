<?php

namespace App\Http\Controllers;

use App\Models\RefTarif;
use Illuminate\Http\Request;

class RefTarifController extends Controller
{
    /**
     * Menampilkan semua data
     */
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
        $query = RefTarif::with(['jenisTarif', 'tahunAnggaran']);

        if ($request->filled('id_jenis_tarif')) {
            $query->where('ID_JENIS_TARIF', $request->id_jenis_tarif);
        }

        if ($request->filled('id_ta_anggaran')) {
            $query->where('ID_TA_ANGGARAN', $request->id_ta_anggaran);
        }

        return $query->get();
    }

    /**
     * Menambah Tarif
     */
    public function store(Request $request)
    {
        $request->validate([
            'ID_JENIS_TARIF' => 'required|exists:REF_JENIS_TARIF,ID_JENIS_TARIF',
            'ID_TA_ANGGARAN' => 'required|exists:REF_TAHUN_ANGGARAN,ID_TA_ANGGARAN',
            'NOMINAL' => 'required|numeric|min:0',
            'TGL_PENETAPAN' => 'required|date',
        ]);

        $data = RefTarif::create([
            'ID_JENIS_TARIF' => $request->ID_JENIS_TARIF,
            'ID_TA_ANGGARAN' => $request->ID_TA_ANGGARAN,
            'NOMINAL' => $request->NOMINAL,
            'TGL_PENETAPAN' => $request->TGL_PENETAPAN,
        ]);

        return response()->json($data, 201);
    }

    /**
     * Mengubah Tarif
     */
    public function update(Request $request, $idJenisTarif, $idTaAnggaran)
    {
        $data = RefTarif::where('ID_JENIS_TARIF', $idJenisTarif)
            ->where('ID_TA_ANGGARAN', $idTaAnggaran)
            ->firstOrFail();

        $request->validate([
            'NOMINAL' => 'required|numeric|min:0',
            'TGL_PENETAPAN' => 'required|date',
        ]);

        $data->update([
            'NOMINAL' => $request->NOMINAL,
            'TGL_PENETAPAN' => $request->TGL_PENETAPAN,
        ]);

        return response()->json($data);
    }

    /**
     * Menghapus Tarif
     */
    public function destroy($idJenisTarif, $idTaAnggaran)
    {
        $data = RefTarif::where('ID_JENIS_TARIF', $idJenisTarif)
            ->where('ID_TA_ANGGARAN', $idTaAnggaran)
            ->firstOrFail();

        $data->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus'
        ]);
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
}