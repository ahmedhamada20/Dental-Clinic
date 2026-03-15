<?php

namespace App\Models\System;

use App\Models\Patient\Patient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class DeviceToken
 *
 * Firebase device tokens for push notifications.
 *
 * @property int $patient_id
 * @property string $device_type
 * @property string $firebase_token
 * @property string|null $device_name
 * @property string|null $app_version
 * @property bool $is_active
 * @property \Carbon\Carbon|null $last_used_at
 */
class DeviceToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'device_type',
        'firebase_token',
        'device_name',
        'app_version',
        'is_active',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    // ==================== Relationships ====================

    /**
     * The patient this device token belongs to.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}

