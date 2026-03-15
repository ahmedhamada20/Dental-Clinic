<?php

namespace App\Models\System;

use App\Enums\NotificationType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class NotificationLog
 *
 * Tracks every notification dispatch attempt across all channels.
 *
 * @property int $id
 * @property int|null $system_notification_id
 * @property string $notifiable_type
 * @property int $notifiable_id
 * @property string $channel        (database|email|sms|push)
 * @property string $notification_type
 * @property string $title
 * @property string $body
 * @property string $status         (pending|sent|failed|delivered)
 * @property string|null $error_message
 * @property array|null $meta
 * @property \Carbon\Carbon|null $sent_at
 * @property int|null $triggered_by
 * @property string|null $triggered_by_type
 */
class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'system_notification_id',
        'notifiable_type',
        'notifiable_id',
        'channel',
        'notification_type',
        'title',
        'body',
        'status',
        'error_message',
        'meta',
        'sent_at',
        'triggered_by',
        'triggered_by_type',
    ];

    protected function casts(): array
    {
        return [
            'meta'    => 'array',
            'sent_at' => 'datetime',
        ];
    }

    // ==================== Scopes ====================

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('notification_type', $type);
    }

    // ==================== Relationships ====================

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function systemNotification(): BelongsTo
    {
        return $this->belongsTo(SystemNotification::class);
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}

