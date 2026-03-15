<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AttachDoctorToSpecialtyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function (Builder $query) {
                    $query->where('user_type', UserType::DOCTOR->value);
                }),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $specialty = $this->route('specialty');
            $doctorId = (int) $this->input('doctor_id');

            if (! $specialty || $doctorId <= 0) {
                return;
            }

            $alreadyAssigned = User::query()
                ->whereKey($doctorId)
                ->where('specialty_id', $specialty->id)
                ->exists();

            if ($alreadyAssigned) {
                $validator->errors()->add('doctor_id', __('specialties.messages.doctor_already_in_specialty'));
            }
        });
    }
}

