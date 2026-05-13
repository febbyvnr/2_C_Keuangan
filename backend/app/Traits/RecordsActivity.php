<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

        // 3. Ekstraksi Token Kebal Error (Bulletproof)
        $username    = 'Sistem / Tidak Ditemukan';
        $role        = 'Unknown';
        $accessLogId = null;

        // Membaca token otorisasi melalui fungsi bawaan atau pengaksesan header mentah
        $token = request()->bearerToken() ?? request()->header('Authorization');

        if ($token) {
            try {
                // Hapus prefiks 'Bearer ' serta pangkas sisa tanda kutip/spasi
                $cleanToken = trim(str_replace('Bearer ', '', $token), '"\' ');
                $payload = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($cleanToken));
                
                if ($payload) {
                    $username    = $payload->nip ?? $username;
                    $role        = $payload->role ?? $role;
                    $accessLogId = $payload->id_access_log ?? null;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('RecordsActivity gagal mengurai token: ' . $e->getMessage());
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