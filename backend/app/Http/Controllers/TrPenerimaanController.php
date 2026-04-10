<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenerimaanRequest;
use App\Http\Requests\UpdatePenerimaanRequest;
use App\Models\TrPenerimaan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrPenerimaanController extends Controller
{
    public function store(StorePenerimaanRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validData = $request->validated();
            $lastData = TrPenerimaan::orderBy('ID_TR_PENERIMAAN', 'desc')->first();
            $newId = $lastData ? $lastData->ID_TR_PENERIMAAN + 1 : 1;

            $validData['ID_TR_PENERIMAAN'] = $newId;

            $penerimaan = TrPenerimaan::create($validData);

            $this->logActivity($request, 'TAMBAH_PENERIMAAN', $penerimaan->ID_TR_PENERIMAAN,
                "Menambah transaksi penerimaan ID {$penerimaan->ID_TR_PENERIMAAN} sejumlah {$penerimaan->JUMLAH_TR_PENERIMAAN}");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penerimaan berhasil ditambahkan.',
                'data'    => $penerimaan->load(['refPenerimaan', 'refSumberDana', 'penerima']),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal menambah penerimaan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data penerimaan.',
            ], 500);
        }
    }

    public function update(UpdatePenerimaanRequest $request, int $id): JsonResponse
    {
        $penerimaan = TrPenerimaan::find($id);

        if (!$penerimaan) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        DB::beginTransaction();
        try {
            $dataLama = $penerimaan->toArray();
            $penerimaan->fill($request->validated());
            $penerimaan->save();

            $this->logActivity($request, 'UBAH_PENERIMAAN', $penerimaan->ID_TR_PENERIMAAN,
                "Mengubah penerimaan ID {$penerimaan->ID_TR_PENERIMAAN}. Sebelum: " . json_encode($dataLama));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data penerimaan berhasil diubah.',
                'data'    => $penerimaan->load(['refPenerimaan', 'refSumberDana', 'penerima']),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal mengubah penerimaan ID ' . $id . ': ' . $e->getMessage());
            
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan sistem saat mengubah data.'
            ], 500);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {

        // if (!$request->user() || !$request->user()->hasRole('bendahara')) {
        //     return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        // }

        $penerimaan = TrPenerimaan::find($id);

        if (!$penerimaan) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        DB::beginTransaction();
        try {
            $idLog = $penerimaan->ID_TR_PENERIMAAN;
            $jumlah = $penerimaan->JUMLAH_TR_PENERIMAAN;

            $penerimaan->delete();

            $this->logActivity($request, 'HAPUS_PENERIMAAN', $idLog, "Menghapus penerimaan ID {$idLog}");

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal menghapus penerimaan ID ' . $id . ': ' . $e->getMessage());
            
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data.'], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        // if (!$request->user() || !$request->user()->hasAnyRole(['bendahara', 'kepala_sekolah'])) {
        //     return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        // }

        $query = TrPenerimaan::with(['refPenerimaan', 'refSumberDana', 'penerima']);

        if ($request->filled('search')) {
            $query->where('DESKRIPSI_TR_PENERIMAAN', 'like', "%{$request->input('search')}%");
        }
        if ($request->filled('id_ref_dana')) {
            $query->where('ID_REF_DANA', $request->input('id_ref_dana'));
        }
        if ($request->filled('id_ref_penerimaan')) {
            $query->where('ID_REF_PENERIMAAN', $request->input('id_ref_penerimaan'));
        }
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('TANGGAL_TR_PENERIMAAN', '>=', $request->input('tanggal_awal'));
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('TANGGAL_TR_PENERIMAAN', '<=', $request->input('tanggal_akhir'));
        }
        if ($request->filled('nip_penerima')) {
            $query->where('NIP_PENERIMA', $request->input('nip_penerima'));
        }

        $query->orderBy('TANGGAL_TR_PENERIMAAN', 'desc')->orderBy('ID_TR_PENERIMAAN', 'desc');

        $perPage = $request->input('per_page', 15);
        $data    = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $data->items(),
            'meta'    => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        // if (!$request->user() || !$request->user()->hasAnyRole(['bendahara', 'kepala_sekolah'])) {
        //     return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        // }

        $penerimaan = TrPenerimaan::with(['refPenerimaan', 'refSumberDana', 'penerima'])->find($id);

        if (!$penerimaan) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        return response()->json(['success' => true, 'data' => $penerimaan]);
    }

    public function export(Request $request)
    {
        // if (!$request->user() || !$request->user()->hasAnyRole(['bendahara', 'kepala_sekolah'])) {
        //     return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        // }

        try {
            $query = TrPenerimaan::with(['refPenerimaan', 'refSumberDana', 'penerima']);

            if ($request->filled('search')) $query->where('DESKRIPSI_TR_PENERIMAAN', 'like', '%' . $request->input('search') . '%');
            if ($request->filled('id_ref_dana')) $query->where('ID_REF_DANA', $request->input('id_ref_dana'));
            if ($request->filled('id_ref_penerimaan')) $query->where('ID_REF_PENERIMAAN', $request->input('id_ref_penerimaan'));
            if ($request->filled('tanggal_awal')) $query->whereDate('TANGGAL_TR_PENERIMAAN', '>=', $request->input('tanggal_awal'));
            if ($request->filled('tanggal_akhir')) $query->whereDate('TANGGAL_TR_PENERIMAAN', '<=', $request->input('tanggal_akhir'));

            $data = $query->orderBy('TANGGAL_TR_PENERIMAAN', 'desc')->get();

            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Laporan Penerimaan');

            $sheet->setCellValue('A1', 'SMK Bopkri Dua');
            $sheet->setCellValue('A2', 'LAPORAN TRANSAKSI PENERIMAAN');
            
            $headers = ['A6'=>'No.','B6'=>'Tanggal','C6'=>'Jenis Penerimaan','D6'=>'Sumber Dana','E6'=>'Deskripsi','F6'=>'Penerima','G6'=>'Jumlah (Rp)'];
            foreach ($headers as $cell => $label) {
                $sheet->setCellValue($cell, $label);
            }

            $headerStyle = [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill'      => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
            ];
            $sheet->getStyle('A6:G6')->applyFromArray($headerStyle);

            $row = 7;
            $total = 0;
            foreach ($data as $i => $item) {
                $sheet->setCellValue('A' . $row, $i + 1);
                
                $tanggal = $item->TANGGAL_TR_PENERIMAAN ? date('d/m/Y', strtotime($item->TANGGAL_TR_PENERIMAAN)) : '-';
                $sheet->setCellValue('B' . $row, $tanggal);
                
                $sheet->setCellValue('C' . $row, $item->refPenerimaan->DESKRIPSI_REF_PENERIMAAN ?? '-');
                $sheet->setCellValue('D' . $row, $item->refSumberDana->ID_REF_DANA ?? '-');
                $sheet->setCellValue('E' . $row, $item->DESKRIPSI_TR_PENERIMAAN);
                $sheet->setCellValue('F' . $row, $item->penerima->NAMA_KARYAWAN ?? $item->NIP_PENERIMA);
                $sheet->setCellValue('G' . $row, $item->JUMLAH_TR_PENERIMAAN);
                
                $total += $item->JUMLAH_TR_PENERIMAAN;
                $row++;
            }

            $sheet->setCellValue('F' . $row, 'TOTAL');
            $sheet->setCellValue('G' . $row, $total);

            $filename = 'laporan_penerimaan_' . now()->format('Ymd_His') . '.xlsx';

            return response()->streamDownload(function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);

        } catch (\Throwable $e) {
            Log::error('Gagal export excel penerimaan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat mengekspor data laporan.',
            ], 500);
        }
    }

    private function logActivity(Request $request, string $activityName, ?int $relatedId, string $description): void
    {
        try {
            DB::table('activity_log')->insert([
                'EVENT_TIME'           => now()->toDateString(),
                'ACTOR_USERNAME'       => $request->user()?->username ?? $request->user()?->NIP_KARYAWAN ?? 'system',
                'ACTIVITY_NAME'        => $activityName,
                'RELATED_DATA'         => $relatedId ? "tr_penerimaan::{$relatedId}" : null,
                'ACTIVITY_DESCRIPTION' => mb_substr($description, 0, 255),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal mencatat activity log: ' . $e->getMessage());
        }
    }
}