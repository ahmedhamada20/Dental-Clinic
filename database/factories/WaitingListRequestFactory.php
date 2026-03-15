<?php

namespace Database\Factories;

use App\Models\Appointment\WaitingListRequest;
use App\Models\Clinic\Service;
use App\Models\Patient\Patient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WaitingListRequest>
 */
class WaitingListRequestFactory extends Factory
{
    protected $model = WaitingListRequest::class;

    public function definition(): array
    {
        $preferredDate = Carbon::instance(fake()->dateTimeBetween('+1 days', '+30 days'));
        while ($preferredDate->dayOfWeekIso === 5) {
            $preferredDate->addDay();
        }

        $fromHour = fake()->numberBetween(10, 16);
        $fromMinute = fake()->randomElement([0, 30]);
        $from = $preferredDate->copy()->setTime($fromHour, $fromMinute, 0);
        $to = $from->copy()->addMinutes(fake()->randomElement([30, 45, 60, 90]));

        if ($to->hour > 20 || ($to->hour === 20 && $to->minute > 0)) {
            $to = $preferredDate->copy()->setTime(20, 0, 0);
        }

        return [
            'patient_id' => Patient::query()->inRandomOrder()->value('id'),
            'service_id' => Service::query()->where('is_active', true)->where('is_bookable', true)->inRandomOrder()->value('id')
                ?? Service::query()->where('is_active', true)->inRandomOrder()->value('id')
                ?? Service::query()->inRandomOrder()->value('id'),
            'preferred_date' => $preferredDate->toDateString(),
            'preferred_from_time' => $from->format('H:i:s'),
            'preferred_to_time' => $to->format('H:i:s'),
            'status' => 'waiting',
            'notified_at' => null,
            'expires_at' => null,
            'booked_appointment_id' => null,
            'created_at' => now()->subDays(fake()->numberBetween(1, 20)),
            'updated_at' => now(),
        ];
    }
}

