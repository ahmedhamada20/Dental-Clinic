<?php

namespace App\Modules\Notifications\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'body'        => ['required', 'string', 'max:4000'],
            'channels'    => ['required', 'array', 'min:1'],
            'channels.*'  => ['required', 'string', Rule::in(['database', 'in_app', 'email', 'sms', 'push'])],
            'scheduled_at'=> ['nullable', 'date', 'after:now'],
        ];
    }
}

