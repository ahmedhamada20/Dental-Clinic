<?php

namespace App\Modules\Appointments\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class ClaimWaitingSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    public function rules(): array
    {
        return [
            'waiting_list_request_id' => ['required', 'integer', 'exists:waiting_list_requests,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'waiting_list_request_id.exists' => 'Waiting list request does not exist',
        ];
    }
}

