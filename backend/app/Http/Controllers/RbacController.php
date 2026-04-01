<?php

namespace App\Http\Controllers;

use App\Models\JabatanMenu;
use App\Models\MstSiMenu;
use App\Models\RefJabatanStr;
use App\Services\AuditTrailService;
use App\Services\RbacService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RbacController extends Controller
{
    public function __construct(
        protected RbacService $rbacService,
        protected AuditTrailService $auditTrailService
    ) {
    }

    /**
     * List semua jabatan aktif/valid.
     *
     * NOTE:
     * - modul RBAC admin butuh daftar role/jabatan yang bisa dikelola
     * - sementara kita filter IS_VALID_JABATAN = 1 kalau field itu memang dipakai
     * - kalau ternyata data master jabatan di project kalian tidak konsisten,
     *   query ini bisa disederhanakan lagi nanti
     */
    public function listJabatan(): JsonResponse
    {
        $jabatan = RefJabatanStr::query()
            ->when(
                $this->columnExists('ref_jabatan_str', 'IS_VALID_JABATAN'),
                fn ($query) => $query->where('IS_VALID_JABATAN', 1)
            )
            ->orderBy('DESKRIPSI_JABATAN')
            ->get([
                'ID_JABATAN',
                'DESKRIPSI_JABATAN',
                'IS_VALID_JABATAN',
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Daftar jabatan berhasil diambil.',
            'data' => $jabatan,
        ]);
    }

    /**
     * List semua menu permission yang tersedia.
     *
     * Query param opsional:
     * - id_si: filter per sistem/modul
     * - search: cari berdasarkan nama/deskripsi menu
     *
     * NOTE:
     * - permission kita baca dari mst_si_menu.NAMA_MENU
     * - idealnya isi NAMA_MENU granular, misalnya coa.view, coa.create, dst
     */
    public function listMenu(Request $request): JsonResponse
    {
        $query = MstSiMenu::query()
            ->with('sistem')
            ->where('IS_DELETE', 0);

        if ($request->filled('id_si')) {
            $query->where('ID_SI', $request->integer('id_si'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('NAMA_MENU', 'like', "%{$search}%")
                    ->orWhere('DESKRIPSI_MENU', 'like', "%{$search}%");
            });
        }

        $menus = $query
            ->orderBy('ID_SI')
            ->orderBy('NAMA_MENU')
            ->get([
                'ID_SI_ROLE_MENU',
                'ID_SI',
                'NAMA_MENU',
                'DESKRIPSI_MENU',
                'IS_DELETE',
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Daftar menu berhasil diambil.',
            'data' => $menus,
        ]);
    }

    /**
     * Lihat mapping jabatan -> menu.
     *
     * Query param opsional:
     * - id_jabatan
     * - id_si
     *
     * NOTE:
     * - endpoint ini penting buat admin lihat hak akses role saat ini
     */
    public function listJabatanMenu(Request $request): JsonResponse
    {
        $query = JabatanMenu::query()
            ->with([
                'jabatan:ID_JABATAN,DESKRIPSI_JABATAN',
                'menu:ID_SI_ROLE_MENU,ID_SI,NAMA_MENU,DESKRIPSI_MENU,IS_DELETE',
                'menu.sistem:ID_SI,NAMA_SI,DESKRIPSI_SI',
            ]);

        if ($request->filled('id_jabatan')) {
            $query->where('ID_JABATAN', $request->integer('id_jabatan'));
        }

        if ($request->filled('id_si')) {
            $idSi = $request->integer('id_si');

            $query->whereHas('menu', function ($q) use ($idSi) {
                $q->where('ID_SI', $idSi);
            });
        }

        $data = $query
            ->orderBy('ID_JABATAN')
            ->orderBy('ID_SI_ROLE_MENU')
            ->get([
                'ID_HAK_AKSES',
                'ID_JABATAN',
                'ID_SI_ROLE_MENU',
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Mapping jabatan-menu berhasil diambil.',
            'data' => $data,
        ]);
    }

    /**
     * Tambah 1 menu ke 1 jabatan.
     *
     * Body:
     * - id_jabatan
     * - id_si_role_menu
     *
     * NOTE:
     * - kita cek duplicate dulu biar tidak insert ganda
     * - ID_HAK_AKSES diasumsikan tidak auto increment, jadi kita generate manual
     *   karena dari pola DB kalian banyak ID master/transaksi manual
     * - kalau ternyata tabel ini auto increment, bagian set ID_HAK_AKSES bisa dihapus nanti
     */
    public function assignMenuToJabatan(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_jabatan' => ['required', 'integer', 'exists:ref_jabatan_str,ID_JABATAN'],
            'id_si_role_menu' => ['required', 'integer', 'exists:mst_si_menu,ID_SI_ROLE_MENU'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $idJabatan = (int) $request->id_jabatan;
        $idMenu = (int) $request->id_si_role_menu;

        $existing = JabatanMenu::query()
            ->where('ID_JABATAN', $idJabatan)
            ->where('ID_SI_ROLE_MENU', $idMenu)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Menu sudah terpasang pada jabatan ini.',
                'data' => $existing,
            ], 409);
        }

        $mapping = DB::transaction(function () use ($idJabatan, $idMenu, $request) {
            $nextId = ((int) JabatanMenu::max('ID_HAK_AKSES')) + 1;

            $created = JabatanMenu::query()->create([
                'ID_HAK_AKSES' => $nextId,
                'ID_JABATAN' => $idJabatan,
                'ID_SI_ROLE_MENU' => $idMenu,
            ]);

            $created->load([
                'jabatan:ID_JABATAN,DESKRIPSI_JABATAN',
                'menu:ID_SI_ROLE_MENU,ID_SI,NAMA_MENU,DESKRIPSI_MENU',
            ]);

            $this->auditTrailService->log(
                actor: $request->user(),
                activityName: 'RBAC_ASSIGN_MENU',
                relatedData: json_encode([
                    'id_hak_akses' => $created->ID_HAK_AKSES,
                    'id_jabatan' => $created->ID_JABATAN,
                    'jabatan' => $created->jabatan?->DESKRIPSI_JABATAN,
                    'id_si_role_menu' => $created->ID_SI_ROLE_MENU,
                    'menu' => $created->menu?->NAMA_MENU,
                ], JSON_UNESCAPED_UNICODE),
                description: 'Admin menambahkan hak akses menu ke jabatan.',
                actorRole: 'Admin Sistem'
            );

            return $created;
        });

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil ditambahkan ke jabatan.',
            'data' => $mapping,
        ], 201);
    }

    /**
     * Hapus 1 menu dari 1 jabatan berdasarkan ID_HAK_AKSES.
     *
     * NOTE:
     * - hapus by primary key paling aman, supaya tidak ambigu
     * - admin UI nanti cukup kirim ID_HAK_AKSES dari hasil list mapping
     */
    public function revokeMenuFromJabatan(int $idHakAkses, Request $request): JsonResponse
    {
        $mapping = JabatanMenu::query()
            ->with([
                'jabatan:ID_JABATAN,DESKRIPSI_JABATAN',
                'menu:ID_SI_ROLE_MENU,ID_SI,NAMA_MENU,DESKRIPSI_MENU',
            ])
            ->find($idHakAkses);

        if (!$mapping) {
            return response()->json([
                'success' => false,
                'message' => 'Mapping hak akses tidak ditemukan.',
            ], 404);
        }

        $snapshot = [
            'id_hak_akses' => $mapping->ID_HAK_AKSES,
            'id_jabatan' => $mapping->ID_JABATAN,
            'jabatan' => $mapping->jabatan?->DESKRIPSI_JABATAN,
            'id_si_role_menu' => $mapping->ID_SI_ROLE_MENU,
            'menu' => $mapping->menu?->NAMA_MENU,
        ];

        DB::transaction(function () use ($mapping, $snapshot, $request) {
            $mapping->delete();

            $this->auditTrailService->log(
                actor: $request->user(),
                activityName: 'RBAC_REVOKE_MENU',
                relatedData: json_encode($snapshot, JSON_UNESCAPED_UNICODE),
                description: 'Admin menghapus hak akses menu dari jabatan.',
                actorRole: 'Admin Sistem'
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil dicabut dari jabatan.',
            'data' => $snapshot,
        ]);
    }

    /**
     * Sync full daftar menu untuk 1 jabatan.
     *
     * Body:
     * - id_jabatan
     * - menu_ids: array ID_SI_ROLE_MENU
     *
     * NOTE:
     * - ini enak dipakai kalau frontend nanti kirim "state final" checkbox
     * - method ini akan:
     *   1) tambah yang belum ada
     *   2) hapus yang tidak ada di daftar baru
     */
    public function syncJabatanMenu(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_jabatan' => ['required', 'integer', 'exists:ref_jabatan_str,ID_JABATAN'],
            'menu_ids' => ['required', 'array'],
            'menu_ids.*' => ['integer', 'exists:mst_si_menu,ID_SI_ROLE_MENU'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $idJabatan = (int) $request->id_jabatan;
        $newMenuIds = collect($request->menu_ids)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $result = DB::transaction(function () use ($idJabatan, $newMenuIds, $request) {
            $currentMappings = JabatanMenu::query()
                ->where('ID_JABATAN', $idJabatan)
                ->get(['ID_HAK_AKSES', 'ID_SI_ROLE_MENU']);

            $currentMenuIds = $currentMappings
                ->pluck('ID_SI_ROLE_MENU')
                ->map(fn ($id) => (int) $id)
                ->all();

            $toAdd = array_values(array_diff($newMenuIds, $currentMenuIds));
            $toDelete = $currentMappings
                ->filter(fn ($row) => !in_array((int) $row->ID_SI_ROLE_MENU, $newMenuIds, true))
                ->values();

            foreach ($toAdd as $menuId) {
                $nextId = ((int) JabatanMenu::max('ID_HAK_AKSES')) + 1;

                JabatanMenu::query()->create([
                    'ID_HAK_AKSES' => $nextId,
                    'ID_JABATAN' => $idJabatan,
                    'ID_SI_ROLE_MENU' => $menuId,
                ]);
            }

            foreach ($toDelete as $row) {
                JabatanMenu::query()
                    ->where('ID_HAK_AKSES', $row->ID_HAK_AKSES)
                    ->delete();
            }

            $jabatan = RefJabatanStr::query()
                ->find($idJabatan, ['ID_JABATAN', 'DESKRIPSI_JABATAN']);

            $menuNames = MstSiMenu::query()
                ->whereIn('ID_SI_ROLE_MENU', $newMenuIds)
                ->pluck('NAMA_MENU')
                ->values()
                ->all();

            $this->auditTrailService->log(
                actor: $request->user(),
                activityName: 'RBAC_SYNC_MENU',
                relatedData: json_encode([
                    'id_jabatan' => $idJabatan,
                    'jabatan' => $jabatan?->DESKRIPSI_JABATAN,
                    'menu_ids_final' => $newMenuIds,
                    'menu_names_final' => $menuNames,
                    'added_menu_ids' => $toAdd,
                    'removed_menu_ids' => $toDelete->pluck('ID_SI_ROLE_MENU')->values()->all(),
                ], JSON_UNESCAPED_UNICODE),
                description: 'Admin melakukan sinkronisasi hak akses menu pada jabatan.',
                actorRole: 'Admin Sistem'
            );

            return [
                'id_jabatan' => $idJabatan,
                'added_menu_ids' => $toAdd,
                'removed_menu_ids' => $toDelete->pluck('ID_SI_ROLE_MENU')->values()->all(),
                'final_menu_ids' => $newMenuIds,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Hak akses jabatan berhasil disinkronkan.',
            'data' => $result,
        ]);
    }

    /**
     * Helper kecil untuk jaga-jaga kalau column tertentu belum pasti ada.
     * Ini optional, tapi aman buat project besar yang masih bergerak.
     */
    private function columnExists(string $table, string $column): bool
    {
        static $cache = [];

        $key = "{$table}.{$column}";

        if (!array_key_exists($key, $cache)) {
            $cache[$key] = DB::getSchemaBuilder()->hasColumn($table, $column);
        }

        return $cache[$key];
    }
}