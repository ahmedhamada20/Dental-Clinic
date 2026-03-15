<?php

namespace App\Modules\Appointments\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class JoinWaitingListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    public function rules(): array
    {
        return [
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'preferred_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'preferred_from_time' => ['nullable', 'date_format:H:i'],
            'preferred_to_time' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.exists' => 'Selected service does not exist',
        ];
    }
}

