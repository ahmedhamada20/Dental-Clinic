<?php

namespace Database\Factories\Clinic;

use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceCategory>
 */
class ServiceCategoryFactory extends Factory
{
    protected $model = ServiceCategory::class;

    public function definition(): array
    {
        return [
            'medical_specialty_id' => MedicalSpecialty::factory(),
            'name_ar' => 'تصنيف ' . fake()->word(),
            'name_en' => fake()->unique()->words(2, true),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}

