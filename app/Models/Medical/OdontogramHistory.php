<?php

namespace App\Models\Medical;

use App\Models\Patient\Patient;
use App\Models\User;
use App\Models\Visit\Visit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class OdontogramHistory
 *
 * Historical changes to odontogram teeth records.
 *
 * @property int $patient_id
 * @property int $tooth_number
 * @property string|null $old_status
 * @property string $new_status
 * @property string|null $surface
 * @property string|null $notes
 * @property int|null $visit_id
 * @property int $changed_by
 * @property \Carbon\Carbon $created_at
 */
class OdontogramHistory extends Model
{
    use HasFactory;

    protected $table = 'odontogram_history';

    public $timestamps = false;

    protected $fillable = [
        'patient_id',
        'tooth_number',
        'old_status',
        'new_status',
        'surface',
        'notes',
        'visit_id',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    // ==================== Relationships ====================

    /**
     * The patient whose tooth was changed.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * The visit where this change occurred.
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    /**
     * The user who made this change.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
