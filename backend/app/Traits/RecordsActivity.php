<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Crypt;
use Exception;

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
        $username = 'System';
        $role = 'Unknown';
        $accessLogId = null;

        // 1. Ambil & Decrypt token dari request header
        $token = request()->bearerToken();

        if ($token) {
            try {
                $decrypted = Crypt::decryptString($token);
                $tokenData = json_decode($decrypted);

                $username = $tokenData->nip ?? 'System';
                $role = $tokenData->role ?? 'Unknown';
                $accessLogId = $tokenData->id_access_log ?? null;
            } catch (Exception $e) {
                // Jika token tidak valid/bisa didecrypt, abaikan atau biarkan tercatat sebagai System
            }
        }

        // 2. Nama Aktivitas
        $activityName = strtoupper($event) . ' ' . class_basename($this);

        // 3. Data yang Terkait
        $relatedData = 'PK ID: ' . $this->getKey();

        // 4. Deskripsi Perubahan
        $description = '';
        if ($event === 'updated') {
            $changes = [
                'old' => $this->getOriginal(),
                'new' => $this->getChanges()
            ];
            $description = json_encode($changes);
        } else {
            $description = json_encode($this->getAttributes());
        }

        $description = substr($description, 0, 250); 

        // 5. Simpan Log
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