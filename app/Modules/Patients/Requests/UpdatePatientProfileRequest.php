<?php

namespace App\Modules\Patients\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'unique:patients,email,' . auth('sanctum')->user()?->id],
            'phone' => ['sometimes', 'required', 'string', 'max:20'],
            'alternate_phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['sometimes', 'required', 'in:male,female,other'],
            'date_of_birth' => ['sometimes', 'required', 'date', 'before:today'],
            'address' => ['sometimes', 'required', 'string'],
            'city' => ['sometimes', 'required', 'string'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'marital_status' => ['nullable', 'string', 'max:255'],
            'blood_group' => ['nullable', 'string', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already in use.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
        ];
    }
}

