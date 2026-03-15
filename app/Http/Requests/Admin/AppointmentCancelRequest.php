<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentCancelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'waiting_list_request_id' => ['nullable', 'integer', 'exists:waiting_list_requests,id'],
            'convert_waiting_list' => ['nullable', 'boolean'],
            'reschedule_date' => ['nullable', 'date'],
            'reschedule_time' => ['nullable', 'date_format:H:i'],
        ];
    }
}
