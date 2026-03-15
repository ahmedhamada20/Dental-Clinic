<?php

namespace Database\Factories\Clinic;

use App\Models\Clinic\Service;
use App\Models\Clinic\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'category_id' => ServiceCategory::factory(),
            'code' => 'SRV-' . strtoupper(fake()->bothify('###??')),
            'name_ar' => 'خدمة ' . fake()->word(),
            'name_en' => fake()->words(2, true),
            'description_ar' => fake()->optional()->sentence(),
            'description_en' => fake()->optional()->sentence(),
            'default_price' => fake()->randomFloat(2, 50, 1500),
            'duration_minutes' => fake()->randomElement([15, 30, 45, 60]),
            'is_bookable' => true,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}

