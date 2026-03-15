<?php

namespace App\Modules\Patients\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $patientId = $this->route('patient') ?? $this->route('id');

        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'unique:patients,email,' . $patientId],
            'phone' => ['sometimes', 'required', 'string', 'max:20', 'unique:patients,phone,' . $patientId],
            'alternate_phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['sometimes', 'required', 'in:male,female,other'],
            'date_of_birth' => ['sometimes', 'required', 'date', 'before:today'],
            'address' => ['sometimes', 'required', 'string'],
            'city' => ['sometimes', 'required', 'string', 'max:255'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'status' => ['sometimes', 'required', 'in:active,inactive,blocked'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
        ];
    }
}

