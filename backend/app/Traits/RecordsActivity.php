<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

trait RecordsActivity
{
    public static function bootRecordsActivity()
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            // WAJIB PAKAI TANDA DOLAR ($event) AGAR DINAMIS:
            static::$event(function ($model) use ($event) {
                $model->recordActivity($event);
            });
        }
    }

protected function recordActivity($event)
    {
        // 1. Blokir pencatatan ganda saat data pertama kali dibuat
        if ($event === 'updated' && $this->wasRecentlyCreated) {
            return; 
        }

        // 2. Pembajakan status Soft Delete agar antarmuka menampilkan label 'DELETED'
        $perubahan = $this->getChanges();
        if ($event === 'updated' && isset($perubahan['IS_DELETE']) && $perubahan['IS_DELETE'] == 1) {
            $event = 'deleted';
        }

        // 3. Identitas aktor (prioritas: user Sanctum aktif)
        $username    = 'Sistem / Tidak Ditemukan';
        $role        = 'Unknown';
        $accessLogId = null;

        $request = request();
        if ($request) {
            $actor = $request->user();

            if ($actor) {
                $username = (string) ($actor->NIP_KARYAWAN ?? $actor->NISN_SISWA ?? $actor->ID_SISWA_TETAP ?? $username);

                if (method_exists($actor, 'jabatans')) {
                    $jabatan = $actor->jabatans()->pluck('DESKRIPSI_JABATAN')->filter()->values()->all();
                    if (!empty($jabatan)) {
                        $role = implode(', ', $jabatan);
                    }
                }

                if ($role === 'Unknown') {
                    if ($request->user()->tokenCan('role:super-admin')) {
                        $role = 'Super Admin';
                    } elseif ($request->user()->tokenCan('role:siswa')) {
                        $role = 'Siswa';
                    } elseif ($request->user()->tokenCan('role:karyawan')) {
                        $role = 'Karyawan';
                    }
                }
            } else {
                // Fallback Sanctum: resolve user langsung dari bearer token
                $plainToken = $request->bearerToken();
                if ($plainToken) {
                    $pat = PersonalAccessToken::findToken($plainToken);
                    $tokenable = $pat?->tokenable;

                    if ($tokenable) {
                        $username = (string) ($tokenable->NIP_KARYAWAN ?? $tokenable->NISN_SISWA ?? $tokenable->ID_SISWA_TETAP ?? $username);

                        if (method_exists($tokenable, 'jabatans')) {
                            $jabatan = $tokenable->jabatans()->pluck('DESKRIPSI_JABATAN')->filter()->values()->all();
                            if (!empty($jabatan)) {
                                $role = implode(', ', $jabatan);
                            }
                        }

                        if ($role === 'Unknown') {
                            $abilities = $pat->abilities ?? [];
                            if (in_array('role:super-admin', $abilities, true)) {
                                $role = 'Super Admin';
                            } elseif (in_array('role:siswa', $abilities, true)) {
                                $role = 'Siswa';
                            } elseif (in_array('role:karyawan', $abilities, true)) {
                                $role = 'Karyawan';
                            }
                        }
                    }
                }

                if ($username !== 'Sistem / Tidak Ditemukan') {
                    // sudah sukses resolve dari Sanctum token, skip fallback legacy
                } else {
                // Fallback legacy untuk endpoint lama yang masih menyimpan payload terenkripsi di bearer token
                $token = $request->bearerToken() ?? $request->header('Authorization');
                if ($token) {
                    try {
                        $cleanToken = trim(str_replace('Bearer ', '', $token), '"\' ');
                        $payload = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($cleanToken));
                        if ($payload) {
                            $username    = $payload->nip ?? $username;
                            $role        = $payload->role ?? $role;
                            $accessLogId = $payload->id_access_log ?? null;
                        }
                    } catch (\Exception $e) {
                        // Abaikan fallback error agar tidak memutus proses bisnis utama
                    }
                }
                }
            }
        }

        // 4. Peracikan Nama Aktivitas
        $activityName = strtoupper($event) . ' ' . class_basename($this);

        // 5. Peracikan Deskripsi Riwayat
        $relatedData = 'ID Record: ' . $this->getKey();
        $description = '';

        if ($event === 'created') {
            $description = "Berhasil menginput data baru ke dalam sistem.";
        } elseif ($event === 'deleted') {
            $description = "Data telah dihapus dari sistem.";
        } elseif ($event === 'updated') {
            $listPerubahan = [];
            foreach ($perubahan as $kolom => $nilaiBaru) {
                if ($kolom === 'IS_DELETE') continue;
                $nilaiLama = $this->getOriginal($kolom) ?? '(kosong)';
                $listPerubahan[] = "$kolom dari '$nilaiLama' menjadi '$nilaiBaru'";
            }
            $description = !empty($listPerubahan) ? "Mengubah nilai " . implode(', ', $listPerubahan) : "Melakukan pembaruan data.";
        }

        $description = Str::limit($description, 245);

        // 6. Penyimpanan Riwayat
        \App\Models\ActivityLog::create([
            'ID_ACCESS_LOG'        => $accessLogId,
            'EVENT_TIME'           => now(),
            'ACTOR_USERNAME'       => $username,
            'ACTOR_ROLE'           => $role,
            'ACTIVITY_NAME'        => $activityName,
            'RELATED_DATA'         => $relatedData,
            'ACTIVITY_DESCRIPTION' => $description
        ]);
    }
}
