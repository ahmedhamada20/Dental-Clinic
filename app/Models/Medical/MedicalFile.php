<?php

namespace App\Models\Medical;

use App\Enums\FileCategory;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Models\Visit\Visit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class MedicalFile
 *
 * Medical documents and files uploaded for a patient.
 *
 * @property int $patient_id
 * @property int|null $visit_id
 * @property int $uploaded_by
 * @property FileCategory $file_category
 * @property string $title
 * @property string|null $notes
 * @property string $file_path
 * @property string $file_name
 * @property string|null $file_extension
 * @property string|null $mime_type
 * @property int|null $file_size
 * @property bool $is_visible_to_patient
 * @property \Carbon\Carbon $uploaded_at
 */
class MedicalFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'visit_id',
        'uploaded_by',
        'file_category',
        'title',
        'notes',
        'file_path',
        'file_name',
        'file_extension',
        'mime_type',
        'file_size',
        'is_visible_to_patient',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'is_visible_to_patient' => 'boolean',
            'uploaded_at' => 'datetime',
            'file_category' => FileCategory::class,
        ];
    }

    // ==================== Relationships ====================

    /**
     * The patient this file belongs to.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * The visit associated with this file.
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    /**
     * The user who uploaded this file.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

