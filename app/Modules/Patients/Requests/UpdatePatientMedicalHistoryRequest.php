<?php

namespace App\Modules\Patients\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientMedicalHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'allergies' => ['nullable', 'string'],
            'chronic_diseases' => ['nullable', 'string'],
            'current_medications' => ['nullable', 'string'],
            'medical_notes' => ['nullable', 'string'],
            'dental_history' => ['nullable', 'string'],
            'important_alerts' => ['nullable', 'string'],
        ];
    }
}

