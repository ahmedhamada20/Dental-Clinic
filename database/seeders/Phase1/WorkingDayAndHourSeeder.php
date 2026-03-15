<?php

namespace Database\Seeders\Phase1;

use App\Models\Clinic\WorkingDay;
use App\Models\Clinic\WorkingHour;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * WorkingDayAndHourSeeder
 *
 * Seeds default working days (Monday-Sunday) and working hours for the clinic.
 * Creates standard business hours: 8 AM - 5 PM, Monday-Friday (closed weekends).
 * Idempotent: Uses firstOrCreate for safe re-runs.
 */
class WorkingDayAndHourSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $workingDays = $this->getWorkingDays();

        foreach ($workingDays as $dayData) {
            $workingDay = WorkingDay::firstOrCreate(
                ['day_of_week' => $dayData['day_of_week']],
                ['is_open' => $dayData['is_open']]
            );

            // Only create working hours if the clinic is open on this day
            if ($dayData['is_open'] && isset($dayData['hours'])) {
                foreach ($dayData['hours'] as $hour) {
                    WorkingHour::firstOrCreate(
                        [
                            'working_day_id' => $workingDay->id,
                            'start_time' => $hour['start_time'],
                            'end_time' => $hour['end_time'],
                        ],
                        [
                            'max_patients_per_day' => $hour['max_patients_per_day'] ?? null,
                            'slot_granularity_minutes' => $hour['slot_granularity_minutes'] ?? 30,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Get working days configuration (0 = Sunday, 1 = Monday, ..., 6 = Saturday).
     * Standard 8 AM - 5 PM with 1-hour lunch break (12 PM - 1 PM).
     *
     * @return array
     */
    private function getWorkingDays(): array
    {
        return [
            [
                'day_of_week' => 0, // Sunday
                'is_open' => false,
                'hours' => [],
            ],
            [
                'day_of_week' => 1, // Monday
                'is_open' => true,
                'hours' => [
                    [
                        'start_time' => '08:00',
                        'end_time' => '12:00',
                        'max_patients_per_day' => 12,
                        'slot_granularity_minutes' => 30,
                    ],
                    [
                        'start_time' => '13:00',
                        'end_time' => '17:00',
                        'max_patients_per_day' => 10,
                        'slot_granularity_minutes' => 30,
                    ],
                ],
            ],
            [
                'day_of_week' => 2, // Tuesday
                'is_open' => true,
                'hours' => [
                    [
                        'start_time' => '08:00',
                        'end_time' => '12:00',
                        'max_patients_per_day' => 12,
                        'slot_granularity_minutes' => 30,
                    ],
                    [
                        'start_time' => '13:00',
                        'end_time' => '17:00',
                        'max_patients_per_day' => 10,
                        'slot_granularity_minutes' => 30,
                    ],
                ],
            ],
            [
                'day_of_week' => 3, // Wednesday
                'is_open' => true,
                'hours' => [
                    [
                        'start_time' => '08:00',
                        'end_time' => '12:00',
                        'max_patients_per_day' => 12,
                        'slot_granularity_minutes' => 30,
                    ],
                    [
                        'start_time' => '13:00',
                        'end_time' => '17:00',
                        'max_patients_per_day' => 10,
                        'slot_granularity_minutes' => 30,
                    ],
                ],
            ],
            [
                'day_of_week' => 4, // Thursday
                'is_open' => true,
                'hours' => [
                    [
                        'start_time' => '08:00',
                        'end_time' => '12:00',
                        'max_patients_per_day' => 12,
                        'slot_granularity_minutes' => 30,
                    ],
                    [
                        'start_time' => '13:00',
                        'end_time' => '17:00',
                        'max_patients_per_day' => 10,
                        'slot_granularity_minutes' => 30,
                    ],
                ],
            ],
            [
                'day_of_week' => 5, // Friday
                'is_open' => true,
                'hours' => [
                    [
                        'start_time' => '08:00',
                        'end_time' => '12:00',
                        'max_patients_per_day' => 12,
                        'slot_granularity_minutes' => 30,
                    ],
                    [
                        'start_time' => '13:00',
                        'end_time' => '16:00',
                        'max_patients_per_day' => 8,
                        'slot_granularity_minutes' => 30,
                    ],
                ],
            ],
            [
                'day_of_week' => 6, // Saturday
                'is_open' => false,
                'hours' => [],
            ],
        ];
    }
}

