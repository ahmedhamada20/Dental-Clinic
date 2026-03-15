<?php

namespace Database\Factories\Clinic;

use App\Models\Clinic\MedicalSpecialty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalSpecialty>
 */
class MedicalSpecialtyFactory extends Factory
{
    protected $model = MedicalSpecialty::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
            'icon' => null,
        ];
    }
}

