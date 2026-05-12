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
            static::$event(function ($model) use ($event) {
                $model->recordActivity($event);
            });
        }
    }

    protected function recordActivity($event)
    {
        // 1. Blokir eksekusi ganda jika objek baru saja di-insert
        if ($event === 'updated' && $this->wasRecentlyCreated) {
            return; 
        }

        // --- 2. MANIPULASI STATUS SOFT DELETE DEMI UI FRONTEND ---
        // Menggunakan array $perubahan secara langsung agar terhindar dari Undefined Property
        $perubahan = $this->getChanges();
        if ($event === 'updated' && isset($perubahan['IS_DELETE']) && $perubahan['IS_DELETE'] == 1) {
            $event = 'deleted';
        }

        // 3. Tangkap identitas user dari Bearer Token
        $token = request()->bearerToken();
        $accessLogId = null;
        $username    = 'Sistem / Tidak Ditemukan';
        $role        = 'Unknown';

        if ($token) {
            try {
                $payload = json_decode(Crypt::decryptString($token));
                $accessLogId = $payload->id_access_log ?? null;
                $username    = $payload->nip ?? 'Unknown';
                $role        = $payload->role ?? 'Unknown';
            } catch (\Exception $e) {
                Log::warning('Token log gagal diurai: ' . $e->getMessage());
            }
        }

        // 4. Cetak string aktivitas ("DELETED MstCoa", "UPDATED RefTarif", dll.)
        $activityName = strtoupper($event) . ' ' . class_basename($this);

        // 5. Susun deskripsi riwayat yang bersih
        $relatedData = 'ID Record: ' . $this->getKey();
        $description = '';

        if ($event === 'created') {
            $description = "Berhasil menginput data baru ke dalam sistem.";
        } elseif ($event === 'deleted') {
            $description = "Data telah dihapus dari sistem.";
        } elseif ($event === 'updated') {
            $listPerubahan = [];
            foreach ($perubahan as $kolom => $nilaiBaru) {
                // Abaikan pencatatan teknis jika ada flag sistem lain yang ikut terubah
                if ($kolom === 'IS_DELETE') continue;

                $nilaiLama = $this->getOriginal($kolom) ?? '(kosong)';
                $nilaiBaru = $nilaiBaru ?? '(kosong)';
                $listPerubahan[] = "$kolom dari '$nilaiLama' menjadi '$nilaiBaru'";
            }
            
            $description = !empty($listPerubahan) 
                ? "Mengubah nilai " . implode(', ', $listPerubahan)
                : "Melakukan pembaruan atribut data.";
        }

        // Batasi panjang string agar aman masuk ke database
        $description = Str::limit($description, 245);

        // 6. Eksekusi penyimpanan ke tabel riwayat
        ActivityLog::create([
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