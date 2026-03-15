<?php

namespace App\Models\System;

use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SystemNotification
 *
 * Internal notifications and push notifications for patients and staff.
 * Uses polymorphic relations to notify any entity type.
 *
 * @property string $notifiable_type
 * @property int $notifiable_id
 * @property string $channel
 * @property string $title
 * @property string $body
 * @property NotificationType $type
 * @property array|null $data
 * @property \Carbon\Carbon|null $sent_at
 * @property \Carbon\Carbon|null $read_at
 * @property string|null $status
 */
class SystemNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
        'channel',
        'title',
        'body',
        'type',
        'data',
        'sent_at',
        'read_at',
        'status',
        'tiltle',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'sent_at' => 'datetime',
            'read_at' => 'datetime',
            'type' => NotificationType::class,
        ];
    }

    // ==================== Scopes ====================

    /**
     * Scope to get unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope to filter by notification type value.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by channel.
     */
    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    // ==================== Relationships ====================

    /**
     * The model that receives this notification (polymorphic).
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    /**
     * Delivery log entries for this notification.
     */
    public function logs()
    {
        return $this->hasMany(NotificationLog::class);
    }
}

