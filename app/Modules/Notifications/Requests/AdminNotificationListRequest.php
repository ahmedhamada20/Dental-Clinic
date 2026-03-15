<?php

namespace App\Modules\Notifications\Requests;

use App\Enums\NotificationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminNotificationListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'unread_only'       => ['nullable', 'boolean'],
            'per_page'          => ['nullable', 'integer', 'min:1', 'max:100'],
            'patient_id'        => ['nullable', 'integer', 'exists:patients,id'],
            'channel'           => ['nullable', 'string', Rule::in(['database', 'in_app', 'email', 'sms', 'push'])],
            'notification_type' => ['nullable', 'string', Rule::in(array_column(NotificationType::cases(), 'value'))],
            'status'            => ['nullable', 'string', Rule::in(['pending', 'sent', 'failed', 'read', 'delivered'])],
            'date_from'         => ['nullable', 'date'],
            'date_to'           => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }
}
