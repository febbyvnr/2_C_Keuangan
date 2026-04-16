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

    public function store(Request $request)
    {
        $request->validate([
            'ID_JENIS_TARIF' => 'nullable|exists:REF_JENIS_TARIF,ID_JENIS_TARIF',
            'ID_TA_ANGGARAN' => 'nullable|exists:REF_TAHUN_ANGGARAN,ID_TA_ANGGARAN',
            'NOMINAL' => 'nullable|numeric|min:0',
            'TGL_PENETAPAN' => 'nullable|date',
        ]);

        $data = RefTarif::create([
            'ID_JENIS_TARIF' => $request->ID_JENIS_TARIF,
            'ID_TA_ANGGARAN' => $request->ID_TA_ANGGARAN,
            'NOMINAL' => $request->NOMINAL,
            'TGL_PENETAPAN' => $request->TGL_PENETAPAN,
        ]);

        return response()->json([
            'success' => true,
            'data' => $data
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $data = RefTarif::findOrFail($id);
        $request->validate([
            'ID_JENIS_TARIF' => 'nullable|exists:REF_JENIS_TARIF,ID_JENIS_TARIF',
            'ID_TA_ANGGARAN' => 'nullable|exists:REF_TAHUN_ANGGARAN,ID_TA_ANGGARAN',
            'NOMINAL' => 'nullable|numeric|min:0',
            'TGL_PENETAPAN' => 'nullable|date',
        ]);
        $data->update([
            'ID_JENIS_TARIF' => $request->ID_JENIS_TARIF,
            'ID_TA_ANGGARAN' => $request->ID_TA_ANGGARAN,
            'NOMINAL' => $request->NOMINAL,
            'TGL_PENETAPAN' => $request->TGL_PENETAPAN,
        ]);
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function destroy($id)
    {
        $data = RefTarif::findOrFail($id);
        $data->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus'
        ]);
    }

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