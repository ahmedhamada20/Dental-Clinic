<?php

namespace Database\Factories\System;


use App\Models\Patient\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeviceToken>
 */
class DeviceTokenFactory extends Factory
{
    public function definition(): array
    {
        $deviceType = fake()->randomElement(['android', 'ios']);

        return [
            'patient_id' => Patient::factory(),
            'device_type' => $deviceType,
            'firebase_token' => Str::random(180),
            'device_name' => $deviceType === 'android'
                ? fake()->randomElement(['Samsung A54', 'Xiaomi Redmi Note', 'OPPO Reno', 'Huawei Nova'])
                : fake()->randomElement(['iPhone 12', 'iPhone 13', 'iPhone 14 Pro', 'iPhone 15']),
            'app_version' => fake()->randomElement(['1.0.0', '1.1.2', '1.2.0', '2.0.1']),
            'is_active' => fake()->boolean(85),
            'last_used_at' => fake()->dateTimeBetween('-45 days', 'now'),
        ];
    }
}
