<?php

namespace App\Http\Controllers;

use App\Models\RefTarif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefTarifController extends Controller
{
    public function index()
    {
        return RefTarif::with(['jenisTarif', 'tahunAnggaran'])
            ->orderBy('TGL_PENETAPAN', 'desc')
            ->get();
    }

    // Task 44: Mencari Tarif
    public function search(Request $request)
    {
        $keyword = $request->query('keyword');

        $query = RefTarif::with(['jenisTarif', 'tahunAnggaran'])
            ->join('ref_jenis_tarif', 'ref_tarif.ID_JENIS_TARIF', '=', 'ref_jenis_tarif.ID_JENIS_TARIF');

        if ($keyword) {
            $query->where('ref_jenis_tarif.DESKRIPSI_JENIS_TARIF', 'like', "%{$keyword}%");
        }

        return $query->select('ref_tarif.*')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'ID_JENIS_TARIF' => 'required|exists:ref_jenis_tarif,ID_JENIS_TARIF',
            'ID_TA_ANGGARAN' => 'required|exists:ref_tahun_anggaran,ID_TA_ANGGARAN',
            'NOMINAL' => 'required|numeric|min:0',
            'TGL_PENETAPAN' => 'required|date',
        ]);

        $data = RefTarif::create($request->all());
        return response()->json($data, 201);
    }

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

    // Task 42: Menghapus Tarif (Tanpa validasi RKA karena tidak ada relasi di DB)
    public function destroy($idJenisTarif, $idTaAnggaran)
    {
        $data = RefTarif::where('ID_JENIS_TARIF', $idJenisTarif)
            ->where('ID_TA_ANGGARAN', $idTaAnggaran)
            ->firstOrFail();

        $data->delete();

        return response()->json(['message' => 'Data berhasil dihapus dengan aman']);
    }

    // Task 45: Mengekspor Tarif (Kebal Error Null)
    public function export()
    {
        $data = RefTarif::with(['jenisTarif', 'tahunAnggaran'])->get();
        
        $filename = "data_tarif_" . date('Ymd') . ".csv";
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, ['ID Jenis', 'Jenis Tarif', 'Tahun Anggaran', 'Nominal', 'Tanggal Penetapan']);

        foreach ($data as $row) {
            fputcsv($handle, [
                $row->ID_JENIS_TARIF,
                $row->jenisTarif?->DESKRIPSI_JENIS_TARIF ?? '-', // Pake ?-> biar ga error null
                $row->tahunAnggaran?->DESKRIPSI_TAHUN_ANGGARAN ?? '-',
                $row->NOMINAL,
                $row->TGL_PENETAPAN,
            ]);
        }

        fclose($handle);
        exit;
    }
}