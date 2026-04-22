<?php

namespace App\Http\Controllers;

use App\Models\RefTarif;
use App\Exports\TarifExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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

    public function exportExcel(Request $request)
    {
        $role         = Auth::check() ? (Auth::user()->role ?? 'Bendahara') : 'Bendahara';
        $idJenisTarif = $request->query('id_jenis_tarif');
        $idTaAnggaran = $request->query('id_ta_anggaran');

        $filename = 'Data_Tarif_' . date('Ymd_His') . '.xlsx';

        return Excel::download(
            new TarifExport($role, $idJenisTarif, $idTaAnggaran),
            $filename
        );
    }

    public function exportPdf(Request $request)
    {
        $idJenisTarif = $request->query('id_jenis_tarif');
        $idTaAnggaran = $request->query('id_ta_anggaran');

        $query = RefTarif::with(['jenisTarif', 'tahunAnggaran'])
            ->orderBy('TGL_PENETAPAN', 'desc');

        if ($idJenisTarif) {
            $query->where('ID_JENIS_TARIF', $idJenisTarif);
        }
        if ($idTaAnggaran) {
            $query->where('ID_TA_ANGGARAN', $idTaAnggaran);
        }

        $data = $query->get()->map(function ($item) {
    
            return [
                'id'            => $item->ID_REF_TARIF,
                'jenis_tarif'   => $item->jenisTarif?->DESKRIPSI_JENIS_TARIF
                    ?? ($item->ID_JENIS_TARIF ? "ID: {$item->ID_JENIS_TARIF}" : '-'),
                'tahun_anggaran'=> $item->tahunAnggaran?->DESKRIPSI_TAHUN_ANGGARAN
                    ?? ($item->ID_TA_ANGGARAN ? "ID: {$item->ID_TA_ANGGARAN}" : '-'),
                'deskripsi'     => $item->DESKRIPSI_TARIF ?? '-',
                'nominal'       => $item->NOMINAL ?? 0,
                'tgl_penetapan' => $this->formatTanggal($item->TGL_PENETAPAN),
            ];
        });

        $role       = Auth::check() ? (Auth::user()->role ?? 'Bendahara') : 'Bendahara';
        $tanggalCetak = Carbon::now()->translatedFormat('d F Y');

        $pdf = Pdf::loadView('exports.tarif_pdf', [
            'data'         => $data,
            'role'         => $role,
            'tanggalCetak' => $tanggalCetak,
        ])->setPaper('a4', 'landscape');

        $filename = 'Data_Tarif_' . date('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    private function formatTanggal($tanggal): string
    {
        if (empty($tanggal)) {
            return '-';
        }
        try {
            return Carbon::parse($tanggal)->translatedFormat('d F Y');
        } catch (\Exception $e) {
            return (string) $tanggal;
        }
    }
}