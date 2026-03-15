<?php

namespace App\Modules\Audit\Support;

use App\Models\System\AuditLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    public function __construct(private readonly Request $request)
    {
    }

    public function log(
        string $module,
        string $action,
        Model|string|null $entity = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Authenticatable $actor = null
    ): AuditLog {
        $actor ??= auth()->user();

        $entityType = is_string($entity) ? $entity : $entity?->getMorphClass();
        $entityId = $entity instanceof Model ? $entity->getKey() : null;

        return AuditLog::query()->create([
            'actor_type' => $actor ? class_basename($actor) : 'system',
//            'actor_id' => $actor?->getAuthIdentifier(),
            'action' => $action,
            'module' => $module,
            'entity_type' => $entityType ?? 'system',
            'entity_id' => $entityId,
            'old_values' => AuditValueSanitizer::sanitize($oldValues),
            'new_values' => AuditValueSanitizer::sanitize($newValues),
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
        ]);
    }
}

