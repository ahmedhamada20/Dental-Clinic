<?php

namespace App\Modules\Settings\Services;

use App\Modules\Settings\DTOs\ClinicSettingDTO;
use App\Modules\Settings\DTOs\HolidayDTO;
use App\Modules\Settings\DTOs\WorkingDaysDTO;
use App\Modules\Settings\DTOs\WorkingHourDTO;
use Illuminate\Support\Facades\Cache;

class ClinicScheduleService
{
    private const CLINIC_SETTINGS_KEY = 'settings.clinic';
    private const WORKING_DAYS_KEY = 'settings.working_days';
    private const WORKING_HOURS_KEY = 'settings.working_hours';
    private const HOLIDAYS_KEY = 'settings.holidays';

    public function getClinicSettings(): array
    {
        return Cache::get(self::CLINIC_SETTINGS_KEY, [
            'clinic_name' => 'Dental Clinic',
            'phone' => null,
            'email' => null,
            'address' => null,
            'timezone' => config('app.timezone'),
        ]);
    }

    public function updateClinicSettings(ClinicSettingDTO $dto): array
    {
        $data = [
            'clinic_name' => $dto->clinicName,
            'phone' => $dto->phone,
            'email' => $dto->email,
            'address' => $dto->address,
            'timezone' => $dto->timezone,
        ];

        Cache::forever(self::CLINIC_SETTINGS_KEY, $data);
        return $data;
    }

    public function getWorkingDays(): array
    {
        return Cache::get(self::WORKING_DAYS_KEY, [
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        ]);
    }

    public function updateWorkingDays(WorkingDaysDTO $dto): array
    {
        $data = ['days' => array_values(array_unique($dto->days))];
        Cache::forever(self::WORKING_DAYS_KEY, $data);
        return $data;
    }

    public function getWorkingHours(): array
    {
        return array_values(Cache::get(self::WORKING_HOURS_KEY, []));
    }

    public function storeWorkingHour(WorkingHourDTO $dto): array
    {
        $hours = Cache::get(self::WORKING_HOURS_KEY, []);
        $id = $this->nextId($hours);

        $hours[$id] = [
            'id' => $id,
            'day' => $dto->day,
            'start_time' => $dto->startTime,
            'end_time' => $dto->endTime,
            'is_active' => $dto->isActive,
        ];

        Cache::forever(self::WORKING_HOURS_KEY, $hours);
        return $hours[$id];
    }

    public function updateWorkingHour(int $id, WorkingHourDTO $dto): array
    {
        $hours = Cache::get(self::WORKING_HOURS_KEY, []);
        abort_unless(isset($hours[$id]), 404, 'Working hour not found.');

        $hours[$id] = [
            'id' => $id,
            'day' => $dto->day,
            'start_time' => $dto->startTime,
            'end_time' => $dto->endTime,
            'is_active' => $dto->isActive,
        ];

        Cache::forever(self::WORKING_HOURS_KEY, $hours);
        return $hours[$id];
    }

    public function deleteWorkingHour(int $id): void
    {
        $hours = Cache::get(self::WORKING_HOURS_KEY, []);
        abort_unless(isset($hours[$id]), 404, 'Working hour not found.');

        unset($hours[$id]);
        Cache::forever(self::WORKING_HOURS_KEY, $hours);
    }

    public function getHolidays(): array
    {
        return array_values(Cache::get(self::HOLIDAYS_KEY, []));
    }

    public function storeHoliday(HolidayDTO $dto): array
    {
        $holidays = Cache::get(self::HOLIDAYS_KEY, []);
        $id = $this->nextId($holidays);

        $holidays[$id] = [
            'id' => $id,
            'name' => $dto->name,
            'date' => $dto->date,
            'description' => $dto->description,
        ];

        Cache::forever(self::HOLIDAYS_KEY, $holidays);
        return $holidays[$id];
    }

    public function updateHoliday(int $id, HolidayDTO $dto): array
    {
        $holidays = Cache::get(self::HOLIDAYS_KEY, []);
        abort_unless(isset($holidays[$id]), 404, 'Holiday not found.');

        $holidays[$id] = [
            'id' => $id,
            'name' => $dto->name,
            'date' => $dto->date,
            'description' => $dto->description,
        ];

        Cache::forever(self::HOLIDAYS_KEY, $holidays);
        return $holidays[$id];
    }

    public function deleteHoliday(int $id): void
    {
        $holidays = Cache::get(self::HOLIDAYS_KEY, []);
        abort_unless(isset($holidays[$id]), 404, 'Holiday not found.');

        unset($holidays[$id]);
        Cache::forever(self::HOLIDAYS_KEY, $holidays);
    }

    private function nextId(array $items): int
    {
        if ($items === []) {
            return 1;
        }

        return ((int) max(array_keys($items))) + 1;
    }
}
