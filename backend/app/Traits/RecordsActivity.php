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

        // 4. Deskripsi Perubahan (Bahasa Manusia)
        $description = '';
        
        if ($event === 'created') {
            $description = "Menambah data baru (ID: " . $this->getKey() . ")";
        } elseif ($event === 'deleted') {
            $description = "Menghapus data (ID: " . $this->getKey() . ")";
        } elseif ($event === 'updated') {
            $changes = $this->getChanges();
            $original = $this->getOriginal();
            $teksPerubahan = [];

            foreach ($changes as $kolom => $nilaiBaru) {
                // Abaikan jika yang berubah cuma timestamp
                if ($kolom === 'updated_at' || $kolom === 'created_at') continue;

                $nilaiLama = $original[$kolom] ?? 'kosong';
                
                // Pastikan nilainya string supaya tidak error saat digabung
                $nilaiLamaStr = is_scalar($nilaiLama) ? $nilaiLama : json_encode($nilaiLama);
                $nilaiBaruStr = is_scalar($nilaiBaru) ? $nilaiBaru : json_encode($nilaiBaru);

                $teksPerubahan[] = "kolom {$kolom} dari '{$nilaiLamaStr}' menjadi '{$nilaiBaruStr}'";
            }

            if (count($teksPerubahan) > 0) {
                $description = "Mengubah " . implode(', ', $teksPerubahan);
            } else {
                $description = "Memperbarui data";
            }
        }

        // Potong string agar tidak error batas varchar(255) di database
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