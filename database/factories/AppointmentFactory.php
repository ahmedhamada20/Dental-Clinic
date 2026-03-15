<?php

namespace Database\Factories;

use App\Models\Appointment\Appointment;
use App\Models\Clinic\Service;
use App\Models\Patient\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $service = Service::query()
            ->with('category:id,medical_specialty_id')
            ->where('is_active', true)
            ->where('is_bookable', true)
            ->inRandomOrder()
            ->first()
            ?? Service::query()->with('category:id,medical_specialty_id')->inRandomOrder()->first();

        $specialtyId = $service?->category?->medical_specialty_id;
        $doctorId = User::query()
            ->where('user_type', 'doctor')
            ->when($specialtyId, fn ($query) => $query->where('specialty_id', $specialtyId))
            ->inRandomOrder()
            ->value('id');

        $duration = max((int) ($service?->duration_minutes ?? 30), 15);
        $baseDate = Carbon::instance(fake()->dateTimeBetween('-21 days', '+45 days'))->startOfDay();

        // Keep schedule inside clinic-friendly windows.
        while (in_array($baseDate->dayOfWeekIso, [5])) {
            $baseDate->addDay();
        }

        $startHour = fake()->numberBetween(9, 17);
        $startMinute = fake()->randomElement([0, 15, 30, 45]);
        $startAt = $baseDate->copy()->setTime($startHour, $startMinute, 0);

        if ($startAt->hour >= 13 && $startAt->hour < 14) {
            $startAt->setTime(14, 0, 0);
        }

        $endAt = $startAt->copy()->addMinutes($duration);
        if ($endAt->hour > 20 || ($endAt->hour === 20 && $endAt->minute > 0)) {
            $endAt = $baseDate->copy()->setTime(20, 0, 0);
            $startAt = $endAt->copy()->subMinutes($duration);
        }

        return [
            'appointment_no' => 'APT-' . strtoupper(fake()->bothify('######??')),
            'patient_id' => Patient::query()->inRandomOrder()->value('id'),
            'specialty_id' => $specialtyId,
            'service_id' => $service?->id,
            'assigned_doctor_id' => $doctorId,
            'appointment_date' => $startAt->toDateString(),
            'start_time' => $startAt->format('H:i:s'),
            'end_time' => $endAt->format('H:i:s'),
            'status' => 'pending',
            'booking_source' => fake()->randomElement(['mobile_app', 'web_app']),
            'cancellation_reason' => null,
            'cancelled_at' => null,
            'cancelled_by_type' => null,
            'cancelled_by_id' => null,
            'confirmed_at' => null,
            'checked_in_at' => null,
            'notes' => fake()->optional(0.4)->sentence(),
            'created_at' => $startAt->copy()->subDays(fake()->numberBetween(1, 20)),
            'updated_at' => now(),
        ];
    }
}
