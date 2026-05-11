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
        // --- 1. BLOKIR DOUBLE LOG ---
        if ($event === 'updated' && $this->wasRecentlyCreated) {
            return; 
        }

        // --- 2. AMBIL DATA USER DARI TOKEN ---
        $token = request()->bearerToken();
        $accessLogId = null;
        $username    = 'Sistem / Tidak Ditemukan';
        $role        = 'Unknown';

        if ($token) {
            try {
                $decryptedToken = json_decode(Crypt::decryptString($token));
                $accessLogId = $decryptedToken->id_access_log ?? null;
                $username    = $decryptedToken->nip ?? 'Unknown';
                $role        = $decryptedToken->role ?? 'Unknown';
            } catch (\Exception $e) {
                Log::error('Gagal decrypt token di RecordsActivity');
            }
        }

        // --- 3. AKTIVITAS TETAP SIMPLE & SINGKAT (Demi UI Badge Frontend) ---
        // Contoh Output: "CREATED MstCoa", "UPDATED TrPenerimaan"
        $activityName = strtoupper($event) . ' ' . class_basename($this);

        // --- 4. DESKRIPSI TETAP DIMANUSIAKAN (Tanpa JSON mentah) ---
        $relatedData = 'ID Record: ' . $this->getKey();
        $description = '';

        if ($event === 'created') {
            $description = "Berhasil menginput data baru ke dalam sistem.";
        } elseif ($event === 'deleted') {
            $description = "Data telah dihapus dari sistem.";
        } elseif ($event === 'updated') {
            $listPerubahan = [];
            foreach ($this->getChanges() as $kolom => $nilaiBaru) {
                $nilaiLama = $this->getOriginal($kolom);
                $nilaiLama = $nilaiLama ?? '(kosong)';
                $nilaiBaru = $nilaiBaru ?? '(kosong)';
                
                $listPerubahan[] = "$kolom dari '$nilaiLama' menjadi '$nilaiBaru'";
            }
            $description = "Mengubah nilai " . implode(', ', $listPerubahan);
        }

        // Batasi panjang teks agar aman di database
        $description = Str::limit($description, 245);

        // --- 5. SIMPAN LOG ---
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