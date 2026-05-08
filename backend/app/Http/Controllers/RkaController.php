<?php

namespace App\Http\Controllers;

use App\Models\Rka;
use App\Models\MstProgramKerja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RkasExport;

class RkaController extends Controller
{
    private function findActiveRka($id)
    {
        return Rka::with(['rkt', 'refDana'])
            ->where('ID_DT_PROGKER', $id)
            ->whereHas('rkt', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('IS_DELETE', '!=', 1)
                        ->orWhereNull('IS_DELETE');
                });
            })
            ->first();
    }

    // public function index(Request $request): JsonResponse
    // {
    //     try {
    //         $data = Rka::with(['rkt', 'refDana'])
    //             ->whereHas('rkt', function ($q) {
    //                 $q->where(function ($sub) {
    //                     $sub->where('IS_DELETE', '!=', 1)
    //                         ->orWhereNull('IS_DELETE');
    //                 });
    //                 $q->whereNotNull('NIP_VALIDATOR_PROGKER');
    //             })
    //             ->get();
    //         return response()->json([
    //             'success' => true,
    //             'count' => $data->count(),
    //             'data' => $data,
    //         ]);
    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'success' => false,
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function index(Request $request): JsonResponse
    {
        try {
            $data = MstProgramKerja::with([
                'Rka.refDana'
            ])
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
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $keyword = trim((string) $request->query('keyword', ''));
            $query = Rka::with(['rkt', 'refDana'])
                ->whereHas('rkt', function ($q) use ($keyword) {
                    $q->where(function ($sub) {
                        $sub->where('IS_DELETE', '!=', 1)
                            ->orWhereNull('IS_DELETE');
                    });
                    $q->whereNotNull('NIP_VALIDATOR_PROGKER');
                    if ($keyword !== '') {
                        $q->where(function ($x) use ($keyword) {
                            $x->where('PROGRAM_KERJA', 'LIKE', "%{$keyword}%")
                                ->orWhere('INDIKATOR', 'LIKE', "%{$keyword}%")
                                ->orWhere('KELUARAN_PROGKER', 'LIKE', "%{$keyword}%");
                        });
                    }
                });
            $results = $query->get();
            return response()->json([
                'success' => true,
                'message' => 'Hasil pencarian untuk: ' . $keyword,
                'count' => $results->count(),
                'data' => $results
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $rka = $this->findActiveRka($id);
            if (!$rka) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.'
                ], 404);
            }
            return response()->json([
                'success' => true,
                'data' => $rka
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'ID_PROGRAM_KERJA' => 'required|integer|exists:mst_program_kerja,ID_PROGRAM_KERJA',
            'ID_REF_DANA' => 'required|integer|exists:ref_sumber_dana,ID_REF_DANA',
            'QTY' => 'required|integer|min:1',
            'HARGA_SATUAN' => 'required|numeric|min:0',
            'VOLUME' => 'required|integer|min:1',
            'SATUAN' => 'nullable|string|max:50',
            'TGL_AWAL' => 'nullable|date',
            'TGL_AKHIR' => 'nullable|date',
        ]);
        try {
            DB::beginTransaction();
            $subtotal =
                $request->QTY *
                $request->HARGA_SATUAN *
                $request->VOLUME;
            $rka = Rka::create([
                'ID_PROGRAM_KERJA' => $request->ID_PROGRAM_KERJA,
                'ID_REF_DANA' => $request->ID_REF_DANA,
                'QTY' => $request->QTY,
                'HARGA_SATUAN' => $request->HARGA_SATUAN,
                'VOLUME' => $request->VOLUME,
                'SATUAN' => $request->SATUAN,
                'NOMINAL' => $subtotal,
                'TGL_AWAL' => $request->TGL_AWAL,
                'TGL_AKHIR' => $request->TGL_AKHIR,
            ]);
            $this->logActivity(
                'CREATE_RKA',
                'Tambah RKA ID: ' . $rka->ID_DT_PROGKER
            );
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'RKA berhasil disimpan.',
                'data' => $rka
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'ID_REF_DANA' => 'required|integer|exists:ref_sumber_dana,ID_REF_DANA',
            'QTY' => 'required|integer|min:1',
            'HARGA_SATUAN' => 'required|numeric|min:0',
            'VOLUME' => 'required|integer|min:1',
            'SATUAN' => 'nullable|string|max:50',
            'TGL_AWAL' => 'nullable|date',
            'TGL_AKHIR' => 'nullable|date',
        ]);
        try {
            $rka = $this->findActiveRka($id);
            if (!$rka) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.'
                ], 404);
            }
            if ($this->isRkaLocked($id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sudah digunakan transaksi.'
                ], 400);
            }
            DB::beginTransaction();
            $nominal =
                $request->QTY *
                $request->HARGA_SATUAN *
                $request->VOLUME;
            $rka->update([
                'ID_REF_DANA' => $request->ID_REF_DANA,
                'QTY' => $request->QTY,
                'HARGA_SATUAN' => $request->HARGA_SATUAN,
                'VOLUME' => $request->VOLUME,
                'SATUAN' => $request->SATUAN,
                'NOMINAL' => $nominal,
                'TGL_AWAL' => $request->TGL_AWAL,
                'TGL_AKHIR' => $request->TGL_AKHIR,
            ]);
            $this->logActivity(
                'UPDATE_RKA',
                'Update RKA ID: ' . $id
            );
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui.',
                'data' => $rka
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $rka = $this->findActiveRka($id);
            if (!$rka) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.'
                ], 404);
            }
            if ($this->isRkaLocked($id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sudah digunakan transaksi.'
                ], 400);
            }
            DB::beginTransaction();
            $rka->delete();
            $this->logActivity(
                'DELETE_RKA',
                'Hapus RKA ID: ' . $id
            );
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function export()
    {
        try {
            return Excel::download(
                new RkasExport(),
                'Laporan_RKA.xlsx'
            );
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $data = Rka::with(['rkt', 'refDana'])
                ->whereHas('rkt', function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('IS_DELETE', '!=', 1)
                            ->orWhereNull('IS_DELETE');
                    })
                    ->whereNotNull('NIP_VALIDATOR_PROGKER');
                })
                ->get();
            $pdf = app('dompdf.wrapper')
                ->loadView('exports.rka_pdf', compact('data'));
            $pdf->setPaper('a4', 'landscape');
            return $pdf->download('Laporan_RKA.pdf');
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function isRkaLocked($id_rka)
    {
        $tables = [
            'dtl_fpd',
            'tr_bku',
            'tr_bkk',
            'tr_bkm'
        ];
        foreach ($tables as $table) {
            if (
                Schema::hasTable($table) &&
                DB::table($table)
                    ->where('ID_DT_PROGKER', $id_rka)
                    ->exists()
            ) {
                return true;
            }
        }
        return false;
    }

    private function logActivity($name, $desc)
    {
        if (Schema::hasTable('activity_log')) {
            $nextId =
                (DB::table('activity_log')
                    ->max('ID_ACTIVITY_LOG') ?? 0) + 1;
            $username = Auth::check()
                ? Auth::user()->username
                : 'Admin_Testing';
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