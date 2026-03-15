<?php

namespace App\Modules\Medical\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:users,id'],
            'allergies' => ['nullable', 'string'],
            'medical_history' => ['nullable', 'string'],
            'medications' => ['nullable', 'string'],
        ];
    }
}

