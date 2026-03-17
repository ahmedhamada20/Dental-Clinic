<?php

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'max:30',
                Rule::unique('patients', 'phone'),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('patients', 'email')->whereNotNull('email'),
            ],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'firebase_token' => ['nullable', 'string', 'max:1000'],
            'device_type' => ['required_with:firebase_token', 'string', Rule::in(['android', 'ios'])],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'phone.required' => 'Phone number is required.',
            'phone.unique' => 'This phone number is already registered.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'gender.in' => 'Gender must be male or female.',
        ];
    }
}

