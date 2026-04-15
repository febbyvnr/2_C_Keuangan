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

    public function show($id)
    {
        return RefTarif::with(['jenisTarif', 'tahunAnggaran'])->findOrFail($id);
    }

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

    public function update(Request $request, $id)
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

    public function destroy($id)
    {
        $data = RefTarif::findOrFail($id);
        $data->delete();

        return response()->json(['message' => 'Data berhasil dihapus dengan aman']);
    }

    public function export()
    {
        $data = RefTarif::with(['jenisTarif', 'tahunAnggaran'])->get();
        
        $filename = "data_tarif_" . date('Ymd') . ".csv";
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, ['ID Tarif', 'Jenis Tarif', 'Tahun Anggaran', 'Deskripsi Tarif', 'Nominal', 'Tanggal Penetapan']);

        foreach ($data as $row) {
            fputcsv($handle, [
                $row->ID_REF_TARIF,
                $row->jenisTarif?->DESKRIPSI_JENIS_TARIF ?? '-', 
                $row->tahunAnggaran?->DESKRIPSI_TAHUN_ANGGARAN ?? '-',
                $row->DESKRIPSI_TARIF ?? '-',
                $row->NOMINAL,
                $row->TGL_PENETAPAN,
            ]);
        }

        fclose($handle);
        exit;
    }
}