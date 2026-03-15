<?php

namespace App\Modules\Appointments\Rules;

use App\Enums\UserType;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DoctorMatchesSpecialty implements ValidationRule
{
    public function __construct(private readonly ?int $specialtyId) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if ($this->specialtyId === null) {
            $fail(__('admin.validation.select_specialty_before_doctor'));

            return;
        }

        $doctor = User::query()->find($value);



        if ((int) $doctor->specialty_id !== $this->specialtyId) {
            $fail(__('admin.validation.selected_doctor_mismatch_specialty'));
        }
    }
}
