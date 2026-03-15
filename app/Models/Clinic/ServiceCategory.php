<?php

namespace App\Models\Clinic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class ServiceCategory
 *
 * Service categories for clinic services.
 *
 * @property int $medical_specialty_id
 * @property string $name_ar
 * @property string|null $name_en
 * @property bool $is_active
 * @property int|null $sort_order
 */
class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_specialty_id',
        'name_ar',
        'name_en',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'medical_specialty_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    // ==================== Relationships ====================

    /**
     * The specialty this category belongs to.
     */
    public function medicalSpecialty(): BelongsTo
    {
        return $this->belongsTo(MedicalSpecialty::class);
    }

    /**
     * Services in this category.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'category_id');
    }
}
