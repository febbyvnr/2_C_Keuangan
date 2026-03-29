<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Contracts\Auth\Authenticatable;

class AuditTrailService
{
    /**
     * NOTE:
     * Table activity_log hanya punya EVENT_TIME bertipe date, bukan datetime.
     * Jadi kita simpan tanggal hari ini.
     *
     * ID_ACCESS_LOG sementara nullable karena flow login final belum jelas.
     * Nanti kalau access_log sudah dipakai beneran, tinggal isi argumen $accessLogId.
     */
    public function log(
        ?Authenticatable $actor,
        string $activityName,
        ?string $relatedData = null,
        ?string $description = null,
        ?int $accessLogId = null,
        ?string $actorRole = null
    ): ActivityLog {
        $nextId = ((int) ActivityLog::max('ID_ACTIVITY_LOG')) + 1;

        $username = $this->resolveActorUsername($actor);

        return ActivityLog::query()->create([
            'ID_ACTIVITY_LOG' => $nextId,
            'ID_ACCESS_LOG' => $accessLogId,
            'EVENT_TIME' => now()->toDateString(),
            'ACTOR_USERNAME' => $username,
            'ACTOR_ROLE' => $actorRole,
            'ACTIVITY_NAME' => $activityName,
            'RELATED_DATA' => $relatedData,
            'ACTIVITY_DESCRIPTION' => $description,
        ]);
    }

    private function resolveActorUsername(?Authenticatable $actor): ?string
    {
        if (!$actor) {
            return null;
        }

        foreach (['username', 'USERNAME', 'email', 'NIP_KARYAWAN', 'nip_karyawan', 'nip'] as $field) {
            if (isset($actor->{$field}) && filled($actor->{$field})) {
                return (string) $actor->{$field};
            }
        }

        return method_exists($actor, 'getAuthIdentifier')
            ? (string) $actor->getAuthIdentifier()
            : null;
    }
}