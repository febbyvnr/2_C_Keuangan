<?php

namespace App\Services;

use App\Models\JabatanMenu;
use App\Models\TrJabatan;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

class RbacService
{
    /**
     * NOTE:
     * Auth final project belum fix.
     * Jadi identitas user sementara dicari dari beberapa kemungkinan field.
     * Nanti kalau timmu sudah punya model auth final, cukup rapikan method resolveUserIdentifier().
     */
    public function resolveUserIdentifier(?Authenticatable $user): ?string
    {
        if (!$user) {
            return null;
        }

        foreach (['NIP_KARYAWAN', 'nip_karyawan', 'nip', 'username', 'email'] as $field) {
            if (isset($user->{$field}) && filled($user->{$field})) {
                return (string) $user->{$field};
            }
        }

        return null;
    }

    /**
     * Ambil semua jabatan aktif user.
     * NOTE:
     * tr_jabatan tidak punya flag aktif, jadi kita anggap aktif kalau:
     * - TGL_MULAI_JABATAN null / <= hari ini
     * - TGL_SELESAI_JABATAN null / >= hari ini
     */
    public function getActiveAssignments(?Authenticatable $user): Collection
    {
        $identifier = $this->resolveUserIdentifier($user);

        if (!$identifier) {
            return collect();
        }

        $today = Carbon::today()->toDateString();

        return TrJabatan::query()
            ->with('jabatan')
            ->where('NIP_KARYAWAN', $identifier)
            ->where(function ($query) use ($today) {
                $query->whereNull('TGL_MULAI_JABATAN')
                    ->orWhereDate('TGL_MULAI_JABATAN', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('TGL_SELESAI_JABATAN')
                    ->orWhereDate('TGL_SELESAI_JABATAN', '>=', $today);
            })
            ->get();
    }

    public function getActiveJabatanIds(?Authenticatable $user): array
    {
        return $this->getActiveAssignments($user)
            ->pluck('ID_JABATAN')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function getActiveJabatanNames(?Authenticatable $user): array
    {
        return $this->getActiveAssignments($user)
            ->pluck('jabatan.DESKRIPSI_JABATAN')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function hasJabatan(?Authenticatable $user, string $jabatanName): bool
    {
        $target = mb_strtolower(trim($jabatanName));

        return collect($this->getActiveJabatanNames($user))
            ->map(fn ($name) => mb_strtolower(trim((string) $name)))
            ->contains($target);
    }

    /**
     * NOTE:
     * Permission kita baca dari mst_si_menu.NAMA_MENU.
     * Jadi isi NAMA_MENU nanti harus konsisten, misalnya:
     * - coa.view
     * - coa.create
     * - coa.update
     * - coa.delete
     */
    public function hasMenuAccess(?Authenticatable $user, string $menuName): bool
    {
        $jabatanIds = $this->getActiveJabatanIds($user);

        if (empty($jabatanIds)) {
            return false;
        }

        return JabatanMenu::query()
            ->whereIn('ID_JABATAN', $jabatanIds)
            ->whereHas('menu', function ($query) use ($menuName) {
                $query->where('IS_DELETE', 0)
                    ->where('NAMA_MENU', $menuName);
            })
            ->exists();
    }

    public function isAdmin(?Authenticatable $user): bool
    {
        /**
         * NOTE:
         * Ini sementara pakai nama jabatan business-readable.
         * Kalau nanti data master jabatan memakai nama lain, ganti string di sini.
         */
        return $this->hasJabatan($user, 'Admin Sistem');
    }
}