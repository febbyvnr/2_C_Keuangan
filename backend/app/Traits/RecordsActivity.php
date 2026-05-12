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
        // 1. Blokir eksekusi ganda jika objek baru saja dibuat
        if ($event === 'updated' && $this->wasRecentlyCreated) {
            return; 
        }

        // 2. Tangkap identitas dari token secara mandiri dan aman
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
                // Gagal silent agar tidak merusak respons JSON ke React
                Log::warning('Token log gagal diurai: ' . $e->getMessage());
            }
        }

        // 3. Susun nama aktivitas singkat demi mempertahankan UI badge aslimu
        $activityName = strtoupper($event) . ' ' . class_basename($this);

        // 4. Buat deskripsi perubahan teks manusiawi
        $relatedData = 'ID Record: ' . $this->getKey();
        $description = '';

        if ($event === 'created') {
            $description = "Berhasil menginput data baru ke dalam sistem.";
        } elseif ($event === 'deleted') {
            $description = "Data telah dihapus dari sistem.";
        } elseif ($event === 'updated') {
            $listPerubahan = [];
            foreach ($this->getChanges() as $kolom => $nilaiBaru) {
                $nilaiLama = $this->getOriginal($kolom) ?? '(kosong)';
                $nilaiBaru = $nilaiBaru ?? '(kosong)';
                $listPerubahan[] = "$kolom dari '$nilaiLama' menjadi '$nilaiBaru'";
            }
            $description = "Mengubah nilai " . implode(', ', $listPerubahan);
        }

        // Amankan dari batas kolom VARCHAR(255) MySQL
        $description = Str::limit($description, 245);

        // 5. Simpan ke database
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