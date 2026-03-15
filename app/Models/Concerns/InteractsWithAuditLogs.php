<?php

namespace App\Models\Concerns;

use App\Models\System\AuditLog;
use Illuminate\Support\Facades\Request;

/**
 * Trait InteractsWithAuditLogs
 *
 * For models that need to log actions to the audit log.
 * Can be applied to any model that requires activity tracking.
 */
trait InteractsWithAuditLogs
{
    /**
     * Log an action to the audit log.
     *
     * @param string $action
     * @param string $module
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return AuditLog
     */
    public function logAction(
        string $action,
        string $module,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        $actor = auth()->user();

        return AuditLog::create([
            'actor_type' => $actor ? get_class($actor) : 'System',
            'actor_id' => $actor?->id,
            'action' => $action,
            'module' => $module,
            'entity_type' => get_class($this),
            'entity_id' => $this->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Get audit logs for this model.
     */
    public function auditLogs()
    {
        return AuditLog::where('entity_type', get_class($this))
            ->where('entity_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Boot the trait and set up model event listeners.
     */
    protected static function bootInteractsWithAuditLogs(): void
    {
        static::created(function ($model) {
            if ($model->shouldLogAudit()) {
                $model->logAction('created', $model->getAuditModule(), null, $model->getAttributes());
            }
        });

        static::updated(function ($model) {
            if ($model->shouldLogAudit() && $model->wasChanged()) {
                $model->logAction('updated', $model->getAuditModule(), $model->getOriginal(), $model->getChanges());
            }
        });

        static::deleted(function ($model) {
            if ($model->shouldLogAudit()) {
                $model->logAction('deleted', $model->getAuditModule(), $model->getAttributes(), null);
            }
        });
    }

    /**
     * Determine if this model should log audit trails.
     * Can be overridden in the model.
     */
    protected function shouldLogAudit(): bool
    {
        return true;
    }

    /**
     * Get the module name for audit logging.
     * Should be overridden in the model.
     */
    protected function getAuditModule(): string
    {
        return class_basename($this);
    }
}

