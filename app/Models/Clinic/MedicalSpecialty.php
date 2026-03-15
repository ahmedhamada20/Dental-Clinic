<?php

namespace App\Models\Clinic;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalSpecialty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Doctor users assigned to this specialty.
     */
    public function doctors(): HasMany
    {
        return $this->hasMany(User::class, 'specialty_id')
            ->where('user_type', UserType::DOCTOR->value);

    }

    /**
     * Service categories assigned to this specialty.
     */
    public function serviceCategories(): HasMany
    {
        return $this->hasMany(ServiceCategory::class);
    }
}
