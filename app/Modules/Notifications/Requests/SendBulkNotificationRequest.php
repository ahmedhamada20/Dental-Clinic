<?php

namespace App\Modules\Notifications\Requests;

use App\Enums\NotificationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendBulkNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'body'         => ['required', 'string', 'max:4000'],
            'type'         => ['required', 'string', Rule::in(array_column(NotificationType::cases(), 'value'))],
            'channels'     => ['required', 'array', 'min:1'],
            'channels.*'   => ['required', 'string', Rule::in(['database', 'in_app', 'email', 'sms', 'push'])],
            'audience'     => ['required', 'string', Rule::in(['all_patients', 'active_patients', 'patient_ids'])],
            'patient_ids'  => ['nullable', 'array'],
            'patient_ids.*'=> ['integer', 'exists:patients,id'],
        ];
    }
}

