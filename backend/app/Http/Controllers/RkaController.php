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


    public function index(Request $request): JsonResponse
    {
        try {
            $data = Rka::with(['details'])
                ->where(function ($q) {
                    $q->where('IS_DELETE', '!=', 1)
                    ->orWhereNull('IS_DELETE');
                })
                ->whereNotNull('NIP_VALIDATOR_PROGKER')
                ->get();

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
     * SEARCH: Mencari data aktif tanpa error 500 (FR-3.2.3.2)
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $keyword = trim((string) $request->query('keyword', ''));
            
            $query = Rka::with(['details'])
                ->where(function ($q) {
                    $q->where('mst_program_kerja.IS_DELETE', '!=', 1)
                      ->orWhereNull('mst_program_kerja.IS_DELETE');
                })->whereNotNull('NIP_VALIDATOR_PROGKER');

            if ($keyword !== '') {
                $query->where(function ($q) use ($keyword) {
                    $q->where('mst_program_kerja.PROGRAM_KERJA', 'LIKE', "%{$keyword}%")
                      ->orWhere('mst_program_kerja.INDIKATOR', 'LIKE', "%{$keyword}%")
                      ->orWhere('mst_program_kerja.KELUARAN_PROGKER', 'LIKE', "%{$keyword}%");
                });
            }

            $results = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Hasil pencarian untuk: ' . $keyword,
                'count' => $results->count(),
                'data' => $results
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * SHOW: Menampilkan satu data aktif
     */
    public function show($id): JsonResponse
    {
        try {
            $rka = $this->findActiveRka($id);
            if (!$rka) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan atau sudah dihapus'], 404);
            }
            return response()->json(['success' => true, 'data' => $rka]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * STORE: Simpan rincian anggaran (Poin 64)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'ID_PROGRAM_KERJA' => [
                'required', 'integer', 
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
            $rka = Rka::findOrFail($request->ID_PROGRAM_KERJA);
            $subtotalInput = 0;

            foreach ($request->details as $d) {
                $subtotalInput += ($d['QTY'] * $d['HARGA_SATUAN'] * ($d['VOLUME'] ?? 1));
            }

            if (Schema::hasTable('ref_pagu_unit')) {
                $pagu = DB::table('ref_pagu_unit')
                            ->where('ID_UNIT', $rka->ID_UNIT)
                            ->where('ID_TA_ANGGARAN', $rka->ID_TA_ANGGARAN)
                            ->value('NOMINAL_PAGU') ?? 0;
                            
                if ($pagu > 0 && ($rka->TOTAL_PROGKER + $subtotalInput) > $pagu) {
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
                    'NOMINAL'          => $subtotal,
                    'SATUAN'           => $detail['SATUAN'] ?? null,
                    'TGL_AWAL'         => $rka->WAKTU_AWAL,
                    'TGL_AKHIR'        => $rka->WAKTU_AKHIR,

                ]);
            }

            // $rka->TOTAL_PROGKER += $subtotalInput;
            // $rka->save();

            $this->logActivity('CREATE_RKA', 'Tambah RKA ID: ' . $rka->ID_PROGRAM_KERJA);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Anggaran berhasil disimpan.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * UPDATE: Perbarui data aktif & sinkronisasi detail (Poin 65)
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
            $rka->update($request->except(['details', 'NOMINAL']));

            if ($request->has('details')) {
                RkaDetail::where('ID_PROGRAM_KERJA', $id)->delete();
                // $newTotal = 0;
                foreach ($request->details as $detail) {
                    $subtotal = $detail['QTY'] * $detail['HARGA_SATUAN'] * ($detail['VOLUME'] ?? 1);
                    RkaDetail::create([
                        'ID_PROGRAM_KERJA' => $id,
                        'ID_REF_DANA'      => $detail['ID_REF_DANA'],
                        'QTY'              => $detail['QTY'],
                        'HARGA_SATUAN'     => $detail['HARGA_SATUAN'],
                        'VOLUME'           => $detail['VOLUME'] ?? 1,
                        'NOMINAL'          => $subtotal,
                        'SATUAN'           => $detail['SATUAN'] ?? null,
                        'TGL_AWAL'         => $rka->WAKTU_AWAL,
                        'TGL_AKHIR'        => $rka->WAKTU_AKHIR,
                    ]);
                    // $newTotal += $subtotal;
                }
                // $rka->TOTAL_PROGKER = $newTotal;
                // $rka->save();
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
     * DESTROY: Soft delete data aktif (Poin 66)
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

    public function updateDetail(Request $request, $id): JsonResponse
    {
        $request->validate([
            'ID_REF_DANA' => 'nullable|integer',
            'QTY' => 'required|integer|min:1',
            'VOLUME' => 'required|integer|min:1',
            'SATUAN' => 'required|string|max:50',
            'HARGA_SATUAN' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $detail = RkaDetail::find($id);

            if (!$detail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detail RKA tidak ditemukan.'
                ], 404);
            }

            $subtotal = $request->QTY * $request->HARGA_SATUAN * $request->VOLUME;

            $detail->update([
                'ID_REF_DANA' => $request->ID_REF_DANA,
                'QTY' => $request->QTY,
                'VOLUME' => $request->VOLUME,
                'SATUAN' => $request->SATUAN,
                'HARGA_SATUAN' => $request->HARGA_SATUAN,
                'NOMINAL' => $subtotal,
            ]);

            $this->logActivity('UPDATE_DETAIL_RKA', 'Update Detail RKA ID: ' . $id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Detail RKA berhasil diperbarui.',
                'data' => $detail,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyDetail($id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $detail = RkaDetail::find($id);

            if (!$detail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detail RKA tidak ditemukan.'
                ], 404);
            }

            $detail->delete();

            $this->logActivity('DELETE_DETAIL_RKA', 'Hapus Detail RKA ID: ' . $id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Detail RKA berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * EXPORT PDF: Hanya cetak data aktif (Poin 69)
     */
    public function exportPdf(Request $request)
    {
        try {
            $data = Rka::with(['details'])
                ->where(function ($q) {
                    $q->where('mst_program_kerja.IS_DELETE', '!=', 1)
                      ->orWhereNull('mst_program_kerja.IS_DELETE');
                })->whereNotNull('NIP_VALIDATOR_PROGKER')
                 ->get();

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

    public function approve(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'NIP_VALIDATOR_PROGKER' => 'required|string|max:20',
        ]);

        try {
            $rka = Rka::findOrFail($id);

            // Cek validator (Kepala Sekolah)
            $validator = DB::table('mst_karyawan')
                ->where('NIP_KARYAWAN', $request->NIP_VALIDATOR_PROGKER)
                ->first();

            if (!$validator || !str_contains(strtolower($validator->JABATAN_FUNGSIONAL ?? ''), 'kepala sekolah')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Kepala Sekolah yang boleh approve RKA',
                ], 403);
            }

            // Cek sudah pernah approve atau belum
            if ($rka->NIP_VALIDATOR_PROGKER) {
                return response()->json([
                    'success' => false,
                    'message' => 'RKA sudah disetujui',
                ], 400);
            }

            // cek pernah diajukan
            $lastPm = DB::table('tr_pm')
                ->where('ID_PROGRAM_KERJA', $id)
                ->orderByDesc('ID_PM')
                ->first();

            if ($lastPm && str_starts_with(strtolower($lastPm->DESKRIPSI_TR_PM ?? ''), 'ditolak')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sebelumnya ditolak, tidak bisa di-approve',
                ], 400);
            }

            // Cek FPD (anggaran tersedia)
            $fpd = DB::table('fpd_anggaran')
                ->where('ID_PROGRAM_KERJA', $rka->ID_PROGRAM_KERJA)
                ->first();

            if (!$fpd) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data anggaran FPD tidak ditemukan',
                ], 400);
            }

            // Cek sisa dana
            if ($fpd->NOMINAL_SISA <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dana sudah habis',
                ], 400);
            }

            // Cek total tidak melebihi sisa dana
            if ($rka->TOTAL_PROGKER > $fpd->NOMINAL_SISA) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total melebihi sisa dana FPD',
                ], 400);
            }

            // Approve
            $rka->update([
                'NIP_VALIDATOR_PROGKER' => $request->NIP_VALIDATOR_PROGKER,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'RKA berhasil disetujui',
                'data' => $rka
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal approve RKA',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'NIP_VALIDATOR_PROGKER' => 'required|string|max:20',
            'DESKRIPSI_TR_PM' => 'required|string', // alasan langsung di sini
        ]);

        try {
            $rka = Rka::findOrFail($id);

            // Cek validator (Kepala Sekolah)
            $validator = DB::table('mst_karyawan')
                ->where('NIP_KARYAWAN', $request->NIP_VALIDATOR_PROGKER)
                ->first();

            if (!$validator || !str_contains(strtolower($validator->JABATAN_FUNGSIONAL ?? ''), 'kepala sekolah')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Kepala Sekolah yang boleh reject RKA',
                ], 403);
            }

            // Cek sudah di-approve atau belum
            if ($rka->NIP_VALIDATOR_PROGKER) {
                return response()->json([
                    'success' => false,
                    'message' => 'RKA sudah disetujui, tidak bisa ditolak',
                ], 400);
            }

            // Ambil log terakhir 
            $lastPm = DB::table('tr_pm')
                ->where('ID_PROGRAM_KERJA', $id)
                ->orderByDesc('ID_PM')
                ->first();

            $baseDesc = $lastPm?->DESKRIPSI_TR_PM ?? 'Program Kerja';

            // Format deskripsi
            $deskripsiBaru = $baseDesc . ' : Ditolak: ' . $request->DESKRIPSI_TR_PM;

            // Simpan ke tr_pm
            DB::table('tr_pm')->insert([
                'ID_PROGRAM_KERJA' => $rka->ID_PROGRAM_KERJA,
                'NIP_VALIDATOR_PM' => $request->NIP_VALIDATOR_PROGKER,
                'DESKRIPSI_TR_PM' => $deskripsiBaru,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'RKA berhasil ditolak',
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal reject RKA',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}