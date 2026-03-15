<?php

namespace Database\Factories;

use App\Models\Appointment\Appointment;
use App\Models\Appointment\AppointmentStatusLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppointmentStatusLog>
 */
class AppointmentStatusLogFactory extends Factory
{
    protected $model = AppointmentStatusLog::class;

    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::query()->inRandomOrder()->value('id'),
            'old_status' => null,
            'new_status' => 'pending',
            'changed_by_type' => 'system',
            'changed_by_id' => null,
            'notes' => fake()->optional(0.35)->sentence(),
            'created_at' => now(),
        ];
    }
}

