<?php

namespace App\Models\System;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AuditLog
 *
 * Tracks all important actions in the system for audit purposes.
 *
 * @property string $actor_type
 * @property int $actor_id
 * @property string $action
 * @property string $module
 * @property string $entity_type
 * @property int $entity_id
 * @property array|null $old_values
 * @property array|null $new_values
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\Carbon $created_at
 */
class AuditLog extends Model
{
    use HasFactory;

    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';
    public const ACTION_STATUS_CHANGE = 'status_change';

    public $timestamps = false;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'action',
        'module',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['from_date'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['to_date'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date))
            ->when($filters['module'] ?? null, fn (Builder $q, $module) => $q->where('module', $module))
            ->when($filters['action'] ?? null, fn (Builder $q, $action) => $q->where('action', $action))
            ->when($filters['actor_id'] ?? null, fn (Builder $q, $actorId) => $q->where('actor_id', $actorId))
            ->when($filters['entity_type'] ?? null, fn (Builder $q, $entityType) => $q->where('entity_type', $entityType))
            ->when($filters['entity_id'] ?? null, fn (Builder $q, $entityId) => $q->where('entity_id', $entityId))
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $nested) use ($search) {
                    $nested->where('module', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('entity_type', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%");
                });
            });
    }

    public function getActorNameAttribute(): string
    {
        return $this->actor?->display_name
            ?? $this->new_values['actor_name']
            ?? $this->old_values['actor_name']
            ?? 'System';
    }

    public function getEntityLabelAttribute(): string
    {
        return class_basename($this->entity_type).($this->entity_id ? " #{$this->entity_id}" : '');
    }
}
