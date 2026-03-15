<?php

namespace App\Modules\Appointments\Rules;

use App\Models\Clinic\Service;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ServiceMatchesSpecialty implements ValidationRule
{
    public function __construct(private readonly ?int $specialtyId) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->specialtyId === null) {
            $fail(__('admin.validation.select_specialty_before_service'));

            return;
        }

        $service = Service::query()->with('category:id,medical_specialty_id')->find($value);

        if (!$service) {
            $fail(__('admin.validation.selected_service_invalid'));

            return;
        }

        if ((int) $service->category?->medical_specialty_id !== $this->specialtyId) {
            $fail(__('admin.validation.selected_service_mismatch_specialty'));
        }
    }
}

