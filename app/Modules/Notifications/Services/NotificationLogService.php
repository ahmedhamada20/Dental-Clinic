<?php

namespace App\Modules\Notifications\Services;

use App\Models\System\AuditLog;
use Illuminate\Http\Request;

class NotificationLogService
{
    public function log(
        Request $request,
        string $action,
        string $entityType,
        int $entityId,
        array $newValues = [],
        array $oldValues = []
    ): void {
        AuditLog::query()->create([
            'actor_type' => get_class($request->user()),
            'actor_id' => (int) $request->user()->getAuthIdentifier(),
            'action' => $action,
            'module' => 'notifications',
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => (string) $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            'created_at' => now(),
        ]);
    }
}
