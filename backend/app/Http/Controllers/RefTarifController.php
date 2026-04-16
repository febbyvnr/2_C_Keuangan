<?php

namespace App\Http\Controllers;

use App\Models\Rka;
use App\Models\RkaDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class RkaController extends Controller
{
    /**
     * Helper Sakti: Mencari RKT yang benar-benar AKTIF
     * Jika sudah di-delete (IS_DELETE=1), maka dianggap TIDAK ADA.
     */
    private function findActiveRka($id)
    {
        return Rka::with(['details'])
            ->where('ID_PROGRAM_KERJA', $id)
            ->where(function ($q) {
                $q->where('IS_DELETE', '!=', 1)->orWhereNull('IS_DELETE');
            })->first();
    }

    /**
     * GET List: Hanya menampilkan yang aktif
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $data = Rka::with(['details'])
                ->where(function ($q) {
                    $q->where('IS_DELETE', '!=', 1)->orWhereNull('IS_DELETE');
                })->get();

            return response()->json([
                'success' => true,
                'count' => $data->count(),
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST Store: Menolak jika RKT tujuan sudah dihapus (FR-3.2.3.4)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'ID_PROGRAM_KERJA' => [
                'required', 
                'integer', 
                // VALIDASI KETAT: ID harus ada di tabel DAN IS_DELETE tidak boleh 1
                Rule::exists('mst_program_kerja', 'ID_PROGRAM_KERJA')->where(function ($q) {
                    $q->where('IS_DELETE', '!=', 1)->orWhereNull('IS_DELETE');
                })
            ],
            'details' => 'required|array',
            'details.*.QTY' => 'required|integer|min:1',
            'details.*.HARGA_SATUAN' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();
            // findOrFail di sini sudah pasti aman karena sudah lolos validasi ketat di atas
            $rka = Rka::findOrFail($request->ID_PROGRAM_KERJA);
            
            $subtotalInput = 0;
            foreach ($request->details as $d) {
                $subtotalInput += ($d['QTY'] * $d['HARGA_SATUAN'] * ($d['VOLUME'] ?? 1));
            }

            // Cek Pagu (Poin 64)
            if (Schema::hasTable('ref_pagu_unit')) {
                $pagu = DB::table('ref_pagu_unit')
                            ->where('ID_UNIT', $rka->ID_UNIT)
                            ->where('ID_TA_ANGGARAN', $rka->ID_TA_ANGGARAN)
                            ->value('NOMINAL_PAGU') ?? 0;
                            
                if ($pagu > 0 && ($rka->NOMINAL + $subtotalInput) > $pagu) {
                    return response()->json(['success' => false, 'message' => 'Gagal: Melebihi Pagu Unit'], 400);
                }
            }

            foreach ($request->details as $detail) {
                $subtotal = $detail['QTY'] * $detail['HARGA_SATUAN'] * ($detail['VOLUME'] ?? 1);
                RkaDetail::create([
                    'ID_PROGRAM_KERJA' => $rka->ID_PROGRAM_KERJA,
                    'ID_REF_DANA'      => $detail['ID_REF_DANA'],
                    'QTY'              => $detail['QTY'],
                    'HARGA_SATUAN'     => $detail['HARGA_SATUAN'],
                    'VOLUME'           => $detail['VOLUME'] ?? 1,
                    'TOTAL_PROGKER'    => $subtotal,
                    'NOMINAL'          => $subtotal,
                    'SATUAN'           => $detail['SATUAN'] ?? null,
                    'TGL_AWAL'         => $detail['TGL_AWAL'] ?? null,
                    'TGL_AKHIR'        => $detail['TGL_AKHIR'] ?? null,
                ]);
            }

            $rka->NOMINAL += $subtotalInput;
            $rka->save();

            $this->logActivity('CREATE_RKA', 'Tambah RKA ID: ' . $rka->ID_PROGRAM_KERJA);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Anggaran berhasil disimpan.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT Update: Menolak jika data sudah di-soft delete (Poin 65)
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $rka = $this->findActiveRka($id);
            
            if (!$rka) {
                return response()->json(['success' => false, 'message' => 'Update gagal: Data tidak ditemukan atau sudah dihapus.'], 404);
            }

            if ($this->isRkaLocked($id)) {
                return response()->json(['success' => false, 'message' => 'Data terkunci oleh transaksi keuangan.'], 400);
            }

            DB::beginTransaction();
            $rka->update($request->except('details'));

            if ($request->has('details')) {
                RkaDetail::where('ID_PROGRAM_KERJA', $id)->delete();
                $newTotal = 0;
                foreach ($request->details as $detail) {
                    $subtotal = $detail['QTY'] * $detail['HARGA_SATUAN'] * ($detail['VOLUME'] ?? 1);
                    RkaDetail::create([
                        'ID_PROGRAM_KERJA' => $id,
                        'ID_REF_DANA'      => $detail['ID_REF_DANA'],
                        'QTY'              => $detail['QTY'],
                        'HARGA_SATUAN'     => $detail['HARGA_SATUAN'],
                        'VOLUME'           => $detail['VOLUME'] ?? 1,
                        'TOTAL_PROGKER'    => $subtotal,
                        'NOMINAL'          => $subtotal,
                        'SATUAN'           => $detail['SATUAN'] ?? null,
                    ]);
                    $newTotal += $subtotal;
                }
                $rka->NOMINAL = $newTotal;
                $rka->save();
            }

            $this->logActivity('UPDATE_RKA', 'Update RKA ID: ' . $id);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data diperbarui.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE Destroy: Mencegah penghapusan berulang (Poin 66)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $rka = $this->findActiveRka($id);

            if (!$rka) {
                return response()->json(['success' => false, 'message' => 'Hapus gagal: Data sudah tidak ada.'], 404);
            }

            if ($this->isRkaLocked($id)) {
                return response()->json(['success' => false, 'message' => 'Ditolak: Data sudah masuk laporan keuangan.'], 400);
            }

            DB::beginTransaction();
            $rka->update(['IS_DELETE' => 1]);
            $this->logActivity('DELETE_RKA', 'Hapus RKA ID: ' . $id);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * EXPORT PDF: Hanya data aktif
     */
    public function exportPdf(Request $request)
    {
        try {
            $data = Rka::with(['details'])
                ->where(function ($q) {
                    $q->where('IS_DELETE', '!=', 1)->orWhereNull('IS_DELETE');
                })->get();

            $pdf = app('dompdf.wrapper')->loadView('exports.rka_pdf', ['data' => $data]);
            $pdf->setPaper('a4', 'landscape');
            return $pdf->download('Laporan_RKA.pdf');
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function isRkaLocked($id_rka)
    {
        $details = RkaDetail::where('ID_PROGRAM_KERJA', $id_rka)->pluck('ID_DT_PROGKER');
        $tables = ['dtl_fpd', 'tr_bku', 'tr_bkk', 'tr_bkm'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && DB::table($table)->whereIn('ID_DT_PROGKER', $details)->exists()) {
                return true;
            }
        }
        return false;
    }

    private function logActivity($name, $desc)
    {
        if (Schema::hasTable('activity_log')) {
            $nextId = DB::table('activity_log')->max('ID_ACTIVITY_LOG') + 1;
            $username = Auth::check() ? Auth::user()->username : 'Admin_Testing';
            DB::table('activity_log')->insert([
                'ID_ACTIVITY_LOG' => $nextId,
                'ACTOR_USERNAME' => $username,
                'ACTIVITY_NAME' => $name,
                'ACTIVITY_DESCRIPTION' => $desc,
                'EVENT_TIME' => now(),
            ]);
        }
    }
}